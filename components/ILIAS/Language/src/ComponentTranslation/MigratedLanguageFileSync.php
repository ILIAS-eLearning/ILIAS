<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\ComponentTranslation;

use DateTimeImmutable;
use DateTimeZone;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use RuntimeException;

/**
 * Reads the shipped `.po` of a migrated module and maintains its per-installation overlay: the `.po` with
 * bookkeeping and the compiled revisions native gettext serves.
 *
 * A module counts as "migrated" for a language when its component contributes a LanguageFileDirectory with
 * the module as prefix AND ships `<module>_<lang>.po` there. For such a module:
 * - the shipped `.po` is the only source of the shipped values (the legacy `.lang` files are not consulted,
 *   see LanguageInstallationManager);
 * - the overlay (see MigratedLanguageFilePaths, `<client data dir>/lang/<module>/<lang>/`) holds the delta to
 *   the shipped state (see sync()), which MigratedTranslations serves on top at runtime; it only exists where
 *   there are local changes. Its `.po` carries per entry the value in use plus the LocalChangeComments
 *   bookkeeping ("original" = shipped value it is tracked against, "local_change" = when changed locally).
 *   Every change of the `.po` is compiled into a new revision `r-<hash>/` (catalog and `keys.json`, see
 *   ShippedTranslations::compileCatalog()) and `current` is switched to it atomically: native gettext never
 *   reloads a catalog it has read once in a process, so a revision is never changed in place. The current and
 *   the previous revision are kept.
 *
 * lng_data/lng_modules are still written for migrated modules (rollback-safe fallback); this class only
 * handles the files. Every method throws instead of logging; callers decide how to report.
 *
 * Plural messages: every identifier => value map this class takes or returns (sync(), shipped values,
 * loadModuleTranslations(), loadLocalChanges()) holds a plural message as one entry per form, keyed
 * PluralFormKey::of() (`<identifier> [<form>]`), never under its identifier, so every comparison works per
 * form. The number of forms is that of the file's "Plural-Forms" header (PluralForms, Germanic if missing or
 * invalid). A plain value for the identifier of a shipped plural message (a local change from before the
 * module had plurals, or read back from the database) stands for its default form, see
 * mapLegacyPluralValues(). The database only gets the identifier with its default value, see
 * collapsePluralForms().
 */
final class MigratedLanguageFileSync
{
    /**
     * How often acquireLock() retries when the locked file was removed or replaced meanwhile.
     */
    private const int LOCK_ATTEMPTS = 5;

    /**
     * The lock files this request currently holds, see withOverlayLock().
     *
     * @var array<string, true>
     */
    private static array $held_locks = [];

    private static ?TranslationMarkupPolicy $markup_policy = null;

    /**
     * Parsed shipped `.po` files of this request, keyed by path, see readShippedPo().
     *
     * @var array<string, array{0: string, 1: TranslationCatalog}>
     */
    private static array $shipped_catalogs = [];

    private static string $shipped_catalogs_lang_key = '';

    /**
     * How many parsed shipped `.po` files of one language readShippedPo() keeps: more than its modules, so
     * installing a language parses each once, not once per step.
     */
    private const int SHIPPED_CATALOG_CACHE_SIZE = 512;

    /**
     * Brings the overlay of $module/$lang_key in line with $entries, the complete final content of the module.
     * The overlay only holds the delta to the shipped `.po` (see belongsInDelta()): entries whose value differs
     * from the shipped one (unprocessed, as in the file) or whose key it lacks. Everything else is served from
     * the shipped state (see ShippedTranslations) and not written. An entry no longer in the delta is removed;
     * an overlay with an empty delta is removed (`current` and `.po`; `lock` stays, see withOverlayLock(); the
     * revisions go as well, a running request that mapped one keeps reading it). With an empty delta and no
     * overlay nothing is written or locked, except removing revisions an earlier removal left behind. A full
     * overlay of an earlier version shrinks to the delta with the next write (Setup update, reinstall).
     *
     * A no-op if $module is not a contributed directory or $client_data_dir is `null`, and without a shipped
     * `.po` for $lang_key (module or language dropped, possibly temporarily): an existing overlay is then left
     * untouched and not read (see loadModuleTranslations(), getMigratedModules(), ilLanguage); once the `.po` is
     * back the next reconciling write compares against its preserved "original" values.
     *
     * The read-modify-write runs under the overlay lock (see withOverlayLock()); the existing overlay is read
     * only after acquiring it.
     *
     * Per delta entry:
     * - "original" is taken from the shipped `.po` when the entry enters the overlay and, only with
     *   $refresh_original_from_shipped, re-taken on every later call. An entry the shipped `.po` no longer
     *   contains keeps its "original", so a later update can tell an unchanged entry (dropped) from a real local
     *   change (kept). Pass `true` only for reconciling writes whose $entries already went through the
     *   shipped/local decision (install, update, "remove local changes", "apply local changes", see
     *   LanguageInstallationManager); an admin edit passes `false` so its baseline never moves.
     * - "fuzzy" never applies: a delta value is by definition not the shipped one.
     * - "local_change" is recomputed via LocalChangeComments::refresh().
     *
     * Every file is written atomically (temporary file + rename). The `.po` only when its content changed; a
     * new revision whenever the one `current` names does not hold exactly what
     * ShippedTranslations::compileCatalog() compiles from the resulting catalog, so a missing, truncated or
     * stale revision next to an unchanged `.po` is rebuilt too (the output is deterministic, a byte comparison
     * suffices).
     *
     * A `current` without the `.po` (it carries the bookkeeping), or an overlay path that is no regular file,
     * is never replaced or removed: sync() throws, so a local change is not lost silently.
     *
     * @param array<string, string> $entries identifier => value, the complete final set for this
     *        module/language - what the caller just wrote to lng_modules
     * @param bool|null $expected_overlay_exists whether the caller decided on $entries knowing an overlay
     *        exists (see LanguageInstallationManager): with `false`, an overlay that exists once the lock is
     *        held was created concurrently (e.g. an admin edit during an installation) and is left untouched:
     *        sync() throws. `null`: no expectation.
     * Remarks (the administrator's remark of an identifier, counterpart of lng_data.remarks, see
     * LocalChangeComments::setRemark()) are kept in the overlay, too: an entry with a remark belongs into it
     * even if its value is the shipped one (see belongsInDelta()), as a "remark only" entry with an empty
     * msgstr, which the `.mo` leaves out.
     *
     * @param array<string, string>|null $remarks identifier => remark (a plural message under its identifier),
     *        the complete remarks of the module; `null` keeps the remarks the overlay holds. A remark of an
     *        identifier that is neither shipped nor in $entries is dropped.
     * @param array<string, string> $local_change_dates identifier => when it was changed locally (database
     *        timestamp "Y-m-d H:i:s", UTC; a plural message under its identifier), e.g. lng_data.local_change:
     *        the "local_change" of an entry entering the overlay with this write (instead of the write time)
     * @throws RuntimeException if the shipped `.po` or the overlay cannot be read or written, see also above
     */
    public static function sync(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        string $module,
        array $entries,
        ?string $client_data_dir,
        bool $refresh_original_from_shipped = false,
        ?bool $expected_overlay_exists = null,
        ?array $remarks = null,
        array $local_change_dates = []
    ): void {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return;
        }
        $shipped_po = MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po';
        if (!is_file($shipped_po)) {
            // not migrated (any more) for this language: leave an existing overlay alone, see above
            return;
        }

        // A copy: the catalog of readShippedPo() is shared with the readers of this request
        $shipped = clone self::readShippedPo($shipped_po);
        $delta = self::deltaOf($shipped, $module, $entries);
        $overlay = self::overlayFiles($client_data_dir, $directory, $lang_key);
        // entries "merge" took over into the shipped .po stay while the build serves the former one
        $keep_merged = !ShippedTranslationsBuild::servesShippedPo(
            MigratedLanguageFilePaths::shippedArtifactDirectory($ilias_absolute_path),
            $module,
            $lang_key,
            $shipped_po
        );
        if (
            $delta === []
            && array_filter($remarks ?? [], static fn($remark): bool => (string) $remark !== '') === []
            && !self::hasOverlayFiles($overlay)
        ) {
            if (self::findRevisions($overlay) !== []) {
                self::lockAndRun(
                    $directory,
                    $lang_key,
                    $client_data_dir,
                    static fn() => self::hasOverlayFiles($overlay) ? null : self::removeRevisions($overlay, $client_data_dir, []),
                    $ilias_absolute_path,
                    false
                );
            }
            return;
        }

        self::lockAndRun(
            $directory,
            $lang_key,
            $client_data_dir,
            static fn() => self::syncLocked(
                $shipped,
                $overlay,
                $lang_key,
                $module,
                $delta,
                $client_data_dir,
                $refresh_original_from_shipped,
                $expected_overlay_exists,
                $remarks,
                $keep_merged ? self::mapLegacyPluralValues($entries, self::flatModuleValues($shipped, $module)) : null,
                $local_change_dates
            ),
            $ilias_absolute_path,
            false
        );
    }

    /**
     * The one place that decides what a locally written $value means for a key whose shipped value is
     * $shipped_value (`null`: not shipped): the value actually kept. Used by the overlay (belongsInDelta()) and
     * the admin GUI's database write (ilObjLanguageExt::_saveValues()). An empty value resets the entry to its
     * shipped value; an empty value of a key that is not shipped stays empty.
     */
    public static function resolveLocalValue(?string $shipped_value, string $value): string
    {
        return $value === '' && $shipped_value !== null ? $shipped_value : $value;
    }

    /**
     * The values of $entries as written to lng_data/lng_modules (the database fallback of a migrated module):
     * an entry whose value is its unprocessed shipped value ($shipped_entries, as in the `.po`) gets the value
     * the build compiles (TranslationMarkupPolicy::sanitize()), so the fallback never serves markup the shipped
     * state does not. Other values (local changes, checked when written) stay. Database only: the overlay
     * (sync()) keeps comparing with the unprocessed value, so this creates no delta.
     *
     * @param array<string|int, string> $entries identifier => value
     * @param array<string|int, string> $shipped_entries identifier => unprocessed shipped value
     * @return array<string|int, string>
     */
    public static function databaseValues(array $entries, array $shipped_entries): array
    {
        $policy = self::$markup_policy ??= new TranslationMarkupPolicy();
        foreach ($entries as $identifier => $value) {
            if (array_key_exists($identifier, $shipped_entries) && (string) $shipped_entries[$identifier] === (string) $value) {
                $entries[$identifier] = $policy->sanitize((string) $value);
            }
        }

        return $entries;
    }

    /**
     * databaseValues() for $module/$lang_key from its shipped `.po`, with plural messages collapsed (see
     * collapsePluralForms()); $entries unchanged if the module is not migrated for $lang_key or its `.po` is
     * unreadable (the overlay write reports that).
     *
     * @param array<string|int, string> $entries identifier => value
     * @return array<string|int, string>
     */
    public static function databaseValuesOf(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        string $module,
        array $entries
    ): array {
        $shipped_po = self::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key)[$module] ?? null;
        if ($shipped_po === null) {
            return $entries;
        }
        try {
            $shipped = array_map(
                static fn(array $entry): string => $entry['value'],
                self::loadShippedModuleEntries($shipped_po, $module)
            );
        } catch (RuntimeException) {
            return $entries;
        }

        return self::databaseValues(
            self::collapsePluralForms(self::mapLegacyPluralValues($entries, $shipped), $shipped),
            self::collapsePluralForms($shipped, $shipped)
        );
    }

    /**
     * The key of the default form (the last, see PluralForms) of the plural message $identifier of
     * $shipped_entries (form keys included): where a plain value for the identifier goes (an import line,
     * "add new variable"). `null` if $identifier is no plain identifier of a plural message there (also for a
     * form key).
     *
     * @param array<string|int, mixed> $shipped_entries
     */
    public static function defaultFormKeyOf(string $identifier, array $shipped_entries): ?string
    {
        if (PluralFormKey::parse($identifier) !== null || self::pluralMessageOf($identifier, $shipped_entries) !== $identifier) {
            return null;
        }
        $count = self::pluralFormCounts($shipped_entries)[$identifier] ?? 1;

        return PluralFormKey::of($identifier, PluralForms::defaultFormIndexForCount($count));
    }

    /**
     * The identifier of the plural message of $shipped_entries (form keys included) $key belongs to, as a form
     * key or the identifier itself; `null` otherwise.
     *
     * @param array<string|int, mixed> $shipped_entries
     */
    public static function pluralMessageOf(string $key, array $shipped_entries): ?string
    {
        $identifier = PluralFormKey::parse($key)[0] ?? $key;

        return !array_key_exists($identifier, $shipped_entries)
            && array_key_exists(PluralFormKey::of($identifier, 0), $shipped_entries)
            ? $identifier
            : null;
    }

    /**
     * $entries (see the class docblock) as the database holds them: the forms of every plural message of
     * $shipped_entries replaced by its identifier with the value of the default form (PluralForms; a form
     * missing in $entries counts with its shipped value), at the position of its first form. Everything else
     * is kept.
     *
     * @param array<string|int, string> $entries
     * @param array<string|int, string> $shipped_entries
     * @return array<string|int, string>
     */
    public static function collapsePluralForms(array $entries, array $shipped_entries): array
    {
        $form_counts = self::pluralFormCounts($shipped_entries);
        if ($form_counts === []) {
            return $entries;
        }

        $collapsed = [];
        foreach ($entries as $key => $value) {
            $parsed = PluralFormKey::parse((string) $key);
            $identifier = $parsed[0] ?? (string) $key;
            if (!isset($form_counts[$identifier])) {
                $collapsed[$key] = $value;
                continue;
            }
            if ($parsed === null && self::hasAnyForm($entries, $identifier, $form_counts[$identifier])) {
                // a plain value next to the forms: the forms win
                continue;
            }
            if ($parsed !== null && !array_key_exists($identifier, $collapsed)) {
                $forms = [];
                for ($form = 0; $form < $form_counts[$identifier]; $form++) {
                    $form_key = PluralFormKey::of($identifier, $form);
                    $forms[] = (string) ($entries[$form_key] ?? $shipped_entries[$form_key] ?? '');
                }
                $collapsed[$identifier] = PluralForms::defaultValueForCount($forms, $form_counts[$identifier]);
            } elseif ($parsed === null) {
                $collapsed[$key] = $value;
            }
        }

        return $collapsed;
    }

    /**
     * $entries with a plain value of the identifier of a plural message of $shipped_entries moved to the key of
     * its default form, unless that form is already in $entries (then the plain value is dropped). Such a value
     * is a local change from before the module had plurals, or read back from the database (see
     * collapsePluralForms()).
     *
     * @template T
     * @param array<string|int, T> $entries
     * @param array<string|int, mixed> $shipped_entries
     * @return array<string|int, T>
     */
    public static function mapLegacyPluralValues(array $entries, array $shipped_entries): array
    {
        $form_counts = self::pluralFormCounts($shipped_entries);
        foreach ($form_counts as $identifier => $count) {
            if (!array_key_exists($identifier, $entries)) {
                continue;
            }
            $default_key = PluralFormKey::of($identifier, PluralForms::defaultFormIndexForCount($count));
            if (!array_key_exists($default_key, $entries)) {
                $entries[$default_key] = $entries[$identifier];
            }
            unset($entries[$identifier]);
        }

        return $entries;
    }

    /**
     * The plural messages among $shipped_entries with their number of forms: identifiers that only appear as
     * form keys.
     *
     * @param array<string|int, mixed> $shipped_entries
     * @return array<string, int>
     */
    private static function pluralFormCounts(array $shipped_entries): array
    {
        $counts = [];
        foreach (array_keys($shipped_entries) as $key) {
            $parsed = PluralFormKey::parse((string) $key);
            if ($parsed !== null && !array_key_exists($parsed[0], $shipped_entries)) {
                $counts[$parsed[0]] = max($counts[$parsed[0]] ?? 0, $parsed[1] + 1);
            }
        }

        return $counts;
    }

    /**
     * @param array<string|int, mixed> $entries
     */
    private static function hasAnyForm(array $entries, string $identifier, int $count): bool
    {
        for ($form = 0; $form < $count; $form++) {
            if (array_key_exists(PluralFormKey::of($identifier, $form), $entries)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The forms of a plural message written under the rule $previous ($previous_forms) that keep their meaning
     * under $rule: new form => previous form. A new form gets a previous one if every sampled quantity it serves
     * was served by that one form (see PluralForms::previousFormsOf()). If it covers several previous forms it
     * still gets one where that is unambiguous: if they all carry the same value, or if it is the default form
     * of $rule and the default form of $previous is among them (both are the last form, the catch-all for
     * integers: CLDR "other", or "many" where "other" only covers fractions). Every other new form (also one no
     * sampled quantity selects) is left out: no previous value has its meaning.
     *
     * @param array<int, string> $previous_forms
     * @return array<int, int> form of $rule => form of $previous
     */
    public static function mapPluralForms(PluralForms $previous, PluralForms $rule, array $previous_forms): array
    {
        $map = [];
        foreach ($rule->previousFormsOf($previous) as $form => $candidates) {
            if ($candidates === []) {
                continue;
            }
            $values = array_unique(array_map(
                static fn(int $candidate): string => (string) ($previous_forms[$candidate] ?? ''),
                $candidates
            ));
            if (count($candidates) === 1 || count($values) === 1) {
                $map[$form] = $candidates[0];
            } elseif ($form === $rule->defaultFormIndex() && in_array($previous->defaultFormIndex(), $candidates, true)) {
                $map[$form] = $previous->defaultFormIndex();
            }
        }

        return $map;
    }

    /**
     * $overlay with the plural messages of $module rewritten for the rule of $shipped, if it was written under
     * another one (number of forms or formula changed with an update, e.g. CLDR 49): every form gets the value
     * and "original" of the previous form with its meaning (see mapPluralForms()); a form without one gets the
     * shipped value as value and "original" (no local change); if the message is no longer shipped as plural:
     * the value of the previous default form, without "original". $overlay itself is unchanged and returned as
     * it is if its rule selects the same forms (see PluralForms::selectsSameFormsAs()).
     *
     * @param list<string>|null $not_taken_over set to the form keys (see PluralFormKey, the previous
     *        forms) of the local values that are in no form of the result
     */
    public static function withPluralRuleOf(
        TranslationCatalog $overlay,
        TranslationCatalog $shipped,
        string $module,
        ?array &$not_taken_over = null
    ): TranslationCatalog {
        $not_taken_over = [];
        $previous = self::pluralFormsOf($overlay);
        $rule = self::pluralFormsOf($shipped);
        if ($rule->selectsSameFormsAs($previous)) {
            return $overlay;
        }

        $result = clone $overlay;
        $result->setHeader('Plural-Forms', $rule->getHeader());
        foreach (self::moduleEntries($result, $module) as $entry) {
            if (!$entry->isPlural() || self::isRemarkOnly($entry)) {
                continue;
            }
            $previous_forms = self::formsOf($entry, $previous);
            $previous_originals = [];
            foreach (array_keys($previous_forms) as $form) {
                $previous_originals[$form] = LocalChangeComments::getOriginal($entry, $form);
            }
            $shipped_entry = self::findModuleEntry($shipped, $module, $entry->getId());
            $shipped_forms = $shipped_entry?->isPlural() ? self::formsOf($shipped_entry, $rule) : null;
            $map = self::mapPluralForms($previous, $rule, $previous_forms);

            $forms = [];
            LocalChangeComments::removeFormOriginals($entry);
            for ($form = 0; $form < $rule->getCount(); $form++) {
                if (isset($map[$form])) {
                    $forms[] = $previous_forms[$map[$form]];
                    $original = $previous_originals[$map[$form]];
                } elseif ($shipped_forms !== null) {
                    $forms[] = $shipped_forms[$form];
                    $original = $shipped_forms[$form];
                } else {
                    // not shipped (as a plural message) any more: only the message's own default value is left
                    $forms[] = $previous->defaultValueOf($previous_forms);
                    $original = null;
                }
                if ($original !== null) {
                    LocalChangeComments::setOriginal($entry, $original, $form);
                }
            }
            foreach ($previous_forms as $form => $value) {
                if ($value !== $previous_originals[$form] && !in_array($value, $forms, true)) {
                    $not_taken_over[] = PluralFormKey::of($entry->getId(), $form);
                }
            }
            $entry->setPlural((string) $entry->getPluralId(), $forms);
        }

        return $result;
    }

    /**
     * The "Plural-Forms" rule of $catalog - the Germanic one if its header is missing or invalid.
     */
    public static function pluralFormsOf(TranslationCatalog $catalog): PluralForms
    {
        return PluralForms::fromHeaderOrGermanic($catalog->getHeader('Plural-Forms'));
    }

    /**
     * $entry as identifier => value (see the class docblock): a singular message under its identifier, a
     * plural one as exactly $plural_forms->getCount() form keys (missing forms empty, surplus ones dropped).
     *
     * @return array<string, string>
     */
    public static function flatValuesOf(TranslationEntry $entry, PluralForms $plural_forms): array
    {
        if (!$entry->isPlural()) {
            return [$entry->getId() => $entry->getTranslation()];
        }
        $values = [];
        foreach (self::formsOf($entry, $plural_forms) as $form => $value) {
            $values[PluralFormKey::of($entry->getId(), $form)] = $value;
        }

        return $values;
    }

    /** @return list<string> exactly $plural_forms->getCount() forms of the plural message $entry */
    private static function formsOf(TranslationEntry $entry, PluralForms $plural_forms): array
    {
        $count = $plural_forms->getCount();

        return array_pad(array_slice($entry->getPluralTranslations(), 0, $count), $count, '');
    }

    /** @return array<string, string> the values of the entries of $module in $catalog, see flatValuesOf() */
    private static function flatModuleValues(TranslationCatalog $catalog, string $module): array
    {
        $plural_forms = self::pluralFormsOf($catalog);
        $values = [];
        foreach (self::moduleEntries($catalog, $module) as $entry) {
            $values += self::shippedValuesOf($entry, $plural_forms);
        }

        return $values;
    }

    /**
     * flatValuesOf() for an entry of a shipped `.po`: nothing for an entry without translation (an empty
     * singular msgstr, a plural message with all forms empty). The build leaves it out of the `.mo` (see
     * TranslationCatalog::toMoString()), so it is not shipped at all: not served by ilLanguage, not written
     * to lng_data/lng_modules, not listed by the admin GUI, like a key the legacy `.lang` file lacks.
     *
     * @return array<string, string>
     */
    private static function shippedValuesOf(TranslationEntry $entry, PluralForms $plural_forms): array
    {
        $values = self::flatValuesOf($entry, $plural_forms);

        return implode('', $values) === '' ? [] : $values;
    }

    /**
     * Whether $value belongs into the overlay (the delta) given the $shipped_value of its key (`null`: not
     * shipped), see resolveLocalValue(). An empty value never does. With a non-empty $remark an entry of a
     * shipped key belongs into it even with the shipped value, only for the remark (see sync(); the `.mo`
     * stays unchanged).
     */
    public static function belongsInDelta(?string $shipped_value, string $value, ?string $remark = null): bool
    {
        $resolved = self::resolveLocalValue($shipped_value, $value);
        if ($resolved !== '' && $resolved !== $shipped_value) {
            return true;
        }

        return $remark !== null && $remark !== '' && ($shipped_value !== null || $resolved !== '');
    }

    /**
     * The remarks of the overlay of $module/$lang_key (see LocalChangeComments::setRemark()), identifier =>
     * remark: `[]` without overlay, `null` where loadModuleTranslations() returns `null`.
     *
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @return array<string, string>|null
     * @throws RuntimeException see loadLocalChanges()
     */
    public static function loadRemarks(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): ?array {
        $overlay = self::overlayIfShipped($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
        if ($overlay === null) {
            return null;
        }
        self::assertReadableOverlay($overlay);
        if (!is_file($overlay['po'])) {
            return [];
        }

        return self::remarksOf(TranslationCatalog::fromPoFile($overlay['po']), $module);
    }

    /**
     * Changes remarks of $module/$lang_key in its overlay (the values stay as they are): identifier =>
     * remark, `null` or '' removes the remark. Remarks not in $changes are kept. A no-op like sync().
     *
     * @param array<string, ?string> $changes
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @throws RuntimeException see sync()
     */
    public static function setRemarks(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        array $changes,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): void {
        if ($changes === []) {
            return;
        }
        $ilias_absolute_path ??= defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
        self::withOverlayLock(
            $language_file_directory_manager,
            $lang_key,
            $module,
            $client_data_dir,
            static function () use ($language_file_directory_manager, $lang_key, $module, $changes, $client_data_dir, $ilias_absolute_path): void {
                $current = self::loadModuleTranslations($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
                $remarks = self::loadRemarks($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
                if ($current === null || $remarks === null) {
                    return;
                }
                foreach ($changes as $identifier => $remark) {
                    $identifier = PluralFormKey::parse((string) $identifier)[0] ?? (string) $identifier;
                    if ($remark === null || $remark === '') {
                        unset($remarks[$identifier]);
                    } else {
                        $remarks[$identifier] = $remark;
                    }
                }
                self::sync(
                    $language_file_directory_manager,
                    $ilias_absolute_path,
                    $lang_key,
                    $module,
                    array_map(static fn(array $entry): string => $entry['value'], $current),
                    $client_data_dir,
                    false,
                    null,
                    $remarks
                );
            },
            $ilias_absolute_path
        );
    }

    /** @return array<string, string> identifier => remark of the entries of $module in $catalog */
    private static function remarksOf(TranslationCatalog $catalog, string $module): array
    {
        $remarks = [];
        foreach (self::moduleEntries($catalog, $module) as $entry) {
            $remark = LocalChangeComments::getRemark($entry);
            if ($remark !== null && $remark !== '') {
                $remarks[$entry->getId()] = $remark;
            }
        }

        return $remarks;
    }

    /**
     * Whether $entry of an overlay only carries a remark (see sync()): no value of its own.
     */
    public static function isRemarkOnly(TranslationEntry $entry): bool
    {
        return implode('', $entry->getPluralTranslations()) === '';
    }

    /**
     * @param array<string, string> $entries
     * @return array<string, string> identifier => value, sorted by identifier
     */
    private static function deltaOf(TranslationCatalog $shipped, string $module, array $entries): array
    {
        $shipped_values = self::flatModuleValues($shipped, $module);
        $delta = [];
        foreach (self::mapLegacyPluralValues($entries, $shipped_values) as $identifier => $value) {
            $identifier = (string) $identifier;
            $value = (string) $value;
            if (self::belongsInDelta($shipped_values[$identifier] ?? null, $value)) {
                $delta[$identifier] = $value;
            }
        }
        ksort($delta, SORT_STRING);

        return $delta;
    }

    /**
     * Whether $module/$lang_key has an overlay (anything at the place of its `.po` or `current`), i.e.
     * local changes. `false` if $module is not a contributed directory or $client_data_dir is `null`.
     */
    public static function hasOverlay(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir
    ): bool {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return false;
        }

        return self::hasOverlayFiles(self::overlayFiles($client_data_dir, $directory, $lang_key));
    }

    /**
     * The entries of $catalog that belong to $module: those with the module as context (msgctxt) and those
     * without any context (a file of a single module need not repeat its name, e.g. a plugin's `.po`, which
     * must survive the plugin's move to a component with another module name unchanged). An explicit empty
     * context (`msgctxt ""`) is a context of its own and does not belong to $module. For an identifier with
     * and without module context, the one with the context wins.
     *
     * @return array<string, TranslationEntry> identifier => entry, in catalog order
     */
    public static function moduleEntries(TranslationCatalog $catalog, string $module): array
    {
        $entries = [];
        foreach ($catalog->getEntries() as $entry) {
            $context = $entry->getContext();
            if ($context === $module || ($context === null && !isset($entries[$entry->getId()]))) {
                $entries[$entry->getId()] = $entry;
            }
        }

        return $entries;
    }

    /**
     * The entry of $identifier that belongs to $module, see moduleEntries().
     */
    public static function findModuleEntry(TranslationCatalog $catalog, string $module, string $identifier): ?TranslationEntry
    {
        $entry = $catalog->find($module, $identifier);
        if ($entry !== null) {
            return $entry;
        }
        // gettext/gettext looks up "no context" and the empty context under the same id - only an
        // entry that really has no context belongs to the module
        $entry = $catalog->find(null, $identifier);

        return $entry !== null && $entry->getContext() === null ? $entry : null;
    }

    /**
     * The files of the overlay of $directory's module for $lang_key, see MigratedLanguageFilePaths.
     *
     * @return array{directory: string, po: string, lock: string, current: string}
     * @throws \InvalidArgumentException for an invalid module name, language key or file name pattern
     */
    private static function overlayFiles(string $client_data_dir, LanguageFileDirectory $directory, string $lang_key): array
    {
        $overlay_directory = MigratedLanguageFilePaths::overlayDirectory($client_data_dir, $directory->getPrefix(), $lang_key);

        return [
            'directory' => $overlay_directory,
            'po' => MigratedLanguageFilePaths::overlayPoFile($client_data_dir, $directory, $lang_key),
            'lock' => MigratedLanguageFilePaths::overlayLockFile($overlay_directory),
            'current' => MigratedLanguageFilePaths::overlayCurrentFile($overlay_directory),
        ];
    }

    /**
     * Whether anything exists at the place of the overlay `.po` or `current`, also a non-regular file.
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     */
    private static function hasOverlayFiles(array $overlay): bool
    {
        foreach ([$overlay['po'], $overlay['current']] as $file) {
            if (file_exists($file) || is_link($file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @throws RuntimeException if the overlay cannot be read as a whole: a `.po` or `current` that is
     *         no regular file, or a `current` without the `.po` (which carries the bookkeeping) -
     *         treating that as "no overlay" would let the next write remove it
     */
    private static function assertReadableOverlay(array $overlay): void
    {
        foreach ([$overlay['po'], $overlay['current']] as $file) {
            if ((file_exists($file) || is_link($file)) && (!is_file($file) || is_link($file))) {
                throw new RuntimeException(sprintf('The overlay file "%s" is no regular file.', $file));
            }
        }
        if (is_file($overlay['current']) && !is_file($overlay['po'])) {
            throw new RuntimeException(sprintf(
                'The overlay "%s" has no "%s" - it is left untouched.',
                $overlay['current'],
                $overlay['po']
            ));
        }
    }

    /**
     * The revision `current` of $overlay names, `null` if there is none (or no valid one).
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     */
    private static function currentRevision(array $overlay): ?string
    {
        $revision = is_file($overlay['current']) ? @file_get_contents($overlay['current']) : false;
        $revision = $revision === false ? '' : trim($revision);

        return MigratedLanguageFilePaths::isOverlayRevision($revision) ? $revision : null;
    }

    /**
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @return list<string> the revisions in the directory of $overlay
     */
    private static function findRevisions(array $overlay): array
    {
        $revisions = [];
        foreach (is_dir($overlay['directory']) && !is_link($overlay['directory']) ? scandir($overlay['directory']) ?: [] : [] as $name) {
            if (MigratedLanguageFilePaths::isOverlayRevision($name)) {
                $revisions[] = $name;
            }
        }

        return $revisions;
    }

    /**
     * Removes every revision of $overlay except $keep.
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @param list<string> $keep
     */
    private static function removeRevisions(array $overlay, string $client_data_dir, array $keep): void
    {
        foreach (self::findRevisions($overlay) as $revision) {
            if (!in_array($revision, $keep, true)) {
                self::removeTree($client_data_dir, MigratedLanguageFilePaths::overlayRevisionDirectory($overlay['directory'], $revision));
            }
        }
    }

    /**
     * Removes $path recursively without following symbolic links (a link itself is removed), only below the
     * overlay root.
     *
     * @throws RuntimeException see assertNoSymbolicLinkBelowOverlayRoot()
     */
    private static function removeTree(string $client_data_dir, string $path): void
    {
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, dirname($path));
        if (is_link($path) || is_file($path)) {
            @unlink($path);
            return;
        }
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) ?: [] as $name) {
            if ($name !== '.' && $name !== '..') {
                self::removeTree($client_data_dir, $path . '/' . $name);
            }
        }
        @rmdir($path);
    }

    /**
     * Writes the revision of the compiled overlay $mo/$keys_json into the directory of $overlay - or
     * reuses an existing one with exactly this content.
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @return string the revision
     */
    private static function writeRevision(array $overlay, string $client_data_dir, string $module, string $lang_key, string $mo, string $keys_json): string
    {
        $revision = 'r-' . substr(hash('sha256', $mo . "\0" . $keys_json), 0, 32);
        $revision_directory = MigratedLanguageFilePaths::overlayRevisionDirectory($overlay['directory'], $revision);
        if (self::isRevision($revision_directory, $module, $lang_key, $mo, $keys_json)) {
            return $revision;
        }
        if (file_exists($revision_directory) || is_link($revision_directory)) {
            // a damaged revision: it may be loaded by a running process already - never changed in place
            $revision = 'r-' . bin2hex(random_bytes(16));
            $revision_directory = MigratedLanguageFilePaths::overlayRevisionDirectory($overlay['directory'], $revision);
        }
        $mo_file = MigratedLanguageFilePaths::overlayMoFile($revision_directory, $module, $lang_key);
        self::ensureDirectoryExists(dirname($mo_file), $client_data_dir);
        AtomicFileWriter::write($mo_file, $mo, self::overlayRoot($client_data_dir));
        AtomicFileWriter::write(MigratedLanguageFilePaths::overlayKeysFile($revision_directory), $keys_json, self::overlayRoot($client_data_dir));

        return $revision;
    }

    /**
     * Whether $revision_directory holds exactly $mo and $keys_json.
     */
    private static function isRevision(string $revision_directory, string $module, string $lang_key, string $mo, string $keys_json): bool
    {
        $mo_file = MigratedLanguageFilePaths::overlayMoFile($revision_directory, $module, $lang_key);
        $keys_file = MigratedLanguageFilePaths::overlayKeysFile($revision_directory);

        return !is_link($revision_directory)
            && is_file($mo_file) && !is_link($mo_file) && @file_get_contents($mo_file) === $mo
            && is_file($keys_file) && !is_link($keys_file) && @file_get_contents($keys_file) === $keys_json;
    }

    /**
     * sync() once the overlay lock is held.
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @param array<string, string> $delta see deltaOf()
     */
    private static function syncLocked(
        TranslationCatalog $shipped,
        array $overlay,
        string $lang_key,
        string $module,
        array $delta,
        string $client_data_dir,
        bool $refresh_original_from_shipped,
        ?bool $expected_overlay_exists,
        ?array $remarks = null,
        ?array $keep_merged_with = null,
        array $local_change_dates = []
    ): void {
        $overlay_po = $overlay['po'];
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay_po);
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay['current']);
        self::assertReadableOverlay($overlay);
        if ($expected_overlay_exists === false && self::hasOverlayFiles($overlay)) {
            throw new RuntimeException(sprintf(
                'The overlay "%s" was created after the values to write were determined - it is left untouched.',
                $overlay_po
            ));
        }

        $overlay_exists = is_file($overlay_po);
        $existing = null;
        if ($overlay_exists) {
            try {
                $existing = TranslationCatalog::fromPoFile($overlay_po);
            } catch (RuntimeException) {
                // a corrupt overlay is rebuilt like a missing one: $delta carries every value and "original"/
                // "local_change" are recomputed; only the old local_change timestamps and remarks are lost
                $overlay_exists = false;
            }
        }
        if ($existing !== null) {
            // written under another plural rule: its forms by meaning, see withPluralRuleOf()
            $existing = self::withPluralRuleOf($existing, $shipped, $module, $not_taken_over);
            if ($not_taken_over !== []) {
                self::logWarning(PlainLogText::of(sprintf(
                    'The plural rule of language "%s" changed: these local values of module "%s" have no form with their meaning under the new rule and were replaced by the shipped value: %s',
                    $lang_key,
                    $module,
                    implode(', ', $not_taken_over)
                )));
            }
        }
        if ($existing !== null && $keep_merged_with !== null) {
            $delta = self::withMergedEntries($delta, $existing, $shipped, $module, $keep_merged_with);
        }

        // the remarks given or held by the overlay, only of identifiers that are shipped or in the delta
        $remarks ??= $existing === null ? [] : self::remarksOf($existing, $module);
        $shipped_values = self::flatModuleValues($shipped, $module);
        $delta_identifiers = [];
        foreach (array_keys($delta) as $key) {
            $delta_identifiers[self::pluralMessageOf((string) $key, $shipped_values) ?? PluralFormKey::parse((string) $key)[0] ?? (string) $key] = true;
            $delta_identifiers[(string) $key] = true;
        }
        $kept_remarks = [];
        foreach ($remarks as $identifier => $remark) {
            $identifier = (string) $identifier;
            if (!mb_check_encoding((string) $remark, 'UTF-8')) {
                // would make the overlay unreadable - dropped, the rest is written
                self::logWarning(PlainLogText::of(sprintf('The remark of "%s" (module "%s") is not valid UTF-8 and was dropped.', $identifier, $module)));
                continue;
            }
            $is_plural = self::pluralMessageOf($identifier, $shipped_values) !== null;
            $shipped_value = $shipped_values[$identifier] ?? ($is_plural ? '' : null);
            if (
                (string) $remark !== ''
                && (isset($delta_identifiers[$identifier]) || self::belongsInDelta($shipped_value, $shipped_value ?? '', (string) $remark))
            ) {
                $kept_remarks[$identifier] = (string) $remark;
            }
        }

        if ($delta === [] && $kept_remarks === []) {
            self::removeOverlayFiles($overlay, $module, $lang_key, $client_data_dir);
            // a running request that already read a revision keeps it mapped (a removed file stays
            // readable for whoever has it open)
            self::removeRevisions($overlay, $client_data_dir, []);
            return;
        }
        // a stable order: the same content always gives the same file
        ksort($kept_remarks, SORT_STRING);

        $catalog = new TranslationCatalog();
        foreach ($shipped->getHeaders() as $name => $value) {
            $catalog->setHeader($name, $value);
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $plural_forms = self::pluralFormsOf($shipped);
        [$delta, $plural_delta] = self::splitPluralDelta($shipped, $existing, $module, $delta);
        foreach ($plural_delta as $identifier => $forms) {
            $entry = self::overlayPluralEntry(
                $shipped,
                $existing,
                $module,
                (string) $identifier,
                $forms,
                $plural_forms,
                $refresh_original_from_shipped,
                $existing !== null && self::findModuleEntry($existing, $module, (string) $identifier) !== null
                    ? $now
                    : self::localChangeDate($local_change_dates, (string) $identifier, $now)
            );
            LocalChangeComments::setRemark($entry, $kept_remarks[(string) $identifier] ?? null);
            unset($kept_remarks[(string) $identifier]);
            $previous_entry = $existing === null ? null : self::findModuleEntry($existing, $module, (string) $identifier);
            if (
                $keep_merged_with === null
                || $previous_entry === null
                || $previous_entry->getPluralTranslations() !== $entry->getPluralTranslations()
            ) {
                // the build serves the taken over value now - or it was changed again since merge
                LocalChangeComments::setMergedIntoShipped($entry, false);
            }
            $catalog->add($entry);
        }
        foreach ($delta as $identifier => $value) {
            $identifier = (string) $identifier;
            $shipped_entry = self::findModuleEntry($shipped, $module, $identifier);
            $existing_entry = $existing === null ? null : self::findModuleEntry($existing, $module, $identifier);
            if ($existing_entry?->isPlural() || ($existing_entry !== null && self::isRemarkOnly($existing_entry))) {
                // was a plural message in the overlay (or only a remark), is a singular one now: rebuilt from scratch
                $existing_entry = null;
            }
            // without msgctxt, like the shipped files (the overlay belongs to one module): an older entry loses its msgctxt
            $entry = $existing_entry?->withContext(null) ?? new TranslationEntry(null, $identifier);
            // An entry not in the overlay so far had the shipped value (or did not exist)
            $previous_value = $existing_entry?->getTranslation() ?? $shipped_entry?->getTranslation() ?? '';

            // an entry no longer in the shipped .po keeps its "original", see sync() and
            // LanguageInstallationManager::resolveMigratedModule()
            if ($shipped_entry !== null && ($existing_entry === null || $refresh_original_from_shipped)) {
                LocalChangeComments::setOriginal($entry, $shipped_entry->getTranslation());
                $entry->setExtractedComments(self::shippedNotesOf($shipped_entry));
            }

            $entry->translate($value);
            $entry->removeFlag('fuzzy');
            LocalChangeComments::refresh(
                $entry,
                $previous_value,
                $value,
                $existing_entry === null ? self::localChangeDate($local_change_dates, $identifier, $now) : $now
            );
            LocalChangeComments::setRemark($entry, $kept_remarks[$identifier] ?? null);
            unset($kept_remarks[$identifier]);
            if ($keep_merged_with === null || $existing_entry === null || $existing_entry->getTranslation() !== $value) {
                LocalChangeComments::setMergedIntoShipped($entry, false);
            }
            $catalog->add($entry);
        }

        // the remaining remarks belong to entries with the shipped value: "remark only" entries without a value,
        // left out of the .mo
        foreach ($kept_remarks as $identifier => $remark) {
            $identifier = (string) $identifier;
            $shipped_entry = self::findModuleEntry($shipped, $module, $identifier);
            $entry = new TranslationEntry(null, $identifier);
            if ($shipped_entry?->isPlural()) {
                $entry->setPlural((string) $shipped_entry->getPluralId(), array_fill(0, $plural_forms->getCount(), ''));
            }
            LocalChangeComments::setRemark($entry, $remark);
            $catalog->add($entry);
        }

        $po_content = $catalog->toPoString();
        if (!mb_check_encoding($po_content, 'UTF-8')) {
            // fromPoString() refuses such a file: writing it would lose every local change of the
            // module with the next read - nothing is written instead
            throw new RuntimeException(sprintf('The overlay "%s" would not be valid UTF-8 - it is left unchanged.', $overlay_po));
        }
        $compiled = ShippedTranslations::compileCatalog($catalog, $module, false);
        $keys_json = json_encode(
            array_map('strval', array_keys($compiled['values'])),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ) . "\n";
        $po_unchanged = $overlay_exists && $po_content === file_get_contents($overlay_po);
        $previous_revision = self::currentRevision($overlay);
        $revision_unchanged = $previous_revision !== null && self::isRevision(
            MigratedLanguageFilePaths::overlayRevisionDirectory($overlay['directory'], $previous_revision),
            $module,
            $lang_key,
            $compiled['mo'],
            $keys_json
        );
        if ($po_unchanged && $revision_unchanged) {
            return;
        }

        self::ensureDirectoryExists($overlay['directory'], $client_data_dir);
        // the .po first: it carries the bookkeeping the revision lacks, and the runtime only reads the revision
        // `current` names, so a failure in between keeps serving the previous revision (a non-matching .po is
        // repaired by the next sync), never a new revision whose bookkeeping got lost
        if (!$po_unchanged) {
            AtomicFileWriter::write($overlay_po, $po_content, self::overlayRoot($client_data_dir));
        }
        if (!$revision_unchanged) {
            $revision = self::writeRevision($overlay, $client_data_dir, $module, $lang_key, $compiled['mo'], $keys_json);
            AtomicFileWriter::write($overlay['current'], $revision . "\n", self::overlayRoot($client_data_dir));
            // the previous revision may still be read by a running request
            self::removeRevisions($overlay, $client_data_dir, array_values(array_filter([$revision, $previous_revision])));
        }

        \ilLanguage::invalidateMigratedLanguageFileCache($module, $lang_key);
    }

    /**
     * When the local change of $identifier was made according to $local_change_dates (identifier => database
     * timestamp "Y-m-d H:i:s", UTC, see sync()); $now if unknown or unreadable. A plural message without a date
     * under its identifier takes the latest one of its form keys (see PluralFormKey).
     *
     * @param array<string, string> $local_change_dates
     */
    private static function localChangeDate(array $local_change_dates, string $identifier, DateTimeImmutable $now): DateTimeImmutable
    {
        $read = static fn(string $date): ?DateTimeImmutable
            => DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $date, new DateTimeZone('UTC')) ?: null;
        if (isset($local_change_dates[$identifier])) {
            return $read($local_change_dates[$identifier]) ?? $now;
        }
        $latest = null;
        foreach ($local_change_dates as $key => $date) {
            if ((PluralFormKey::parse((string) $key)[0] ?? null) === $identifier) {
                $form_date = $read($date);
                $latest = $form_date !== null && ($latest === null || $form_date > $latest) ? $form_date : $latest;
            }
        }

        return $latest ?? $now;
    }

    /**
     * $delta plus the overlay entries "merge" took over into the shipped `.po` (see
     * LocalChangeComments::setMergedIntoShipped()): their value equals the shipped one now, but the build still
     * serves the former `.po`, so without them it would fall back to the former shipped value until the next
     * build. Only while the caller's value ($values, see deltaOf()) is still the one taken over.
     *
     * @param array<string, string> $delta
     * @param array<string|int, string> $values
     * @return array<string, string>
     */
    private static function withMergedEntries(
        array $delta,
        TranslationCatalog $existing,
        TranslationCatalog $shipped,
        string $module,
        array $values
    ): array {
        $plural_forms = self::pluralFormsOf($shipped);
        foreach (self::moduleEntries($existing, $module) as $entry) {
            if (!LocalChangeComments::isMergedIntoShipped($entry) || self::isRemarkOnly($entry)) {
                continue;
            }
            foreach (self::flatValuesOf($entry, $plural_forms) as $key => $value) {
                if (!isset($delta[$key]) && array_key_exists($key, $values) && (string) $values[$key] === $value) {
                    $delta[$key] = $value;
                }
            }
        }
        ksort($delta, SORT_STRING);

        return $delta;
    }

    /**
     * Marks the overlay entries of $identifiers (a plural message under its identifier) as taken over into the
     * shipped `.po` by "merge" (see LocalChangeComments::setMergedIntoShipped() and sync()). Only the overlay
     * `.po` changes (the served values stay), and only if an entry is not marked yet. Run under the overlay lock
     * (re-entrant).
     *
     * @param list<string> $identifiers
     * @throws RuntimeException if the overlay cannot be read or written
     */
    public static function markMergedIntoShipped(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        array $identifiers,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): void {
        if ($identifiers === []) {
            return;
        }
        $overlay = self::overlayIfShipped($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
        if ($overlay === null || !self::hasOverlayFiles($overlay)) {
            // nothing to mark - and no directory or lock file is created for it
            return;
        }
        self::withOverlayLock(
            $language_file_directory_manager,
            $lang_key,
            $module,
            $client_data_dir,
            static function () use ($language_file_directory_manager, $lang_key, $module, $identifiers, $client_data_dir, $ilias_absolute_path): void {
                $overlay = self::overlayIfShipped($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
                if ($overlay === null || $client_data_dir === null) {
                    return;
                }
                self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay['po']);
                self::assertReadableOverlay($overlay);
                if (!is_file($overlay['po'])) {
                    return;
                }
                $catalog = TranslationCatalog::fromPoFile($overlay['po']);
                $changed = false;
                foreach ($identifiers as $identifier) {
                    $entry = self::findModuleEntry($catalog, $module, $identifier);
                    if ($entry !== null && !LocalChangeComments::isMergedIntoShipped($entry)) {
                        LocalChangeComments::setMergedIntoShipped($entry, true);
                        $changed = true;
                    }
                }
                if ($changed) {
                    AtomicFileWriter::write($overlay['po'], $catalog->toPoString(), self::overlayRoot($client_data_dir));
                }
            },
            $ilias_absolute_path
        );
    }

    /**
     * $delta split into the singular entries and the forms of plural messages: a form key (see
     * PluralFormKey) whose identifier is a plural message of the shipped `.po` or of the existing
     * overlay belongs to that message, every other key is an ordinary identifier.
     *
     * @param array<string, string> $delta
     * @return array{0: array<string, string>, 1: array<string, array<int, string>>}
     */
    private static function splitPluralDelta(
        TranslationCatalog $shipped,
        ?TranslationCatalog $existing,
        string $module,
        array $delta
    ): array {
        $singular = [];
        $plural = [];
        foreach ($delta as $key => $value) {
            $parsed = PluralFormKey::parse((string) $key);
            if (
                $parsed !== null
                && (
                    self::findModuleEntry($shipped, $module, $parsed[0])?->isPlural()
                    || ($existing !== null && self::findModuleEntry($existing, $module, $parsed[0])?->isPlural())
                )
            ) {
                $plural[$parsed[0]][$parsed[1]] = $value;
                continue;
            }
            $singular[(string) $key] = $value;
        }

        return [$singular, $plural];
    }

    /**
     * The overlay entry of the plural message $identifier whose forms in the delta are $delta_forms: every form
     * (one not in the delta has its shipped value, empty if not shipped) and an "original" per form (see
     * LocalChangeComments), by the same rules as a singular entry of syncLocked().
     *
     * @param array<int, string> $delta_forms form => value
     */
    private static function overlayPluralEntry(
        TranslationCatalog $shipped,
        ?TranslationCatalog $existing,
        string $module,
        string $identifier,
        array $delta_forms,
        PluralForms $plural_forms,
        bool $refresh_original_from_shipped,
        DateTimeImmutable $now
    ): TranslationEntry {
        $shipped_entry = self::findModuleEntry($shipped, $module, $identifier);
        $shipped_forms = $shipped_entry?->isPlural() ? self::formsOf($shipped_entry, $plural_forms) : null;
        $existing_entry = $existing === null ? null : self::findModuleEntry($existing, $module, $identifier);
        if ($existing_entry !== null && (!$existing_entry->isPlural() || self::isRemarkOnly($existing_entry))) {
            // a singular message in the overlay, a plural one now (or only a remark): rebuilt from scratch
            $existing_entry = null;
        }

        $forms = [];
        for ($form = 0; $form < $plural_forms->getCount(); $form++) {
            $forms[] = $delta_forms[$form] ?? $shipped_forms[$form] ?? '';
        }
        $previous_forms = $existing_entry !== null
            ? self::formsOf($existing_entry, $plural_forms)
            : ($shipped_forms ?? []);

        $entry = $existing_entry?->withContext(null) ?? new TranslationEntry(null, $identifier);
        if ($shipped_forms !== null && ($existing_entry === null || $refresh_original_from_shipped)) {
            LocalChangeComments::removeOriginal($entry);
            LocalChangeComments::removeFormOriginals($entry);
            foreach ($shipped_forms as $form => $value) {
                LocalChangeComments::setOriginal($entry, $value, $form);
            }
            $entry->setExtractedComments(self::shippedNotesOf($shipped_entry));
        }

        $entry->setPlural(
            $shipped_entry?->getPluralId() ?? $existing_entry?->getPluralId() ?? $identifier . '_plural',
            $forms
        );
        $entry->removeFlag('fuzzy');
        LocalChangeComments::refreshForms($entry, $previous_forms, $forms, $now);

        return $entry;
    }

    /**
     * The shipped values of every module migrated for $lang_key.
     *
     * @return array<string, array<string, string>> module => identifier => shipped value
     */
    public static function loadShippedModules(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key
    ): array {
        $modules = [];
        foreach (self::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key) as $module => $shipped_po) {
            $modules[$module] = array_map(
                static fn(array $entry): string => $entry['value'],
                self::loadShippedModuleEntries($shipped_po, $module)
            );
        }

        return $modules;
    }

    /**
     * Whether any module is migrated for any of $lang_keys, see findShippedModuleFiles().
     *
     * @param list<string> $lang_keys
     */
    public static function hasShippedModules(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        array $lang_keys
    ): bool {
        foreach ($lang_keys as $lang_key) {
            if (self::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key) !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * Every module migrated for $lang_key (its shipped `.po` exists, parseable or not) with the path of that `.po`.
     *
     * @return array<string, string> module => shipped `.po` path
     */
    public static function findShippedModuleFiles(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key
    ): array {
        $files = [];
        foreach ($language_file_directory_manager->getDirectories() as $directory) {
            $module = $directory->getPrefix();
            if ($module === '') {
                continue;
            }
            $shipped_po = MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po';
            if (is_file($shipped_po)) {
                $files[$module] = $shipped_po;
            }
        }

        return $files;
    }

    /**
     * The entries of $module in the shipped `.po` $shipped_po (see findShippedModuleFiles(); a plural message
     * as its forms, see the class docblock), with their notes (see shippedNotesOf(), the counterpart of a `.lang`
     * file's "###" comment) joined into one line (`null` if none).
     *
     * @return array<string, array{value: string, comment: ?string}> identifier => entry
     * @throws RuntimeException if the file cannot be read or parsed
     */
    public static function loadShippedModuleEntries(string $shipped_po, string $module): array
    {
        $entries = [];
        $catalog = self::readShippedPo($shipped_po);
        $plural_forms = self::pluralFormsOf($catalog);
        foreach (self::moduleEntries($catalog, $module) as $entry) {
            $comment = trim(preg_replace('/\s*[\r\n]+\s*/', ' ', implode(' ', self::shippedNotesOf($entry))) ?? '');
            foreach (self::shippedValuesOf($entry, $plural_forms) as $identifier => $value) {
                $entries[$identifier] = [
                    'value' => $value,
                    'comment' => $comment === '' ? null : $comment,
                ];
            }
        }

        return $entries;
    }

    /**
     * The notes (see shippedNotesOf()) of every entry of $module in the shipped `.po` $shipped_po, each trimmed on
     * its own, by identifier (a plural message under its identifier).
     *
     * @return array<string, list<string>>
     * @throws RuntimeException if the file cannot be read or parsed
     */
    public static function loadShippedNotes(string $shipped_po, string $module): array
    {
        $notes = [];
        foreach (self::moduleEntries(self::readShippedPo($shipped_po), $module) as $identifier => $entry) {
            $notes[(string) $identifier] = array_values(array_map('trim', self::shippedNotesOf($entry)));
        }

        return $notes;
    }

    /**
     * The language's own note (translator comments), else the reference note (extracted comments).
     *
     * @return list<string>
     */
    public static function shippedNotesOf(TranslationEntry $entry): array
    {
        return $entry->getTranslatorComments() ?: $entry->getExtractedComments();
    }

    /**
     * The identifiers of $module in $shipped_po (form keys for a plural message, as in
     * loadShippedModuleEntries()) flagged "fuzzy": not translated yet (in a module converted from the legacy
     * `.lang` files the dated "... new variable" marker, or a value copied into several plural forms, see
     * convert_module_to_po.php).
     *
     * @return array<string, true>
     * @throws RuntimeException if the file cannot be read or parsed
     */
    public static function loadShippedFuzzyIdentifiers(string $shipped_po, string $module): array
    {
        $catalog = self::readShippedPo($shipped_po);
        $plural_forms = self::pluralFormsOf($catalog);
        $fuzzy = [];
        foreach (self::moduleEntries($catalog, $module) as $entry) {
            if ($entry->hasFlag('fuzzy')) {
                foreach (array_keys(self::shippedValuesOf($entry, $plural_forms)) as $identifier) {
                    $fuzzy[$identifier] = true;
                }
            }
        }

        return $fuzzy;
    }

    /**
     * The directories the overlay of the modules migrated for any of $lang_keys would have to be written to,
     * but cannot: for each, the closest existing path (the directory or the first existing parent it would be
     * created in) must be a writable directory. To be checked BEFORE writing, so a missing permission is
     * reported up front, typically because Setup does not run as the web server user (required: the web
     * server writes the overlay later, too) or a file occupies the directory's place. Empty if
     * $client_data_dir is `null` (no overlay is maintained).
     *
     * With $for_user_id the check is evaluated for that user (permission bits, owner and group of each path)
     * instead of the current process: Setup passes the owner of the client data directory (the web server
     * user) when running as another user, e.g. root, for whom is_writable() is always `true`. Without the
     * POSIX extension the current process is checked.
     *
     * @param list<string> $lang_keys
     * @return list<string> the affected overlay directories
     */
    public static function findUnwritableOverlayDirectories(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        array $lang_keys,
        ?string $client_data_dir,
        ?int $for_user_id = null
    ): array {
        if ($client_data_dir === null) {
            return [];
        }

        $unwritable = [];
        foreach ($lang_keys as $lang_key) {
            foreach ($language_file_directory_manager->getDirectories() as $directory) {
                if ($directory->getPrefix() === '') {
                    continue;
                }
                $shipped_po = MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po';
                if (!is_file($shipped_po)) {
                    continue;
                }
                $overlay_directory = MigratedLanguageFilePaths::overlayDirectory($client_data_dir, $directory->getPrefix(), $lang_key);
                if (!self::isCreatableOrWritableDirectory($overlay_directory, $for_user_id)) {
                    $unwritable[$overlay_directory] = $overlay_directory;
                }
            }
        }

        return array_values($unwritable);
    }

    /**
     * Removes the overlay of $module/$lang_key as a whole (uninstalling a language or plugin): `current`, the
     * `.po`, every revision and the `lock` file, and the overlay directory if nothing else is left. The lock
     * file is deleted last while its lock is still held; a waiting process notices that and retries on a new
     * lock file (see acquireLock()). A no-op if $module is not a contributed directory or $client_data_dir is
     * `null`.
     */
    public static function removeOverlay(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir
    ): void {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return;
        }

        $overlay = self::overlayFiles($client_data_dir, $directory, $lang_key);
        if (!is_dir($overlay['directory']) || is_link($overlay['directory'])) {
            return;
        }
        self::lockAndRun(
            $directory,
            $lang_key,
            $client_data_dir,
            static function () use ($overlay, $module, $lang_key, $client_data_dir): void {
                self::removeOverlayFiles($overlay, $module, $lang_key, $client_data_dir);
                self::removeRevisions($overlay, $client_data_dir, []);
            },
            null,
            true
        );
        // left empty: the directory of the language and the one of the module (if nothing else)
        @rmdir($overlay['directory']);
        @rmdir(dirname($overlay['directory']));
    }

    /**
     * Runs $callback while holding an exclusive flock() on the `lock` file of the overlay of $module/$lang_key,
     * so concurrent writers (two administrators, Setup and the GUI) cannot interleave their read-modify-write
     * of the database row and the overlay files. Callers that merge onto the current state must read it inside
     * $callback. Re-entrant within a request (sync() and removeOverlay() take the lock themselves, also when
     * called from inside $callback).
     *
     * $callback runs without a lock if $language_file_directory_manager is `null`, $module is not a contributed
     * directory, $client_data_dir is `null`, the overlay directory does not exist and $module is not migrated
     * for $lang_key (no directory is created for a module without overlay), the lock file cannot be
     * created/opened (e.g. a directory the user cannot write to: the overlay write then fails and the caller
     * reports it), or the lock file was removed or replaced on each of LOCK_ATTEMPTS attempts (logged, see
     * acquireLock()). The lock file is only removed by removeOverlay() (uninstall) while holding its lock;
     * acquireLock() detects that.
     *
     * @template T
     * @param \Closure():T $callback
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @return T
     */
    public static function withOverlayLock(
        ?LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        \Closure $callback,
        ?string $ilias_absolute_path = null
    ): mixed {
        if ($language_file_directory_manager === null) {
            return $callback();
        }
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return $callback();
        }

        return self::lockAndRun($directory, $lang_key, $client_data_dir, $callback, $ilias_absolute_path, false);
    }

    /**
     * withOverlayLock() for a resolved $directory; with $remove_lock_file the lock file is deleted after
     * $callback succeeded, still under the lock, but only by the call that acquired it, never by a nested
     * (re-entrant) one.
     *
     * @template T
     * @param \Closure():T $callback
     * @return T
     */
    private static function lockAndRun(
        LanguageFileDirectory $directory,
        string $lang_key,
        string $client_data_dir,
        \Closure $callback,
        ?string $ilias_absolute_path,
        bool $remove_lock_file
    ): mixed {
        $lock_file = self::overlayFiles($client_data_dir, $directory, $lang_key)['lock'];
        if (isset(self::$held_locks[$lock_file])) {
            return $callback();
        }
        if (!is_dir(dirname($lock_file)) && !self::isShipped($ilias_absolute_path, $directory, $lang_key)) {
            return $callback();
        }

        return self::runWithLockFile($lock_file, $client_data_dir, $callback, $remove_lock_file);
    }

    /**
     * Runs $callback while holding the lock of $module's template (`.pot`, see
     * MigratedLanguageFilePaths::templateLockFile()): one lock for all languages, as the template is shared.
     * Taken after an overlay lock, never before (fixed order). Runs without a lock in the cases
     * withOverlayLock() does.
     *
     * @template T
     * @param \Closure():T $callback
     * @return T
     */
    public static function withTemplateLock(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $module,
        ?string $client_data_dir,
        \Closure $callback
    ): mixed {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return $callback();
        }
        $lock_file = MigratedLanguageFilePaths::templateLockFile($client_data_dir, $directory);
        if (isset(self::$held_locks[$lock_file])) {
            return $callback();
        }

        return self::runWithLockFile($lock_file, $client_data_dir, $callback, false);
    }

    /**
     * lockAndRun() once the lock file is determined.
     *
     * @template T
     * @param \Closure():T $callback
     * @return T
     */
    private static function runWithLockFile(string $lock_file, string $client_data_dir, \Closure $callback, bool $remove_lock_file): mixed
    {
        $handle = self::acquireLock($lock_file, $client_data_dir);
        if ($handle === null) {
            return $callback();
        }
        self::$held_locks[$lock_file] = true;
        try {
            $result = $callback();
            if ($remove_lock_file) {
                self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $lock_file);
                if (!@unlink($lock_file) && file_exists($lock_file)) {
                    throw new RuntimeException(sprintf('Could not remove the lock file "%s".', $lock_file));
                }
            }
            return $result;
        } finally {
            unset(self::$held_locks[$lock_file]);
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Opens and exclusively locks $lock_file. A lock on a file that removeOverlay() deleted or replaced while
     * this process waited excludes nobody (a newcomer locks the new file at that path), so after flock() the
     * handle must still be the file at the path (same inode and device, compared without following a link);
     * otherwise it is released and the whole attempt, including the symbolic link checks, is repeated.
     *
     * @return resource|null `null` if the lock file cannot be created or opened (the callback then runs
     *         unlocked; the overlay write fails for the same reason and the caller reports it), and, logged as
     *         a warning, if it was removed or replaced on every one of LOCK_ATTEMPTS attempts: the callback
     *         then runs unlocked instead of aborting a write whose `lng_data` part may already be done
     */
    private static function acquireLock(string $lock_file, string $client_data_dir)
    {
        for ($attempt = 1; $attempt <= self::LOCK_ATTEMPTS; $attempt++) {
            try {
                self::ensureDirectoryExists(dirname($lock_file), $client_data_dir);
                self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $lock_file);
            } catch (RuntimeException) {
                return null;
            }
            // "r" as fallback: flock() needs no write access, so a lock file created by another user
            // (e.g. a Setup run as root) still serializes
            $handle = @fopen($lock_file, 'c') ?: @fopen($lock_file, 'r');
            if ($handle === false) {
                // e.g. missing permission or too many open files - the callback runs unlocked
                self::logWarning(sprintf(
                    'Could not open the lock file "%s" (%s) - continuing without the lock.',
                    $lock_file,
                    error_get_last()['message'] ?? 'unknown error'
                ));
                return null;
            }
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
                return null;
            }
            if (self::isHandleOfPath($handle, $lock_file)) {
                return $handle;
            }
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        self::logWarning(sprintf(
            'Could not lock "%s": it was removed or replaced on each of %d attempts - continuing without the lock.',
            $lock_file,
            self::LOCK_ATTEMPTS
        ));

        return null;
    }

    /**
     * Logs to the `lang` component logger if available (not in every Setup context), else to the PHP error log.
     */
    private static function logWarning(string $message): void
    {
        global $DIC;
        if ($DIC instanceof \ILIAS\DI\Container && $DIC->offsetExists('ilLoggerFactory')) {
            $DIC->logger()->forComponent('lang')->warning($message);
            return;
        }
        error_log($message);
    }

    /**
     * @param resource $handle
     */
    private static function isHandleOfPath($handle, string $path): bool
    {
        clearstatcache(true, $path);
        $opened = fstat($handle);
        $current = @lstat($path);

        return $opened !== false
            && $current !== false
            && $opened['ino'] === $current['ino']
            && $opened['dev'] === $current['dev'];
    }


    /**
     * Removes `current` (first: from then on the overlay is not served) and the `.po` of $overlay; the callers
     * remove the revisions.
     *
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     */
    private static function removeOverlayFiles(array $overlay, string $module, string $lang_key, string $client_data_dir): void
    {
        // unlink() of a path with a symlinked directory component would delete outside the overlay
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay['po']);
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $overlay['current']);
        $removed_anything = false;
        foreach ([$overlay['current'], $overlay['po']] as $file) {
            if (!is_file($file)) {
                continue;
            }
            if (!unlink($file)) {
                throw new RuntimeException(sprintf('Could not remove overlay file "%s".', $file));
            }
            $removed_anything = true;
        }

        if ($removed_anything) {
            \ilLanguage::invalidateMigratedLanguageFileCache($module, $lang_key);
        }
    }

    /**
     * The content of $module/$lang_key as ilLanguage::txt() serves it (the shipped `.po` with the overlay delta
     * on top, see sync()), or `null` if the module is not migrated for $lang_key (no contributed directory, no
     * shipped `.po`) or $client_data_dir is `null`. The shipped values are the unprocessed ones of the `.po` (as
     * written to lng_data), not the markup-cleaned ones ilLanguage serves.
     *
     * An entry without overlay entry carries the shipped value, no local change and the shipped value as
     * "original". An overlay entry is returned as the overlay holds it, also one whose key is not shipped. The
     * overlay's `.po` is read (it carries the bookkeeping); an overlay `current` without it, or a path that is
     * no regular file, throws (see loadLocalChanges()).
     *
     * @param string|null $ilias_absolute_path where the shipped `.po` is looked up, defaults to
     *        ILIAS_ABSOLUTE_PATH (or this installation's root if that constant is not defined)
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>|null
     *         identifier => details; local_change_date in the database's "Y-m-d H:i:s" format
     * @throws RuntimeException if the shipped `.po` or the overlay `.po` cannot be read
     */
    public static function loadModuleTranslations(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): ?array {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || $client_data_dir === null) {
            return null;
        }
        $ilias_absolute_path ??= defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
        $shipped_po = MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po';
        if (!is_file($shipped_po)) {
            return null;
        }

        $result = [];
        $shipped = self::readShippedPo($shipped_po);
        foreach (self::flatModuleValues($shipped, $module) as $identifier => $value) {
            $result[$identifier] = [
                'value' => $value,
                'local_change' => false,
                'local_change_date' => null,
                'original' => $value,
            ];
        }

        return array_replace(
            $result,
            // a singular overlay entry of a message shipped as a plural one stands for its default form
            self::mapLegacyPluralValues(
                self::readOverlayEntries(
                    self::overlayFiles($client_data_dir, $directory, $lang_key),
                    $module,
                    static fn(): TranslationCatalog => $shipped
                ),
                $result
            )
        );
    }

    /**
     * The content the administration shows or edits, never from the database: that of loadModuleTranslations(),
     * without $client_data_dir the shipped `.po` alone. `null` where loadModuleTranslations() returns `null`.
     *
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @param bool $for_writing an unreadable overlay throws: a write onto the shipped values alone would drop
     *        its other local changes; otherwise (display) it is reported and the shipped `.po` alone returned
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>|null
     * @throws RuntimeException if the shipped `.po` (or for writing the overlay) cannot be read
     */
    public static function loadEditableModuleTranslations(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null,
        bool $for_writing = false
    ): ?array {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if ($directory === null || !MigratedLanguageFilePaths::isValidLanguageKey($lang_key)) {
            return null;
        }
        $ilias_absolute_path ??= defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
        $shipped_po = MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po';
        if (!is_file($shipped_po)) {
            return null;
        }
        if ($client_data_dir !== null) {
            try {
                return self::loadModuleTranslations($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
            } catch (\Throwable $t) {
                if ($for_writing) {
                    throw $t instanceof RuntimeException ? $t : new RuntimeException($t->getMessage(), 0, $t);
                }
                MigratedTranslations::reportProblem(sprintf(
                    'The local changes of module "%s" for "%s" cannot be read, the shipped values are used: %s',
                    $module,
                    $lang_key,
                    $t->getMessage()
                ));
            }
        }

        $result = [];
        foreach (self::flatModuleValues(self::readShippedPo($shipped_po), $module) as $identifier => $value) {
            $result[$identifier] = ['value' => $value, 'local_change' => false, 'local_change_date' => null, 'original' => $value];
        }

        return $result;
    }

    /**
     * Only the overlay delta of $module/$lang_key (see sync()), i.e. the entries loadModuleTranslations() takes
     * from the overlay; `[]` without overlay, `null` where loadModuleTranslations() returns `null`. The shipped
     * `.po` is only read for an overlay with a plural message: its forms are those of the shipped rule (see
     * withPluralRuleOf()).
     *
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>|null
     * @throws RuntimeException if the overlay cannot be read - also for an overlay `current` without its
     *         `.po` or an overlay path that is no regular file, which must not count as "no overlay"
     */
    public static function loadLocalChanges(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): ?array {
        $overlay = self::overlayIfShipped($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
        if ($overlay === null) {
            return null;
        }
        $ilias_absolute_path ??= defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);
        $directory = self::findDirectory($language_file_directory_manager, $module);

        // the plural forms follow the rule of the shipped `.po` (see withPluralRuleOf()), which is only parsed
        // for an overlay with a plural message
        return self::readOverlayEntries(
            $overlay,
            $module,
            $directory === null
                ? null
                : static fn(): TranslationCatalog => self::readShippedPo(
                    MigratedLanguageFilePaths::shippedBasePath((string) $ilias_absolute_path, $directory, $lang_key) . '.po'
                )
        );
    }

    /**
     * The overlay `.po` of $module/$lang_key as it is (values, remarks, LocalChangeComments bookkeeping); `null`
     * without overlay and where loadModuleTranslations() returns `null`. For callers that need more than the
     * values (e.g. ShippedPoMerger); read it under the overlay lock (see withOverlayLock()) if it is written
     * afterwards.
     *
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @throws RuntimeException see loadLocalChanges()
     */
    public static function readOverlayCatalog(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): ?TranslationCatalog {
        $overlay = self::overlayIfShipped($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
        if ($overlay === null) {
            return null;
        }
        self::assertReadableOverlay($overlay);

        return is_file($overlay['po']) ? TranslationCatalog::fromPoFile($overlay['po']) : null;
    }

    /**
     * The files of the overlay (see overlayFiles()) of $module/$lang_key; `null` where
     * loadModuleTranslations() returns `null` (no contributed directory, no $client_data_dir, or the module is
     * not shipped for $lang_key).
     *
     * @return array{directory: string, po: string, lock: string, current: string}|null
     */
    private static function overlayIfShipped(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        ?string $client_data_dir,
        ?string $ilias_absolute_path
    ): ?array {
        $directory = self::findDirectory($language_file_directory_manager, $module);
        if (
            $directory === null
            || $client_data_dir === null
            || !self::isShipped($ilias_absolute_path, $directory, $lang_key)
        ) {
            return null;
        }

        return self::overlayFiles($client_data_dir, $directory, $lang_key);
    }

    /**
     * @param array{directory: string, po: string, lock: string, current: string} $overlay
     * @param \Closure():TranslationCatalog|null $shipped the shipped `.po`: the plural forms are read
     *        for its rule (see withPluralRuleOf()), not for the one the overlay was written with -
     *        only called if the overlay has a plural message
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>
     * @throws RuntimeException see loadLocalChanges()
     */
    private static function readOverlayEntries(array $overlay, string $module, ?\Closure $shipped = null): array
    {
        self::assertReadableOverlay($overlay);
        if (!is_file($overlay['po'])) {
            return [];
        }

        $result = [];
        $catalog = TranslationCatalog::fromPoFile($overlay['po']);
        $has_plural = array_filter(
            self::moduleEntries($catalog, $module),
            static fn(TranslationEntry $entry): bool => $entry->isPlural()
        ) !== [];
        if ($shipped !== null && $has_plural) {
            try {
                $catalog = self::withPluralRuleOf($catalog, $shipped(), $module);
            } catch (RuntimeException $e) {
                // the shipped .po cannot be read: the overlay is read with its own rule, as written (and served)
                self::logWarning(PlainLogText::of(sprintf(
                    'The shipped PO file of module "%s" cannot be read, the plural forms of its overlay are read with the overlay\'s own rule: %s',
                    $module,
                    $e->getMessage()
                )));
            }
        }
        $plural_forms = self::pluralFormsOf($catalog);
        foreach (self::moduleEntries($catalog, $module) as $entry) {
            if (self::isRemarkOnly($entry)) {
                // carries a remark only (see sync()), no value - see loadRemarks()
                continue;
            }
            $is_local_change = LocalChangeComments::getLocalChange($entry) !== null;
            $local_change_date = LocalChangeComments::getLocalChangeAsDatabaseTimestamp($entry);
            if (!$entry->isPlural()) {
                $result[$entry->getId()] = [
                    'value' => $entry->getTranslation(),
                    'local_change' => $is_local_change,
                    'local_change_date' => $local_change_date,
                    'original' => LocalChangeComments::getOriginal($entry),
                ];
                continue;
            }
            // per form: only a form that differs from its "original" is a local change
            foreach (self::formsOf($entry, $plural_forms) as $form => $value) {
                $original = LocalChangeComments::getOriginal($entry, $form);
                $is_form_changed = $is_local_change && ($original === null || $original !== $value);
                $result[PluralFormKey::of($entry->getId(), $form)] = [
                    'value' => $value,
                    'local_change' => $is_form_changed,
                    'local_change_date' => $is_form_changed ? $local_change_date : null,
                    'original' => $original,
                ];
            }
        }

        return $result;
    }

    /**
     * Every module migrated for $lang_key (its shipped `.po` exists), with or without overlay (which only
     * exists for local changes). Empty if $client_data_dir is `null` (no overlay is maintained, see
     * loadModuleTranslations()).
     *
     * @param string|null $ilias_absolute_path see loadModuleTranslations()
     * @return list<string>
     */
    public static function getMigratedModules(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        ?string $client_data_dir,
        ?string $ilias_absolute_path = null
    ): array {
        if ($client_data_dir === null) {
            return [];
        }

        $modules = [];
        foreach ($language_file_directory_manager->getDirectories() as $directory) {
            if (
                $directory->getPrefix() !== ''
                && self::isShipped($ilias_absolute_path, $directory, $lang_key)
            ) {
                $modules[] = $directory->getPrefix();
            }
        }

        return $modules;
    }

    /**
     * TranslationCatalog::fromPoFile() for a shipped `.po`, parsed once per request while its content stays
     * unchanged (an installation reads each shipped `.po` several times). Only the files of one language are
     * kept: reading another language's file empties the cache, so an update over every language never holds more
     * than the modules of one. The catalog is shared: callers only read it (loadShippedModuleEntries() and
     * loadModuleTranslations() copy values into arrays), sync() works on a clone.
     *
     * @throws RuntimeException see TranslationCatalog::fromPoFile()
     */
    private static function readShippedPo(string $shipped_po): TranslationCatalog
    {
        $lang_key = preg_match('/_([a-z]{2})\.po\z/', $shipped_po, $matches) === 1 ? $matches[1] : '';
        if ($lang_key !== self::$shipped_catalogs_lang_key) {
            self::$shipped_catalogs = [];
            self::$shipped_catalogs_lang_key = $lang_key;
        }
        // The content hash, not the modification time: that only has a resolution of seconds
        $version = is_file($shipped_po) ? (string) @hash_file('xxh128', $shipped_po) : '';
        $cached = self::$shipped_catalogs[$shipped_po] ?? null;
        if ($cached !== null && $cached[0] === $version && $version !== '') {
            return $cached[1];
        }

        $catalog = TranslationCatalog::fromPoFile($shipped_po);
        unset(self::$shipped_catalogs[$shipped_po]);
        if (count(self::$shipped_catalogs) >= self::SHIPPED_CATALOG_CACHE_SIZE) {
            array_shift(self::$shipped_catalogs);
        }
        self::$shipped_catalogs[$shipped_po] = [$version, $catalog];

        return $catalog;
    }

    private static function isShipped(?string $ilias_absolute_path, LanguageFileDirectory $directory, string $lang_key): bool
    {
        $ilias_absolute_path ??= defined('ILIAS_ABSOLUTE_PATH') ? (string) ILIAS_ABSOLUTE_PATH : dirname(__DIR__, 5);

        return is_file(MigratedLanguageFilePaths::shippedBasePath($ilias_absolute_path, $directory, $lang_key) . '.po');
    }

    /**
     * The contributed directory of $module, `null` if there is none.
     */
    public static function findDirectory(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $module
    ): ?LanguageFileDirectory {
        if ($module === '') {
            return null;
        }
        foreach ($language_file_directory_manager->getDirectories() as $candidate) {
            if ($candidate->getPrefix() === $module) {
                return $candidate;
            }
        }

        return null;
    }

    private static function isCreatableOrWritableDirectory(string $directory, ?int $for_user_id): bool
    {
        $path = $directory;
        while (!file_exists($path)) {
            // A dangling symlink does not "exist", but mkdir() cannot create anything in its place
            if (is_link($path)) {
                return false;
            }
            $parent = dirname($path);
            if ($parent === $path) {
                return false;
            }
            $path = $parent;
        }

        return is_dir($path) && self::isWritableDirectoryFor($path, $for_user_id);
    }

    /**
     * Whether $for_user_id may create and replace files in the existing directory $path (write and
     * search permission). Root may always. Only the user's primary group and the groups listing the
     * user as a member are taken into account.
     */
    private static function isWritableDirectoryFor(string $path, ?int $for_user_id): bool
    {
        if ($for_user_id === null || !function_exists('posix_getpwuid') || !function_exists('posix_getgrgid')) {
            return is_writable($path);
        }
        if ($for_user_id === 0) {
            return true;
        }
        $stat = @stat($path);
        if ($stat === false) {
            return false;
        }
        $mode = $stat['mode'];
        if ($stat['uid'] === $for_user_id) {
            return ($mode & 0300) === 0300;
        }

        $user = @posix_getpwuid($for_user_id);
        $group = @posix_getgrgid($stat['gid']);
        $in_group = is_array($user) && (
            $user['gid'] === $stat['gid']
            || (is_array($group) && in_array($user['name'], $group['members'], true))
        );
        if ($in_group) {
            return ($mode & 0030) === 0030;
        }

        return ($mode & 0003) === 0003;
    }

    /**
     * The modules whose overlay is the only source of local changes (see markLocalChangesTakenOver()).
     * An unreadable file counts as none: the rows are taken over again rather than lost.
     *
     * @return list<string>
     */
    public static function modulesWithLocalChangesTakenOver(?string $client_data_dir, string $lang_key): array
    {
        if ($client_data_dir === null || !MigratedLanguageFilePaths::isValidLanguageKey($lang_key)) {
            return [];
        }
        $file = MigratedLanguageFilePaths::takenOverFile($client_data_dir, $lang_key);
        $content = is_file($file) && !is_link($file) ? @file_get_contents($file) : false;
        $modules = $content === false ? null : json_decode($content, true);

        return is_array($modules) && array_is_list($modules)
            ? array_values(array_filter($modules, static fn($module): bool => is_string($module) && MigratedLanguageFilePaths::isValidModule($module)))
            : [];
    }

    /**
     * Records that the lng_data local changes of $modules are in their overlays, so lng_data is not read again.
     *
     * @param list<string> $modules
     * @throws RuntimeException if the file cannot be written
     */
    public static function markLocalChangesTakenOver(string $client_data_dir, string $lang_key, array $modules): void
    {
        $root = self::overlayRoot($client_data_dir);
        if (!is_dir($root) && !@mkdir($root, 0755, true) && !is_dir($root)) {
            throw new RuntimeException(sprintf('Could not create directory "%s".', $root));
        }
        // two runs for the same language (Setup, administration) must not lose each other's modules
        self::runWithLockFile(
            MigratedLanguageFilePaths::takenOverLockFile($client_data_dir, $lang_key),
            $client_data_dir,
            static function () use ($client_data_dir, $lang_key, $modules, $root): void {
                $known = self::modulesWithLocalChangesTakenOver($client_data_dir, $lang_key);
                $all = array_values(array_unique([...$known, ...$modules]));
                sort($all, SORT_STRING);
                if ($all !== $known) {
                    AtomicFileWriter::write(MigratedLanguageFilePaths::takenOverFile($client_data_dir, $lang_key), json_encode($all, JSON_THROW_ON_ERROR) . "\n", $root);
                }
            },
            false
        );
    }

    /**
     * Forgets markLocalChangesTakenOver() for $lang_key (e.g. when uninstalled).
     */
    public static function forgetLocalChangesTakenOver(?string $client_data_dir, string $lang_key): void
    {
        if ($client_data_dir === null || !MigratedLanguageFilePaths::isValidLanguageKey($lang_key)) {
            return;
        }
        $file = MigratedLanguageFilePaths::takenOverFile($client_data_dir, $lang_key);
        if (is_file($file) && !is_link($file)) {
            @unlink($file);
        }
    }

    /**
     * Creates $directory (below the overlay root) if missing, refusing to create anything through a symbolic
     * link (see assertNoSymbolicLinkBelowOverlayRoot()).
     */
    private static function ensureDirectoryExists(string $directory, string $client_data_dir): void
    {
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $directory);
        if (is_dir($directory)) {
            return;
        }
        if (!@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Could not create directory "%s".', $directory));
        }
        // mkdir() follows a link that appeared in the meantime - checked again afterwards
        self::assertNoSymbolicLinkBelowOverlayRoot($client_data_dir, $directory);
    }

    /**
     * `<client data dir>/lang`, the root below which every overlay file lives.
     */
    private static function overlayRoot(string $client_data_dir): string
    {
        return rtrim($client_data_dir, '/') . '/lang';
    }

    /**
     * Symbolic links are never followed below the overlay root (CWE-59): no existing path component of $path
     * below `<client data dir>/lang` (itself included) may be a link, so a link in the client data directory
     * cannot redirect an overlay write, lock or removal outside. The overlay root itself and everything above
     * it is the administrator's configuration and may be a link.
     *
     * Accepted residual risk (TOCTOU, CWE-367): this checks path names and PHP has no openat()/O_NOFOLLOW to
     * bind the following fopen()/mkdir()/unlink() to the checked components; a link swapped in between is
     * followed. That needs write access below the overlay root (the web server user); writes are re-checked
     * afterwards (see AtomicFileWriter, ensureDirectoryExists(), acquireLock()), best effort only:
     * - a link swapped in and restored before such a re-check stays undetected;
     * - acquireLock()'s fopen($lock_file, 'c') follows a link swapped in after the check and can create an
     *   empty file at its target (the re-check only refuses to use it as lock).
     *
     * @throws RuntimeException for a link, or a $path outside the overlay root
     */
    private static function assertNoSymbolicLinkBelowOverlayRoot(string $client_data_dir, string $path): void
    {
        $root = self::overlayRoot($client_data_dir);
        if (!str_starts_with($path, $root . '/')) {
            throw new RuntimeException(sprintf('"%s" is not below the overlay directory "%s".', $path, $root));
        }

        $current = $root;
        foreach (explode('/', substr($path, strlen($root) + 1)) as $component) {
            if ($component === '' || $component === '.') {
                continue;
            }
            if ($component === '..') {
                throw new RuntimeException(sprintf('"%s" must not contain "..".', $path));
            }
            $current .= '/' . $component;
            if (is_link($current)) {
                throw new RuntimeException(sprintf('Refusing to follow the symbolic link "%s".', $current));
            }
            if (!file_exists($current)) {
                return;
            }
        }
    }
}
