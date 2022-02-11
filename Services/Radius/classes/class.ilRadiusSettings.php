<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesRadius
*/
class ilRadiusSettings
{
    const RADIUS_CHARSET_UTF8 = 0;
    const RADIUS_CHARSET_LATIN1 = 1;

    const SYNC_DISABLED = 0;
    const SYNC_RADIUS = 1;
    const SYNC_LDAP = 2;
    
    
    private ilSetting $settings;
    private ilDBInterface $db;
    private static ?ilRadiusSettings $instance = null;
    
    private bool $account_migration = false;
    private bool $creation = false;
    
    private array $servers = array();
    private bool $active = false;
    
    private string $name = "";
    private int $port = 0;
    private string $secret = "";
    private int $charset = 0;
    
    /**
     * singleton constructor
     *
     * @access private
     *
     */
    private function __construct()
    {
        global $DIC;
        
        $this->settings = $DIC->settings();
        $this->db = $DIC->database();
        
        $this->read();
    }
    
    /**
     * singleton get instance
     *
     * @access public
     * @static
     *
     */
    public static function _getInstance() : ilRadiusSettings
    {
        if (isset(self::$instance) and self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilRadiusSettings();
    }
    
    public static function isDN($a_str)
    {
        return (preg_match("/^[a-z]+([a-z0-9-]*[a-z0-9]+)?(\.([a-z]+([a-z0-9-]*[a-z0-9]+)?)+)*$/", $a_str));
    }
    
    public static function isIPv4($a_str)
    {
        return (preg_match(
            "/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\." .
            "(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$/",
            $a_str
        ));
    }
    
    public function isActive() : bool
    {
        return $this->active;
    }
    public function setActive(bool $a_status) : void
    {
        $this->active = $a_status;
    }
    public function setPort(int $a_port) : void
    {
        $this->port = $a_port;
    }
    public function getPort() : int
    {
        return $this->port;
    }
    public function setSecret(string $a_secret) : void
    {
        $this->secret = $a_secret;
    }
    public function getSecret() : string
    {
        return $this->secret;
    }
    public function setServerString(string $a_server_string) : void
    {
        $this->server_string = $a_server_string;
        $this->servers = explode(',', $this->server_string);
    }
    public function getServersAsString() : string
    {
        return implode(',', $this->servers);
    }
    public function getServers() : array
    {
        return $this->servers ? $this->servers : array();
    }
    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Get default role for new radius users
     *
     * @return int role_id
     *
     */
    public function getDefaultRole() : int
    {
        return $this->default_role;
    }
    
    public function setDefaultRole(int $a_role)
    {
        $this->default_role = $a_role;
    }
    
    /**
     * Enable creation of users
     */
    public function enabledCreation() : bool
    {
        return $this->creation;
    }
    
    /**
     * Enable creation
     */
    public function enableCreation(bool $a_status) : void
    {
        $this->creation = $a_status;
    }
    
    /**
     * Enable account migration
     */
    public function enableAccountMigration(bool $a_status) : void
    {
        $this->account_migration = $a_status;
    }
    
    /**
     * enabled account migration
     *
     * @access public
     *
     */
    public function isAccountMigrationEnabled() : bool
    {
        return $this->account_migration ? true : false;
    }
    
    /**
     * get charset
     */
    public function getCharset() : int
    {
        return $this->charset;
    }
    
    /**
     * set charset
     */
    public function setCharset(int $a_charset) : void
    {
        // TODO add check for valid input (may be 0 or 1 according to constants
        $this->charset = $a_charset;
    }
    
    /**
     * Save settings
     */
    public function save() : bool
    {
        // first delete old servers
        $this->settings->deleteLike('radius_server%');
        
        $this->settings->set('radius_active', (string) $this->isActive());
        $this->settings->set('radius_port', (string) $this->getPort());
        $this->settings->set('radius_shared_secret', $this->getSecret());
        $this->settings->set('radius_name', $this->getName());
        $this->settings->set('radius_creation', (string) $this->enabledCreation());
        $this->settings->set('radius_migration', (string) $this->isAccountMigrationEnabled());
        $this->settings->set('radius_charset', (string) $this->getCharset());
        
        $counter = 0;
        foreach ($this->getServers() as $server) {
            if (++$counter == 1) {
                $this->settings->set('radius_server', trim($server));
            } else {
                $this->settings->set('radius_server' . $counter, trim($server));
            }
        }
        
        ilObjRole::_resetAuthMode('radius');
        
        if ($this->getDefaultRole()) {
            ilObjRole::_updateAuthMode(array($this->getDefaultRole() => 'radius'));
        }
        return true;
    }
    
    /**
     * Validate required
     */
    public function validateRequired()
    {
        $ok = strlen($this->getServersAsString()) and strlen($this->getSecret()) and strlen($this->getName());
        
        $role_ok = true;
        if ($this->enabledCreation() and !$this->getDefaultRole()) {
            $role_ok = false;
        }
        return $ok and $role_ok;
    }
    
    /**
     * Validate port
     */
    public function validatePort()
    {
        return 0 < $this->getPort() && $this->getPort() < 65535;
    }
    
    /**
     * Validate servers
     */
    public function validateServers()
    {
        $servers = explode(",", $this->server_string);
        
        foreach ($servers as $server) {
            $server = trim($server);

            if (!self::isIPv4($server) and !self::isDN($server)) {
                return false;
            }
        }
        return true;
    }
    
    
    /**
     * Read settings
     */
    private function read()
    {
        $this->setActive((bool) $this->settings->get("radius_active"));
        $this->setPort((int) $this->settings->get("radius_port"));
        $this->setSecret($this->settings->get("radius_shared_secret", ""));
        $this->setName($this->settings->get("radius_name", ""));
        $this->enableCreation((bool) $this->settings->get("radius_creation"));
        $this->enableAccountMigration((bool) $this->settings->get("radius_migration"));
        $this->setCharset((int) $this->settings->get("radius_charset"));
        
        $all_settings = $this->settings->getAll();
        
        foreach ($all_settings as $k => $v) {
            if (substr($k, 0, 13) == "radius_server") {
                $this->servers[] = $v;
            }
        }
        
        $roles = ilObjRole::_getRolesByAuthMode('radius');
        $this->default_role = 0;
        if (isset($roles[0]) && $roles[0]) {
            $this->default_role = $roles[0];
        }
    }
}
