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

use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;
use ILIAS\Language\Setup\TranslationCache;
use ILIAS\Language\Setup\NullTranslationCache;
use ILIAS\Language\Setup\FixedInstallationRoot;

/**
 * language handling for setup
 *
 * this class offers the language handling for an application.
 * the constructor is called with a small language abbreviation
 * e.g. $lng = new Language("en");
 * the constructor reads the single-languagefile en.lang and puts this into an array.
 * with
 * e.g. $lng->txt("user_updated");
 * you can translate a lang-topic into the actual language
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 *
 * Language implementation for Setup (txt() before the runtime DIC exists). Installation is delegated to
 * \ILIAS\Language\Setup\InstalledLanguageRepository (read) and LanguageInstallationManager (write);
 * kept as entry point for callers not wired through Language.php.
 */
class ilSetupLanguage extends ilLanguage
{
    private string $absolute_path;
    public array $text;
    public string $lang_default = "en";
    public string $lang_key;
    public string $separator = "#:#";
    public string $comment_separator = "###";
    protected ?ilDBInterface $db = null;
    private ?string $client_data_dir = null;
    private readonly bool $uses_default_language_file_directories;
    private readonly LanguageFileDirectoryManager $language_file_directory_manager;
    private ?LanguageInstallationManager $manager = null;
    private ?ilDBInterface $manager_db = null;

    /**
     * @param LanguageFileDirectoryManager|null $language_file_directory_manager directories contributed through the
     *        component graph; without it only main lang/ and customizing are known (usesDefaultLanguageFileDirectories())
     * @param ilDBInterface|null $connection none for callers outside the component graph
     */
    public function __construct(
        string $a_lang_key,
        ?LanguageFileDirectoryManager $language_file_directory_manager = null,
        private readonly ?ilDBInterface $connection = null,
        private readonly TranslationCache $translation_cache = new NullTranslationCache(),
    ) {
        $this->lang_key = $a_lang_key ?: $this->lang_default;
        $this->absolute_path = (string) realpath(__DIR__ . "/../../../../../");
        $this->uses_default_language_file_directories = $language_file_directory_manager === null;
        $this->language_file_directory_manager = $language_file_directory_manager ?? new LanguageFileDirectoryManager(
            new CustomizingLanguageFileDirectory(),
            new MainLanguageFileDirectory()
        );
        $this->cust_lang_path = $this->absolute_path . "/lang/customizing";
        $this->lang_path = $this->absolute_path . "/lang";
    }

    /**
     * Resolved per call, so a later setDbHandler() takes effect. Order:
     * setDbHandler(), constructor connection, $GLOBALS['ilDB'].
     */
    private function db(): ?ilDBInterface
    {
        return $this->db ?? $this->connection ?? $GLOBALS["ilDB"] ?? null;
    }

    /**
     * Without a database only the file-based methods work.
     */
    private function repository(): InstalledLanguageRepository
    {
        return new InstalledLanguageDatabaseRepository(
            $this->db(),
            $this->language_file_directory_manager,
            new FixedInstallationRoot($this->absolute_path)
        );
    }

    /**
     * Kept across calls because the manager collects getSkippedInvalidMarkupEntries() over a run;
     * rebuilt only when db() changes.
     */
    private function manager(): LanguageInstallationManager
    {
        $db = $this->db() ?? throw new LogicException("No database available for " . self::class . ".");
        if ($this->manager === null || $this->manager_db !== $db) {
            $this->manager_db = $db;
            $this->manager = new LanguageInstallationManager(
                $db,
                $this->language_file_directory_manager,
                $this->absolute_path,
                $this->repository(),
                null,
                $this->translation_cache,
                fn(): ?string => $this->getClientDataDir()
            );
        }
        return $this->manager;
    }

    /**
     * The client data directory the overlay of migrated modules is maintained in. Setup Objectives
     * set it from the Setup environment (ilias.ini `datadir` + client id); without it, it is
     * resolved via MigratedLanguageFilePaths::resolveClientDataDir(). `null` resets to that default.
     */
    public function setClientDataDir(?string $client_data_dir): void
    {
        $this->client_data_dir = $client_data_dir;
    }

    public function getClientDataDir(): ?string
    {
        return $this->client_data_dir ?? MigratedLanguageFilePaths::resolveClientDataDir($this->absolute_path);
    }

    /**
     * Such an instance (e.g. `new ilSetupLanguage('en')` in plugin Setup Objectives) must not refresh an
     * installed language: that would drop the lng_data rows of every component-contributed module.
     */
    public function usesDefaultLanguageFileDirectories(): bool
    {
        return $this->uses_default_language_file_directories;
    }

    public function getLanguageFileDirectoryManager(): LanguageFileDirectoryManager
    {
        return $this->language_file_directory_manager;
    }

    /**
     * gets the text for a given topic
     *
     * if the topic is not in the list, the topic itself with "-" will be returned
     *
     * $a_topic    topic
     */
    public function txt(string $a_topic, string $a_default_lang_fallback_mod = ''): string
    {
        /** @var ilLogger $log */
        global $log;

        if (empty($a_topic)) {
            return "";
        }

        $translation = $this->text[$a_topic] ?? '';

        //get position of the comment_separator
        $pos = strpos($translation, $this->comment_separator);

        if ($pos !== false) {
            // remove comment
            $translation = substr($translation, 0, $pos);
        }

        if ($translation === "") {
            $log->debug("Language (" . $this->lang_key . "): topic -" . $a_topic . "- not present");
            return "-" . $a_topic . "-";
        }

        return $translation;
    }

    /**
     * install languages
     *
     * $a_lang_keys    array with lang_keys of languages to install
     *
     * @return array|bool
     */
    public function installLanguages(array $a_lang_keys, array $a_local_keys = [])
    {
        if (empty($a_lang_keys)) {
            $a_lang_keys = [];
        }

        return $this->manager()->installLanguages($a_lang_keys, $a_local_keys);
    }

    /**
     * Single place for the object_data row ("installed" vs. "installed_local"), also used by InstallLanguage::perform().
     *
     * @param array<string, array{obj_id:int, status:string}> $known_languages result of getAvailableLanguages()/getAvailableLanguagesForInstallation()
     * @param list<string> $local_language_keys result of getLocalLanguages()
     */
    public function registerInstalledLanguage(
        string $lang_key,
        array $known_languages,
        array $local_language_keys
    ): void {
        $this->manager()->registerInstalledLanguage($lang_key, $known_languages, $local_language_keys);
    }

    public function getAvailableLanguagesForInstallation(): array
    {
        return $this->repository()->getAvailableLanguages();
    }

    public function checkLanguageForInstallation(string $lang_key): bool
    {
        return $this->repository()->checkLanguage($lang_key);
    }

    /**
     * Unlike checkLanguageForInstallation(), a missing local file is not acceptable here.
     */
    public function checkLocalLanguageFileForInstallation(string $lang_key): bool
    {
        return $this->repository()->checkLocalLanguageFile($lang_key);
    }

    public function flushLanguageForInstallation(string $lang_key): void
    {
        $this->manager()->flushLanguageForInstallation($lang_key);
    }

    public function flushLanguageForUninstallation(string $lang_key): void
    {
        $this->manager()->flushLanguageForUninstallation($lang_key);
    }

    /**
     * @return array<string, array<string, list<string>>>
     */
    public function getSkippedInvalidMarkupEntries(): array
    {
        return $this->manager?->getSkippedInvalidMarkupEntries() ?? [];
    }

    /**
     * @return list<string> the migrated modules whose overlay could not be written
     */
    public function insertLanguageForInstallation(string $lang_key): array
    {
        return $this->manager()->insertLanguageForInstallation($lang_key);
    }

    /**
     * See LanguageInstallationManager::insertLanguageForApplyingLocalChanges().
     *
     * @return list<string> see insertLanguageForInstallation()
     */
    public function insertLanguageForApplyingLocalChanges(string $lang_key): array
    {
        return $this->manager()->insertLanguageForApplyingLocalChanges($lang_key);
    }

    /**
     * See LanguageInstallationManager::findUnwritableOverlayDirectories() - checked by Setup before
     * installing/updating languages. File-based like the manager's, but without needing a database
     * (the manager is only built with one, see manager()).
     *
     * @param list<string> $lang_keys
     * @param int|null $for_user_id evaluate the writability for this user instead of the current process
     * @return list<string>
     */
    public function findUnwritableOverlayDirectories(array $lang_keys, ?int $for_user_id = null): array
    {
        return MigratedLanguageFileSync::findUnwritableOverlayDirectories(
            $this->language_file_directory_manager,
            $this->absolute_path,
            $lang_keys,
            $this->getClientDataDir(),
            $for_user_id
        );
    }

    /**
     * See LanguageInstallationManager::hasMigratedModules() - file-based, needs no database.
     *
     * @param list<string> $lang_keys
     */
    public function hasMigratedModules(array $lang_keys): bool
    {
        foreach ($lang_keys as $lang_key) {
            if (MigratedLanguageFileSync::findShippedModuleFiles(
                $this->language_file_directory_manager,
                $this->absolute_path,
                $lang_key
            ) !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * get already installed languages (in db)
     */
    public function getInstalledLanguages(): array
    {
        return $this->repository()->getInstalledLanguages();
    }

    /**
     * get already installed local languages (in db)
     */
    public function getInstalledLocalLanguages(): array
    {
        return $this->repository()->getInstalledLocalLanguages();
    }

    /**
     * validate the logical structure of a lang-file
     *
     * This function checks if a lang-file of a given lang_key exists,
     * the file has a header, and each lang-entry consists of exactly
     * three elements (module, identifier, value).
     *
     * $a_lang_key     international language key (2 digits)
     */
    protected function checkLanguage(string $a_lang_key): bool
    {
        return $this->repository()->checkLanguage($a_lang_key);
    }

    /**
    * Delete languge data
    *
    * $a_lang_key lang key
    */
    public static function _deleteLangData(string $a_lang_key, bool $a_keep_local_change): void
    {
        global $ilDB;

        if (!$a_keep_local_change) {
            $ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = " .
                $ilDB->quote($a_lang_key, "text"));
        } else {
            $ilDB->manipulate("DELETE FROM lng_data WHERE lang_key = " .
                $ilDB->quote($a_lang_key, "text") .
                " AND local_change IS NULL");
        }
    }

    /**
    * get locally changed language entries
    * $a_lang_key language key
    * $a_min_date minimum change date "yyyy-mm-dd hh:mm:ss"
    * $a_max_date maximum change date "yyyy-mm-dd hh:mm:ss"
    * Returned value       [module][identifier] => value
    */
    public function getLocalChanges(string $a_lang_key, string $a_min_date = "", string $a_max_date = ""): array
    {
        return $this->repository()->getLocalChanges($a_lang_key, $a_min_date, $a_max_date);
    }

    /**
     * Searches for the existence of *.lang.local files.
     * Returns array with language keys
     */
    public function getLocalLanguages(): array
    {
        return $this->repository()->getLocalLanguages();
    }

    /**
     * Local language files that contain a requested key but do not match the naming scheme.
     *
     * @param list<string> $language_keys
     * @return list<string>
     */
    public function getInvalidLocalLanguageFiles(array $language_keys): array
    {
        return $this->repository()->getInvalidLocalLanguageFiles($language_keys);
    }

    /**
     * Return installable languages
     */
    public function getInstallableLanguages(): array
    {
        return $this->repository()->getInstallableLanguages();
    }

    /**
     * Takes precedence over $GLOBALS['ilDB'].
     *
     * Return true on success
     */
    public function setDbHandler(ilDBInterface $a_db_handler): bool
    {
        $this->db = $a_db_handler;
        return true;
    }

    public function loadLanguageModule(string $a_module): void
    {
    }
}
