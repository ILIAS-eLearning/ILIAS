<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

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

    /**
     * Constructor
     *
     * $a_id    reference_id or object_id
     * $a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = false)
    {
        global $DIC;
        $lng = $DIC->language();

        $this->type = "lng";
        parent::__construct($a_id, $a_call_by_reference);

        $this->type = "lng";
        $this->key = $this->title;
        $this->status = (string) $this->desc;
        $this->lang_default = $lng->lang_default;
        $this->lang_user = $lng->lang_user;
        $this->lang_path = $lng->lang_path;
        $this->cust_lang_path = $lng->getCustomLangPath();
        $this->separator = $lng->separator;
        $this->comment_separator = $lng->comment_separator;
    }


    /**
     * Get the language objects of the installed languages
     */
    public static function getInstalledLanguages() : array
    {
        $objects = array();
        $languages = ilObject::_getObjectsByType("lng");
        foreach ($languages as $lang) {
            $langObj = new ilObjLanguage($lang["obj_id"], false);
            if ($langObj->isInstalled()) {
                $objects[] = $langObj;
            } else {
                unset($langObj);
            }
        }
        return $objects;
    }


    /**
     * get language key
     *
     * Return language key
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * get language status
     *
     * Return language status
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * check if language is system language
     */
    public function isSystemLanguage() : bool
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
    public function isUserLanguage() : bool
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
    public function isInstalled() : bool
    {
        if (strpos($this->getStatus(), "installed") === 0) {
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
    public function isLocal() : bool
    {
        if (substr($this->getStatus(), 10) == "local") {
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
    public function install(string $scope = "") : string
    {
        if (!empty($scope)) {
            if ($scope == "global") {
                $scope = "";
            } else {
                $scopeExtension = "." . $scope;
            }
        }

        if (($this->isInstalled() == false) ||
                ($this->isInstalled() == true && $this->isLocal() == false && !empty($scope))) {
            if ($this->check($scope)) {
                // lang-file is ok. Flush data in db and...
                if (empty($scope)) {
                    $this->flush("keep_local");
                }

                // ...re-insert data from lang-file
                $this->insert($scope);

                // update information in db-table about available/installed languages
                if (empty($scope)) {
                    $newDesc = "installed";
                } elseif ($scope == "local") {
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
    public function uninstall() : string
    {
        if ((strpos($this->status, "installed") === 0) && ($this->key != $this->lang_default) && ($this->key != $this->lang_user)) {
            $this->flush('all');
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
     */
    public function refresh() : bool
    {
        if ($this->isInstalled() == true) {
            if ($this->check()) {
                $this->flush("keep_local");
                $this->insert();
                $this->setTitle($this->getKey());
                $this->setDescription($this->getStatus());
                $this->update();

                if ($this->isLocal() == true) {
                    if ($this->check("local")) {
                        $this->insert("local");
                        $this->setTitle($this->getKey());
                        $this->setDescription($this->getStatus());
                        $this->update();
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
    * Refresh all installed languages
    */
    public static function refreshAll() : void
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
    public static function refreshPlugins(array $a_lang_keys = null) : void
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
    public static function _deleteLangData(string $a_lang_key, bool $a_keep_local_change = false) : void
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
    public function flush(string $a_mode = "all") : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        ilObjLanguage::_deleteLangData($this->key, ($a_mode == "keep_local"));

        if ($a_mode == "all") {
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
    public function getLocalChanges(string $a_min_date = "", string $a_max_date = "") : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        if ($a_min_date == "") {
            $a_min_date = "1980-01-01 00:00:00";
        }
        if ($a_max_date == "") {
            $a_max_date = "2200-01-01 00:00:00";
        }
        
        $q = sprintf(
            "SELECT * FROM lng_data WHERE lang_key = %s " .
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
    public static function _getLastLocalChange(string $a_key) : string
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
    public static function _getLocalChangesByModule(string $a_key, string $a_module) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $changes = array();
        $result = $ilDB->queryF(
            "SELECT * FROM lng_data WHERE lang_key = %s AND module = %s AND local_change IS NOT NULL",
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
     * $scope  empty (global) or "local"
     */
    public function insert(string $scope = "") : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $scopeExtension = "";
        if (!empty($scope)) {
            if ($scope == "global") {
                $scope = "";
            } else {
                $scopeExtension = "." . $scope;
            }
        }
        
        $path = $this->lang_path;
        if ($scope == "local") {
            $path = $this->cust_lang_path;
        }

        $lang_file = $path . "/ilias_" . $this->key . ".lang" . $scopeExtension;

        if (is_file($lang_file)) {
            // initialize the array for updating lng_modules below
            $lang_array = array();
            $lang_array["common"] = array();

            // remove header first
            if ($content = $this->cut_header(file($lang_file))) {
                if (empty($scope)) {
                    // reset change date for a global file
                    // get all local changes for a global file
                    $change_date = null;
                    $local_changes = $this->getLocalChanges();
                } elseif ($scope == "local") {
                    // set the change date to import time for a local file
                    // get the modification date of the local file
                    // get the newer local changes for a local file
                    $change_date = date("Y-m-d H:i:s", time());
                    $min_date = date("Y-m-d H:i:s", filemtime($lang_file));
                    $local_changes = $this->getLocalChanges($min_date);
                }
                $double_checker = [];
                foreach ($content as $key => $val) {
                    // split the line of the language file
                    // [0]: module
                    // [1]: identifier
                    // [2]: value
                    // [3]: comment (optional)
                    $separated = explode($this->separator, trim($val));
                    $pos = strpos($separated[2], $this->comment_separator);
                    if ($pos !== false) {
                        $separated[3] = substr($separated[2], $pos + strlen($this->comment_separator));
                        $separated[2] = substr($separated[2], 0, $pos);
                    }

                    // check if the value has a local change
                    $local_value = $local_changes[$separated[0]][$separated[1]] ?? "";

                    if (empty($scope)) {
                        // import of a global language file

                        if ($local_value != "" and $local_value != $separated[2]) {
                            // keep an existing and different local calue
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // check for double entries in global file
                            if ($double_checker[$separated[0]][$separated[1]][$this->key] ?? false) {
                                $this->ilias->raiseError(
                                    "Duplicate Language Entry in $lang_file:\n$val",
                                    $this->ilias->error_obj->MESSAGE
                                );
                            }
                            $double_checker[$separated[0]][$separated[1]][$this->key] = true;
                            
                            // insert a new value if no local value exists
                            // reset local change date if the values are equal
                            ilObjLanguage::replaceLangEntry(
                                $separated[0],
                                $separated[1],
                                $this->key,
                                $separated[2],
                                $change_date,
                                $separated[3] ?? null
                            );

                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    } elseif ($scope == "local") {
                        // import of a local language file

                        if ($local_value != "") {
                            // keep a locally changed value that is newer than the file
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // insert a new value if no global value exists
                            // (local files may have additional entries for customizations)
                            // set the change date to the import date
                            ilObjLanguage::replaceLangEntry(
                                $separated[0],
                                $separated[1],
                                $this->key,
                                $separated[2],
                                $change_date,
                                $separated[3]
                            );

                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    }
                }

                $ld = "";
                if (empty($scope)) {
                    $ld = "installed";
                } elseif ($scope == "local") {
                    $ld = "installed_local";
                }
                if ($ld) {
                    $query = "UPDATE object_data SET " .
                            "description = " . $ilDB->quote($ld, "text") . ", " .
                            "last_update = " . $ilDB->now() . " " .
                            "WHERE title = " . $ilDB->quote($this->key, "text") . " " .
                            "AND type = 'lng'";
                    $ilDB->manipulate($query);
                }
            }
            
            foreach ($lang_array as $module => $lang_arr) {
                if ($scope == "local") {
                    $q = "SELECT * FROM lng_modules WHERE " .
                        " lang_key = " . $ilDB->quote($this->key, "text") .
                        " AND module = " . $ilDB->quote($module, "text");
                    $set = $ilDB->query($q);
                    $row = $ilDB->fetchAssoc($set);
                    $arr2 = unserialize($row["lang_array"]);
                    if (is_array($arr2)) {
                        $lang_arr = array_merge($arr2, $lang_arr);
                    }
                }
                ilObjLanguage::replaceLangModule($this->key, $module, $lang_arr);
            }
        }
    }

    /**
    * Replace language module array
    */
    final public static function replaceLangModule(string $a_key, string $a_module, array $a_array) : void
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
            "lang_array" => array("clob", serialize((array) $a_array))
            ));

        // check if the module is correctly saved
        // see mantis #20046 and #19140
        $result = $ilDB->queryF(
            "SELECT lang_array FROM lng_modules WHERE lang_key = %s AND module = %s",
            array("text","text"),
            array($a_key, $a_module)
        );
        $row = $ilDB->fetchAssoc($result);

        $unserialied = unserialize($row["lang_array"]);
        if (!is_array($unserialied)) {
            /** @var ilErrorHandling $ilErr */
            $ilErr = $DIC["ilErr"];
            $ilErr->raiseError(
                "Data for module '" . $a_module . "' of  language '" . $a_key . "' is not correctly saved. " .
                "Please check the collation of your database tables lng_data and lng_modules. It must be utf8_unicode_ci.",
                $ilErr->MESSAGE
            );
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
        string $a_local_change = null,
        string $a_remarks = null
    ) : bool {
        global $DIC;
        $ilDB = $DIC->database();

        // avoid a cache flush here (see mantis #28818)
        // ilGlobalCache::flushAll();

        if (isset($a_remarks)) {
            $a_remarks = substr($a_remarks, 0, 250);
        }
        if ($a_remarks == '') {
            $a_remarks = null;
        }

        if (isset($a_value)) {
            $a_value = substr($a_value, 0, 4000);
        }
        if ($a_value == '') {
            $a_value = null;
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
        string $a_local_change = null,
        string $a_remarks = null
    ) : void {
        global $DIC;
        $ilDB = $DIC->database();

        if (isset($a_remarks)) {
            $a_remarks = substr($a_remarks, 0, 250);
        }
        if ($a_remarks == "") {
            unset($a_remarks);
        }

        if (isset($a_value)) {
            $a_value = substr($a_value, 0, 4000);
        }
        if ($a_value == "") {
            unset($a_value);
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
    final public static function deleteLangEntry(string $a_module, string $a_identifier, string $a_lang_key) : bool
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
    public function resetUserLanguage(string $lang_key) : void
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
            if (trim($val) == "<!-- language file start -->") {
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
    public function optimizeData() : bool
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
     * $scope  empty (global) or "local"
     * Return system message
     */
    public function check(string $scope = "") : bool
    {
        include_once "./Services/Utilities/classes/class.ilStr.php";
        $scopeExtension = "";
        if (!empty($scope)) {
            if ($scope == "global") {
                $scope = "";
            } else {
                $scopeExtension = "." . $scope;
            }
        }

        $path = $this->lang_path;
        if ($scope == "local") {
            $path = $this->cust_lang_path;
        }
        
        $tmpPath = getcwd();
        
        // dir check
        if (!is_dir($path)) {
            $this->ilias->raiseError("Directory not found: " . $path, $this->ilias->error_obj->MESSAGE);
        }

        chdir($path);

        // compute lang-file name format
        $lang_file = "ilias_" . $this->key . ".lang" . $scopeExtension;

        // file check
        if (!is_file($lang_file)) {
            $this->ilias->raiseError("File not found: " . $lang_file, $this->ilias->error_obj->MESSAGE);
        }

        // header check
        $content = $this->cut_header(file($lang_file));
        if ($content === false) {
            $this->ilias->raiseError("Wrong Header in " . $lang_file, $this->ilias->error_obj->MESSAGE);
        }
        
        // check (counting) elements of each lang-entry
        $line = 0;
        $n = 0;
        foreach ($content as $key => $val) {
            $separated = explode($this->separator, trim($val));
            $num = count($separated);
            ++$n;
            if ($num != 3) {
                $line = $n + 36;
                $this->ilias->raiseError("Wrong parameter count in " . $lang_file . " in line $line (Value: $val)! Please check your language file!", $this->ilias->error_obj->MESSAGE);
            }
            if (!ilStr::isUtf8($separated[2])) {
                $this->ilias->raiseError("Non UTF8 character found in " . $lang_file . " in line $line (Value: $val)! Please check your language file!", $this->ilias->error_obj->MESSAGE);
            }
        }

        chdir($tmpPath);

        // no error occured
        return true;
    }
    
    /**
    * Count number of users that use a language
    */
    public static function countUsers(string $a_lang) : int
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
