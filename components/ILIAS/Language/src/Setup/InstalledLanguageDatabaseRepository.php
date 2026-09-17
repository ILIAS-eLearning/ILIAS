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

use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;

class InstalledLanguageDatabaseRepository implements InstalledLanguageRepository
{
    use LanguageFileParsing;

    private const SEPARATOR = "#:#";

    /**
     * The language-key part of the `ilias_<key>.lang...` file naming
     * convention that getLocalLanguages()/getInstallableLanguages() below
     * discover files by. Mirrors
     * \ILIAS\Language\Activities\ParsesLanguageKeyList::LANGUAGE_KEY_FORMAT
     * (exactly two lowercase ASCII letters) so that a file this repository
     * lists as installable/local can never fail that later, stricter
     * validation once an Activity actually tries to install/refresh it.
     * Kept as a parallel definition, not a shared reference: PHP trait
     * constants cannot be accessed via the trait's own name (only through a
     * class that uses the trait), and this class has no reason to use
     * ParsesLanguageKeyList itself, which exists to parse a
     * comma-separated form/request value, not to discover files.
     */
    private const LANGUAGE_KEY_PATTERN = '[a-z]{2}';

    /**
     * @param \ilDBInterface|\Closure():\ilDBInterface $db Accepted as a closure so this
     *        can be built once (e.g. in Language.php's DI wiring) before a database
     *        connection is available, and resolved lazily on first use - mirroring
     *        the $GLOBALS['ilDB'] ?? $DIC->database() convention used for
     *        \ILIAS\Language\Activities\InstallLanguage.
     */
    public function __construct(
        private readonly \ilDBInterface|\Closure $db,
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly string $absolute_path,
    ) {
    }

    private function db(): \ilDBInterface
    {
        return $this->db instanceof \Closure ? ($this->db)() : $this->db;
    }

    public function getInstalledLanguages(): array
    {
        $ilDB = $this->db();

        $arr = [];
        $query = "SELECT title FROM object_data " .
            "WHERE type = " . $ilDB->quote("lng", "text") . " " .
            "AND " . $ilDB->like("description", "text", "installed%");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }
        return $arr;
    }

    public function getInstalledLocalLanguages(): array
    {
        $ilDB = $this->db();

        $arr = [];
        $query = "SELECT title FROM object_data " .
            "WHERE type = " . $ilDB->quote("lng", "text") . " " .
            "AND description = " . $ilDB->quote("installed_local", "text");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }
        return $arr;
    }

    public function getAvailableLanguages(): array
    {
        $ilDB = $this->db();

        $arr = [];
        // Unlike the two queries above, obj_id and description are both
        // actually read below (title alone would not be enough here) - see
        // the loop body.
        $query = "SELECT obj_id, title, description FROM object_data " .
            "WHERE type = " . $ilDB->quote("lng", "text");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[$row->title]["obj_id"] = $row->obj_id;
            $arr[$row->title]["status"] = $row->description;
        }

        return $arr;
    }

    public function getLocalChanges(string $lang_key, string $min_date = "", string $max_date = ""): array
    {
        $ilDB = $this->db();

        if ($min_date === "") {
            $min_date = "1980-01-01 00:00:00";
        }
        if ($max_date === "") {
            $max_date = "2200-01-01 00:00:00";
        }

        $q = sprintf(
            "SELECT module, identifier, value FROM lng_data WHERE lang_key = %s " .
            "AND local_change >= %s AND local_change <= %s",
            $ilDB->quote($lang_key, "text"),
            $ilDB->quote($min_date, "timestamp"),
            $ilDB->quote($max_date, "timestamp")
        );
        $result = $ilDB->query($q);

        $changes = [];
        while ($row = $result->fetchRow(\ilDBConstants::FETCHMODE_ASSOC)) {
            $changes[$row["module"]][$row["identifier"]] = $row["value"];
        }
        return $changes;
    }

    public function getLanguageEntries(string $lang_key): array
    {
        $ilDB = $this->db();

        $q = sprintf(
            "SELECT module, identifier, value FROM lng_data WHERE lang_key = %s",
            $ilDB->quote($lang_key, "text")
        );
        $result = $ilDB->query($q);

        $entries = [];
        while ($row = $result->fetchRow(\ilDBConstants::FETCHMODE_ASSOC)) {
            $entries[$row["module"]][$row["identifier"]] = $row["value"];
        }
        return $entries;
    }

    public function getLocalLanguages(): array
    {
        $local_langs = [];
        $working_dir = getcwd();

        // See LanguageInstallationManager::insertLanguage() for why every
        // chdir() here must be undone even if something throws in between -
        // a long-lived worker process would otherwise leak a broken cwd into
        // unrelated later requests.
        try {
            foreach ($this->language_file_directory_manager->getCustomizingDirectories() as $directory) {
                $path = $this->absoluteDirectoryPath($this->absolute_path, $directory);
                if (is_dir($path)) {
                    $d = dir($path);
                    chdir($path);
                    while ($entry = $d->read()) {
                        if (is_file($entry) && (preg_match("~(^ilias_" . self::LANGUAGE_KEY_PATTERN . "\.lang" . preg_quote($directory->getSuffix(), "~") . "$)~", $entry))) {
                            $lang_key = substr($entry, 6, 2);
                            $local_langs[] = $lang_key;
                        }
                    }
                    chdir($working_dir);
                }
            }
        } finally {
            chdir($working_dir);
        }

        return array_unique($local_langs);
    }

    public function getInstallableLanguages(): array
    {
        $installableLanguages = [];
        $working_dir = getcwd();

        // See LanguageInstallationManager::insertLanguage() for why every
        // chdir() here must be undone even if something throws in between.
        try {
            foreach ($this->language_file_directory_manager->getDirectories() as $directory) {
                $path = $this->absoluteDirectoryPath($this->absolute_path, $directory);
                if (is_dir($path)) {
                    $d = dir($path);
                    chdir($path);
                    while ($entry = $d->read()) {
                        if (is_file($entry) && (preg_match("~(^ilias_" . self::LANGUAGE_KEY_PATTERN . "\.lang" . preg_quote($directory->getSuffix(), "~") . "$)~", $entry))) {
                            $lang_key = substr($entry, 6, 2);
                            $installableLanguages[] = $lang_key;
                        }
                    }
                    chdir($working_dir);
                }
            }
        } finally {
            chdir($working_dir);
        }

        return array_unique($installableLanguages);
    }

    public function getInvalidLocalLanguageFiles(array $language_keys): array
    {
        $invalid_files = [];
        $language_keys = array_values(array_unique($language_keys));

        foreach ($this->language_file_directory_manager->getCustomizingDirectories() as $directory) {
            $path = $this->absoluteDirectoryPath($this->absolute_path, $directory);
            if (!is_dir($path)) {
                continue;
            }

            $entries = scandir($path);
            if ($entries === false) {
                continue;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..' || !is_file($path . DIRECTORY_SEPARATOR . $entry)) {
                    continue;
                }

                foreach ($language_keys as $language_key) {
                    $expected_file = 'ilias_' . $language_key . '.lang' . $directory->getSuffix();
                    if (str_starts_with($entry, 'ilias_' . $language_key . '.') && $entry !== $expected_file) {
                        $invalid_files[] = $entry;
                        break;
                    }
                }
            }
        }

        return array_values(array_unique($invalid_files));
    }

    public function checkLanguage(string $lang_key): bool
    {
        foreach ($this->language_file_directory_manager->getAllDirectories() as $directory) {
            $required = $directory instanceof \ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
            if (!$this->checkLanguageFile($lang_key, $directory, $required)) {
                return false;
            }
        }

        return true;
    }

    public function checkLocalLanguageFile(string $lang_key): bool
    {
        // getCustomizingDirectories() yields exactly one directory today
        // (see LanguageFileDirectoryManager), but is typed as a generator of
        // possibly many - so check only the first one explicitly, instead of
        // a foreach that returns on its first iteration (which reads like an
        // accidental early return, not an intentional "only the first one").
        $directories = iterator_to_array(
            $this->language_file_directory_manager->getCustomizingDirectories(),
            false
        );

        if ($directories === []) {
            return false;
        }

        return $this->checkLanguageFile($lang_key, $directories[0], true);
    }

    /**
     * Validates a single directory's lang-file for $lang_key: the file
     * itself has a header and each entry consists of exactly three elements
     * (module, identifier, value). If $required is false, a missing
     * directory/file is treated as acceptable (valid); if true, it fails
     * the check.
     */
    private function checkLanguageFile(
        string $lang_key,
        \ILIAS\Language\ComponentTranslation\LanguageFileDirectory $directory,
        bool $required
    ): bool {
        $working_dir = getcwd();
        $lang_file = "ilias_" . $lang_key . ".lang" . $directory->getSuffix();
        $path = $this->absoluteDirectoryPath($this->absolute_path, $directory);

        try {
            if (!is_dir($path)) {
                return !$required;
            }
            chdir($path);

            if (!is_file($lang_file)) {
                return !$required;
            }

            $content = $this->cutHeader(file($lang_file));
            if ($content === false) {
                return false;
            }

            $prefix = $directory->getPrefix();

            foreach ($content as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = explode(self::SEPARATOR, $line);

                if (!empty($prefix)) {
                    array_unshift($parts, $prefix);
                }
                if (count($parts) !== 3) {
                    return false;
                }
                if (!\ilStr::isUtf8($parts[2])) {
                    return false;
                }
            }
        } finally {
            chdir($working_dir);
        }

        return true;
    }
}
