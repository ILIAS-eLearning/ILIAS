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
     * Mirrors ParsesLanguageKeyList::LANGUAGE_KEY_FORMAT, so a file listed here never fails the
     * stricter Activity validation later. Not shared: a trait constant is not reachable via the trait.
     */
    private const LANGUAGE_KEY_PATTERN = '[a-z]{2}';

    /** @param \ilDBInterface|null $db without one, only the file-based methods work */
    public function __construct(
        private readonly ?\ilDBInterface $db,
        private readonly LanguageFileDirectoryManager $language_file_directory_manager,
        private readonly InstallationRoot $installation_root,
    ) {
    }

    private function db(): \ilDBInterface
    {
        return $this->db ?? throw new \LogicException('No database available for ' . self::class . '.');
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

        // chdir() must be undone even on exceptions, see LanguageInstallationManager::insertLanguage()
        try {
            foreach ($this->language_file_directory_manager->getCustomizingDirectories() as $directory) {
                $path = $this->absoluteDirectoryPath($this->installation_root->getPath(), $directory);
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

        // chdir() must be undone even on exceptions, see LanguageInstallationManager::insertLanguage()
        try {
            foreach ($this->language_file_directory_manager->getDirectories() as $directory) {
                $path = $this->absoluteDirectoryPath($this->installation_root->getPath(), $directory);
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
            $path = $this->absoluteDirectoryPath($this->installation_root->getPath(), $directory);
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
        // only the first directory is checked on purpose: getCustomizingDirectories() yields exactly
        // one today, and a foreach returning in its first iteration would read like an accidental early return
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
     * Validates the lang-file of a single directory for $lang_key: header present and each entry
     * has exactly three elements. A missing directory/file is valid unless $required.
     */
    private function checkLanguageFile(
        string $lang_key,
        \ILIAS\Language\ComponentTranslation\LanguageFileDirectory $directory,
        bool $required
    ): bool {
        $working_dir = getcwd();
        $lang_file = "ilias_" . $lang_key . ".lang" . $directory->getSuffix();
        $path = $this->absoluteDirectoryPath($this->installation_root->getPath(), $directory);

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
