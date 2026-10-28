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

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;

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
     * @param (\Closure():\DateTimeImmutable)|null $now Injectable clock, defaults to
     *        the current UTC time. Exists so change/update timestamps can be
     *        asserted in tests without depending on wall-clock time.
     */
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly string $absolute_path,
        private readonly InstalledLanguageRepository $repository,
        private readonly ?\Closure $now = null,
        private readonly TranslationCache $translation_cache = new NullTranslationCache(),
    ) {
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
            $this->translation_cache->invalidate($lang_key);
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

    public function insertLanguageForInstallation(string $lang_key): void
    {
        // Every directory the LanguageFileDirectoryManager knows about,
        // including the customizing/local one - so an existing custom
        // language file is (re-)applied automatically - merged on top of
        // whatever local changes are already recorded in the DB.
        $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getAllDirectories(),
            $this->repository->getLocalChanges($lang_key)
        );
    }

    /**
     * Re-seed a language purely from the global/component language files,
     * deliberately leaving out the customizing/local directory.
     *
     * This is the write path behind "remove local changes": that action
     * flushes all data for the language and must reinstall a clean copy -
     * if it went through insertLanguageForInstallation() instead, the
     * customizing directory's file would immediately be re-applied (and
     * re-marked with a fresh local_change timestamp), silently undoing the
     * removal it was just asked to perform.
     */
    public function insertLanguageForRemovingLocalChanges(string $lang_key): void
    {
        // Global/component directories only, and an empty seed - a clean
        // slate with nothing to preserve or merge.
        $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getDirectories(),
            []
        );
    }

    /**
     * Applies only the customizing/local file on top of an installed language, leaving the base data
     * untouched: the write path of "Install local" (see InstallLanguage::perform()). The caller flushes nothing.
     *
     * The seed must hold every stored entry, not only the local changes: insertLanguage() rebuilds the
     * lng_modules row of each module from the seed plus the given directories, so a narrower seed would
     * silently drop base entries the customizing file does not override.
     */
    public function insertLanguageForApplyingLocalChanges(string $lang_key): void
    {
        $this->insertLanguage(
            $lang_key,
            $this->language_file_directory_manager->getCustomizingDirectories(),
            $this->repository->getLanguageEntries($lang_key)
        );
    }

    /**
     * Writes whatever data the given $directories/$lang_array seed produce - it does not
     * itself decide which directories to read or what to seed with; that is entirely up to
     * the caller (see the three call sites above, each of which already states its intent in its
     * own name).
     *
     * @param iterable<LanguageFileDirectory> $directories
     * @param array<string, array<string, string>> $lang_array module => identifier => value
     */
    private function insertLanguage(string $lang_key, iterable $directories, array $lang_array): void
    {
        $ilDB = $this->db;
        $working_dir = getcwd();

        // Every exit path below - including an exception thrown out of a DB
        // call - must restore the working directory. This method chdir()s
        // into each language directory in turn to read its file with a
        // relative path; PHP-FPM/mod_php worker processes are long-lived and
        // reused across unrelated requests, so a cwd left dangling here (e.g.
        // inside lang/customizing/ after an error) would silently corrupt
        // relative-path filesystem checks - such as this same class'
        // checkLanguageFile() - for every later request handled by that
        // worker, for any language, not just this one. This is why a
        // language check can spuriously fail for a file that is perfectly
        // valid on disk.
        try {
            $values_sql = [];

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

                // Both of these depend only on the language and on this
                // directory's file - never on the individual entry - so they
                // are resolved once per directory. Doing it inside the loop
                // below meant one "SELECT ... FROM lng_data" per line of the
                // file: a customizing file with a few thousand entries issued
                // a few thousand identical queries.
                $change_date = null;
                $newer_db_changes = [];
                if ($is_local) {
                    // a local file overwrites unless the database holds a newer change for the entry
                    $newer_db_changes = $this->repository->getLocalChanges(
                        $lang_key,
                        $this->utcTimestamp(filemtime($lang_file))
                    );
                    $change_date = $this->utcTimestamp();
                }

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

                    if (!$is_local) {
                        // respect recorded local changes; an earlier directory wins
                        if (isset($lang_array[$module][$identifier])) {
                            continue;
                        }
                    } elseif (isset($newer_db_changes[$module][$identifier])) {
                        $lang_array[$module][$identifier] = $newer_db_changes[$module][$identifier];
                        continue;
                    }

                    $values_sql[] = sprintf(
                        "(%s,%s,%s,%s,%s,%s)",
                        $ilDB->quote($module, "text"),
                        $ilDB->quote($identifier, "text"),
                        $ilDB->quote($lang_key, "text"),
                        $ilDB->quote($value, "text"),
                        $ilDB->quote($change_date, "timestamp"),
                        $ilDB->quote($separated[3] ?? null, "text")
                    );

                    $lang_array[$module][$identifier] = $value;
                }
            }

            if ($values_sql !== []) {
                $query = "INSERT INTO lng_data (module,identifier,lang_key,value,local_change,remarks) VALUES "
                    . implode(',', $values_sql)
                    . " ON DUPLICATE KEY UPDATE value=VALUES(value),remarks=VALUES(remarks),local_change=VALUES(local_change);";
                $ilDB->manipulate($query);
            }

            if ($lang_array === []) {
                return;
            }

            $modules = array_keys($lang_array);
            $inModulesToDelete = $ilDB->in('module', $modules, false, 'text');
            $ilDB->manipulate(sprintf(
                "DELETE FROM lng_modules WHERE lang_key = %s AND $inModulesToDelete",
                $ilDB->quote($lang_key, "text")
            ));

            $modulesValuesSql = [];
            foreach ($lang_array as $module => $lang_arr) {
                $modulesValuesSql[] = sprintf(
                    "(%s,%s,%s)",
                    $ilDB->quote($module, "text"),
                    $ilDB->quote($lang_key, "text"),
                    $ilDB->quote(serialize($lang_arr), "clob")
                );
            }

            $query = "INSERT INTO lng_modules (module, lang_key, lang_array) VALUES "
                . implode(',', $modulesValuesSql)
                . ";";
            $ilDB->manipulate($query);
            $this->translation_cache->invalidate($lang_key);
        } finally {
            chdir($working_dir);
        }
    }
}
