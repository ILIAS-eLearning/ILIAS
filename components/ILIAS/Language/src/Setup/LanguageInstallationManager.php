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

namespace ILIAS\Language\Setup;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LegacyFuzzyMarker;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\ComponentTranslation\TranslationMarkupPolicy;
use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\Logging\Logger\LoggerInterface;

/**
 * Write access to the language installation: installing, flushing and registering languages in the
 * database. Reading lives in InstalledLanguageRepository.
 */
class LanguageInstallationManager
{
    use LanguageFileParsing;

    private const string SEPARATOR = "#:#";
    private const string COMMENT_SEPARATOR = "###";

    /**
     * @var array<string, array<string, list<string>>> see getSkippedInvalidMarkupEntries()
     */
    private array $skipped_invalid_markup_entries = [];

    /**
     * @var array<string, list<string>> language key => modules, see getSkippedUnreadableOverlays()
     */
    private array $skipped_unreadable_overlays = [];

    /**
     * @var array<string, list<string>> language key => modules
     */
    private array $overlays_of_unshipped_modules = [];

    /**
     * @var array<string, list<string>> language key => modules
     */
    private array $unrecorded_taken_over_modules = [];
    private ?TranslationMarkupPolicy $markup_policy = null;

    /**
     * @param (\Closure():\DateTimeImmutable)|null $now injectable clock (UTC now by default), for tests
     * @param (\Closure():?string)|null $client_data_dir_resolver resolved on every call, as no client may
     *        exist at construction time; `null` = no overlay is maintained, the database is written regardless
     * @param LoggerInterface|null $logger `null` in the Setup: warnings go to the PHP error log
     */
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly string $absolute_path,
        private readonly InstalledLanguageRepository $repository,
        private readonly ?\Closure $now = null,
        private readonly TranslationCache $translation_cache = new NullTranslationCache(),
        private readonly ?\Closure $client_data_dir_resolver = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    private function logWarning(string $message): void
    {
        if ($this->logger !== null) {
            try {
                $this->logger->warning($message);
                return;
            } catch (\Throwable) {
                // a failing logger must not abort the installation
            }
        }
        error_log($message);
    }

    /**
     * Entries of the customizing/local files that the insertLanguageFor...() calls of this instance left
     * out because of markup TranslationMarkupPolicy does not allow.
     *
     * @return array<string, array<string, list<string>>> language key => module#:#identifier => violations
     */
    public function getSkippedInvalidMarkupEntries(): array
    {
        return $this->skipped_invalid_markup_entries;
    }

    /**
     * The migrated modules left out of the insertLanguageFor...() calls because their overlay cannot be read.
     *
     * @return array<string, list<string>> language key => modules
     */
    public function getSkippedUnreadableOverlays(): array
    {
        return $this->skipped_unreadable_overlays;
    }

    /**
     * Modules no longer shipped for the language that still have an overlay or a taken-over record. Only reported:
     * once migrated again, they would win over lng_data changes made in between.
     *
     * @return array<string, list<string>> language key => modules
     */
    public function getOverlaysOfUnshippedModules(): array
    {
        return $this->overlays_of_unshipped_modules;
    }

    /**
     * Modules whose taken-over record could not be written: they are taken over again by the next run.
     *
     * @return array<string, list<string>> language key => modules
     */
    public function getUnrecordedTakenOverModules(): array
    {
        return $this->unrecorded_taken_over_modules;
    }

    private function markupPolicy(): TranslationMarkupPolicy
    {
        return $this->markup_policy ??= new TranslationMarkupPolicy();
    }

    /**
     * The violations of a customizing $value, only if it changes something (see
     * TranslationMarkupPolicy::findInvalidChangedValues()).
     *
     * @return list<string>
     */
    private function invalidMarkupOfCustomizingValue(string $value, ?string $current, ?string $shipped): array
    {
        return $this->markupPolicy()->findInvalidChangedValues(
            ['value' => $value],
            $current === null ? [] : ['value' => $current],
            $shipped === null ? [] : ['value' => $shipped]
        )['value'] ?? [];
    }

    private function clientDataDir(): ?string
    {
        return $this->client_data_dir_resolver !== null ? ($this->client_data_dir_resolver)() : null;
    }

    private function utcTimestamp(?int $unix_timestamp = null): string
    {
        if ($unix_timestamp !== null) {
            return gmdate("Y-m-d H:i:s", $unix_timestamp);
        }
        $now = $this->now !== null ? ($this->now)() : new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Installs the given languages, flushes all other known ones. Mirrors ilSetupLanguage::installLanguages().
     *
     * @param list<string> $lang_keys
     * @param list<string> $local_keys unused, kept for backwards compatibility (as in ilSetupLanguage)
     * @return list<string>|bool list of language keys that failed validation, or true
     */
    public function installLanguages(array $lang_keys, array $local_keys = []): array|bool
    {
        $ilDB = $this->db;

        $err_lang = [];
        $db_langs = $this->repository->getAvailableLanguages();
        $local_langs = $this->repository->getLocalLanguages();

        foreach ($lang_keys as $lang_key) {
            if ($this->repository->checkLanguage($lang_key)) {
                $this->flushLanguage($lang_key, "keep_local");
                $this->insertLanguageForInstallation($lang_key);

                // an already-known language is (re-)registered below
                if (!array_key_exists($lang_key, $db_langs)) {
                    $this->registerInstalledLanguage($lang_key, $db_langs, $local_langs);
                }
            } else {
                $err_lang[] = $lang_key;
            }
        }

        foreach ($db_langs as $key => $val) {
            if (!in_array($key, $err_lang, true)) {
                if (in_array($key, $lang_keys, true)) {
                    $this->registerInstalledLanguage($key, $db_langs, $local_langs);
                } else {
                    $this->flushLanguage($key, "all");
                    $this->removeOverlays((string) $key);

                    if (strpos($val["status"], "installed") === 0) {
                        $query = "UPDATE object_data SET " .
                            "description = " . $ilDB->quote("not_installed", "text") . ", " .
                            "last_update = " . $ilDB->quote($this->utcTimestamp(), "timestamp") . " " .
                            "WHERE obj_id = " . $ilDB->quote($val["obj_id"], "integer") . " " .
                            "AND type = " . $ilDB->quote("lng", "text");
                        $ilDB->manipulate($query);
                    }
                }
            }
        }

        return ($err_lang) ?: true;
    }

    /**
     * Registers or updates the object_data row of a (re-)inserted language ("installed" vs.
     * "installed_local"); single place for this bookkeeping of installLanguages() and InstallLanguage::perform().
     *
     * @param array<string, array{obj_id:int, status:string}> $known_languages result of
     *        InstalledLanguageRepository::getAvailableLanguages()
     * @param list<string> $local_language_keys result of
     *        InstalledLanguageRepository::getLocalLanguages()
     */
    public function registerInstalledLanguage(
        string $lang_key,
        array $known_languages,
        array $local_language_keys
    ): void {
        $db = $this->db;
        $installation_type = in_array($lang_key, $local_language_keys, true) ? "installed_local" : "installed";

        if (!array_key_exists($lang_key, $known_languages)) {
            $language_id = $db->nextId("object_data");
            $query = "INSERT INTO object_data " .
                    "(obj_id,type,title,description,owner,create_date,last_update) " .
                    "VALUES " .
                    "(" .
                    $db->quote($language_id, "integer") . "," .
                    $db->quote("lng", "text") . "," .
                    $db->quote($lang_key, "text") . "," .
                    $db->quote($installation_type, "text") . "," .
                    $db->quote("-1", "integer") . "," .
                    $db->now() . "," .
                    $db->now() .
                    ")";
            $db->manipulate($query);
            return;
        }

        $query = "UPDATE object_data SET " .
                "description = " . $db->quote($installation_type, "text") . ", " .
                "last_update = " . $db->quote($this->utcTimestamp(), "timestamp") . " " .
                "WHERE obj_id = " . $db->quote($known_languages[$lang_key]["obj_id"], "integer") . " " .
                "AND type = " . $db->quote("lng", "text");
        $db->manipulate($query);
    }

    /**
     * The overlay counterpart of flushLanguage($lang_key, "all") for a language that is no longer
     * installed - see ilObjLanguage::uninstall(). A failure is logged and does not undo the flush.
     */
    private function removeOverlays(string $lang_key): void
    {
        $client_data_dir = $this->clientDataDir();
        MigratedLanguageFileSync::forgetLocalChangesTakenOver($client_data_dir, $lang_key);
        foreach ($this->language_file_directory_manager->getDirectories() as $directory) {
            $module = $directory->getPrefix();
            try {
                MigratedLanguageFileSync::removeOverlay(
                    $this->language_file_directory_manager,
                    $lang_key,
                    $module,
                    $client_data_dir
                );
            } catch (\Throwable $t) {
                $this->logWarning(sprintf(
                    'Could not remove the overlay of migrated module "%s", language "%s": %s',
                    $module,
                    $lang_key,
                    $t->getMessage()
                ));
            }
        }
    }

    public function flushLanguageForInstallation(string $lang_key): void
    {
        $this->flushLanguage($lang_key, "keep_local");
    }

    public function flushLanguageForUninstallation(string $lang_key): void
    {
        $this->flushLanguage($lang_key, "all");
    }

    /**
     * @param string $mode either "all" or "keep_local"
     */
    private function flushLanguage(string $lang_key, string $mode = "all"): void
    {
        $this->deleteLangData($lang_key, ($mode === "keep_local"));

        if ($mode === "all") {
            $this->db->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                $this->db->quote($lang_key, "text"));
            $this->invalidateLanguageCache($lang_key);
        }
    }

    private function deleteLangData(string $lang_key, bool $keep_local_change): void
    {
        $ilDB = $this->db;

        if (!$keep_local_change) {
            $ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = " .
                $ilDB->quote($lang_key, "text"));
        } else {
            $ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = " .
                $ilDB->quote($lang_key, "text") .
                " AND local_change IS NULL");
        }
    }

    /**
     * Installs or refreshes a language from every directory the LanguageFileDirectoryManager knows
     * (including customizing/local), merged on top of the recorded local changes.
     *
     * A migrated module's shipped `.po` is the only source of its shipped values; a three-way comparison
     * per entry decides shipped vs. local (see resolveMigratedModule()).
     *
     * @return list<string> the migrated modules whose overlay could not be written (see
     *         insertLanguage()) - empty if every overlay is in sync
     */
    public function insertLanguageForInstallation(string $lang_key): array
    {
        return $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getAllDirectories(),
            $this->repository->getLocalChanges($lang_key),
            true,
            true
        );
    }

    /**
     * Re-seeds a language purely from the shipped global/component files (`.lang`, shipped `.po`), without
     * customizing/local files and local changes: the write path of "remove local changes".
     * insertLanguageForInstallation() would re-apply the customizing file.
     *
     * Also removes the overlay of every migrated module, as no delta to the shipped `.po` is left (the
     * counterpart of the caller's flush of lng_data/lng_modules).
     *
     * @return list<string> see insertLanguageForInstallation()
     */
    public function insertLanguageForRemovingLocalChanges(string $lang_key): array
    {
        return $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getDirectories(),
            [],
            true,
            false
        );
    }

    /**
     * Applies only the customizing/local file on top of an installed language, leaving the base data
     * untouched: the write path of "Install local" (see InstallLanguage::perform()). The caller flushes nothing.
     *
     * The seed must hold every stored entry, not only the local changes: insertLanguage() rebuilds the
     * lng_modules row of each module from the seed plus the given directories, so a narrower seed would
     * silently drop base entries the customizing file does not override.
     *
     * Migrated modules are the exception: they are reconciled with their shipped `.po` as in
     * insertLanguageForInstallation() (see resolveMigratedModule()) and seeded with their local changes
     * only. Seeding every stored entry would turn a stale value into a permanent "local change" and lose
     * the shipped update.
     *
     * @return list<string> see insertLanguageForInstallation()
     */
    public function insertLanguageForApplyingLocalChanges(string $lang_key): array
    {
        $entries = $this->repository->getLanguageEntries($lang_key);
        $local_changes = $this->repository->getLocalChanges($lang_key);
        $migrated_modules = array_keys(MigratedLanguageFileSync::findShippedModuleFiles(
            $this->language_file_directory_manager,
            $this->absolute_path,
            $lang_key
        ));
        foreach ($migrated_modules as $module) {
            // an empty seed is fine: insertLanguage() drops a module that ends up without entries
            $entries[$module] = $local_changes[$module] ?? [];
        }

        return $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getCustomizingDirectories(),
            $entries,
            true,
            true,
            true
        );
    }


    /**
     * The overlay directories of the modules migrated for $lang_keys that cannot be written, see
     * MigratedLanguageFileSync::findUnwritableOverlayDirectories(). To be checked before installing,
     * so a missing permission is reported up front instead of only logged per module.
     *
     * @param list<string> $lang_keys
     * @param int|null $for_user_id see MigratedLanguageFileSync::findUnwritableOverlayDirectories()
     * @return list<string>
     */
    public function findUnwritableOverlayDirectories(array $lang_keys, ?int $for_user_id = null): array
    {
        return MigratedLanguageFileSync::findUnwritableOverlayDirectories(
            $this->language_file_directory_manager,
            $this->absolute_path,
            $lang_keys,
            $this->clientDataDir(),
            $for_user_id
        );
    }

    /**
     * Writes the data of the given $directories/$lang_array seed; the callers above decide both.
     *
     * Of a migrated module (see MigratedLanguageFileSync) whose shipped `.po` is merged
     * ($merge_shipped_migrated_modules), the `.lang` lines of the global/component files are ignored and each
     * shipped entry is reconciled via resolveMigratedModule(); customizing lines still apply as local changes.
     *
     * Afterwards the stored module arrays are verified (collation, mantis #20046/#19140), the language
     * cache is invalidated and each migrated overlay is synced to the final content: only the delta to the
     * shipped `.po` is written, see MigratedLanguageFileSync::sync().
     *
     * Concurrent writes: every migrated module that has an overlay is locked for the whole run
     * (MigratedLanguageFileSync::withOverlayLock()). A module without overlay is not locked; if one appears
     * meanwhile, its sync() is refused and the module reported (see $expected_overlay_exists of sync()).
     *
     * @param iterable<LanguageFileDirectory> $directories
     * @param array<string, array<string, string>> $lang_array module => identifier => value
     * @param bool $keep_local_changes whether local changes recorded only in a migrated module's overlay
     *        count as local changes (false for "remove local changes")
     * @param bool $delete_unchanged_migrated_rows delete the not locally changed lng_data rows of migrated
     *        modules that are no longer written from the shipped `.po`, for callers that did not flush first
     * @return list<string> the migrated modules whose overlay could not be written; the database write
     *         is not undone for them
     */
    private function insertLanguage(
        string $lang_key,
        iterable $directories,
        array $lang_array,
        bool $merge_shipped_migrated_modules,
        bool $keep_local_changes,
        bool $delete_unchanged_migrated_rows = false
    ): array {
        // Reported per call (see getSkippedInvalidMarkupEntries()): only what this run left out
        unset(
            $this->skipped_invalid_markup_entries[$lang_key],
            $this->skipped_unreadable_overlays[$lang_key],
            $this->overlays_of_unshipped_modules[$lang_key],
            $this->unrecorded_taken_over_modules[$lang_key]
        );
        $client_data_dir = $this->clientDataDir();
        $modules_with_local_changes = array_map('strval', array_keys(array_filter($lang_array)));
        $shipped_migrated_modules = $merge_shipped_migrated_modules
            ? MigratedLanguageFileSync::loadShippedModules(
                $this->language_file_directory_manager,
                $this->absolute_path,
                $lang_key
            )
            : [];
        $this->findOverlaysOfUnshippedModules(
            $lang_key,
            $client_data_dir,
            $merge_shipped_migrated_modules ? array_map('strval', array_keys($shipped_migrated_modules)) : null
        );

        $locked_modules = [];
        foreach (array_keys($shipped_migrated_modules) as $module) {
            if (MigratedLanguageFileSync::hasOverlay(
                $this->language_file_directory_manager,
                $lang_key,
                (string) $module,
                $client_data_dir
            )) {
                $locked_modules[] = (string) $module;
            }
        }
        // locks are always acquired in this global order (module names), not in getDirectories() order,
        // which differs between entry points: other orders would deadlock (flock() has no timeout)
        sort($locked_modules, SORT_STRING);

        // lng_data changes are taken over once; with an overlay, or once taken over, it is their only source.
        // A module with an unreadable overlay is left out completely.
        $taken_over = MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($client_data_dir, $lang_key);
        $overlay_only_modules = [];
        $skipped_modules = [];
        foreach (array_keys($shipped_migrated_modules) as $module) {
            $module = (string) $module;
            $has_overlay = in_array($module, $locked_modules, true);
            if ($has_overlay && !$this->isReadableOverlay($lang_key, $module, $client_data_dir)) {
                $skipped_modules[] = $module;
                unset($lang_array[$module]);
            } elseif ($has_overlay || in_array($module, $taken_over, true)) {
                $overlay_only_modules[] = $module;
                if (isset($lang_array[$module])) {
                    $lang_array[$module] = [];
                }
            }
        }
        if ($skipped_modules !== []) {
            $this->skipped_unreadable_overlays[$lang_key] = $skipped_modules;
            $this->logWarning(sprintf(
                'The overlay of the modules %s of language "%s" cannot be read: they are left unchanged, their local changes are only served again once the overlay is repaired; until then their rollback copy in lng_data/lng_modules is incomplete.',
                implode(', ', $skipped_modules),
                $lang_key
            ));
            $shipped_migrated_modules = array_diff_key($shipped_migrated_modules, array_flip($skipped_modules));
        }

        $write = fn(): array => $this->writeLanguage(
            $lang_key,
            $directories,
            $lang_array,
            $shipped_migrated_modules,
            $locked_modules,
            $keep_local_changes,
            $delete_unchanged_migrated_rows,
            $client_data_dir,
            $overlay_only_modules,
            $skipped_modules
        );
        // Built inside out, so the outermost closure acquires the first lock of $locked_modules
        foreach (array_reverse($locked_modules) as $module) {
            $locked = $write;
            $write = fn(): array => MigratedLanguageFileSync::withOverlayLock(
                $this->language_file_directory_manager,
                $lang_key,
                $module,
                $client_data_dir,
                $locked,
                $this->absolute_path
            );
        }

        $failed_modules = $write();
        // not recorded, so taken over again next run: overlay not written, or an entry left out for its markup
        $not_taken_over = $failed_modules;
        foreach (array_keys($this->skipped_invalid_markup_entries[$lang_key] ?? []) as $key) {
            $not_taken_over[] = explode(self::SEPARATOR, (string) $key, 2)[0];
        }
        if ($client_data_dir !== null && $shipped_migrated_modules !== []) {
            $taken_over_now = array_values(array_diff(array_map('strval', array_keys($shipped_migrated_modules)), $not_taken_over));
            try {
                MigratedLanguageFileSync::markLocalChangesTakenOver($client_data_dir, $lang_key, $taken_over_now);
            } catch (\Throwable $t) {
                // taken over again next run; only modules with lng_data local changes are reported
                $unrecorded = array_values(array_intersect(array_diff($taken_over_now, $taken_over), $modules_with_local_changes));
                if ($unrecorded !== []) {
                    $this->unrecorded_taken_over_modules[$lang_key] = $unrecorded;
                }
                $this->logWarning(sprintf('Could not record the taken over local changes of language "%s": %s', $lang_key, $t->getMessage()));
            }
        }

        return $failed_modules;
    }

    /**
     * @param list<string>|null $shipped_modules `null`: looked up here
     */
    private function findOverlaysOfUnshippedModules(string $lang_key, ?string $client_data_dir, ?array $shipped_modules): void
    {
        if ($client_data_dir === null) {
            return;
        }
        try {
            $modules = array_diff(
                array_unique([
                    ...MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($client_data_dir, $lang_key),
                    ...MigratedLanguageFileSync::findModulesWithOverlay($client_data_dir, $lang_key),
                ]),
                $shipped_modules ?? array_map('strval', array_keys(MigratedLanguageFileSync::findShippedModuleFiles(
                    $this->language_file_directory_manager,
                    $this->absolute_path,
                    $lang_key
                )))
            );
        } catch (\Throwable $t) {
            $this->logWarning(sprintf('Could not look for overlays of modules not shipped for language "%s": %s', $lang_key, $t->getMessage()));
            return;
        }
        if ($modules !== []) {
            sort($modules, SORT_STRING);
            $this->overlays_of_unshipped_modules[$lang_key] = $modules;
        }
    }

    /**
     * insertLanguage() once the overlays of $locked_modules are locked.
     *
     * @param iterable<LanguageFileDirectory> $directories
     * @param array<string, array<string, string>> $lang_array
     * @param array<string, array<string, string>> $shipped_migrated_modules
     * @param list<string> $locked_modules
     * @param list<string> $overlay_only_modules migrated modules whose lng_data rows are never read
     * @param list<string> $skipped_modules migrated modules of which nothing is written
     * @return list<string> see insertLanguage()
     */
    private function writeLanguage(
        string $lang_key,
        iterable $directories,
        array $lang_array,
        array $shipped_migrated_modules,
        array $locked_modules,
        bool $keep_local_changes,
        bool $delete_unchanged_migrated_rows,
        ?string $client_data_dir,
        array $overlay_only_modules = [],
        array $skipped_modules = []
    ): array {
        $ilDB = $this->db;
        $working_dir = getcwd();

        $values_sql = [];
        // rows of migrated modules are buffered until their final content is known: the database holds a
        // plural message only under its identifier with its default value (see MigratedLanguageFileSync::collapsePluralForms())
        $migrated_rows = [];
        $sql_row = static function (
            string $module,
            string $identifier,
            string $value,
            ?string $local_change,
            ?string $remarks
        ) use (&$values_sql, $ilDB, $lang_key): void {
            $values_sql[] = sprintf(
                "(%s,%s,%s,%s,%s,%s)",
                $ilDB->quote($module, "text"),
                $ilDB->quote($identifier, "text"),
                $ilDB->quote($lang_key, "text"),
                $ilDB->quote($value, "text"),
                $ilDB->quote($local_change, "timestamp"),
                $ilDB->quote($remarks, "text")
            );
        };
        $add_row = static function (
            string $module,
            string $identifier,
            string $value,
            ?string $local_change,
            ?string $remarks
        ) use (&$migrated_rows, $sql_row, $shipped_migrated_modules): void {
            if (!isset($shipped_migrated_modules[$module])) {
                $sql_row($module, $identifier, $value, $local_change, $remarks);
                return;
            }
            // a later row of the same key replaces an earlier one, like the INSERT below does
            unset($migrated_rows[$module][$identifier]);
            $migrated_rows[$module][$identifier] = [$value, $local_change, $remarks];
        };

        // chdir()s into each language directory; must be restored on every exit path (also exceptions),
        // as long-lived workers would leak the cwd into later requests
        try {
            $customized = [];
            $customized_remarks = [];
            $duplicates = [];
            // markup check of the customizing file: values before this run (the seed) and shipped values
            $current_values = $lang_array;
            $shipped_line_values = [];
            $shipped_line_comments = [];

            foreach ($directories as $directory) {
                $lang_file = "ilias_" . $lang_key . ".lang" . $directory->getSuffix();
                $path = $this->absoluteDirectoryPath($this->absolute_path, $directory);

                if (!is_dir($path)) {
                    continue;
                }
                chdir($path);

                if (!is_file($lang_file)) {
                    continue;
                }

                $content = $this->cutHeader(file($lang_file));
                if (!$content) {
                    continue;
                }

                $prefix = $directory->getPrefix();
                $is_local = $directory->isLocal();

                // resolved once per directory, not per line
                $change_date = null;
                $newer_db_changes = [];
                if ($is_local) {
                    // a local file overwrites unless the database holds a newer change for the entry
                    $newer_db_changes = array_diff_key(
                        $this->repository->getLocalChanges($lang_key, $this->utcTimestamp(filemtime($lang_file))),
                        array_flip($overlay_only_modules)
                    );
                    // of those modules from their overlay instead
                    foreach ($overlay_only_modules as $module) {
                        foreach ($this->loadOverlay($lang_key, $module, $client_data_dir) ?? [] as $identifier => $state) {
                            // without a date it counts as newer: the file must not overwrite it
                            if ($state['local_change'] && ($state['local_change_date'] === null || $state['local_change_date'] >= $this->utcTimestamp(filemtime($lang_file)))) {
                                $newer_db_changes[$module][(string) $identifier] = $state['value'];
                            }
                        }
                    }
                    $change_date = $this->utcTimestamp();
                }

                $seen_in_file = [];
                foreach ($content as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }
                    $separated = explode(self::SEPARATOR, $line);

                    if (!empty($prefix)) {
                        array_unshift($separated, $prefix);
                    }

                    $pos = strpos($separated[2], self::COMMENT_SEPARATOR);
                    if ($pos !== false) {
                        $separated[3] = substr($separated[2], $pos + strlen(self::COMMENT_SEPARATOR));
                        $separated[2] = substr($separated[2], 0, $pos);
                    }

                    $module = $separated[0];
                    $identifier = $separated[1];
                    $value = $separated[2];
                    if (in_array($module, $skipped_modules, true)) {
                        continue;
                    }

                    if (isset($seen_in_file[$module][$identifier])) {
                        $duplicates[] = $path . $lang_file . ': ' . $module . self::SEPARATOR . $identifier;
                        continue;
                    }
                    $seen_in_file[$module][$identifier] = true;

                    if (!$is_local) {
                        $shipped_line_values[$module][$identifier] ??= $value;
                        if (($separated[3] ?? '') !== '') {
                            // the shipped "###" comment, formerly held by lng_data.remarks, see migratedModuleRemarks()
                            $shipped_line_comments[$module][$identifier] ??= $separated[3];
                        }
                        if (isset($shipped_migrated_modules[$module])) {
                            // Migrated: the shipped .po is the only source, see resolveMigratedModule()
                            continue;
                        }
                        // respect recorded local changes; an earlier directory wins
                        if (isset($lang_array[$module][$identifier])) {
                            continue;
                        }
                    } elseif (isset($newer_db_changes[$module][$identifier])) {
                        $lang_array[$module][$identifier] = $newer_db_changes[$module][$identifier];
                        continue;
                    } elseif (($violations = $this->invalidMarkupOfCustomizingValue(
                        $value,
                        $current_values[$module][$identifier] ?? null,
                        isset($shipped_migrated_modules[$module])
                            ? ($shipped_migrated_modules[$module][$identifier] ?? null)
                            : ($shipped_line_values[$module][$identifier] ?? null)
                    )) !== []) {
                        // a customizing value with disallowed markup is left out and reported, the language is still installed
                        $this->skipped_invalid_markup_entries[$lang_key][$module . self::SEPARATOR . $identifier] = $violations;
                        continue;
                    }

                    $add_row($module, $identifier, $value, $change_date, $separated[3] ?? null);
                    $lang_array[$module][$identifier] = $value;
                    if ($is_local) {
                        $customized[$module][$identifier] = true;
                        if (isset($shipped_migrated_modules[$module]) && ($separated[3] ?? '') !== '') {
                            $customized_remarks[$module][$identifier] = $separated[3];
                        }
                    }
                }
            }

            $skipped_for_language = $this->skipped_invalid_markup_entries[$lang_key] ?? [];
            if ($skipped_for_language !== []) {
                $this->logWarning(PlainLogText::of(sprintf(
                    'Customizing entries of language "%s" not applied - they contain markup that is not allowed: %s',
                    $lang_key,
                    implode(', ', array_keys($skipped_for_language))
                )));
            }

            if ($duplicates !== []) {
                // shipped files contain duplicates (e.g. ilias_fa.lang), so aborting would block updating them:
                // the first occurrence wins and the duplicates are reported
                $this->logWarning(sprintf(
                    'Duplicate language entries for language "%s" (first occurrence used): %s',
                    $lang_key,
                    implode(', ', $duplicates)
                ));
            }

            // remarks of migrated modules (also kept in their overlay); later ones win: lng_data, overlay, customizing file
            $migrated_remarks = $keep_local_changes
                ? $this->migratedModuleRemarks($lang_key, $shipped_migrated_modules, $customized_remarks, $shipped_line_comments, $client_data_dir, $overlay_only_modules)
                : [];

            $dropped_identifiers = [];
            foreach ($shipped_migrated_modules as $module => $shipped_entries) {
                $overlay = $keep_local_changes ? $this->loadOverlay($lang_key, (string) $module, $client_data_dir) : null;

                // a plain value of a plural message is its default form
                $lang_array[$module] = $this->resolveMigratedModule(
                    MigratedLanguageFileSync::mapLegacyPluralValues($lang_array[$module] ?? [], $shipped_entries),
                    MigratedLanguageFileSync::mapLegacyPluralValues($customized[$module] ?? [], $shipped_entries),
                    $shipped_entries,
                    MigratedLanguageFileSync::mapLegacyPluralValues($overlay ?? [], $shipped_entries),
                    // the database gets the shipped values as the build serves them, see databaseValues()
                    static fn(string $identifier, string $value, ?string $local_change) =>
                        $add_row(
                            $module,
                            $identifier,
                            MigratedLanguageFileSync::databaseValues([$identifier => $value], $shipped_entries)[$identifier],
                            $local_change,
                            null
                        ),
                    static function (string $identifier) use (&$dropped_identifiers, $module): void {
                        $dropped_identifiers[$module][] = $identifier;
                    }
                );
                if ($lang_array[$module] === []) {
                    unset($lang_array[$module]);
                }
            }
            // module => identifiers of the migrated modules written below
            $written_migrated_identifiers = array_fill_keys(array_map('strval', array_keys($shipped_migrated_modules)), []);
            foreach ($migrated_rows as $module => $rows) {
                foreach (self::databaseRowsOfMigratedModule($rows, $lang_array[$module] ?? [], $shipped_migrated_modules[$module], $migrated_remarks[$module] ?? []) as $identifier => [$value, $local_change, $remarks]) {
                    $sql_row((string) $module, (string) $identifier, $value, $local_change, $remarks);
                    $written_migrated_identifiers[(string) $module][] = (string) $identifier;
                }
            }

            $write_rows = static function (\ilDBInterface $ilDB) use ($values_sql, $dropped_identifiers, $lang_key, $delete_unchanged_migrated_rows, $written_migrated_identifiers): void {
                if ($values_sql !== []) {
                    $query = "INSERT INTO lng_data (module,identifier,lang_key,value,local_change,remarks) VALUES "
                        . implode(',', $values_sql)
                        . " ON DUPLICATE KEY UPDATE value=VALUES(value),remarks=VALUES(remarks),local_change=VALUES(local_change);";
                    $ilDB->manipulate($query);
                }

                // only after the rows are written, so an aborted write leaves the former rows instead of a gap
                if ($delete_unchanged_migrated_rows) {
                    foreach ($written_migrated_identifiers as $module => $identifiers) {
                        $ilDB->manipulate(sprintf(
                            "DELETE FROM lng_data WHERE lang_key = %s AND local_change IS NULL AND module = %s%s",
                            $ilDB->quote($lang_key, "text"),
                            $ilDB->quote((string) $module, "text"),
                            $identifiers === [] ? '' : ' AND ' . $ilDB->in('identifier', $identifiers, true, 'text')
                        ));
                    }
                }

                foreach ($dropped_identifiers as $module => $identifiers) {
                    $ilDB->manipulate(sprintf(
                        "DELETE FROM lng_data WHERE lang_key = %s AND module = %s AND %s",
                        $ilDB->quote($lang_key, "text"),
                        $ilDB->quote((string) $module, "text"),
                        $ilDB->in('identifier', $identifiers, false, 'text')
                    ));
                }
            };
            if ($delete_unchanged_migrated_rows && $shipped_migrated_modules !== []) {
                // one table lock, so a parallel request never sees the state in between (no rollback)
                $atom_query = $ilDB->buildAtomQuery();
                $atom_query->addTableLock('lng_data');
                $atom_query->addQueryCallable($write_rows);
                $atom_query->run();
            } else {
                $write_rows($ilDB);
            }

            if ($lang_array === []) {
                return [];
            }

            $modules = array_map('strval', array_keys($lang_array));
            $inModulesToDelete = $ilDB->in('module', $modules, false, 'text');
            $ilDB->manipulate(sprintf(
                "DELETE FROM lng_modules WHERE lang_key = %s AND $inModulesToDelete",
                $ilDB->quote($lang_key, "text")
            ));

            $modulesValuesSql = [];
            foreach ($lang_array as $module => $lang_arr) {
                // $lang_array itself stays unprocessed: sync() below compares with the raw .po
                if (isset($shipped_migrated_modules[$module])) {
                    $lang_arr = MigratedLanguageFileSync::databaseValues(
                        MigratedLanguageFileSync::collapsePluralForms($lang_arr, $shipped_migrated_modules[$module]),
                        MigratedLanguageFileSync::collapsePluralForms($shipped_migrated_modules[$module], $shipped_migrated_modules[$module])
                    );
                }
                $modulesValuesSql[] = sprintf(
                    "(%s,%s,%s)",
                    $ilDB->quote((string) $module, "text"),
                    $ilDB->quote($lang_key, "text"),
                    $ilDB->quote(serialize($lang_arr), "clob")
                );
            }

            $query = "INSERT INTO lng_modules (module, lang_key, lang_array) VALUES "
                . implode(',', $modulesValuesSql)
                . ";";
            $ilDB->manipulate($query);

            $this->assertModulesCorrectlySaved($lang_key, $modules);
            $this->invalidateLanguageCache($lang_key);

            // a migrated module that was not locked had no overlay when this run decided on its values
            $expected_overlays = [];
            foreach (array_keys($shipped_migrated_modules) as $module) {
                $expected_overlays[(string) $module] = in_array((string) $module, $locked_modules, true) ? null : false;
            }

            // Without keeping local changes ("remove local changes"), the remarks go as well
            $remarks = [];
            foreach (array_keys($shipped_migrated_modules) as $module) {
                $remarks[(string) $module] = $keep_local_changes ? ($migrated_remarks[(string) $module] ?? null) : [];
            }

            // an entry that enters the overlay keeps the time of its local change
            $local_change_dates = $keep_local_changes
                ? $this->migratedLocalChangeDates($lang_key, array_map('strval', array_keys($shipped_migrated_modules)))
                : [];

            return $this->syncMigratedModules($lang_key, $lang_array, $client_data_dir, $expected_overlays, $remarks, $local_change_dates);
        } finally {
            chdir($working_dir);
        }
    }

    /**
     * The lng_data rows of a migrated module from the rows buffered while its content was decided: the
     * rows of a plural message's forms (and a plain row of its identifier) become one row of the
     * identifier with the final default value (see MigratedLanguageFileSync::collapsePluralForms() and
     * databaseValues()), the first remark and, only if that value differs from the shipped default, the
     * first local_change. The row stands for the default value alone, so after a rollback it does not
     * hold back updates of the legacy `.lang` files. Every other row is kept.
     *
     * @param array<string, array{0: string, 1: ?string, 2: ?string}> $rows identifier => value,
     *        local_change, remarks
     * @param array<string, string> $final_entries the module's final identifier => value map
     * @param array<string, string> $shipped_entries
     * @param array<string, string> $remarks identifier => remark, see migratedModuleRemarks()
     * @return array<string, array{0: string, 1: ?string, 2: ?string}>
     */
    private static function databaseRowsOfMigratedModule(array $rows, array $final_entries, array $shipped_entries, array $remarks = []): array
    {
        $values = MigratedLanguageFileSync::collapsePluralForms($final_entries, $shipped_entries);
        $shipped_defaults = MigratedLanguageFileSync::collapsePluralForms($shipped_entries, $shipped_entries);
        $database_values = MigratedLanguageFileSync::databaseValues($values, $shipped_defaults);
        $result = [];
        foreach ($rows as $identifier => [$value, $local_change, $row_remarks]) {
            $identifier = (string) $identifier;
            $plural_identifier = MigratedLanguageFileSync::pluralMessageOf($identifier, $shipped_entries);
            if ($plural_identifier === null) {
                $result[$identifier] = [$value, $local_change, $remarks[$identifier] ?? $row_remarks];
                continue;
            }
            if (!isset($database_values[$plural_identifier])) {
                continue;
            }
            $previous = $result[$plural_identifier] ?? null;
            $is_local_change = (string) $values[$plural_identifier] !== (string) ($shipped_defaults[$plural_identifier] ?? '');
            $result[$plural_identifier] = [
                (string) $database_values[$plural_identifier],
                $is_local_change ? ($previous[1] ?? $local_change) : null,
                $remarks[$plural_identifier] ?? $previous[2] ?? $row_remarks,
            ];
        }

        return $result;
    }

    /**
     * The shipped/local decision for one migrated module: a three-way comparison per shipped entry between
     *   L = the local value ($local_entries: lng_data, customizing/local file or, with $overlay, the overlay),
     *   O = the shipped value the overlay tracked the entry against ("original"), and
     *   S = the value the shipped `.po` carries now:
     *
     * - no L                    -> S
     * - L === S                 -> S, no longer a local change
     * - L === O (so S changed)  -> S: the "local" value only was the previously shipped one
     * - otherwise               -> L: a real local change is never overwritten; only "original" moves to S,
     *                              so "remove local changes" resets to the current shipped value
     *
     * A value from the customizing file ($customized) always stays local. Overlay entries that are neither
     * shipped nor local are dropped. A local entry without shipped counterpart (removed from the `.po` or
     * added locally) is dropped if L === O (via $delete_row; it only carries its last shipped value),
     * otherwise or if O is unknown kept as a local change. The legacy `.lang` update treats a removed key alike.
     *
     * @param array<string, string> $local_entries identifier => value
     * @param array<string, true> $customized identifiers that came from the customizing/local file
     * @param array<string, string> $shipped_entries identifier => shipped value
     * @param array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}> $overlay
     * @param \Closure(string, string, ?string):void $write_row persists one lng_data row
     * @param \Closure(string):void $delete_row removes one lng_data row (after all rows were written)
     * @return array<string, string> the module's final identifier => value map
     */
    private function resolveMigratedModule(
        array $local_entries,
        array $customized,
        array $shipped_entries,
        array $overlay,
        \Closure $write_row,
        \Closure $delete_row
    ): array {
        foreach ($overlay as $identifier => $state) {
            $identifier = (string) $identifier;
            if ($state['local_change'] && !isset($local_entries[$identifier])) {
                $local_entries[$identifier] = $state['value'];
                $write_row($identifier, $state['value'], $state['local_change_date'] ?? $this->utcTimestamp());
            }
        }

        foreach ($shipped_entries as $identifier => $shipped_value) {
            $identifier = (string) $identifier;
            if (isset($local_entries[$identifier]) && !isset($customized[$identifier])) {
                $local_value = $local_entries[$identifier];
                $previously_shipped_value = $overlay[$identifier]['original'] ?? null;
                if ($local_value !== $shipped_value && $local_value !== $previously_shipped_value) {
                    continue;
                }
            } elseif (isset($local_entries[$identifier])) {
                continue;
            }

            $local_entries[$identifier] = $shipped_value;
            $write_row($identifier, $shipped_value, null);
        }

        foreach ($local_entries as $identifier => $local_value) {
            $identifier = (string) $identifier;
            if (isset($shipped_entries[$identifier]) || isset($customized[$identifier])) {
                continue;
            }
            $previously_shipped_value = $overlay[$identifier]['original'] ?? null;
            if ($previously_shipped_value !== null && $local_value === $previously_shipped_value) {
                unset($local_entries[$identifier]);
                $delete_row($identifier);
            }
        }

        return $local_entries;
    }

    private function isReadableOverlay(string $lang_key, string $module, ?string $client_data_dir): bool
    {
        try {
            MigratedLanguageFileSync::loadLocalChanges(
                $this->language_file_directory_manager,
                $lang_key,
                $module,
                $client_data_dir,
                $this->absolute_path
            );
        } catch (\Throwable) {
            return false;
        }

        return true;
    }

    /**
     * The overlay state of a migrated module, or null. An unreadable overlay is reported and treated as
     * absent, so it cannot abort a reinstall whose data was already flushed; sync() rewrites it afterwards.
     * Returns only the delta (see MigratedLanguageFileSync::loadLocalChanges()), the rest carries the shipped value.
     *
     * An entry "merge" took over into the shipped `.po` (see LocalChangeComments::isMergedIntoShipped())
     * gets its own value as "original", so a newer shipped value wins in resolveMigratedModule() instead of
     * the taken-over value staying a local change.
     *
     * @return array<string, array{value: string, local_change: bool, local_change_date: ?string, original: ?string}>|null
     */
    private function loadOverlay(string $lang_key, string $module, ?string $client_data_dir): ?array
    {
        try {
            $overlay = MigratedLanguageFileSync::loadLocalChanges(
                $this->language_file_directory_manager,
                $lang_key,
                $module,
                $client_data_dir,
                $this->absolute_path
            );
            if ($overlay === null || $overlay === []) {
                return $overlay;
            }
            $catalog = MigratedLanguageFileSync::readOverlayCatalog(
                $this->language_file_directory_manager,
                $lang_key,
                $module,
                $client_data_dir,
                $this->absolute_path
            );
            $merged = [];
            foreach ($catalog === null ? [] : MigratedLanguageFileSync::moduleEntries($catalog, $module) as $entry) {
                if (LocalChangeComments::isMergedIntoShipped($entry)) {
                    $merged[$entry->getId()] = true;
                }
            }
            foreach ($overlay as $key => $state) {
                // a form of a plural message (see PluralFormKey) belongs to the entry of its identifier
                if (isset($merged[(string) $key]) || isset($merged[PluralFormKey::parse((string) $key)[0] ?? ''])) {
                    $overlay[$key]['original'] = $state['value'];
                }
            }

            return $overlay;
        } catch (\Throwable $t) {
            $this->logWarning(sprintf(
                'Could not read the overlay of migrated module "%s", language "%s" - ignoring it: %s',
                $module,
                $lang_key,
                $t->getMessage()
            ));
            return null;
        }
    }

    /**
     * A wrong table collation silently corrupts the serialized module arrays, which would otherwise only
     * surface as missing translations (mantis #20046/#19140).
     *
     * @param list<string> $modules
     */
    private function assertModulesCorrectlySaved(string $lang_key, array $modules): void
    {
        $ilDB = $this->db;
        $result = $ilDB->query(sprintf(
            "SELECT module, lang_array FROM lng_modules WHERE lang_key = %s AND %s",
            $ilDB->quote($lang_key, "text"),
            $ilDB->in('module', $modules, false, 'text')
        ));

        while ($row = $ilDB->fetchAssoc($result)) {
            if (!is_array(@unserialize((string) $row["lang_array"], ["allowed_classes" => false]))) {
                throw new LanguageDataNotSavedException((string) $row["module"], $lang_key);
            }
        }
    }

    private function invalidateLanguageCache(string $lang_key): void
    {
        try {
            $this->translation_cache->invalidate($lang_key);
        } catch (\Throwable $t) {
            // A cache that cannot be invalidated must not undo the successful database write
            $this->logWarning(sprintf('Could not invalidate the language cache for "%s": %s', $lang_key, $t->getMessage()));
        }
    }

    /**
     * The remarks of every migrated module (identifier => remark; a plural message under its identifier):
     * lng_data, overridden by the overlay, overridden by the customizing file. A module whose overlay
     * cannot be read is left out, so its sync() keeps the overlay's remarks.
     *
     * Before the migration lng_data.remarks held the shipped "###" comment of the `.lang` line: a lng_data
     * remark is only taken over if it differs from that comment ($shipped_line_comments), from the note of the
     * shipped `.po` and is no dated "new variable" marker, i.e. an administrator's remark.
     *
     * @param array<string, array<string, string>> $shipped_migrated_modules
     * @param array<string, array<string, string>> $customized_remarks
     * @param array<string, array<string, string>> $shipped_line_comments module => identifier =>
     *        "###" comment of the shipped `.lang` lines
     * @param list<string> $overlay_only_modules their lng_data remarks are not read, see insertLanguage()
     * @return array<string, array<string, string>>
     */
    private function migratedModuleRemarks(
        string $lang_key,
        array $shipped_migrated_modules,
        array $customized_remarks,
        array $shipped_line_comments,
        ?string $client_data_dir,
        array $overlay_only_modules = []
    ): array {
        if ($shipped_migrated_modules === []) {
            return [];
        }
        $ilDB = $this->db;
        $database_remarks = [];
        $result = $ilDB->query(sprintf(
            "SELECT module, identifier, remarks FROM lng_data WHERE lang_key = %s AND remarks IS NOT NULL AND %s",
            $ilDB->quote($lang_key, "text"),
            $ilDB->in('module', array_map('strval', array_keys($shipped_migrated_modules)), false, 'text')
        ));
        while ($row = $ilDB->fetchAssoc($result)) {
            if (isset($row['module'], $row['identifier'], $row['remarks']) && (string) $row['remarks'] !== '') {
                $database_remarks[(string) $row['module']][(string) $row['identifier']] = (string) $row['remarks'];
            }
        }
        $database_remarks = array_diff_key($database_remarks, array_flip($overlay_only_modules));

        $shipped_files = MigratedLanguageFileSync::findShippedModuleFiles(
            $this->language_file_directory_manager,
            $this->absolute_path,
            $lang_key
        );
        $remarks = [];
        foreach ($shipped_migrated_modules as $module => $shipped_entries) {
            $module = (string) $module;
            try {
                $po_notes = isset($shipped_files[$module])
                    ? array_filter(array_map(
                        static fn(array $entry): string => (string) $entry['comment'],
                        MigratedLanguageFileSync::loadShippedModuleEntries($shipped_files[$module], $module)
                    ), static fn(string $note): bool => $note !== '')
                    : [];
            } catch (\Throwable) {
                $po_notes = [];
            }
            foreach ($database_remarks[$module] ?? [] as $identifier => $remark) {
                $plural_identifier = MigratedLanguageFileSync::pluralMessageOf((string) $identifier, $shipped_entries);
                $shipped_comments = [
                    $shipped_line_comments[$module][$identifier] ?? null,
                    $po_notes[$identifier] ?? null,
                    $plural_identifier === null ? null : ($po_notes[PluralFormKey::of($plural_identifier, 0)] ?? null),
                ];
                if (
                    in_array(trim($remark), array_map(static fn(?string $c): ?string => $c === null ? null : trim($c), $shipped_comments), true)
                    || LegacyFuzzyMarker::matches($remark)
                ) {
                    unset($database_remarks[$module][$identifier]);
                }
            }
            try {
                $overlay_remarks = MigratedLanguageFileSync::loadRemarks(
                    $this->language_file_directory_manager,
                    $lang_key,
                    $module,
                    $client_data_dir,
                    $this->absolute_path
                ) ?? [];
            } catch (\Throwable) {
                // reported by loadOverlay() - the sync keeps what the overlay holds
                continue;
            }
            // a remark taken over by "merge" is a shipped note now (see ShippedPoMerger): it leaves the overlay
            try {
                $po_notes_by_message = isset($shipped_files[$module])
                    ? MigratedLanguageFileSync::loadShippedNotes($shipped_files[$module], $module)
                    : [];
            } catch (\Throwable) {
                $po_notes_by_message = [];
            }
            foreach ($overlay_remarks as $identifier => $remark) {
                $identifier = (string) $identifier;
                $message = MigratedLanguageFileSync::pluralMessageOf($identifier, $shipped_entries) ?? $identifier;
                $stored_remark = trim(TranslationEntry::plainComment((string) $remark));
                if (in_array($stored_remark, $po_notes_by_message[$message] ?? [], true)) {
                    unset($overlay_remarks[$identifier]);
                }
            }
            $module_remarks = [];
            foreach ([$database_remarks[$module] ?? [], $overlay_remarks, $customized_remarks[$module] ?? []] as $source) {
                foreach ($source as $identifier => $remark) {
                    $identifier = (string) $identifier;
                    $module_remarks[MigratedLanguageFileSync::pluralMessageOf($identifier, $shipped_entries) ?? $identifier] = (string) $remark;
                }
            }
            $remarks[$module] = $module_remarks;
        }

        return $remarks;
    }

    /**
     * lng_data.local_change of every locally changed entry of $modules in $lang_key (as written by
     * this run), module => identifier => database timestamp - for the overlay, see
     * MigratedLanguageFileSync::sync().
     *
     * @param list<string> $modules
     * @return array<string, array<string, string>>
     */
    private function migratedLocalChangeDates(string $lang_key, array $modules): array
    {
        if ($modules === []) {
            return [];
        }
        $ilDB = $this->db;
        $result = $ilDB->query(sprintf(
            "SELECT module, identifier, local_change FROM lng_data WHERE lang_key = %s AND local_change IS NOT NULL AND %s",
            $ilDB->quote($lang_key, "text"),
            $ilDB->in('module', $modules, false, 'text')
        ));
        $dates = [];
        while (is_array($row = $ilDB->fetchAssoc($result))) {
            if (is_string($row['module'] ?? null) && is_string($row['identifier'] ?? null) && is_string($row['local_change'] ?? null)) {
                $dates[$row['module']][$row['identifier']] = $row['local_change'];
            }
        }

        return $dates;
    }

    /**
     * @param array<string, array<string, string>> $lang_array
     * @param array<string, bool|null> $expected_overlays module => $expected_overlay_exists of
     *        MigratedLanguageFileSync::sync()
     * @param array<string, array<string, string>|null> $remarks module => $remarks of
     *        MigratedLanguageFileSync::sync() (`null`/missing: keep the overlay's remarks)
     * @param array<string, array<string, string>> $local_change_dates module => $local_change_dates
     *        of MigratedLanguageFileSync::sync()
     * @return list<string> the modules whose overlay could not be written
     */
    private function syncMigratedModules(
        string $lang_key,
        array $lang_array,
        ?string $client_data_dir,
        array $expected_overlays = [],
        array $remarks = [],
        array $local_change_dates = []
    ): array {
        $failed_modules = [];
        foreach ($lang_array as $module => $entries) {
            try {
                MigratedLanguageFileSync::sync(
                    $this->language_file_directory_manager,
                    $this->absolute_path,
                    $lang_key,
                    (string) $module,
                    $entries,
                    $client_data_dir,
                    // every caller of insertLanguage() reconciles the language with the shipped
                    // files, see MigratedLanguageFileSync::sync()
                    true,
                    $expected_overlays[(string) $module] ?? null,
                    $remarks[(string) $module] ?? null,
                    $local_change_dates[(string) $module] ?? []
                );
            } catch (\Throwable $t) {
                // the lng_modules write already succeeded: the failure is returned, not rolled back
                $this->logWarning(sprintf(
                    'Could not sync migrated language file for module "%s", language "%s": %s',
                    $module,
                    $lang_key,
                    $t->getMessage()
                ));
                $failed_modules[] = (string) $module;
            }
        }

        return $failed_modules;
    }
}
