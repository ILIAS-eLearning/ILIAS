<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* ILIAS Setting Class
*
* @author Alex Killing <alex.killing@databay.de>

* @version $Id$
*/
class ilSetting
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
    * cache for the read settings
    * ilSetting is instantiated more than once per request for some modules
    * The cache avoids reading them from the DB with each instance
    */
    private static $settings_cache = array();

    /**
    * the type of settings value field in database
    * This is determined in the set method to get a correct DB insert
    * Don't set the value type to force a detection at first access
    */
    private static $value_type = null;


    public $setting = array();
    public $module = "";
    
    /**
    * Initialize settings
    */
    public function __construct($a_module = "common", $a_disabled_cache = false)
    {
        global $DIC;

        $this->db = $DIC->database();
        $ilDB = $DIC->database();
        
        $this->cache_disabled = $a_disabled_cache;
        $this->module = $a_module;
        // check whether ini file object exists
        if (!is_object($ilDB)) {
            die("Fatal Error: ilSettings object instantiated without DB initialisation.");
        }
        $this->read();
    }
    
    /**
     * Get currernt module
     */
    public function getModule()
    {
        return $this->module;
    }
        
    /**
    * Read settings data
    */
    public function read()
    {
        $ilDB = $this->db;
        
        // get the settings from the cache if they exist.
        // The setting array of the class is a reference to the cache.
        // So changing settings in one instance will change them in all.
        // This is the same behaviour as if the are read from the DB.
        if (!$this->cache_disabled) {
            if (isset(self::$settings_cache[$this->module])) {
                $this->setting = &self::$settings_cache[$this->module];
                return;
            } else {
                $this->setting = array();
                self::$settings_cache[$this->module] = &$this->setting;
            }
        }

        $query = "SELECT * FROM settings WHERE module=" . $ilDB->quote($this->module, "text");
        $res = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($res)) {
            $this->setting[$row["keyword"]] = $row["value"];
        }
    }
    
    /**
    * get setting
    *
    * @access	public
    *
    * @param	string	keyword
    * @param	string	default_value This value is returned, when no setting has
    *								  been found for the keyword.
    * @return	string	value
    */
    public function get($a_keyword, $a_default_value = false)
    {
        if ($a_keyword == "ilias_version") {
            return ILIAS_VERSION;
        }
        
        if (isset($this->setting[$a_keyword])) {
            return $this->setting[$a_keyword];
        } else {
            return $a_default_value;
        }
    }
    
    /**
     * Delete all settings of a current module
     *
     * @access public
     *
     */
    public function deleteAll()
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM settings WHERE module = " . $ilDB->quote($this->module, "text");
        $ilDB->manipulate($query);

        $this->setting = array();

        return true;
    }
    
    /**
     * Delete all settings corresponding to a like string
     *
     * @access public
     *
     */
    public function deleteLike($a_like)
    {
        $ilDB = $this->db;

        $query = "SELECT keyword FROM settings" .
            " WHERE module = " . $ilDB->quote($this->module, "text") .
            " AND " . $ilDB->like("keyword", "text", $a_like);
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->delete($row["keyword"]);
        }
        
        return true;
    }

    /**
    * delete one value from settingstable
    * @access	public
    * @param	string	keyword
    * @return	string	value
    */
    public function delete($a_keyword)
    {
        $ilDB = $this->db;

        $st = $ilDB->manipulate("DELETE FROM settings WHERE keyword = " .
            $ilDB->quote($a_keyword, "text") . " AND module = " .
            $ilDB->quote($this->module, "text"));

        unset($this->setting[$a_keyword]);

        return true;
    }
    
    

    /**
    * read all values from settingstable
    * @access	public
    * @return	array	keyword/value pairs
    */
    public function getAll()
    {
        return $this->setting;
    }

    /**
    * write one value to db-table settings
    * @access	public
    * @param	string		keyword
    * @param	string		value
    * @return	boolean		true on success
    */
    public function set($a_key, $a_val)
    {
        global $DIC;

        $lng = $DIC["lng"];
        $ilDB = $this->db;
        
        $this->delete($a_key);

        if (!isset(self::$value_type)) {
            self::$value_type = self::_getValueType();
        }

        if (self::$value_type == 'text' and strlen($a_val) >= 4000) {
            ilUtil::sendFailure($lng->txt('setting_value_truncated'), true);
            $a_val = substr($a_val, 0, 4000);
        }

        $ilDB->insert("settings", array(
            "module" => array("text", $this->module),
            "keyword" => array("text", $a_key),
            "value" => array(self::$value_type, $a_val)));

        $this->setting[$a_key] = $a_val;

        return true;
    }
    
    public function setScormDebug($a_key, $a_val)
    {
        $ilDB = $this->db;
        if ($a_val != "1") {
            $ilDB->query("UPDATE sahs_lm SET debug = 'n'");
        }
        $setreturn = ilSetting::set($a_key, $a_val);
        return $setreturn;
    }
    
    public static function _lookupValue($a_module, $a_keyword)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
        $res = $ilDB->queryF($query, array('text', 'text'), array($a_module, $a_keyword));
        $data = $ilDB->fetchAssoc($res);
        return $data['value'];
    }

    /**
     * Get the type of the value column in the database
     *
     * @return string 'text' or 'clob'
     * @throws ilDatabaseException
     */
    public static function _getValueType()
    {
        include_once('./Services/Database/classes/class.ilDBAnalyzer.php');
        $analyzer = new ilDBAnalyzer();
        $info = $analyzer->getFieldInformation('settings');

        if ($info['value']['type'] == 'clob') {
            return 'clob';
        } else {
            return 'text';
        }
    }


    /**
    * change the type of the value column in the database
    *
    * @param   	string  	'text' or 'clob'
    * @return   bolean  	type changed or not
    */
    public static function _changeValueType($a_new_type = 'text')
    {
        global $DIC;

        $ilDB = $DIC->database();

        $old_type = self::_getValueType();

        if ($a_new_type == $old_type) {
            return false;
        } elseif ($a_new_type == 'clob') {
            $ilDB->addTableColumn(
                'settings',
                'value2',
                array(	"type" => "clob",
                                    "notnull" => false,
                                    "default" => null)
            );

            $ilDB->query("UPDATE settings SET value2 = value");
            $ilDB->dropTableColumn('settings', 'value');
            $ilDB->renameTableColumn('settings', 'value2', 'value');

            return true;
        } elseif ($a_new_type == 'text') {
            $ilDB->addTableColumn(
                'settings',
                'value2',
                array(	"type" => "text",
                                    "length" => 4000,
                                    "notnull" => false,
                                    "default" => null)
            );

            $ilDB->query("UPDATE settings SET value2 = value");
            $ilDB->dropTableColumn('settings', 'value');
            $ilDB->renameTableColumn('settings', 'value2', 'value');

            return true;
        } else {
            return false;
        }
    }


    /**
    * get a list of setting records with values loger than a limit
    *
    * @param   	int  		character limit (default: 4000)
    * @return   array       records with longer values
    */
    public static function _getLongerSettings($a_limit = '4000')
    {
        global $DIC;

        $ilDB = $DIC->database();

        $settings = array();

        $query = "SELECT * FROM settings WHERE LENGTH(value) > "
            . $ilDB->quote($a_limit, 'integer');

        $result = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($result)) {
            $settings[] = $row;
        }

        return $settings;
    }
}
