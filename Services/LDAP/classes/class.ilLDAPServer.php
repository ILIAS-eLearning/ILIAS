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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPServer
{
    private static array $instances = [];

    public const LDAP_BIND_ANONYMOUS = 0;
    public const LDAP_BIND_USER = 1;

    public const LDAP_SCOPE_SUB = 0;
    public const LDAP_SCOPE_ONE = 1;
    public const LDAP_SCOPE_BASE = 2;

    private const DEBUG = false;
    private const DEFAULT_VERSION = 3;
    public const DEFAULT_NETWORK_TIMEOUT = 5;
    
    private string $role_bind_dn = '';
    private string $role_bind_pass = '';
    private bool $role_sync_active = false;

    private int $server_id;
    private array $fallback_urls = array();
    private string $url = '';
    private string $url_string = '';

    private bool $enabled_authentication = true;
    private int $authentication_mapping = 0;
    private bool $escape_dn = false;
    
    private bool $active = false;

    private string $name = '';
    private int $version = self::DEFAULT_VERSION;
    private string $base_dn = '';
    private bool $referrals = false;
    private bool $tls = false;
    private int $binding_type = self::LDAP_BIND_ANONYMOUS;
    private string $bind_user = '';
    private string $bind_password = '';
    private string $search_base = '';
    private string $user_attribute = '';
    private int $user_scope = self::LDAP_SCOPE_ONE;
    private string $group_filter = '';
    private string $filter = '';
    private string $group_dn = '';
    private string $group_member = '';
    private int $group_scope = self::LDAP_SCOPE_ONE;
    private string $group_name = '';
    private bool $memberisdn = false;
    private string $group_attribute = '';
    private bool $group_optional = true;
    private string $group_user_filter = '';
    private bool $sync_on_login = false;
    private bool $sync_per_cron = false;
    private bool $account_migration = false;
    private string $username_filter = '';
    private int $global_role = 0;

    private ilDBInterface $db;
    private ilLanguage $lng;
    private ilErrorHandling $ilErr;

    public function __construct(int $a_server_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->ilErr = $DIC['ilErr'];
        
        $this->server_id = $a_server_id;

        $this->read();
    }
    
    /**
     * Get instance by server id
     */
    public static function getInstanceByServerId(int $a_server_id) : ilLDAPServer
    {
        return self::$instances[$a_server_id] ?? (self::$instances[$a_server_id] = new ilLDAPServer($a_server_id));
    }
    
    /**
     * Rotate fallback urls in case of connect timeouts
     */
    public function rotateFallbacks() : bool
    {
        if (!$this->fallback_urls) {
            return false;
        }
        
        $all_urls = array_merge($this->fallback_urls);
        $all_urls[] = $this->getUrl();
        
        $query = 'UPDATE ldap_server_settings SET ' .
                'url = ' . $this->db->quote(implode(',', $all_urls), 'text') . ' ' .
                'WHERE server_id = ' . $this->db->quote($this->getServerId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }


    /**
     * Check if ldap module is installed
     */
    public static function checkLDAPLib() : bool
    {
        return function_exists('ldap_bind');
    }
    
    /**
     * Get active server list
     *
     * @return int[] server ids of active ldap server
     */
    public static function _getActiveServerList() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE active = 1 AND authentication = 1 " .
            "ORDER BY name ";
        $res = $ilDB->query($query);

        $server_ids = [];

        while ($row = $ilDB->fetchObject($res)) {
            $server_ids[] = (int) $row->server_id;
        }
        return $server_ids;
    }
    
    /**
     * Get list of acticve servers with option 'SyncCron'
     *
     * @return int[] server ids of active ldap server
     */
    public static function _getCronServerIds() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE active = 1 " .
            "AND sync_per_cron = 1 " .
            "ORDER BY name";
            
        $res = $ilDB->query($query);

        $server_ids = [];

        while ($row = $ilDB->fetchObject($res)) {
            $server_ids[] = (int) $row->server_id;
        }
        return $server_ids;
    }
    
    /**
     * Check whether there if there is an active server with option role_sync_active
     */
    public static function _getRoleSyncServerIds() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE active = 1 " .
            "AND role_sync_active = 1 ";
            
        $res = $ilDB->query($query);

        $server_ids = [];

        while ($row = $ilDB->fetchObject($res)) {
            $server_ids[] = $row->server_id;
        }
        return $server_ids;
    }
    
    /**
     * Get first active server
     *
     * @return int first active server
     */
    public static function _getFirstActiveServer() : int
    {
        $servers = self::_getActiveServerList();
        if (count($servers)) {
            return $servers[0];
        }
        return 0;
    }

    /**
     * Get list of all configured servers
     *
     * @return int[] list of server ids
     */
    public static function _getServerList() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT server_id FROM ldap_server_settings ORDER BY name";
        $res = $ilDB->query($query);

        $server_ids = [];

        while ($row = $ilDB->fetchObject($res)) {
            $server_ids[] = $row->server_id;
        }
        return $server_ids;
    }

    /**
     * Get all server ids
     * @return int[]
     */
    public static function getServerIds() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT server_id FROM ldap_server_settings ORDER BY name";
        
        $res = $ilDB->query($query);

        $server = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $server[] = (int) $row->server_id;
        }
        return $server;
    }
    
    /**
     * Get list of all configured servers
     *
     * @return int[] list of server
     */
    public static function _getAllServer() : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM ldap_server_settings ORDER BY name";
        
        $server = [];
        
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $server[] = $row;
        }
        return $server;
    }

    public static function getAvailableDataSources(int $a_auth_mode) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE active = " . $ilDB->quote(1, 'integer') . " " .
            "AND authentication = " . $ilDB->quote(0, 'integer') . " " .
            "AND ( authentication_type = " . $ilDB->quote($a_auth_mode, 'integer') . " " .
            "OR authentication_type = " . $ilDB->quote(0, 'integer') . ")";
        $res = $ilDB->query($query);

        $server_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $server_ids[] = $row->server_id;
        }
        return $server_ids;
    }

    /**
     * Check if a data source is active for a specific auth mode
     */
    public static function isDataSourceActive(int $a_auth_mode) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE authentication_type = " . $ilDB->quote($a_auth_mode, 'integer') . " " .
            "AND authentication = " . $ilDB->quote(0, 'integer');
        $res = $ilDB->query($query);
        if ($res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    public static function getDataSource(int $a_auth_mode) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT server_id FROM ldap_server_settings " .
            "WHERE authentication_type = " . $ilDB->quote($a_auth_mode, 'integer') . " ";
        $res = $ilDB->query($query);
        if ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->server_id;
        }
        return 0;
    }
    
    /**
     * Disable data source
     */
    public static function disableDataSourceForAuthMode(int $a_authmode) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'UPDATE ldap_server_settings ' .
            'SET authentication_type = ' . $ilDB->quote(0, 'integer') . ' ' .
            'WHERE authentication_type = ' . $ilDB->quote($a_authmode, 'integer');
        $ilDB->manipulate($query);
        return true;
    }
    
    

    /**
     * Toggle Data Source
     * @todo handle multiple ldap servers
     */
    public static function toggleDataSource(int $a_ldap_server_id, int $a_auth_mode, int $a_status) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        self::disableDataSourceForAuthMode($a_auth_mode);
        
        if ($a_status) {
            $query = "UPDATE ldap_server_settings " .
                'SET authentication_type = ' . $ilDB->quote($a_auth_mode, 'integer') . " " .
                'WHERE server_id = ' . $ilDB->quote($a_ldap_server_id, 'integer');
            $ilDB->manipulate($query);
        }
        return true;
    }
    
    /**
     * Check if user auth mode is LDAP
     */
    public static function isAuthModeLDAP(string $a_auth_mode) : bool
    {
        global $DIC;
        $logger = $DIC->logger()->auth();

        if (!$a_auth_mode) {
            $logger->error(__METHOD__ . ': No auth mode given..............');
            return false;
        }
        $auth_arr = explode('_', $a_auth_mode);
        return ((int) $auth_arr[0] === ilAuthUtils::AUTH_LDAP) && $auth_arr[1];
    }
    
    /**
     * Get auth id by auth mode
     */
    public static function getServerIdByAuthMode(string $a_auth_mode) : ?int
    {
        if (self::isAuthModeLDAP($a_auth_mode)) {
            $auth_arr = explode('_', $a_auth_mode);
            return (int) $auth_arr[1];
        }
        return null;
    }
    
    /**
     * get auth mode by key
     */
    public static function getAuthModeByKey(string $a_auth_key) : string
    {
        $auth_arr = explode('_', $a_auth_key);
        if (count($auth_arr) > 1) {
            return 'ldap_' . $auth_arr[1];
        }
        return 'ldap';
    }
    
    /**
     * Get auth id by auth mode
     * @return int|string auth_mode
     */
    public static function getKeyByAuthMode(string $a_auth_mode)
    {
        $auth_arr = explode('_', $a_auth_mode);
        if (count($auth_arr) > 1) {
            return ilAuthUtils::AUTH_LDAP . '_' . $auth_arr[1];
        }
        return ilAuthUtils::AUTH_LDAP;
    }
    
    // Set/Get
    public function getServerId() : int
    {
        return $this->server_id;
    }

    /**
     * Enable authentication for this ldap server
     */
    public function enableAuthentication(bool $a_status) : void
    {
        $this->enabled_authentication = $a_status;
    }

    /**
     * Check if authentication is enabled
     */
    public function isAuthenticationEnabled() : bool
    {
        return $this->enabled_authentication;
    }

    /**
     * Set mapped authentication mapping
     */
    public function setAuthenticationMapping(int $a_map) : void
    {
        $this->authentication_mapping = $a_map;
    }

    /**
     * Get authentication mode that is mapped
     */
    public function getAuthenticationMapping() : int
    {
        return $this->authentication_mapping;
    }

    /**
     * Get authentication mapping key
     * Default is ldap
     */
    public function getAuthenticationMappingKey() : string
    {
        if ($this->isAuthenticationEnabled() || !$this->getAuthenticationMapping()) {
            return 'ldap_' . $this->getServerId();
        }
        return ilAuthUtils::_getAuthModeName($this->getAuthenticationMapping());
    }

    public function toggleActive(bool $a_status) : void
    {
        $this->active = $a_status;
    }
    public function isActive() : bool
    {
        return $this->active;
    }
    public function getUrl() : string
    {
        return $this->url;
    }
    public function setUrl(string $a_url) : void
    {
        $this->url_string = $a_url;
        
        // Maybe there are more than one url's (comma seperated).
        $urls = explode(',', $a_url);
        
        $counter = 0;
        foreach ($urls as $url) {
            $url = trim($url);
            if (!$counter++) {
                $this->url = $url;
            } else {
                $this->fallback_urls[] = $url;
            }
        }
    }
    public function getUrlString() : string
    {
        return $this->url_string;
    }
    
    /**
     * Check ldap connection and do a fallback to the next server
     * if no connection is possible.
     *
     * @access public
     *
     */
    public function doConnectionCheck() : bool
    {
        foreach (array_merge(array(0 => $this->url), $this->fallback_urls) as $url) {
            try {
                ilLoggerFactory::getLogger('auth')->debug('Using url: ' . $url);
                // Need to do a full bind, since openldap return valid connection links for invalid hosts
                $query = new ilLDAPQuery($this, $url);
                $query->bind(ilLDAPQuery::LDAP_BIND_TEST);
                $this->url = $url;
                return true;
            } catch (ilLDAPQueryException $exc) {
                $this->rotateFallbacks();
                ilLoggerFactory::getLogger('auth')->error('Cannot connect to LDAP server: ' . $url . ' ' . $exc->getCode() . ' ' . $exc->getMessage());
            }
        }
        ilLoggerFactory::getLogger('auth')->warning('No valid LDAP server found');
        return false;
    }
    
    
    public function getName() : string
    {
        return $this->name;
    }
    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }
    public function getVersion() : int
    {
        return $this->version;
    }
    public function setVersion(int $a_version) : void
    {
        $this->version = $a_version;
    }
    public function getBaseDN() : string
    {
        return $this->base_dn;
    }
    public function setBaseDN(string $a_base_dn) : void
    {
        $this->base_dn = $a_base_dn;
    }
    public function isActiveReferrer() : bool
    {
        return $this->referrals;
    }
    public function toggleReferrer(bool $a_status) : void
    {
        $this->referrals = $a_status;
    }
    public function isActiveTLS() : bool
    {
        return $this->tls;
    }
    public function toggleTLS(bool $a_status) : void
    {
        $this->tls = $a_status;
    }
    public function getBindingType() : int
    {
        return $this->binding_type;
    }
    public function setBindingType(int $a_type) : void
    {
        if ($a_type === self::LDAP_BIND_USER) {
            $this->binding_type = self::LDAP_BIND_USER;
        } else {
            $this->binding_type = self::LDAP_BIND_ANONYMOUS;
        }
    }
    public function getBindUser() : string
    {
        return $this->bind_user;
    }
    public function setBindUser(string $a_user) : void
    {
        $this->bind_user = $a_user;
    }
    public function getBindPassword() : string
    {
        return $this->bind_password;
    }
    public function setBindPassword(string $a_password) : void
    {
        $this->bind_password = $a_password;
    }
    public function getSearchBase() : string
    {
        return $this->search_base;
    }
    public function setSearchBase(string $a_search_base) : void
    {
        $this->search_base = $a_search_base;
    }
    public function getUserAttribute() : string
    {
        return $this->user_attribute;
    }
    public function setUserAttribute(string $a_user_attr) : void
    {
        $this->user_attribute = $a_user_attr;
    }
    public function getFilter() : string
    {
        return $this->prepareFilter($this->filter);
    }
    public function setFilter(string $a_filter) : void
    {
        $this->filter = $a_filter;
    }
    public function getGroupDN() : string
    {
        return $this->group_dn;
    }
    public function setGroupDN(string $a_value) : void
    {
        $this->group_dn = $a_value;
    }
    public function getGroupFilter() : string
    {
        return $this->prepareFilter($this->group_filter);
    }
    public function setGroupFilter(string $a_value) : void
    {
        $this->group_filter = $a_value;
    }
    public function getGroupMember() : string
    {
        return $this->group_member;
    }
    public function setGroupMember(string $a_value) : void
    {
        $this->group_member = $a_value;
    }
    public function getGroupName() : string
    {
        return $this->group_name;
    }
    public function setGroupName(string $a_value) : void
    {
        $this->group_name = $a_value;
    }

    /**
     * Get group names as array
     * @return string[]
     */
    public function getGroupNames() : array
    {
        $names = explode(',', $this->getGroupName());

        if (!is_array($names)) {
            return [];
        }

        return array_filter(array_map('trim', $names));
    }
    
    
    public function getGroupAttribute() : string
    {
        return $this->group_attribute;
    }
    public function setGroupAttribute(string $a_value) : void
    {
        $this->group_attribute = $a_value;
    }
    public function toggleMembershipOptional(bool $a_status) : void
    {
        $this->group_optional = $a_status;
    }
    public function isMembershipOptional() : bool
    {
        return $this->group_optional;
    }
    public function setGroupUserFilter(string $a_filter) : void
    {
        $this->group_user_filter = $a_filter;
    }
    public function getGroupUserFilter() : string
    {
        return $this->group_user_filter;
    }

    public function enabledGroupMemberIsDN() : bool
    {
        return $this->memberisdn;
    }
    public function enableGroupMemberIsDN(bool $a_value) : void
    {
        $this->memberisdn = $a_value;
    }
    public function setGroupScope(int $a_value) : void
    {
        $this->group_scope = $a_value;
    }
    public function getGroupScope() : int
    {
        return $this->group_scope;
    }
    public function setUserScope(int $a_value) : void
    {
        $this->user_scope = $a_value;
    }
    public function getUserScope() : int
    {
        return $this->user_scope;
    }
    public function enabledSyncOnLogin() : bool
    {
        return $this->sync_on_login;
    }
    public function enableSyncOnLogin(bool $a_value) : void
    {
        $this->sync_on_login = $a_value;
    }
    public function enabledSyncPerCron() : bool
    {
        return $this->sync_per_cron;
    }
    public function enableSyncPerCron(bool $a_value) : void
    {
        $this->sync_per_cron = $a_value;
    }
    public function setGlobalRole(int $a_role) : void
    {
        $this->global_role = $a_role;
    }
    public function getRoleBindDN() : string
    {
        return $this->role_bind_dn;
    }
    public function setRoleBindDN(string $a_value) : void
    {
        $this->role_bind_dn = $a_value;
    }
    public function getRoleBindPassword() : string
    {
        return $this->role_bind_pass;
    }
    public function setRoleBindPassword(string $a_value) : void
    {
        $this->role_bind_pass = $a_value;
    }
    public function enabledRoleSynchronization() : bool
    {
        return $this->role_sync_active;
    }
    public function enableRoleSynchronization(bool $a_value) : void
    {
        $this->role_sync_active = $a_value;
    }

    public function getUsernameFilter() : string
    {
        return $this->username_filter;
    }
    public function setUsernameFilter(string $a_value) : void
    {
        $this->username_filter = $a_value;
    }

    public function enableEscapeDN(bool $a_value) : void
    {
        $this->escape_dn = $a_value;
    }

    public function enabledEscapeDN() : bool
    {
        return $this->escape_dn;
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
     */
    public function isAccountMigrationEnabled() : bool
    {
        return $this->account_migration;
    }
    
    
    /**
     * Validate user input
     */
    public function validate() : bool
    {
        $this->ilErr->setMessage('');
        if ($this->getName() === '' ||
            $this->getUrl() === '' ||
            $this->getBaseDN() === '' ||
            $this->getUserAttribute() === '') {
            $this->ilErr->setMessage($this->lng->txt('fill_out_all_required_fields'));
        }
        
        if ($this->getBindingType() === self::LDAP_BIND_USER
            && ($this->getBindUser() === '' || $this->getBindPassword() === '')) {
            $this->ilErr->appendMessage($this->lng->txt('ldap_missing_bind_user'));
        }
        
        if (!$this->global_role && ($this->enabledSyncPerCron() || $this->enabledSyncOnLogin())) {
            $this->ilErr->appendMessage($this->lng->txt('ldap_missing_role_assignment'));
        }
        if ($this->getVersion() === 2 && $this->isActiveTLS()) {
            $this->ilErr->appendMessage($this->lng->txt('ldap_tls_conflict'));
        }
        
        return $this->ilErr->getMessage() === '';
    }
    
    public function create() : int
    {
        $next_id = $this->db->nextId('ldap_server_settings');
        
        $query = 'INSERT INTO ldap_server_settings (server_id,active,name,url,version,base_dn,referrals,tls,bind_type,bind_user,bind_pass,' .
            'search_base,user_scope,user_attribute,filter,group_dn,group_scope,group_filter,group_member,group_memberisdn,group_name,' .
            'group_attribute,group_optional,group_user_filter,sync_on_login,sync_per_cron,role_sync_active,role_bind_dn,role_bind_pass,migration, ' .
            'authentication,authentication_type,username_filter, escape_dn) ' .
            'VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)';
        $this->db->queryF(
            $query,
            array(
                'integer','integer','text','text','integer','text','integer','integer','integer','text','text','text','integer',
                'text','text','text','integer','text','text','integer','text','text','integer','text','integer','integer','integer',
                'text','text', 'integer','integer','integer',"text", 'integer'),
            array(
                $next_id,
                $this->isActive(),
                $this->getName(),
                $this->getUrlString(),
                $this->getVersion(),
                $this->getBaseDN(),
                $this->isActiveReferrer(),
                $this->isActiveTLS(),
                $this->getBindingType(),
                $this->getBindUser(),
                $this->getBindPassword(),
                $this->getSearchBase(),
                $this->getUserScope(),
                $this->getUserAttribute(),
                $this->getFilter(),
                $this->getGroupDN(),
                $this->getGroupScope(),
                $this->getGroupFilter(),
                $this->getGroupMember(),
                $this->enabledGroupMemberIsDN(),
                $this->getGroupName(),
                $this->getGroupAttribute(),
                $this->isMembershipOptional(),
                $this->getGroupUserFilter(),
                $this->enabledSyncOnLogin(),
                $this->enabledSyncPerCron(),
                $this->enabledRoleSynchronization(),
                $this->getRoleBindDN(),
                $this->getRoleBindPassword(),
                $this->isAccountMigrationEnabled(),
                $this->isAuthenticationEnabled(),
                $this->getAuthenticationMapping(),
                $this->getUsernameFilter(),
                (int) $this->enabledEscapeDN()
            )
        );
        // end Patch Name Filter
        $this->server_id = $next_id;
        return $next_id;
    }
    
    public function update() : bool
    {
        $query = "UPDATE ldap_server_settings SET " .
            "active = " . $this->db->quote($this->isActive(), 'integer') . ", " .
            "name = " . $this->db->quote($this->getName(), 'text') . ", " .
            "url = " . $this->db->quote($this->getUrlString(), 'text') . ", " .
            "version = " . $this->db->quote($this->getVersion(), 'integer') . ", " .
            "base_dn = " . $this->db->quote($this->getBaseDN(), 'text') . ", " .
            "referrals = " . $this->db->quote($this->isActiveReferrer(), 'integer') . ", " .
            "tls = " . $this->db->quote($this->isActiveTLS(), 'integer') . ", " .
            "bind_type = " . $this->db->quote($this->getBindingType(), 'integer') . ", " .
            "bind_user = " . $this->db->quote($this->getBindUser(), 'text') . ", " .
            "bind_pass = " . $this->db->quote($this->getBindPassword(), 'text') . ", " .
            "search_base = " . $this->db->quote($this->getSearchBase(), 'text') . ", " .
            "user_scope = " . $this->db->quote($this->getUserScope(), 'integer') . ", " .
            "user_attribute = " . $this->db->quote($this->getUserAttribute(), 'text') . ", " .
            "filter = " . $this->db->quote($this->getFilter(), 'text') . ", " .
            "group_dn = " . $this->db->quote($this->getGroupDN(), 'text') . ", " .
            "group_scope = " . $this->db->quote($this->getGroupScope(), 'integer') . ", " .
            "group_filter = " . $this->db->quote($this->getGroupFilter(), 'text') . ", " .
            "group_member = " . $this->db->quote($this->getGroupMember(), 'text') . ", " .
            "group_memberisdn =" . $this->db->quote((int) $this->enabledGroupMemberIsDN(), 'integer') . ", " .
            "group_name = " . $this->db->quote($this->getGroupName(), 'text') . ", " .
            "group_attribute = " . $this->db->quote($this->getGroupAttribute(), 'text') . ", " .
            "group_optional = " . $this->db->quote((int) $this->isMembershipOptional(), 'integer') . ", " .
            "group_user_filter = " . $this->db->quote($this->getGroupUserFilter(), 'text') . ", " .
            "sync_on_login = " . $this->db->quote(($this->enabledSyncOnLogin() ? 1 : 0), 'integer') . ", " .
            "sync_per_cron = " . $this->db->quote(($this->enabledSyncPerCron() ? 1 : 0), 'integer') . ", " .
            "role_sync_active = " . $this->db->quote($this->enabledRoleSynchronization(), 'integer') . ", " .
            "role_bind_dn = " . $this->db->quote($this->getRoleBindDN(), 'text') . ", " .
            "role_bind_pass = " . $this->db->quote($this->getRoleBindPassword(), 'text') . ", " .
            "migration = " . $this->db->quote((int) $this->isAccountMigrationEnabled(), 'integer') . ", " .
            'authentication = ' . $this->db->quote((int) $this->isAuthenticationEnabled(), 'integer') . ', ' .
            'authentication_type = ' . $this->db->quote($this->getAuthenticationMapping(), 'integer') . ' ' .
            ", username_filter = " . $this->db->quote($this->getUsernameFilter(), "text") . " " .
            ", escape_dn = " . $this->db->quote($this->enabledEscapeDN() ? 1 : 0, 'integer') . " " .
            "WHERE server_id = " . $this->db->quote($this->getServerId(), 'integer');
            
        $this->db->manipulate($query);
        return true;
    }
    
    /**
     *  delete
     */
    public function delete() : void
    {
        if (!$this->getServerId()) {
            //TODO check if we need return false
            return;
        }
        
        ilLDAPAttributeMapping::_delete($this->getServerId());
        
        $rules = ilLDAPRoleAssignmentRule::_getRules($this->getServerId());
        
        foreach ($rules as $ruleAssigment) {
            $ruleAssigment->delete();
        }
        
        ilLDAPRoleGroupMappingSettings::_deleteByServerId($this->getServerId());
        
        $query = "DELETE FROM ldap_server_settings " .
            "WHERE server_id = " . $this->db->quote($this->getServerId(), 'integer');
        $this->db->manipulate($query);
    }
    
    //TODO check if this is still needed
    /**
     * Creates an array of options compatible to PEAR Auth
     *
     * @return array auth settings
     */
    public function toPearAuthArray() : array
    {
        $options = array(
            'url' => $this->getUrl(),
            'version' => $this->getVersion(),
            'referrals' => $this->isActiveReferrer());
        
        if ($this->getBindingType() === self::LDAP_BIND_USER) {
            $options['binddn'] = $this->getBindUser();
            $options['bindpw'] = $this->getBindPassword();
        }
        $options['basedn'] = $this->getBaseDN();
        $options['start_tls'] = $this->isActiveTLS();
        $options['userdn'] = $this->getSearchBase();
        if ($this->getUserScope() === self::LDAP_SCOPE_ONE) {
            $options['userscope'] = 'one';
        } else {
            $options['userscope'] = 'sub';
        }
        
        $options['userattr'] = $this->getUserAttribute();
        $options['userfilter'] = $this->getFilter();
        $options['attributes'] = $this->getPearAtributeArray();
        $options['debug'] = self::DEBUG;
        

        $options['enableLogging'] = true;

        switch ($this->getGroupScope()) {
            case self::LDAP_SCOPE_BASE:
                $options['groupscope'] = 'base';
                break;
            case self::LDAP_SCOPE_ONE:
                $options['groupscope'] = 'one';
                break;
            default:
                $options['groupscope'] = 'sub';
                break;
        }
        $options['escape_dn'] = $this->enabledEscapeDN();
        $options['groupdn'] = $this->getGroupDN();
        $options['groupattr'] = $this->getGroupAttribute();
        $options['groupfilter'] = $this->getGroupFilter();
        $options['memberattr'] = $this->getGroupMember();
        $options['memberisdn'] = $this->enabledGroupMemberIsDN();
        $options['group'] = $this->getGroupName();
        
        
        return $options;
    }
    
    /**
     * Create brackets for filters if they do not exist
     */
    private function prepareFilter(string $a_filter) : string
    {
        $filter = trim($a_filter);
        
        if ($filter === '') {
            return $filter;
        }
        
        if (strpos($filter, '(') !== 0) {
            $filter = ('(' . $filter);
        }
        if (substr($filter, -1) !== ')') {
            $filter .= ')';
        }
        return $filter;
    }
    
    /**
     * Get attribute array for pear auth data
     */
    private function getPearAtributeArray() : array
    {
        if ($this->enabledSyncOnLogin()) {
            $mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->getServerId());
            return array_merge(
                array($this->getUserAttribute()),
                $mapping->getFields(),
                array('dn'),
                ilLDAPRoleAssignmentRules::getAttributeNames($this->getServerId())
            );
        }

        return array($this->getUserAttribute());
    }

    /**
     * Read server settings
     *
     */
    private function read() : void
    {
        if (!$this->server_id) {
            return;
        }
        $query = "SELECT * FROM ldap_server_settings WHERE server_id = " . $this->db->quote($this->server_id, ilDBConstants::T_INTEGER);
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->toggleActive((bool) $row->active);
            $this->setName($row->name ?? '');
            $this->setUrl($row->url ?? '');
            $this->setVersion((int) $row->version);
            $this->setBaseDN($row->base_dn ?? '');
            $this->toggleReferrer((bool) $row->referrals);
            $this->toggleTLS((bool) $row->tls);
            $this->setBindingType((int) $row->bind_type);
            $this->setBindUser($row->bind_user ?? '');
            $this->setBindPassword($row->bind_pass ?? '');
            $this->setSearchBase($row->search_base ?? '');
            $this->setUserScope((int) $row->user_scope);
            $this->setUserAttribute($row->user_attribute ?? '');
            $this->setFilter($row->filter ?? '');
            $this->setGroupDN($row->group_dn ?? '');
            $this->setGroupScope((int) $row->group_scope);
            $this->setGroupFilter($row->group_filter ?? '');
            $this->setGroupMember($row->group_member ?? '');
            $this->setGroupAttribute($row->group_attribute ?? '');
            $this->toggleMembershipOptional((bool) $row->group_optional);
            $this->setGroupUserFilter($row->group_user_filter ?? '');
            $this->enableGroupMemberIsDN((bool) $row->group_memberisdn);
            $this->setGroupName($row->group_name ?? '');
            $this->enableSyncOnLogin((bool) $row->sync_on_login);
            $this->enableSyncPerCron((bool) $row->sync_per_cron);
            $this->enableRoleSynchronization((bool) $row->role_sync_active);
            $this->setRoleBindDN($row->role_bind_dn ?? '');
            $this->setRoleBindPassword($row->role_bind_pass ?? '');
            $this->enableAccountMigration((bool) $row->migration);
            $this->enableAuthentication((bool) $row->authentication);
            $this->setAuthenticationMapping((int) $row->authentication_type);
            $this->setUsernameFilter($row->username_filter ?? '');
            $this->enableEscapeDN((bool) $row->escape_dn);
        }
    }
}
