<?php
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
     *
     * @var		string
     * @access	private
     */
    public $separator;
    public $comment_separator;
    public $lang_default;
    public $lang_user;
    public $lang_path;

    public $key;
    public $status;


    /**
     * Constructor
     *
     * @access	public
     * @param	integer	reference_id or object_id
     * @param	boolean	treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = false)
    {
        global $DIC;
        $lng = $DIC->language();

        $this->type = "lng";
        parent::__construct($a_id, $a_call_by_reference);

        $this->type = "lng";
        $this->key = $this->title;
        $this->status = $this->desc;
        $this->lang_default = $lng->lang_default;
        $this->lang_user = $lng->lang_user;
        $this->lang_path = $lng->lang_path;
        $this->cust_lang_path = $lng->cust_lang_path;
        $this->separator = $lng->separator;
        $this->comment_separator = $lng->comment_separator;
    }


    /**
     * Get the language objects of the installed languages
     * @return self[]
     */
    public static function getInstalledLanguages()
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
     * @return	string		language key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * get language status
     *
     * @return	string		language status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * check if language is system language
     */
    public function isSystemLanguage()
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
    public function isUserLanguage()
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
     * @return  boolean     true if installed
     */
    public function isInstalled()
    {
        if (substr($this->getStatus(), 0, 9) == "installed") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check language object status, and return true if a local language file
     * is installed.
     *
     * @return  boolean     true if local language is installed
     */
    public function isLocal()
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
     * @return	string	installed language key
     * @param   string  $scope  empty (global) or "local"
     */
    public function install($scope = '')
    {
        if (!empty($scope)) {
            if ($scope == 'global') {
                $scope = '';
            } else {
                $scopeExtension = '.' . $scope;
            }
        }

        if (($this->isInstalled() == false) ||
                ($this->isInstalled() == true && $this->isLocal() == false && !empty($scope))) {
            if ($this->check($scope)) {
                // lang-file is ok. Flush data in db and...
                if (empty($scope)) {
                    $this->flush('keep_local');
                }

                // ...re-insert data from lang-file
                $this->insert($scope);

                // update information in db-table about available/installed languages
                if (empty($scope)) {
                    $newDesc = 'installed';
                } elseif ($scope == 'local') {
                    $newDesc = 'installed_local';
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
     * @return	string	uninstalled language key
     */
    public function uninstall()
    {
        if ((substr($this->status, 0, 9) == "installed") && ($this->key != $this->lang_default) && ($this->key != $this->lang_user)) {
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
     * @return bool
     */
    public function refresh()
    {
        if ($this->isInstalled() == true) {
            if ($this->check()) {
                $this->flush('keep_local');
                $this->insert();
                $this->setTitle($this->getKey());
                $this->setDescription($this->getStatus());
                $this->update();

                if ($this->isLocal() == true) {
                    if ($this->check('local')) {
                        $this->insert('local');
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
    public static function refreshAll()
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
     * @var array|null	keys of languages to be refreshed (not yet supported, all available will be refreshed)
     */
    public static function refreshPlugins($a_lang_keys = null)
    {
        global $DIC;
        $ilPluginAdmin = $DIC['ilPluginAdmin'];

        // refresh languages of activated plugins
        include_once("./Services/Component/classes/class.ilPluginSlot.php");
        $slots = ilPluginSlot::getAllSlots();
        foreach ($slots as $slot) {
            $act_plugins = $ilPluginAdmin->getActivePluginsForSlot(
                $slot["component_type"],
                $slot["component_name"],
                $slot["slot_id"]
            );
            foreach ($act_plugins as $plugin) {
                include_once("./Services/Component/classes/class.ilPlugin.php");
                $pl = ilPlugin::getPluginObject(
                    $slot["component_type"],
                    $slot["component_name"],
                    $slot["slot_id"],
                    $plugin
                );
                if (is_object($pl)) {
                    $pl->updateLanguages($a_lang_keys);
                }
            }
        }
    }


    /**
    * Delete languge data
    *
    * @param	string		lang key
    */
    public static function _deleteLangData($a_lang_key, $a_keep_local_change = false)
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
     * @param   string     "all" or "keep_local"
     */
    public function flush($a_mode = 'all')
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        ilObjLanguage::_deleteLangData($this->key, ($a_mode == 'keep_local'));

        if ($a_mode == 'all') {
            $ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                $ilDB->quote($this->key, "text"));
        }
    }


    /**
    * get locally changed language entries
    * @param    string  	minimum change date "yyyy-mm-dd hh:mm:ss"
    * @param    string  	maximum change date "yyyy-mm-dd hh:mm:ss"
    * @return   array       [module][identifier] => value
    */
    public function getLocalChanges($a_min_date = "", $a_max_date = "")
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
    * @param    string  	language key
    * @return   array       change_date "yyyy-mm-dd hh:mm:ss"
    */
    public static function _getLastLocalChange($a_key)
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
            return $row['last_change'];
        } else {
            return "";
        }
    }


    /**
     * Get the local changes of a language module
     * @param string	$a_key		Language key
     * @param string	$a_module 	Module key
     * @return array	identifier => value
     */
    public static function _getLocalChangesByModule($a_key, $a_module)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $changes = array();
        $result = $ilDB->queryF(
            "SELECT * FROM lng_data WHERE lang_key = %s AND module = %s AND local_change IS NOT NULL",
            array('text', 'text'),
            array($a_key, $a_module)
        );

        while ($row = $ilDB->fetchAssoc($result)) {
            $changes[$row['identifier']] = $row['value'];
        }
        return $changes;
    }


    /**
     * insert language data from file into database
     *
     * @param   string  $scope  empty (global) or "local"
     */
    public function insert($scope = '')
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        if (!empty($scope)) {
            if ($scope == 'global') {
                $scope = '';
            } else {
                $scopeExtension = '.' . $scope;
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
                } elseif ($scope == 'local') {
                    // set the change date to import time for a local file
                    // get the modification date of the local file
                    // get the newer local changes for a local file
                    $change_date = date("Y-m-d H:i:s", time());
                    $min_date = date("Y-m-d H:i:s", filemtime($lang_file));
                    $local_changes = $this->getLocalChanges($min_date);
                }
                
                foreach ($content as $key => $val) {
                    // split the line of the language file
                    // [0]:	module
                    // [1]:	identifier
                    // [2]:	value
                    // [3]:	comment (optional)
                    $separated = explode($this->separator, trim($val));
                    $pos = strpos($separated[2], $this->comment_separator);
                    if ($pos !== false) {
                        $separated[3] = substr($separated[2], $pos + strlen($this->comment_separator));
                        $separated[2] = substr($separated[2], 0, $pos);
                    }

                    // check if the value has a local change
                    $local_value = $local_changes[$separated[0]][$separated[1]];

                    if (empty($scope)) {
                        // import of a global language file

                        if ($local_value != "" and $local_value != $separated[2]) {
                            // keep an existing and different local calue
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // check for double entries in global file
                            if ($double_checker[$separated[0]][$separated[1]][$this->key]) {
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
                                $separated[3]
                            );

                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    } elseif ($scope == 'local') {
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
                } elseif ($scope == 'local') {
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
    final public static function replaceLangModule($a_key, $a_module, $a_array)
    {
        global $DIC;
        $ilDB = $DIC->database();

        ilGlobalCache::flushAll();

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
            array('text','text'),
            array($a_key, $a_module)
        );
        $row = $ilDB->fetchAssoc($result);

        $unserialied = unserialize($row['lang_array']);
        if (!is_array($unserialied)) {
            /** @var ilErrorHandling $ilErr */
            $ilErr = $DIC['ilErr'];
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
        $a_module,
        $a_identifier,
        $a_lang_key,
        $a_value,
        $a_local_change = null,
        $a_remarks = null
    ) {
        global $DIC;
        $ilDB = $DIC->database();

        ilGlobalCache::flushAll();

        if (isset($a_remarks)) {
            $a_remarks = substr($a_remarks, 0, 250);
        }
        if ($a_remarks == '') {
            unset($a_remarks);
        }

        if (isset($a_value)) {
            $a_value = substr($a_value, 0, 4000);
        }
        if ($a_value == '') {
            unset($a_value);
        }

        $ilDB->replace(
            'lng_data',
            array(
                'module'		=> array('text',$a_module),
                'identifier'	=> array('text',$a_identifier),
                'lang_key'		=> array('text',$a_lang_key)
                ),
            array(
                'value'			=> array('text',$a_value),
                'local_change'	=> array('timestamp',$a_local_change),
                'remarks'       => array('text', $a_remarks)
            )
        );
        return true;
        
        /*
        $ilDB->manipulate(sprintf("DELETE FROM lng_data WHERE module = %s AND ".
            "identifier = %s AND lang_key = %s",
            $ilDB->quote($a_module, "text"), $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text")));


        $ilDB->manipulate(sprintf("INSERT INTO lng_data " .
            "(module, identifier, lang_key, value, local_change) " .
            "VALUES (%s,%s,%s,%s,%s)",
            $ilDB->quote($a_module, "text"), $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text"), $ilDB->quote($a_value, "text"),
            $ilDB->quote($a_local_change, "timestamp")));
        */
    }
    
    /**
    * Replace lang entry
    */
    final public static function updateLangEntry(
        $a_module,
        $a_identifier,
        $a_lang_key,
        $a_value,
        $a_local_change = null,
        $a_remarks = null
    ) {
        global $DIC;
        $ilDB = $DIC->database();

        if (isset($a_remarks)) {
            $a_remarks = substr($a_remarks, 0, 250);
        }
        if ($a_remarks == '') {
            unset($a_remarks);
        }

        if (isset($a_value)) {
            $a_value = substr($a_value, 0, 4000);
        }
        if ($a_value == '') {
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
    final public static function deleteLangEntry($a_module, $a_identifier, $a_lang_key)
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
     * @param	string		$lang_key	international language key (2 digits)
     */
    public function resetUserLanguage($lang_key)
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
     * @param	string	$content	expecting an ILIAS lang-file
     * @return	string	$content	content without header info OR false if no valid header was found
     */
    public static function cut_header($content)
    {
        foreach ($content as $key => $val) {
            if (trim($val) == "<!-- language file start -->") {
                return array_slice($content, $key +1);
            }
        }

        return false;
    }

    /**
     * optimizes the db-table langdata
     *
     * @return	boolean	true on success
     * @deprecated
     */
    public function optimizeData()
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
     * @return	string	system message
     * @param   string  $scope  empty (global) or "local"
     */
    public function check($scope = '')
    {
        include_once("./Services/Utilities/classes/class.ilStr.php");
        
        if (!empty($scope)) {
            if ($scope == 'global') {
                $scope = '';
            } else {
                $scopeExtension = '.' . $scope;
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
    public static function countUsers($a_lang)
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
        
        return (int) $rec["cnt"] + (int) $rec2["cnt"];
    }
} // END class.LanguageObject
