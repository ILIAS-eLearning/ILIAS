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
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;

/**
 * Class ilObjLanguage
 *
 * @author Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 *
 * @extends ilObject
 */
class ilObjLanguage extends ilObject
{
    /**
     * separator of module, comment separator, identifier & values
     * in language files
     */
    public string $separator;
    public string $comment_separator;
    public string $lang_default;
    public string $lang_user;
    public string $lang_path;
    public string $key;
    public string $status;
    public string $cust_lang_path;
    public string $absolute_path;
    private LanguageFileDirectoryManager $language_file_directory_manager;
    private InstalledLanguageRepository $repository;
    private LanguageInstallationManager $manager;

    /**
     * Constructor
     *
     * $a_id    reference_id or object_id
     * $a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = false,
        ?LanguageFileDirectoryManager $language_file_directory_manager = null
    ) {
        global $DIC;
        $lng = $DIC->language();

        // Fallback for when neither an explicit manager is injected nor the
        // DIC provides one. The constructor's first argument is the
        // local/customizing directory, not a global one - passing
        // MainLanguageFileDirectory there (as before) mislabeled the global
        // lang/ directory as "local" and left no global directory at all,
        // which would make every entry in it look like a local override.
        $this->language_file_directory_manager = $language_file_directory_manager
            ?? ($DIC[LanguageFileDirectoryManager::class] ?? null)
            ?? new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory());

        $this->type = "lng";
        parent::__construct($a_id, $a_call_by_reference);

        $this->type = "lng";
        $this->key = $this->title;
        $this->status = $this->desc;
        $this->lang_default = $lng->lang_default;
        $this->lang_user = $lng->lang_user;
        $this->lang_path = $lng->lang_path;
        $this->cust_lang_path = $lng->getCustomLangPath();
        $this->separator = $lng->separator;
        $this->comment_separator = $lng->comment_separator;
        // This file lives at components/ILIAS/Language/classes/ - four
        // levels below the ILIAS root, not five. The extra "../" here used
        // to point one directory too high (e.g. /var/www instead of
        // /var/www/html), so the "Main" language directory (lang/) was never
        // found - check()/refresh()/removeLocalChanges() would then always
        // fail with "file not valid", for every language, even though the
        // file itself was fine. Compare Language.php's own (correct)
        // 3-level "../../../ " from components/ILIAS/Language/.
        $this->absolute_path = (string) realpath(__DIR__ . "/../../../../");

        // Single source of truth for file-based language check/insert
        // operations - see check()/insert() below. This avoids duplicating
        // the file-parsing/DB-writing logic that used to live separately in
        // this class and in ilObjLanguageDBAccess. Repository (read) and
        // Manager (write) used to be bundled into ilSetupLanguage - see its
        // class docblock and docs/development/repository-pattern.md.
        $this->repository = new InstalledLanguageDatabaseRepository(
            $DIC->database(),
            $this->language_file_directory_manager,
            $this->absolute_path
        );
        $this->manager = new LanguageInstallationManager(
            $DIC->database(),
            $this->language_file_directory_manager,
            $this->absolute_path,
            $this->repository
        );
    }


    /**
     * Get the language objects of the installed languages
     */
    public static function getInstalledLanguages(): array
    {
        $objects = array();
        $languages = ilObject::_getObjectsByType("lng");
        foreach ($languages as $lang) {
            $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
            if ($langObj->isInstalled()) {
                $objects[] = $langObj;
            } else {
                unset($langObj);
            }
        }
        return $objects;
    }


    /**
     * Return the language keys of the installed languages
     *
     * KNOWN ISSUE (not fixed here, flagged for a follow-up): this checks for
     * the exact status "installed" and therefore misses languages with the
     * status "installed_local", unlike ilLanguage::_getInstalledLanguages()
     * which matches on str_starts_with($desc, "installed") and thus includes
     * both. This affects ilPluginLanguage's language selection.
     *
     * @return array
     */
    public static function getLangKeysOfInstalledLanguages(): array
    {
        $lang_keys = [];
        foreach (ilObject::_getObjectsByType("lng") as $lang) {
            if ($lang['desc'] === 'installed') {
                $lang_keys[] = $lang['title'];
            }
        }
        return $lang_keys;
    }


    /**
     * get language key
     *
     * Return language key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * get language status
     *
     * Return language status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * check if language is system language
     */
    public function isSystemLanguage(): bool
    {
        if ($this->key == $this->lang_default) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * check if language is system language
     */
    public function isUserLanguage(): bool
    {
        if ($this->key == $this->lang_user) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check language object status, and return true if language is installed.
     *
     * Return     true if installed
     */
    public function isInstalled(): bool
    {
        if (str_starts_with($this->getStatus(), "installed")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check language object status, and return true if a local language file
     * is installed.
     *
     * Return     true if local language is installed
     */
    public function isLocal(): bool
    {
        if (substr($this->getStatus(), 10) === "local") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * install current language
     *
     * $scope empty (global) or "local"
     * Return installed language key
     */
    public function install(string $scope = ""): string
    {
        if ($scope === "global") {
            $scope = "";
        }

        if (!$this->isInstalled() || (!$this->isLocal() && !empty($scope))) {
            if ($this->check($scope)) {
                // lang-file is ok. Flush data in db and...
                if (empty($scope)) {
                    $this->flush("keep_local");
                }

                // ...re-insert data from lang-file
                $this->insert($scope);

                // update information in db-table about available/installed languages
                $newDesc = '';
                if (empty($scope)) {
                    $newDesc = "installed";
                } elseif ($scope === "local") {
                    $newDesc = "installed_local";
                }
                $this->setDescription($newDesc);
                $this->update();
                return $this->getKey();
            }
        }
        return "";
    }


    /**
     * uninstall current language
     *
     * Return uninstalled language key
     */
    public function uninstall(): string
    {
        if ((str_starts_with($this->status, "installed")) && ($this->key != $this->lang_default) && ($this->key != $this->lang_user)) {
            $this->flush();
            $this->setTitle($this->key);
            $this->setDescription("not_installed");
            $this->update();
            $this->resetUserLanguage($this->key);

            return $this->key;
        }
        return "";
    }


    /**
     * refresh current language
     *
     * A single insert() call now covers both global and local/customizing
     * content in one pass (see insert()'s doc comment), so the separate
     * "refresh local on top" pass that used to follow is no longer needed.
     */
    public function refresh(): bool
    {
        if ($this->isInstalled() && $this->check()) {
            $this->flush("keep_local");
            $this->insert();
            $this->setTitle($this->getKey());
            $this->setDescription($this->getStatus());
            $this->update();

            return true;
        }

        return false;
    }

    /**
     * Refresh all installed languages.
     *
     * @deprecated its only caller in this codebase,
     * ilObjLanguageFolderGUI::refreshObject(), was removed as unreachable
     * dead code (no link or ilCtrl command ever dispatched to it - verified
     * repository-wide) once ilObjLanguageFolderGUI::refreshSelectedObject()
     * was migrated to \ILIAS\Language\Activities\UpdateLanguage. Kept here,
     * rather than removed outright, only because this is a public static
     * method of a public API class that external/plugin code could still be
     * calling directly.
     */
    public static function refreshAll(): void
    {
        $languages = ilObject::_getObjectsByType("lng");
        $refreshed = array();

        foreach ($languages as $lang) {
            $langObj = new ilObjLanguage($lang["obj_id"], false);
            if ($langObj->refresh()) {
                $refreshed[] = $langObj->getKey();
            }
            unset($langObj);
        }

        self::refreshPlugins($refreshed);
    }


    /**
     * Refresh languages of activated plugins
     * $a_lang_keys    keys of languages to be refreshed (not yet supported, all available will be refreshed)
     */
    public static function refreshPlugins(?array $a_lang_keys = null): void
    {
        global $DIC;

        $component_repository = $DIC["component.repository"];
        foreach ($component_repository->getPlugins() as $plugin) {
            if (!$plugin->isActive()) {
                continue;
            }
            $handler = new ilPluginLanguage($plugin);
            $handler->updateLanguages($a_lang_keys);
        }
    }


    /**
    * Delete languge data
    ** $a_lang_key    lang key
    */
    public static function _deleteLangData(string $a_lang_key, bool $a_keep_local_change = false): void
    {
        global $DIC;
        $ilDB = $DIC->database();

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
     * remove language data from database
     * $a_mode     "all" or "keep_local"
     */
    public function flush(string $a_mode = "all"): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        self::_deleteLangData($this->key, ($a_mode === "keep_local"));

        if ($a_mode === "all") {
            $ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                $ilDB->quote($this->key, "text"));
        }
    }


    /**
    * get locally changed language entries
    * $a_min_date    minimum change date "yyyy-mm-dd hh:mm:ss"
    * $a_max_date    maximum change date "yyyy-mm-dd hh:mm:ss"
    * Return array       [module][identifier] => value
    */
    public function getLocalChanges(string $a_min_date = "", string $a_max_date = ""): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        if ($a_min_date === "") {
            $a_min_date = "1980-01-01 00:00:00";
        }
        if ($a_max_date === "") {
            $a_max_date = "2200-01-01 00:00:00";
        }

        $q = sprintf(
            "SELECT module, identifier, value FROM lng_data WHERE lang_key = %s " .
            "AND local_change >= %s AND local_change <= %s",
            $ilDB->quote($this->key, "text"),
            $ilDB->quote($a_min_date, "timestamp"),
            $ilDB->quote($a_max_date, "timestamp")
        );
        $result = $ilDB->query($q);

        $changes = array();
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $changes[$row["module"]][$row["identifier"]] = $row["value"];
        }
        return $changes;
    }


    /**
    * get the date of the last local change
    * $a_key    language key
    * Return change_date "yyyy-mm-dd hh:mm:ss"
    */
    public static function _getLastLocalChange(string $a_key): string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $q = sprintf(
            "SELECT MAX(local_change) last_change FROM lng_data " .
                    "WHERE lang_key = %s AND local_change IS NOT NULL",
            $ilDB->quote($a_key, "text")
        );
        $result = $ilDB->query($q);

        if ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return (string) $row["last_change"];
        } else {
            return "";
        }
    }


    /**
     * Get the local changes of a language module
     * $a_key          Language key
     * $a_module       Module key
     * Return array    identifier => value
     */
    public static function _getLocalChangesByModule(string $a_key, string $a_module): array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $changes = array();
        $result = $ilDB->queryF(
            "SELECT identifier, value FROM lng_data WHERE lang_key = %s AND module = %s AND local_change IS NOT NULL",
            array("text", "text"),
            array($a_key, $a_module)
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            $changes[$row["identifier"]] = $row["value"];
        }
        return $changes;
    }


    /**
     * insert language data from file into database
     *
     * @deprecated $scope is accepted for backwards compatibility but no
     * longer selects a separate write path: LanguageInstallationManager::insertLanguageForInstallation()
     * always processes every directory the LanguageFileDirectoryManager
     * knows about (global/component directories *and* the customizing/local
     * one) in a single, idempotent pass, correctly preserving local
     * overrides for global entries. This replaces the previous separate
     * ilObjLanguageDBAccess-based write path (removed). No caller in this
     * repository passes a non-empty $scope any more (verified repo-wide) -
     * the parameter is kept only in case external/plugin code still calls
     * this public method with one; remove it once that can be ruled out.
     */
    public function insert(string $scope = ""): void
    {
        $this->manager->insertLanguageForInstallation($this->key);
    }

    /**
     * Remove all local changes of this language - both entries edited
     * directly via the "adjust language variables" table and any override
     * coming from a customizing/local language file - and reinstall the
     * language purely from the global/component language files.
     *
     * This must go through insertLanguageForRemovingLocalChanges() rather
     * than insert()/insertLanguageForInstallation(): the latter always
     * merges the customizing directory back in, which would immediately
     * reinstate the very data this method is supposed to remove (see
     * LanguageInstallationManager::insertLanguageForRemovingLocalChanges()).
     *
     * Return true if the language was installed and could be reinstalled
     */
    public function removeLocalChanges(): bool
    {
        if (!$this->isInstalled() || !$this->check()) {
            return false;
        }

        $this->flush("all");
        $this->manager->insertLanguageForRemovingLocalChanges($this->key);
        $this->setTitle($this->getKey());
        $this->setDescription("installed");
        $this->update();

        return true;
    }

    /**
    * Replace language module array
    */
    final public static function replaceLangModule(string $a_key, string $a_module, array $a_array): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        // avoid flushing the whole cache (see mantis #28818)
        ilCachedLanguage::getInstance($a_key)->deleteInCache();

        $ilDB->manipulate(sprintf(
            "DELETE FROM lng_modules WHERE lang_key = %s AND module = %s",
            $ilDB->quote($a_key, "text"),
            $ilDB->quote($a_module, "text")
        ));

        /*$ilDB->manipulate(sprintf("INSERT INTO lng_modules (lang_key, module, lang_array) VALUES ".
            "(%s,%s,%s)", $ilDB->quote($a_key, "text"),
            $ilDB->quote($a_module, "text"),
            $ilDB->quote(serialize($a_array), "clob")));*/
        $ilDB->insert("lng_modules", array(
            "lang_key" => array("text", $a_key),
            "module" => array("text", $a_module),
            "lang_array" => array("clob", serialize($a_array))
            ));

        // check if the module is correctly saved
        // see mantis #20046 and #19140
        $result = $ilDB->queryF(
            "SELECT lang_array FROM lng_modules WHERE lang_key = %s AND module = %s",
            array("text","text"),
            array($a_key, $a_module)
        );
        $row = $ilDB->fetchAssoc($result);

        $unserialied = unserialize($row["lang_array"], ["allowed_classes" => false]);
        if (!is_array($unserialied)) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage(
                'failure',
                "Data for module '" . $a_module . "' of  language '" . $a_key . "' is not correctly saved. " .
                "Please check the collation of your database tables lng_data and lng_modules. It must be utf8_unicode_ci.",
                true
            );
            $DIC->ctrl()->redirectByClass(ilobjlanguagefoldergui::class, 'view');
        }
    }

    /**
    * Replace lang entry
    */
    final public static function replaceLangEntry(
        string $a_module,
        string $a_identifier,
        string $a_lang_key,
        string $a_value,
        ?string $a_local_change = null,
        ?string $a_remarks = null
    ): bool {
        global $DIC;
        $ilDB = $DIC->database();

        // avoid a cache flush here (see mantis #28818)
        // ilGlobalCache::flushAll();

        if (is_string($a_remarks) && $a_remarks !== '') {
            $a_remarks = substr($a_remarks, 0, 250);
        }

        if ($a_remarks === '') {
            $a_remarks = null;
        }

        if ($a_value === "") {
            $a_value = null;
        } else {
            $a_value = substr($a_value, 0, 4000);
        }

        $ilDB->replace(
            "lng_data",
            array(
                "module" => array("text",$a_module),
                "identifier" => array("text",$a_identifier),
                "lang_key" => array("text",$a_lang_key)
                ),
            array(
                "value" => array("text",$a_value),
                "local_change" => array("timestamp",$a_local_change),
                "remarks" => array("text", $a_remarks)
            )
        );
        return true;
    }

    /**
    * Replace lang entry
    */
    final public static function updateLangEntry(
        string $a_module,
        string $a_identifier,
        string $a_lang_key,
        string $a_value,
        ?string $a_local_change = null,
        ?string $a_remarks = null
    ): void {
        global $DIC;
        $ilDB = $DIC->database();

        if (is_string($a_remarks) && $a_remarks !== '') {
            $a_remarks = substr($a_remarks, 0, 250);
        }

        if ($a_remarks === '') {
            $a_remarks = null;
        }

        if ($a_value === "") {
            $a_value = null;
        } else {
            $a_value = substr($a_value, 0, 4000);
        }

        $ilDB->manipulate(sprintf(
            "UPDATE lng_data " .
            "SET value = %s, local_change = %s, remarks = %s " .
            "WHERE module = %s AND identifier = %s AND lang_key = %s ",
            $ilDB->quote($a_value, "text"),
            $ilDB->quote($a_local_change, "timestamp"),
            $ilDB->quote($a_remarks, "text"),
            $ilDB->quote($a_module, "text"),
            $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text")
        ));
    }


    /**
    * Delete lang entry
    */
    final public static function deleteLangEntry(string $a_module, string $a_identifier, string $a_lang_key): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulate(sprintf(
            "DELETE FROM lng_data " .
            "WHERE module = %s AND identifier = %s AND lang_key = %s ",
            $ilDB->quote($a_module, "text"),
            $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text")
        ));

        return true;
    }


    /**
     * search ILIAS for users which have selected '$lang_key' as their prefered language and
     * reset them to default language (english). A message is sent to all affected users
     *
     * $lang_key    international language key (2 digits)
     */
    public function resetUserLanguage(string $lang_key): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = "UPDATE usr_pref SET " .
                "value = " . $ilDB->quote($this->lang_default, "text") . " " .
                "WHERE keyword = " . $ilDB->quote('language', "text") . " " .
                "AND value = " . $ilDB->quote($lang_key, "text");
        $ilDB->manipulate($query);
    }

    /**
     * remove lang-file haeder information from '$content'
     * This function seeks for a special keyword where the language information starts.
     * if found it returns the plain language information, otherwise returns false
     *
     * $content   expecting an ILIAS lang-file
     * Return content without header info OR false if no valid header was found
     * @return bool|array
     */
    public static function cut_header(array $content)
    {
        foreach ($content as $key => $val) {
            if (trim($val) === "<!-- language file start -->") {
                return array_slice($content, $key + 1);
            }
        }

        return false;
    }

    /**
     * optimizes the db-table langdata
     *
     * Return true on success
     * @deprecated
     */
    public function optimizeData(): bool
    {
        // Mantis #22313: removed table optimization
        return true;
    }

    /**
     * Validate the logical structure of a lang file.
     * This function checks if a lang file exists, the file has a
     * header, and each lang-entry consists of exactly three elements
     * (module, identifier, value).
     *
     * $scope  empty/"global" (all managed directories) or "local"
     *         (customizing directory only)
     *
     * Delegates to InstalledLanguageRepository, which already implements
     * this validation, keyed off the LanguageFileDirectoryManager. This
     * used to be a separate, largely duplicated implementation that also
     * caused hard UI redirects from this model class - callers (e.g.
     * ilObjLanguageFolderGUI) are now responsible for turning a `false`
     * return into user-facing feedback.
     */
    public function check(string $scope = ""): bool
    {
        if ($scope === "local") {
            return $this->repository->checkLocalLanguageFile($this->key);
        }

        return $this->repository->checkLanguage($this->key);
    }

    /**
    * Count number of users that use a language
    */
    public static function countUsers(string $a_lang): int
    {
        global $DIC;
        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $set = $ilDB->query("SELECT COUNT(*) cnt FROM usr_data ud JOIN usr_pref up" .
            " ON ud.usr_id = up.usr_id " .
            " WHERE up.value = " . $ilDB->quote($a_lang, "text") .
            " AND up.keyword = " . $ilDB->quote("language", "text"));
        $rec = $ilDB->fetchAssoc($set);

        // add users with no usr_pref set to default language
        if ($a_lang == $lng->lang_default) {
            $set2 = $ilDB->query("SELECT COUNT(*) cnt FROM usr_data ud LEFT JOIN usr_pref up" .
                " ON (ud.usr_id = up.usr_id AND up.keyword = " . $ilDB->quote("language", "text") . ")" .
                " WHERE up.value IS NULL ");
            $rec2 = $ilDB->fetchAssoc($set2);
        }

        return (int) $rec["cnt"] + (int) ($rec2["cnt"] ?? 0);
    }
} // END class.LanguageObject
