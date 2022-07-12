<?php declare(strict_types=1);
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
 ********************************************************************
 */
 
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
 *
 * @todo The DATE field is not set correctly on changes of a language (update, install, your stable).
 *  The format functions do not belong in class.Language. Those are also applicable elsewhere.
 *  Therefore, they would be better placed in class.Format
 * @todo This somehow needs to be reconciled with the base class and most probably be factored
 *  into two classes, one for management, one for retrieval.
 */
class ilSetupLanguage extends ilLanguage
{
    public array $text;
    public string $lang_default = "en";
    public string $lang_path;
    public string $lang_key;
    public string $separator = "#:#";
    public string $comment_separator = "###";
    protected ilDBInterface $db;

    public function __construct(string $a_lang_key)
    {
        $this->lang_key = $a_lang_key ?: $this->lang_default;
        $il_absolute_path = realpath(__DIR__ . "/../../../../");
        $this->lang_path = $il_absolute_path . "/lang";
        $this->cust_lang_path = $il_absolute_path . "/Customizing/global/lang";
    }
    
    /**
     * gets the text for a given topic
     *
     * if the topic is not in the list, the topic itself with "-" will be returned
     *
     * $a_topic    topic
     */
    public function txt(string $a_topic, string $a_default_lang_fallback_mod = '') : string
    {
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
            $log->writeLanguageLog($a_topic, $this->lang_key);
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
    public function installLanguages(array $a_lang_keys, array $a_local_keys)
    {
        global $ilDB;
        
        if (empty($a_lang_keys)) {
            $a_lang_keys = array();
        }
        
        if (empty($a_local_keys)) {
            $a_local_keys = array();
        }

        $err_lang = array();

        $db_langs = $this->getAvailableLanguages();

        foreach ($a_lang_keys as $lang_key) {
            if ($this->checkLanguage($lang_key)) {
                $this->flushLanguage($lang_key, "keep_local");
                $this->insertLanguage($lang_key);
                
                if (in_array($lang_key, $a_local_keys, true) && is_dir($this->cust_lang_path)) {
                    if ($this->checkLanguage($lang_key, "local")) {
                        $this->insertLanguage($lang_key, "local");
                    } else {
                        $err_lang[] = $lang_key;
                    }
                }
                
                // register language first time install
                if (!array_key_exists($lang_key, $db_langs)) {
                    if (in_array($lang_key, $a_local_keys, true)) {
                        $itype = "installed_local";
                    } else {
                        $itype = "installed";
                    }
                    $lid = $ilDB->nextId("object_data");
                    $query = "INSERT INTO object_data " .
                            "(obj_id,type,title,description,owner,create_date,last_update) " .
                            "VALUES " .
                            "(" .
                            $ilDB->quote($lid, "integer") . "," .
                            $ilDB->quote("lng", "text") . "," .
                            $ilDB->quote($lang_key, "text") . "," .
                            $ilDB->quote($itype, "text") . "," .
                            $ilDB->quote("-1", "integer") . "," .
                            $ilDB->now() . "," .
                            $ilDB->now() .
                            ")";
                    $ilDB->manipulate($query);
                }
            } else {
                $err_lang[] = $lang_key;
            }
        }
        
        foreach ($db_langs as $key => $val) {
            if (!in_array($key, $err_lang, true)) {
                if (in_array($key, $a_lang_keys, true)) {
                    if (in_array($key, $a_local_keys, true)) {
                        $ld = "installed_local";
                    } else {
                        $ld = "installed";
                    }
                    $query = "UPDATE object_data SET " .
                            "description = " . $ilDB->quote($ld, "text") . ", " .
                            "last_update = " . $ilDB->now() . " " .
                            "WHERE obj_id = " . $ilDB->quote($val["obj_id"], "integer") . " " .
                            "AND type = " . $ilDB->quote("lng", "text");
                    $ilDB->manipulate($query);
                } else {
                    $this->flushLanguage($key, "all");

                    if (strpos($val["status"], "installed") === 0) {
                        $query = "UPDATE object_data SET " .
                                "description = " . $ilDB->quote("not_installed", "text") . ", " .
                                "last_update = " . $ilDB->now() . " " .
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
     * get already installed languages (in db)
     */
    public function getInstalledLanguages() : array
    {
        global $ilDB;
        
        $arr = array();

        $query = "SELECT * FROM object_data " .
                "WHERE type = " . $ilDB->quote("lng", "text") . " " .
                "AND " . $ilDB->like("description", "text", "installed%");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }

        return $arr;
    }
    
    /**
     * get already installed local languages (in db)
     */
    public function getInstalledLocalLanguages() : array
    {
        global $ilDB;
        
        $arr = array();

        $query = "SELECT * FROM object_data " .
                "WHERE type = " . $ilDB->quote("lng", "text") . " " .
                "AND description = " . $ilDB->quote("installed_local", "text");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }

        return $arr;
    }

    /**
     * get already registered languages (in db)
     */
    protected function getAvailableLanguages() : array
    {
        global $ilDB;
        
        $arr = array();

        $query = "SELECT * FROM object_data " .
                "WHERE type = " . $ilDB->quote("lng", "text");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[$row->title]["obj_id"] = $row->obj_id;
            $arr[$row->title]["status"] = $row->description;
        }

        return $arr;
    }

    /**
     * validate the logical structure of a lang-file
     *
     * This function checks if a lang-file of a given lang_key exists,
     * the file has a header, and each lang-entry consists of exactly
     * three elements (module, identifier, value).
     *
     * $a_lang_key     international language key (2 digits)
     * $scope          empty (global) or "local"
     * $info_text      message about results of check OR "1" if all checks successfully passed
     */
    protected function checkLanguage(string $a_lang_key, string $scope = "") : bool
    {
        $scopeExtension = "";
        if (!empty($scope)) {
            if ($scope === "global") {
                $scope = "";
            } else {
                $scopeExtension = "." . $scope;
            }
        }
        
        $path = $this->lang_path;
        if ($scope === "local") {
            $path = $this->cust_lang_path;
        }

        $tmpPath = getcwd();
        chdir($path);

        // compute lang-file name format
        $lang_file = "ilias_" . $a_lang_key . ".lang" . $scopeExtension;

        // file check
        if (!is_file($lang_file)) {
            chdir($tmpPath);
            return false;
        }

        // header check
        if (!$content = $this->cut_header(file($lang_file))) {
            chdir($tmpPath);
            return false;
        }

        // check (counting) elements of each lang-entry
        foreach ($content as $key => $val) {
            $separated = explode($this->separator, trim($val));
            $num = count($separated);

            if ($num !== 3) {
                chdir($tmpPath);
                return false;
            }
        }

        chdir($tmpPath);

        // no error occured
        return true;
    }

    /**
     * Remove *.lang header information from '$content'.
     *
     * This function seeks for a special keyword where the language information starts.
     * If found it returns the plain language information; otherwise returns false.
     *
     * $content    expect an ILIAS lang-file
     *
     * @return bool|string[]
     */
    protected function cut_header(array $content)
    {
        foreach ($content as $key => $val) {
            if (trim($val) === "<!-- language file start -->") {
                return array_slice($content, $key + 1);
            }
        }
        return false;
    }

    /**
     * remove language data from database
     * $a_lang_key     language key
     * $a_mode        "all" or "keep_local"
     */
    protected function flushLanguage(string $a_lang_key, string $a_mode = "all") : void
    {
        global $ilDB;
        
        self::_deleteLangData($a_lang_key, ($a_mode === "keep_local"));

        if ($a_mode === "all") {
            $ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                $ilDB->quote($a_lang_key, "text"));
        }
    }

    /**
    * Delete languge data
    *
    * $a_lang_key lang key
    */
    public static function _deleteLangData(string $a_lang_key, bool $a_keep_local_change) : void
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
    public function getLocalChanges(string $a_lang_key, string $a_min_date = "", string $a_max_date = "") : array
    {
        global $ilDB;
        
        if ($a_min_date === "") {
            $a_min_date = "1980-01-01 00:00:00";
        }
        if ($a_max_date === "") {
            $a_max_date = "2200-01-01 00:00:00";
        }
        
        $q = sprintf(
            "SELECT * FROM lng_data WHERE lang_key = %s " .
            "AND local_change >= %s AND local_change <= %s",
            $ilDB->quote($a_lang_key, "text"),
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


    //TODO: remove redundant checks here!
    /**
     * insert language data from file in database
     *
     * $lang_key   international language key (2 digits)
     * $scope      empty (global) or "local"
     */
    protected function insertLanguage(string $lang_key, string $scope = "") : void
    {
        global $ilDB;
        
        $lang_array = array();
        
        $scopeExtension = "";
        if (!empty($scope)) {
            if ($scope === "global") {
                $scope = "";
            } else {
                $scopeExtension = "." . $scope;
            }
        }

        $path = $this->lang_path;
        if ($scope === "local") {
            $path = $this->cust_lang_path;
        }
        
        $tmpPath = getcwd();
        chdir($path);

        $lang_file = "ilias_" . $lang_key . ".lang" . $scopeExtension;
        $change_date = null;

        if (is_file($lang_file)) {
            // initialize the array for updating lng_modules below
            $lang_array = array();
            $lang_array["common"] = array();

            // remove header first
            if ($content = $this->cut_header(file($lang_file))) {
                // get the local changes from the database
                if (empty($scope)) {
                    $local_changes = $this->getLocalChanges($lang_key);
                } elseif ($scope === "local") {
                    // set the change date to import time for a local file
                    // get the modification date of the local file
                    // get the newer local changes for a local file
                    $change_date = date("Y-m-d H:i:s", time());
                    $min_date = date("Y-m-d H:i:s", filemtime($lang_file));
                    $local_changes = $this->getLocalChanges($lang_key, $min_date);
                }

                foreach ($content as $key => $val) {
                    // split the line of the language file
                    // [0]: module
                    // [1]: identifier
                    // [2]: value
                    // [3]: comment (optional)
                    $separated = explode($this->separator, trim($val));

                    //get position of the comment_separator
                    $pos = strpos($separated[2], $this->comment_separator);

                    if ($pos !== false) {
                        //cut comment of
                        $separated[2] = substr($separated[2], 0, $pos);
                    }

                    // check if the value has a local change
                    if (isset($local_changes[$separated[0]])) {
                        $local_value = $local_changes[$separated[0]][$separated[1]] ?? "";
                    } else {
                        $local_value = "";
                    }

                    if (empty($scope)) {
                        if ($local_value !== "" && $local_value !== $separated[2]) {
                            // keep the locally changed value
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // insert a new value if no local value exists
                            // reset local_change if the values are equal
                            self::replaceLangEntry(
                                $separated[0],
                                $separated[1],
                                $lang_key,
                                $separated[2]
                            );

                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    } elseif ($scope === "local") {
                        if ($local_value !== "") {
                            // keep a locally changed value that is newer than the local file
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // UPDATE because the global values have already been INSERTed
                            self::updateLangEntry(
                                $separated[0],
                                $separated[1],
                                $lang_key,
                                $separated[2],
                                $change_date
                            );
                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    }
                }
            }

            foreach ($lang_array as $module => $lang_arr) {
                if ($scope === "local") {
                    $q = "SELECT * FROM lng_modules WHERE " .
                        " lang_key = " . $ilDB->quote($lang_key, "text") .
                        " AND module = " . $ilDB->quote($module, "text");
                    $set = $ilDB->query($q);
                    $row = $ilDB->fetchAssoc($set);
                    $arr2 = unserialize($row["lang_array"], ["allowed_classes" => false]);
                    if (is_array($arr2)) {
                        $lang_arr = array_merge($arr2, $lang_arr);
                    }
                }
                self::replaceLangModule($lang_key, $module, $lang_arr);
            }
        }

        chdir($tmpPath);
    }

    /**
    * Replace language module array
    */
    final public static function replaceLangModule(string $a_key, string $a_module, array $a_array) : void
    {
        global $ilDB;
        
        $ilDB->manipulate(sprintf(
            "DELETE FROM lng_modules WHERE lang_key = %s AND module = %s",
            $ilDB->quote($a_key, "text"),
            $ilDB->quote($a_module, "text")
        ));
        $ilDB->insert("lng_modules", array(
            "lang_key" => array("text", $a_key),
            "module" => array("text", $a_module),
            "lang_array" => array("clob", serialize($a_array))
            ));
    }

    /**
    * Replace lang entry
    */
    final public static function replaceLangEntry(
        string $a_module,
        string $a_identifier,
        string $a_lang_key,
        string $a_value,
        string $a_local_change = null
    ) : void {
        global $ilDB;

        $ilDB->manipulate(sprintf(
            "DELETE FROM lng_data WHERE module = %s AND " .
            "identifier = %s AND lang_key = %s",
            $ilDB->quote($a_module, "text"),
            $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text")
        ));

        // insert a new value if no local value exists
        // reset local_change if the values are equal
        $ilDB->manipulate(sprintf(
            "INSERT INTO lng_data " .
            "(module, identifier, lang_key, value, local_change) " .
            "VALUES (%s,%s,%s,%s,%s)",
            $ilDB->quote($a_module, "text"),
            $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text"),
            $ilDB->quote($a_value, "text"),
            $ilDB->quote($a_local_change, "timestamp")
        ));
    }

    /**
    * Update lang entry
    */
    final public static function updateLangEntry(
        string $a_module,
        string $a_identifier,
        string $a_lang_key,
        string $a_value,
        string $a_local_change = null
    ) : void {
        global $ilDB;
        
        $ilDB->manipulate(sprintf(
            "UPDATE lng_data " .
            "SET value = %s, local_change = %s " .
            "WHERE module = %s AND identifier = %s AND lang_key = %s ",
            $ilDB->quote($a_value, "text"),
            $ilDB->quote($a_local_change, "timestamp"),
            $ilDB->quote($a_module, "text"),
            $ilDB->quote($a_identifier, "text"),
            $ilDB->quote($a_lang_key, "text")
        ));
    }

    /**
     * Searches for the existence of *.lang.local files.
     * Returns array with language keys
     */
    public function getLocalLanguages() : array
    {
        $local_langs = array();
        if (is_dir($this->cust_lang_path)) {
            $d = dir($this->cust_lang_path);
            $tmpPath = getcwd();
            chdir($this->cust_lang_path);
    
            // get available .lang.local files
            while ($entry = $d->read()) {
                if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang.local$)~", $entry))) {
                    $lang_key = substr($entry, 6, 2);
                    $local_langs[] = $lang_key;
                }
            }
    
            chdir($tmpPath);
        }

        return $local_langs;
    }

    /**
     * Return installable languages
     */
    public function getInstallableLanguages() : array
    {
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        $installableLanguages = [];
        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                $lang_key = substr($entry, 6, 2);
                $installableLanguages[] = $lang_key;
            }
        }

        chdir($tmpPath);

        return $installableLanguages;
    }
    
    /**
     * set db handler object
     * @string   object      db handler
     * Return true on success
     */
    public function setDbHandler(ilDBInterface $a_db_handler) : bool
    {
        $this->db = &$a_db_handler;
        return true;
    }
    
    public function loadLanguageModule(string $a_module) : void
    {
    }
}
