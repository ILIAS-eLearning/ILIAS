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
 *********************************************************************/

/**
 * ILIAS Setting Class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSetting implements \ILIAS\Administration\Setting
{
    protected ilDBInterface $db;

    /**
     * cache for the read settings
     * ilSetting is instantiated more than once per request for some modules
     * The cache avoids reading them from the DB with each instance
     */
    private static array $settings_cache = array();

    /**
     * the type of settings value field in database
     * This is determined in the set method to get a correct DB insert
     * Don't set the value type to force a detection at first access
     */
    private static ?string $value_type = null;
    public array $setting = array();
    public string $module = "";
    protected bool $cache_disabled = false;
    
    public function __construct(
        string $a_module = "common",
        bool $a_disabled_cache = false
    ) {
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
    
    public function getModule() : string
    {
        return $this->module;
    }
        
    public function read() : void
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
     */
    public function get(
        string $a_keyword,
        ?string $a_default_value = null
    ) : ?string {
        return $this->setting[$a_keyword] ?? $a_default_value;
    }
    
    public function deleteAll() : void
    {
        $ilDB = $this->db;
        
        $query = "DELETE FROM settings WHERE module = " . $ilDB->quote($this->module, "text");
        $ilDB->manipulate($query);

        $this->setting = array();
    }
    
    public function deleteLike(string $a_like) : void
    {
        $ilDB = $this->db;

        $query = "SELECT keyword FROM settings" .
            " WHERE module = " . $ilDB->quote($this->module, "text") .
            " AND " . $ilDB->like("keyword", "text", $a_like);
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->delete($row["keyword"]);
        }
    }

    public function delete(string $a_keyword) : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate("DELETE FROM settings WHERE keyword = " .
            $ilDB->quote($a_keyword, "text") . " AND module = " .
            $ilDB->quote($this->module, "text"));

        unset($this->setting[$a_keyword]);
    }
    
    public function getAll() : array
    {
        return $this->setting;
    }

    public function set(string $a_key, string $a_val) : void
    {
        $ilDB = $this->db;
        
        $this->delete($a_key);

        if (!isset(self::$value_type)) {
            self::$value_type = $this->getValueDbType();
        }

        if (self::$value_type === 'text' && strlen($a_val) >= 4000) {
            global $DIC;
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $DIC->language()->txt('setting_value_truncated'), true);
            $a_val = substr($a_val, 0, 4000);
        }

        $ilDB->insert("settings", array(
            "module" => array("text", $this->module),
            "keyword" => array("text", $a_key),
            "value" => array(self::$value_type, $a_val)));

        $this->setting[$a_key] = $a_val;
    }

    /**
     * @todo this must not be part of a general settings class
     * @deprecated
     */
    public function setScormDebug(string $a_key, string $a_val) : void
    {
        $ilDB = $this->db;
        if ($a_val !== "1") {
            $ilDB->query("UPDATE sahs_lm SET debug = 'n'");
        }
        $this->set($a_key, $a_val);
    }
    
    public static function _lookupValue(
        string $a_module,
        string $a_keyword
    ) : ?string {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT value FROM settings WHERE module = %s AND keyword = %s";
        $res = $ilDB->queryF($query, array('text', 'text'), array($a_module, $a_keyword));
        $data = $ilDB->fetchAssoc($res);
        return $data['value'] ?? null;
    }

    /**
     * Get the type of the value column in the database
     * @throws ilDatabaseException
     */
    public function getValueDbType() : string
    {
        $analyzer = new ilDBAnalyzer($this->db);
        $info = $analyzer->getFieldInformation('settings');

        if ($info['value']['type'] === 'clob') {
            return 'clob';
        }

        return 'text';
    }

    /**
     * change the type of the value column in the database
     * @param string $a_new_type 'text' or 'clob'
     * @return bool
     * @throws ilDatabaseException
     */
    public function changeValueDbType(string $a_new_type = 'text') : bool
    {
        $old_type = $this->getValueDbType();

        if ($a_new_type === $old_type) {
            return false;
        }

        if ($a_new_type === 'clob') {
            $this->db->addTableColumn(
                'settings',
                'value2',
                array(	"type" => "clob",
                                    "notnull" => false,
                                    "default" => null)
            );

            $this->db->query("UPDATE settings SET value2 = value");
            $this->db->dropTableColumn('settings', 'value');
            $this->db->renameTableColumn('settings', 'value2', 'value');

            return true;
        }

        if ($a_new_type === 'text') {
            $this->db->addTableColumn(
                'settings',
                'value2',
                array(	"type" => "text",
                                    "length" => 4000,
                                    "notnull" => false,
                                    "default" => null)
            );

            $this->db->query("UPDATE settings SET value2 = value");
            $this->db->dropTableColumn('settings', 'value');
            $this->db->renameTableColumn('settings', 'value2', 'value');

            return true;
        }

        return false;
    }


    /**
     * Get a list of setting records with values longer than a limit
     * @return string[]
     */
    public function getLimitExceedingValues(int $a_limit = 4000) : array
    {
        $settings = [];

        $query = "SELECT value FROM settings WHERE LENGTH(value) > " . $this->db->quote($a_limit, 'integer');

        $result = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($result)) {
            $settings[] = $row['value'];
        }

        return $settings;
    }
}
