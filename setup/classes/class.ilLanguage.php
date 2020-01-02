<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
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
 */
class ilLanguage
{
    /**
     * text elements
     * @var array
     * @access private
     */
    public $text = array();
    
    /**
     * indicator for the system language
     * this language must not be deleted
     * @var      string
     * @access   private
     */
    public $lang_default = "en";

    /**
     * path to language files
     * relative path is taken from ini file
     * and added to absolute path of ilias
     * @var      string
     * @access   private
     */
    public $lang_path;

    /**
     * language key in use by current user
     * @var      string  languagecode (two characters), e.g. "de", "en", "in"
     * @access   private
     */
    public $lang_key;

    /**
     * separator value between module, identifier, and value
     * @var      string
     * @access   private
     */
    public $separator = "#:#";
    
    /**
     * separator value between the content and the comment of the lang entry
     * @var      string
     * @access   private
     */
    public $comment_separator = "###";

    /**
     * Constructor
     * read the single-language file and put this in an array text.
     * the text array is two-dimensional. First dimension is the language.
     * Second dimension is the languagetopic. Content is the translation.
     * @access   public
     * @param    string      languagecode (two characters), e.g. "de", "en", "in"
     * @return   boolean     false if reading failed
     */
    public function __construct($a_lang_key)
    {
        $this->lang_key = ($a_lang_key) ? $a_lang_key : $this->lang_default;
        $this->lang_path = ILIAS_ABSOLUTE_PATH . "/lang";
        $this->cust_lang_path = ILIAS_ABSOLUTE_PATH . "/Customizing/global/lang";

        // set lang file...
        $txt = file($this->lang_path . "/setup_lang_sel_multi.lang");

        // ...and load langdata
        if (is_array($txt)) {
            foreach ($txt as $row) {
                if ($row[0] != "#") {
                    $a = explode($this->separator, trim($row));
                    $this->text[trim($a[0])] = trim($a[1]);
                }
            }
        }

        // set lang file...
        $txt = file($this->lang_path . "/setup_" . $this->lang_key . ".lang");

        // ...and load langdata
        if (is_array($txt)) {
            foreach ($txt as $row) {
                if ($row[0] != "#") {
                    $a = explode($this->separator, trim($row));
                    $this->text[trim($a[0])] = trim($a[1]);
                }
            }

            return true;
        }

        return false;
    }
    
    /**
     * gets the text for a given topic
     *
     * if the topic is not in the list, the topic itself with "-" will be returned
     * @access   public
     * @param    string  topic
     * @return   string  text clear-text
     */
    public function txt($a_topic)
    {
        global $log;
        
        if (empty($a_topic)) {
            return "";
        }

        $translation = $this->text[$a_topic];
        
        //get position of the comment_separator
        $pos = strpos($translation, $this->comment_separator);

        if ($pos !== false) {
            // remove comment
            $translation = substr($translation, 0, $pos);
        }

        if ($translation == "") {
            $log->writeLanguageLog($a_topic, $this->lang_key);
            return "-" . $a_topic . "-";
        } else {
            return $translation;
        }
    }

    /**
     * get all setup languages in the system
     *
     * the functions looks for setup*.lang-files in the languagedirectory
     * @access   public
     * @return   array   langs
     */
    public function getLanguages()
    {
        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        // get available setup-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match('/(^setup_.{2}\.lang$)/', $entry))) {
                $lang_key = substr($entry, 6, 2);
                $languages[] = $lang_key;
            }
        }

        chdir($tmpPath);

        return $languages;
    }

    /**
     * install languages
     *
     * @param    array   array with lang_keys of languages to install
     * @return   boolean true on success
     */
    public function installLanguages($a_lang_keys, $a_local_keys)
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
                $this->flushLanguage($lang_key, 'keep_local');
                $this->insertLanguage($lang_key);
                
                if (in_array($lang_key, $a_local_keys)) {
                    if ($this->checkLanguage($lang_key, "local")) {
                        $this->insertLanguage($lang_key, "local");
                    } else {
                        $err_lang[] = $lang_key;
                    }
                }
                
                // register language first time install
                if (!array_key_exists($lang_key, $db_langs)) {
                    if (in_array($lang_key, $a_local_keys)) {
                        $itype = 'installed_local';
                    } else {
                        $itype = 'installed';
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
                            $ilDB->quote('-1', "integer") . "," .
                            $ilDB->now() . "," .
                            $ilDB->now() .
                            ")";
                    $this->db->manipulate($query);
                }
            } else {
                $err_lang[] = $lang_key;
            }
        }
        
        foreach ($db_langs as $key => $val) {
            if (!in_array($key, $err_lang)) {
                if (in_array($key, $a_lang_keys)) {
                    if (in_array($key, $a_local_keys)) {
                        $ld = 'installed_local';
                    } else {
                        $ld = 'installed';
                    }
                    $query = "UPDATE object_data SET " .
                            "description = " . $ilDB->quote($ld, "text") . ", " .
                            "last_update = " . $ilDB->now() . " " .
                            "WHERE obj_id = " . $ilDB->quote($val["obj_id"], "integer") . " " .
                            "AND type = " . $ilDB->quote("lng", "text");
                    $ilDB->manipulate($query);
                } else {
                    $this->flushLanguage($key, "all");
                    
                    if (substr($val["status"], 0, 9) == "installed") {
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

        return ($err_lang) ? $err_lang : true;
    }



    /**
     * get already installed languages (in db)
     *
     * @return   array   array with inforamtion about each installed language
     */
    public function getInstalledLanguages()
    {
        global $ilDB;
        
        $arr = array();

        $query = "SELECT * FROM object_data " .
                "WHERE type = " . $ilDB->quote("lng", "text") . " " .
                "AND " . $ilDB->like("description", "text", 'installed%');
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }

        return $arr;
    }
    
    /**
     * get already installed local languages (in db)
     *
     * @return   array   array with inforamtion about each installed language
     */
    public function getInstalledLocalLanguages()
    {
        global $ilDB;
        
        $arr = array();

        $query = "SELECT * FROM object_data " .
                "WHERE type = " . $ilDB->quote("lng", "text") . " " .
                "AND description = " . $ilDB->quote('installed_local', "text");
        $r = $ilDB->query($query);

        while ($row = $ilDB->fetchObject($r)) {
            $arr[] = $row->title;
        }

        return $arr;
    }

    /**
     * get already registered languages (in db)
     * @return   array   array with information about languages that has been registered in db
     */
    public function getAvailableLanguages()
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
     * @param    string      $a_lang_key     international language key (2 digits)
     * @param    string      $scope          empty (global) or "local"
     * @return   string      $info_text      message about results of check OR "1" if all checks successfully passed
     */
    public function checkLanguage($a_lang_key, $scope = '')
    {
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

            if ($num != 3) {
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
     * @param    string      $content    expect an ILIAS lang-file
     * @return   string      $content    content without header info OR false if no valid header was found
     * @access   private
     */
    public function cut_header($content)
    {
        foreach ($content as $key => $val) {
            if (trim($val) == "<!-- language file start -->") {
                return array_slice($content, $key +1);
            }
        }

        return false;
    }
    

    /**
     * remove language data from database
     * @param   string     language key
     * @param   string     "all" or "keep_local"
     */
    public function flushLanguage($a_lang_key, $a_mode = 'all')
    {
        $ilDB = $this->db;
        
        ilLanguage::_deleteLangData($a_lang_key, ($a_mode == 'keep_local'));

        if ($a_mode == 'all') {
            $ilDB->manipulate("DELETE FROM lng_modules WHERE lang_key = " .
                $ilDB->quote($a_lang_key, "text"));
        }
    }

    /**
    * Delete languge data
    *
    * @param	string		lang key
    */
    public static function _deleteLangData($a_lang_key, $a_keep_local_change)
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
    * @param   string     language key
    * @param    string  	minimum change date "yyyy-mm-dd hh:mm:ss"
    * @param    string  	maximum change date "yyyy-mm-dd hh:mm:ss"
    * @return   array       [module][identifier] => value
    */
    public function getLocalChanges($a_lang_key, $a_min_date = "", $a_max_date = "")
    {
        $ilDB = $this->db;
        
        if ($a_min_date == "") {
            $a_min_date = "1980-01-01 00:00:00";
        }
        if ($a_max_date == "") {
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
     * @param    string  $lang_key   international language key (2 digits)
     * @param    string  $scope      empty (global) or "local"
     * @return   void
     */
    public function insertLanguage($lang_key, $scope = '')
    {
        $ilDB =&$this->db;
        
        $lang_array = array();
        
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
        chdir($path);

        $lang_file = "ilias_" . $lang_key . ".lang" . $scopeExtension;

        if ($lang_file) {
            // initialize the array for updating lng_modules below
            $lang_array = array();
            $lang_array["common"] = array();

            // remove header first
            if ($content = $this->cut_header(file($lang_file))) {
                // get the local changes from the database
                if (empty($scope)) {
                    $local_changes = $this->getLocalChanges($lang_key);
                } elseif ($scope == 'local') {
                    $change_date = date("Y-m-d H:i:s", time());
                    $min_date = date("Y-m-d H:i:s", filemtime($lang_file));
                    $local_changes = $this->getLocalChanges($lang_key, $min_date);
                }

                foreach ($content as $key => $val) {
                    $separated = explode($this->separator, trim($val));

                    //get position of the comment_separator
                    $pos = strpos($separated[2], $this->comment_separator);

                    if ($pos !== false) {
                        //cut comment of
                        $separated[2] = substr($separated[2], 0, $pos);
                    }

                    // check if the value has a local change
                    $local_value = $local_changes[$separated[0]][$separated[1]];

                    if (empty($scope)) {
                        if ($local_value != "" and $local_value != $separated[2]) {
                            // keep the locally changed value
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // insert a new value if no local value exists
                            // reset local_change if the values are equal
                            ilLanguage::replaceLangEntry(
                                $separated[0],
                                $separated[1],
                                $lang_key,
                                $separated[2]
                            );

                            $lang_array[$separated[0]][$separated[1]] = $separated[2];
                        }
                    } elseif ($scope == 'local') {
                        if ($local_value != "") {
                            // keep a locally changed value that is newer than the local file
                            $lang_array[$separated[0]][$separated[1]] = $local_value;
                        } else {
                            // UPDATE because the global values have already been INSERTed
                            ilLanguage::updateLangEntry(
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
                ilLanguage::replaceLangModule($lang_key, $module, $lang_arr);
            }
        }

        chdir($tmpPath);
    }

    /**
    * Replace language module array
    */
    final public static function replaceLangModule($a_key, $a_module, $a_array)
    {
        global $ilDB;
        
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
    }

    /**
    * Replace lang entry
    */
    final public static function replaceLangEntry(
        $a_module,
        $a_identifier,
        $a_lang_key,
        $a_value,
        $a_local_change = null
    ) {
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
        $a_module,
        $a_identifier,
        $a_lang_key,
        $a_value,
        $a_local_change = null
    ) {
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
     *
     * return    $local_langs    array of language keys
     */
    public function getLocalLanguages()
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

    public function getInstallableLanguages()
    {
        $setup_langs = $this->getLanguages();

        $d = dir($this->lang_path);
        $tmpPath = getcwd();
        chdir($this->lang_path);

        // get available lang-files
        while ($entry = $d->read()) {
            if (is_file($entry) && (preg_match("~(^ilias_.{2}\.lang$)~", $entry))) {
                $lang_key = substr($entry, 6, 2);
                $languages1[] = $lang_key;
            }
        }
        
        //$languages = array_intersect($languages1,$setup_langs);

        chdir($tmpPath);

        return $languages1;
    }
    
    /**
     * set db handler object
     * @string   object      db handler
     * @return   boolean     true on success
     */
    public function setDbHandler($a_db_handler)
    {
        if (empty($a_db_handler) or !is_object($a_db_handler)) {
            return false;
        }
        
        $this->db =&$a_db_handler;
        
        return true;
    }
    
    public function loadLanguageModule()
    {
    }
} // END class.ilLanguage
