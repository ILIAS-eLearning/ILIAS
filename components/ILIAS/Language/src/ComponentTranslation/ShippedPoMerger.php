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

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use RuntimeException;

/**
 * The "merge" maintenance action of developer mode (LANGMODE) for PO-maintained modules, the counterpart of
 * merging local changes into `lang/ilias_<lang>.lang`: the local changes of a module (its overlay, see
 * MigratedLanguageFileSync) are written into its shipped `.po`, the only time a shipped file is written at
 * runtime. Also the backups of the shipped `.po` files the admin GUI filter "conflicts" compares with (see
 * findShippedChangesSinceBackup()).
 *
 * Per module with overlay (in module name order, each under its overlay lock):
 * - a value (every form of a plural message) becomes the msgstr of its entry, "fuzzy" goes; a singular value
 *   of a plural message is its default form (PluralForms). An entry the shipped `.po` lacks (added locally)
 *   is inserted in id order, also into the template (`.pot`) with an empty msgstr (plural: msgid_plural of
 *   the template or "<identifier>_plural"); the `.po` of other languages stay unchanged;
 * - a remark (LocalChangeComments::setRemark()) becomes an extracted comment (`#.`) of its entry, appended
 *   unless identical, also for an entry that only carries a remark. "original"/"local_change" never go into
 *   a shipped file;
 * - a value with disallowed markup (TranslationMarkupPolicy), a plural message that cannot be compiled (empty
 *   msgstr[0] next to other forms, or a plural overlay entry for a singular shipped one) and a new identifier
 *   shaped "<identifier> [<n>]" (it would read as a plural form, see PluralFormKey) are not taken over: they
 *   stay in the overlay and are reported;
 * - before writing, the shipped `.po` is copied into the backup directory (named like the shipped file), then
 *   template and `.po` are written atomically (AtomicFileWriter, confined to the ILIAS directory: a symbolic
 *   link out of it is refused). The template has its own lock (MigratedLanguageFileSync::withTemplateLock(),
 *   taken after the overlay lock), as a merge of another language may write it concurrently. A module whose
 *   shipped `.po`, its directory, the backup directory or (for a new entry) the template is not writable is
 *   skipped as a whole and reported. The written files belong to the web server user afterwards (the rename
 *   of a new file takes over neither owner nor group);
 * - no build is run (the web server does not write `artifacts/`) and the overlay keeps its values: the runtime
 *   serves the build, which still holds the former shipped values. The taken-over entries are marked
 *   (LocalChangeComments::setMergedIntoShipped()), so other writes of the module keep them as long as the
 *   build does not serve the written `.po` (see MigratedLanguageFileSync::sync()); a failed marking is
 *   reported and made up for by the next merge. `php cli/setup.php build` and then `update` clean them up
 *   (its three-way reconciliation drops overlay entries equal to the new shipped value and remarks equal to
 *   an extracted comment).
 *
 * lng_data/lng_modules are not written here (the database belongs to the legacy classes): the caller gets
 * the taken-over identifiers per module, see merge().
 */
final class ShippedPoMerger
{
    /**
     * @param \Closure(string $module, list<string> $merged_identifiers, list<string> $merged_remark_identifiers): void|null $after_module
     *        called for every module whose shipped `.po` was written, under its overlay lock, with the
     *        identifiers whose value (a plural message under its identifier) and remark were taken over,
     *        so the caller can bring the database in line
     * @return array{
     *     written: array<string, string>,
     *     skipped: array<string, string>,
     *     invalid_markup: array<string, array<string, list<string>>>,
     *     not_merged: array<string, list<string>>,
     *     unwritten_database: array<string, string>,
     *     unmarked_overlay: array<string, string>
     *   written: module => path of the written shipped `.po`; skipped: module => reason; invalid_markup:
     *   module => identifier (a form key for a plural form) => violations; not_merged: module =>
     *   identifiers that could not be taken over; unwritten_database: module => reason, $after_module
     *   failed (the files are written); unmarked_overlay: module => reason, the taken-over entries could
     *   not be marked (files written, $after_module ran): another write before "setup build" may drop
     *   them from the overlay until the next merge marks them
     */
    public static function merge(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        ?string $client_data_dir,
        string $backup_directory,
        ?\Closure $after_module = null
    ): array {
        $result = [
            'written' => [],
            'skipped' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unwritten_database' => [],
            'unmarked_overlay' => [],
        ];
        if ($client_data_dir === null) {
            return $result;
        }

        $shipped_files = MigratedLanguageFileSync::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key);
        $backup_names = self::backupFileNames($shipped_files);
        // the fixed lock order, see MigratedLanguageFileSync::withOverlayLock()
        ksort($shipped_files, SORT_STRING);
        foreach ($shipped_files as $module => $shipped_po) {
            $module = (string) $module;
            if (!MigratedLanguageFileSync::hasOverlay($language_file_directory_manager, $lang_key, $module, $client_data_dir)) {
                continue;
            }
            if ($backup_names[$module] === null) {
                $result['skipped'][$module] = sprintf('The shipped file name "%s" is not unique among the modules, it cannot be backed up.', basename($shipped_po));
                continue;
            }
            $outcome = MigratedLanguageFileSync::withOverlayLock(
                $language_file_directory_manager,
                $lang_key,
                $module,
                $client_data_dir,
                static function () use ($language_file_directory_manager, $ilias_absolute_path, $lang_key, $module, $shipped_po, $client_data_dir, $backup_directory, $backup_names, $after_module): array {
                    try {
                        // the template is shared by all languages: its own lock, after the overlay lock
                        $outcome = MigratedLanguageFileSync::withTemplateLock(
                            $language_file_directory_manager,
                            $module,
                            $client_data_dir,
                            static fn(): array => self::mergeModule(
                                $language_file_directory_manager,
                                $ilias_absolute_path,
                                $lang_key,
                                $module,
                                $shipped_po,
                                $client_data_dir,
                                rtrim($backup_directory, '/') . '/' . $backup_names[$module]
                            )
                        );
                    } catch (RuntimeException|\InvalidArgumentException $e) {
                        return ['skipped' => $e->getMessage()];
                    }
                    if ($outcome['written'] && $after_module !== null) {
                        // the files are written - a failing database write must not stop the others
                        try {
                            $after_module($module, $outcome['merged'], $outcome['merged_remarks']);
                        } catch (\Throwable $t) {
                            $outcome['database_error'] = $t->getMessage();
                        }
                    }
                    return $outcome;
                },
                $ilias_absolute_path
            );

            if (isset($outcome['skipped'])) {
                $result['skipped'][$module] = $outcome['skipped'];
                continue;
            }
            if ($outcome['invalid_markup'] !== []) {
                $result['invalid_markup'][$module] = $outcome['invalid_markup'];
            }
            if ($outcome['not_merged'] !== []) {
                $result['not_merged'][$module] = $outcome['not_merged'];
            }
            if ($outcome['written']) {
                $result['written'][$module] = $shipped_po;
            }
            if (isset($outcome['database_error'])) {
                $result['unwritten_database'][$module] = $outcome['database_error'];
            }
            if ($outcome['unmarked'] !== null) {
                $result['unmarked_overlay'][$module] = $outcome['unmarked'];
            }
        }

        return $result;
    }

    /**
     * Copies the shipped `.po` of every module migrated for $lang_key into $directory under its shipped
     * file name: the counterpart of the `save_dist` backup of `lang/ilias_<lang>.lang`, for
     * findShippedChangesSinceBackup().
     *
     * @param string $confine_to_directory $directory must resolve to it or below (no symbolic link
     *        out of it, see AtomicFileWriter), e.g. the client data directory
     * @return list<string> the modules whose `.po` could not be copied (also a shipped file name
     *         that is not unique among the modules)
     */
    public static function backupShippedPoFiles(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        string $directory,
        string $confine_to_directory
    ): array {
        $shipped_files = MigratedLanguageFileSync::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key);
        $failed = [];
        foreach (self::backupFileNames($shipped_files) as $module => $name) {
            try {
                if ($name === null) {
                    throw new RuntimeException('file name not unique');
                }
                self::backup($shipped_files[$module], rtrim($directory, '/') . '/' . $name, $confine_to_directory);
            } catch (RuntimeException) {
                $failed[] = (string) $module;
            }
        }

        return $failed;
    }

    /**
     * What the shipped `.po` of every migrated module changed since its backup in $directory (see
     * backupShippedPoFiles()), like comparing `lang/ilias_<lang>.lang` with its backup: every entry (a
     * plural message as its forms, see PluralFormKey) that is new or differs from the backup. A module
     * without readable backup (or unreadable `.po`) is listed in `without_backup`.
     *
     * @return array{changes: array<string, array<string, string>>, without_backup: list<string>}
     *         changes: module => identifier => current shipped value
     */
    public static function findShippedChangesSinceBackup(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        string $directory
    ): array {
        $shipped_files = MigratedLanguageFileSync::findShippedModuleFiles($language_file_directory_manager, $ilias_absolute_path, $lang_key);
        $result = ['changes' => [], 'without_backup' => []];
        foreach (self::backupFileNames($shipped_files) as $module => $name) {
            $module = (string) $module;
            $backup = $name === null ? null : rtrim($directory, '/') . '/' . $name;
            try {
                if ($backup === null || !is_file($backup) || !is_readable($backup)) {
                    throw new RuntimeException('no backup');
                }
                $former = self::valuesOf(MigratedLanguageFileSync::loadShippedModuleEntries($backup, $module));
                $current = self::valuesOf(MigratedLanguageFileSync::loadShippedModuleEntries($shipped_files[$module], $module));
            } catch (RuntimeException) {
                $result['without_backup'][] = $module;
                continue;
            }
            $changes = array_diff_assoc($current, $former);
            if ($changes !== []) {
                $result['changes'][$module] = $changes;
            }
        }

        return $result;
    }

    /**
     * merge() for one module, under its overlay lock and the template lock of the module.
     *
     * @return array{written: bool, merged: list<string>, merged_remarks: list<string>, invalid_markup: array<string, list<string>>, not_merged: list<string>, unmarked: ?string}
     *         unmarked: why the taken over entries could not be marked in the overlay
     * @throws RuntimeException|\InvalidArgumentException if the module has to be skipped as a whole
     */
    private static function mergeModule(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $ilias_absolute_path,
        string $lang_key,
        string $module,
        string $shipped_po,
        string $client_data_dir,
        string $backup_file
    ): array {
        $outcome = [
            'written' => false,
            'merged' => [],
            'merged_remarks' => [],
            'invalid_markup' => [],
            'not_merged' => [],
            'unmarked' => null,
        ];
        // read under the lock - it may have changed since hasOverlay()
        $overlay = MigratedLanguageFileSync::readOverlayCatalog($language_file_directory_manager, $lang_key, $module, $client_data_dir, $ilias_absolute_path);
        $directory = MigratedLanguageFileSync::findDirectory($language_file_directory_manager, $module);
        if ($overlay === null || $directory === null) {
            return $outcome;
        }
        $shipped = TranslationCatalog::fromPoFile($shipped_po);
        // an overlay written under another plural rule: its forms by meaning, see withPluralRuleOf()
        $overlay = MigratedLanguageFileSync::withPluralRuleOf($overlay, $shipped, $module);
        $overlay_entries = MigratedLanguageFileSync::moduleEntries($overlay, $module);

        // Everything that has to be writable, checked before anything is written
        self::assertWritableFile($shipped_po, $ilias_absolute_path);
        self::assertWritableFile($backup_file, $ilias_absolute_path);
        $template_file = MigratedLanguageFilePaths::shippedTemplatePath($ilias_absolute_path, $directory);
        $template = null;
        foreach ($overlay_entries as $identifier => $entry) {
            if (
                !MigratedLanguageFileSync::isRemarkOnly($entry)
                && PluralFormKey::parse((string) $identifier) === null
                && MigratedLanguageFileSync::findModuleEntry($shipped, $module, (string) $identifier) === null
            ) {
                if (!is_file($template_file)) {
                    throw new RuntimeException(sprintf('The template "%s" for the entries added locally is missing.', $template_file));
                }
                self::assertWritableFile($template_file, $ilias_absolute_path);
                $template = TranslationCatalog::fromPoFile($template_file);
                break;
            }
        }

        $plural_forms = MigratedLanguageFileSync::pluralFormsOf($shipped);
        $policy = new TranslationMarkupPolicy();
        $template_changed = false;
        foreach ($overlay_entries as $identifier => $overlay_entry) {
            $identifier = (string) $identifier;
            $shipped_entry = MigratedLanguageFileSync::findModuleEntry($shipped, $module, $identifier);
            $remark = LocalChangeComments::getRemark($overlay_entry);
            $has_value = !MigratedLanguageFileSync::isRemarkOnly($overlay_entry);

            if ($has_value) {
                // a new identifier shaped like a plural form (see PluralFormKey) would be read as one: not added
                $message = $shipped_entry === null && PluralFormKey::parse($identifier) !== null
                    ? null
                    : self::mergedMessage($overlay_entry, $shipped_entry, $template, $module, $plural_forms);
                if ($message === null) {
                    $outcome['not_merged'][] = $identifier;
                    continue;
                }
                // only a value the shipped .po does not carry yet is checked, see findInvalidChangedValues()
                $shipped_values = $shipped_entry === null ? [] : MigratedLanguageFileSync::flatValuesOf($shipped_entry, $plural_forms);
                $invalid = $policy->findInvalidChangedValues(self::flatValues($identifier, $message), $shipped_values, $shipped_values);
                if ($invalid !== []) {
                    $outcome['invalid_markup'] += $invalid;
                    continue;
                }

                $target = $shipped_entry ?? new TranslationEntry(null, $identifier);
                if ($message['plural_id'] === null) {
                    $target->translate($message['forms'][0]);
                } else {
                    $target->setPlural($message['plural_id'], $message['forms']);
                }
                $target->removeFlag('fuzzy');
                if ($shipped_entry === null) {
                    $shipped->addInIdOrder($target);
                    $template_changed = self::addToTemplate($template, $module, $identifier, $message['plural_id']) || $template_changed;
                }
                $outcome['merged'][] = $identifier;
                $shipped_entry = $target;
            }

            if ($remark !== null && $remark !== '') {
                if ($shipped_entry === null) {
                    // the remark of an entry without value that is not shipped - nothing to put it on
                    continue;
                }
                $shipped_entry->addExtractedComment($remark);
                $outcome['merged_remarks'][] = $identifier;
            }
        }

        if ($outcome['merged'] === [] && $outcome['merged_remarks'] === []) {
            return $outcome;
        }

        $po_content = self::checkedPoContent($shipped, true);
        $template_content = $template_changed && $template !== null ? self::checkedPoContent($template, false) : null;
        $previous_content = @file_get_contents($shipped_po);
        if ($previous_content === false) {
            throw new RuntimeException(sprintf('Could not read "%s".', $shipped_po));
        }
        if ($po_content === $previous_content && $template_content === null) {
            // taken over before (the overlay keeps it until "setup update"): nothing to write - only
            // a marking that failed then is made up for (a marked entry stays as it is)
            $outcome['unmarked'] = self::markMerged($language_file_directory_manager, $lang_key, $module, $outcome['merged'], $client_data_dir, $ilias_absolute_path);
            $outcome['merged'] = [];
            $outcome['merged_remarks'] = [];
            return $outcome;
        }
        // confined to the ILIAS directory: a symbolic link out of it is refused, nothing is written
        self::backup($shipped_po, $backup_file, $ilias_absolute_path, $previous_content);
        if ($template_content !== null) {
            // the template first: an additional entry there is harmless should the .po fail
            AtomicFileWriter::write($template_file, $template_content, $ilias_absolute_path);
        }
        AtomicFileWriter::write($shipped_po, $po_content, $ilias_absolute_path);
        $outcome['written'] = true;
        // the overlay keeps its values and the taken-over entries are marked, see the class docblock
        $outcome['unmarked'] = self::markMerged($language_file_directory_manager, $lang_key, $module, $outcome['merged'], $client_data_dir, $ilias_absolute_path);

        return $outcome;
    }

    /**
     * MigratedLanguageFileSync::markMergedIntoShipped() for the files just written (or written by an
     * earlier merge): a failure must not hide that they are - the next merge makes up for it.
     *
     * @param list<string> $identifiers
     * @return string|null why the entries could not be marked
     */
    private static function markMerged(
        LanguageFileDirectoryManager $language_file_directory_manager,
        string $lang_key,
        string $module,
        array $identifiers,
        string $client_data_dir,
        string $ilias_absolute_path
    ): ?string {
        try {
            MigratedLanguageFileSync::markMergedIntoShipped(
                $language_file_directory_manager,
                $lang_key,
                $module,
                $identifiers,
                $client_data_dir,
                $ilias_absolute_path
            );
        } catch (RuntimeException|\InvalidArgumentException $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * The message $overlay_entry makes of its shipped entry: a singular value, or every form of a plural
     * message (a singular value of a shipped plural message is its default form). `null` if that is no
     * compilable plural message: a plural overlay entry for a singular shipped one, or an empty msgstr[0]
     * next to other forms (see TranslationCatalog::toMoString()).
     *
     * @return array{plural_id: ?string, forms: list<string>}|null
     */
    private static function mergedMessage(
        TranslationEntry $overlay_entry,
        ?TranslationEntry $shipped_entry,
        ?TranslationCatalog $template,
        string $module,
        PluralForms $plural_forms
    ): ?array {
        if (!$overlay_entry->isPlural() && !$shipped_entry?->isPlural()) {
            return ['plural_id' => null, 'forms' => [$overlay_entry->getTranslation()]];
        }
        if ($overlay_entry->isPlural() && $shipped_entry !== null && !$shipped_entry->isPlural()) {
            return null;
        }

        if ($overlay_entry->isPlural()) {
            $forms = array_values(MigratedLanguageFileSync::flatValuesOf($overlay_entry, $plural_forms));
        } else {
            $forms = array_values(MigratedLanguageFileSync::flatValuesOf($shipped_entry, $plural_forms));
            $forms[$plural_forms->defaultFormIndex()] = $overlay_entry->getTranslation();
        }
        if ($forms[0] === '' && implode('', $forms) !== '') {
            return null;
        }
        $template_entry = $template === null ? null : MigratedLanguageFileSync::findModuleEntry($template, $module, $overlay_entry->getId());

        return [
            'plural_id' => $shipped_entry?->getPluralId()
                ?? $template_entry?->getPluralId()
                ?? $overlay_entry->getId() . '_plural',
            'forms' => $forms,
        ];
    }

    /**
     * Adds the locally added entry $identifier to the template without msgstr (a plural message with two
     * empty forms, like the conversion tool), unless it has it already.
     *
     * @return bool whether the template changed
     */
    private static function addToTemplate(?TranslationCatalog $template, string $module, string $identifier, ?string $plural_id): bool
    {
        if ($template === null || MigratedLanguageFileSync::findModuleEntry($template, $module, $identifier) !== null) {
            return false;
        }
        $entry = new TranslationEntry(null, $identifier);
        if ($plural_id !== null) {
            $entry->setPlural($plural_id, ['', '']);
        }
        $template->addInIdOrder($entry);

        return true;
    }

    /**
     * @param array{plural_id: ?string, forms: list<string>} $message
     * @return array<string, string> identifier => value, a plural message as its form keys
     */
    private static function flatValues(string $identifier, array $message): array
    {
        if ($message['plural_id'] === null) {
            return [$identifier => $message['forms'][0]];
        }
        $values = [];
        foreach ($message['forms'] as $form => $value) {
            $values[PluralFormKey::of($identifier, $form)] = $value;
        }

        return $values;
    }

    /**
     * $catalog as `.po` content, after making sure it can be read back and, for a language's `.po`,
     * compiled like the build does.
     *
     * @throws RuntimeException otherwise
     */
    private static function checkedPoContent(TranslationCatalog $catalog, bool $compile): string
    {
        $content = $catalog->toPoString();
        if (!mb_check_encoding($content, 'UTF-8')) {
            throw new RuntimeException('The merged file would not be valid UTF-8.');
        }
        TranslationCatalog::fromPoString($content);
        if ($compile) {
            $catalog->toMoString();
        }

        return $content;
    }

    /**
     * Copies $shipped_po (or $content) to $backup_file atomically, confined to $confine_to_directory
     * (see AtomicFileWriter).
     *
     * @throws RuntimeException
     */
    private static function backup(string $shipped_po, string $backup_file, string $confine_to_directory, ?string $content = null): void
    {
        $content ??= @file_get_contents($shipped_po);
        if ($content === false) {
            throw new RuntimeException(sprintf('Could not read "%s".', $shipped_po));
        }
        if (!is_dir(dirname($backup_file))) {
            throw new RuntimeException(sprintf('The backup directory "%s" does not exist.', dirname($backup_file)));
        }
        AtomicFileWriter::write($backup_file, $content, $confine_to_directory);
    }

    /**
     * @throws RuntimeException unless $file can be replaced atomically: its directory resolves to
     *         $confine_to_directory or below (checked again by AtomicFileWriter), is writable, and so
     *         is the file itself if it exists (a web server may not write the shipped files at all) -
     *         checked for every file before anything is written
     */
    private static function assertWritableFile(string $file, string $confine_to_directory): void
    {
        $root = realpath($confine_to_directory);
        $directory = realpath(dirname($file));
        if (
            $root === false
            || $directory === false
            || ($directory !== $root && !str_starts_with($directory, rtrim($root, '/') . '/'))
            || !is_dir(dirname($file))
            || !is_writable(dirname($file))
            || is_link($file)
            || (file_exists($file) && (!is_file($file) || !is_writable($file)))
        ) {
            throw new RuntimeException(sprintf('"%s" is not writable or does not resolve to a path below "%s".', $file, $confine_to_directory));
        }
    }

    /**
     * The backup name of each shipped `.po` of $shipped_files (module => path): its file name, `null`
     * where two modules share one.
     *
     * @param array<string, string> $shipped_files
     * @return array<string, ?string>
     */
    private static function backupFileNames(array $shipped_files): array
    {
        $counts = array_count_values(array_map('basename', $shipped_files));
        $names = [];
        foreach ($shipped_files as $module => $file) {
            $names[(string) $module] = $counts[basename($file)] === 1 ? basename($file) : null;
        }

        return $names;
    }

    /**
     * @param array<string, array{value: string, comment: ?string}> $entries
     * @return array<string, string>
     */
    private static function valuesOf(array $entries): array
    {
        return array_map(static fn(array $entry): string => $entry['value'], $entries);
    }
}
