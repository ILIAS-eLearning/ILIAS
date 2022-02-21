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
*/
class ilLDAPQuery
{
    public const LDAP_BIND_DEFAULT = 0;
    public const LDAP_BIND_ADMIN = 1;
    public const LDAP_BIND_TEST = 2;
    public const LDAP_BIND_AUTH = 10;

    /**
     * @deprecated with PHP 7.3 (LDAP_CONTROL_PAGEDRESULTS)
     */
    const IL_LDAP_CONTROL_PAGEDRESULTS = '1.2.840.113556.1.4.319';


    const IL_LDAP_SUPPORTED_CONTROL = 'supportedControl';

    const PAGINATION_SIZE = 100;

    private string $ldap_server_url;
    private ilLDAPServer $settings;
    
    private ilLogger $logger;
    
    private ilLDAPAttributeMapping $mapping;

    private array $user_fields = [];
    private array $users = [];

    /**
     * LDAP Handle
     * @var resource
     */
    private $lh;
    
    /**
     * @throws ilLDAPQueryException
     */
    public function __construct(ilLDAPServer $a_server, string $a_url = '')
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();

        $this->settings = $a_server;
        
        if (strlen($a_url)) {
            $this->ldap_server_url = $a_url;
        } else {
            $this->ldap_server_url = $this->settings->getUrl();
        }
        
        $this->mapping = ilLDAPAttributeMapping::_getInstanceByServerId($this->settings->getServerId());

        $this->fetchUserProfileFields();
        $this->connect();
    }
    
    /**
     * Get server
     * @return ilLDAPServer
     */
    public function getServer() : ilLDAPServer
    {
        return $this->settings;
    }
    
    /**
     * Get one user by login name
     *
     * @param string $a_name login name
     * @return array of user data
     */
    public function fetchUser(string $a_name) : array
    {
        if (!$this->readUserData($a_name)) {
            return [];
        } else {
            return $this->users;
        }
    }
    
    
    /**
     * Fetch all users
     *
     * @return array array of user data
     */
    public function fetchUsers() : array
    {
        // First of all check if a group restriction is enabled
        // YES: => fetch all group members
        // No:  => fetch all users
        if (strlen($this->settings->getGroupName())) {
            $this->logger->debug('Searching for group members.');

            $groups = $this->settings->getGroupNames();
            if (count($groups) <= 1) {
                $this->fetchGroupMembers();
            } else {
                foreach ($groups as $group) {
                    $this->fetchGroupMembers($group);
                }
            }
        }
        if (!strlen($this->settings->getGroupName()) or $this->settings->isMembershipOptional()) {
            $this->logger->info('Start reading all users...');
            $this->readAllUsers();
            #throw new ilLDAPQueryException('LDAP: Called import of users without specifying group restrictions. NOT IMPLEMENTED YET!');
        }
        return $this->users;
    }
    
    /**
     * Perform a query
     * @throws ilLDAPQueryException
     */
    public function query(string $a_search_base, string $a_filter, int $a_scope, array $a_attributes) : ilLDAPResult
    {
        $res = $this->queryByScope($a_scope, $a_search_base, $a_filter, $a_attributes);
        if ($res === false) {
            throw new ilLDAPQueryException(__METHOD__ . ' ' . ldap_error($this->lh) . ' ' .
                sprintf(
                    'DN: %s, Filter: %s, Scope: %s',
                    $a_search_base,
                    $a_filter,
                    $a_scope
                ));
        }
        return (new ilLDAPResult($this->lh, $res))->run();
    }
    
    /**
     * Add value to an existing attribute
     *
     * @throws ilLDAPQueryException
     */
    public function modAdd(string $a_dn, array $a_attribute) : bool
    {
        if (@ldap_mod_add($this->lh, $a_dn, $a_attribute)) {
            return true;
        }
        throw new ilLDAPQueryException(__METHOD__ . ' ' . ldap_error($this->lh));
    }
    
    /**
     * Delete value from an existing attribute
     *
     * @throws ilLDAPQueryException
     */
    public function modDelete(string $a_dn, array $a_attribute) : bool
    {
        if (@ldap_mod_del($this->lh, $a_dn, $a_attribute)) {
            return true;
        }
        throw new ilLDAPQueryException(__METHOD__ . ' ' . ldap_error($this->lh));
    }
    
    /**
     * Fetch all users
     * This function splits the query to filters like e.g (uid=a*) (uid=b*)...
     * This avoids AD page_size_limit
     */
    private function readAllUsers()
    {
        // Build search base
        if (($dn = $this->settings->getSearchBase()) && substr($dn, -1) != ',') {
            $dn .= ',';
        }
        $dn .= $this->settings->getBaseDN();
        $tmp_result = null;

        if ($this->checkPaginationEnabled()) {
            try {
                $tmp_result = $this->runReadAllUsersPaged($dn);
            } catch (ilLDAPPagingException $e) {
                $this->logger->warning('Using LDAP with paging failed. Trying to use fallback.');
                $tmp_result = $this->runReadAllUsersPartial($dn);
            }
        } else {
            $tmp_result = $this->runReadAllUsersPartial($dn);
        }

        if (!$tmp_result->numRows()) {
            $this->logger->notice('No users found. Aborting.');
        }
        $this->logger->info('Found ' . $tmp_result->numRows() . ' users.');
        $attribute = strtolower($this->settings->getUserAttribute());
        foreach ($tmp_result->getRows() as $data) {
            if (isset($data[$attribute])) {
                $this->readUserData($data[$attribute], false, false);
            } else {
                $this->logger->warning('Unknown error. No user attribute found.');
            }
        }
        unset($tmp_result);

        return true;
    }

    /**
     * read all users with ldap paging
     *
     * @throws ilLDAPPagingException
     */
    private function runReadAllUsersPaged(string $dn) : ilLDAPResult
    {
        $filter = '(&' . $this->settings->getFilter();
        $filter .= ('(' . $this->settings->getUserAttribute() . '=*))');
        $this->logger->info('Searching with ldap search and filter ' . $filter . ' in ' . $dn);

        $tmp_result = new ilLDAPResult($this->lh);
        $cookie = '';
        $estimated_results = 0;
        do {
            try {
                $res = ldap_control_paged_result($this->lh, self::PAGINATION_SIZE, true, $cookie);
                if ($res === false) {
                    throw new ilLDAPPagingException('Result pagination failed.');
                }
            } catch (Exception $e) {
                $this->logger->warning('Result pagination failed with message: ' . $e->getMessage());
                throw new ilLDAPPagingException($e->getMessage());
            }

            $res = $this->queryByScope(
                $this->settings->getUserScope(),
                $dn,
                $filter,
                array($this->settings->getUserAttribute())
            );
            $tmp_result->setResult($res);
            $tmp_result->run();
            try {
                ldap_control_paged_result_response($this->lh, $res, $cookie, $estimated_results);
                $this->logger->debug('Estimated number of results: ' . $estimated_results);
            } catch (Exception $e) {
                $this->logger->warning('Result pagination failed with message: ' . $e->getMessage());
                throw new ilLDAPPagingException($e->getMessage());
            }
        } while ($cookie !== null && $cookie != '');

        // finally reset cookie
        ldap_control_paged_result($this->lh, 10000, false, $cookie);
        return $tmp_result;
    }

    /**
     * read all users partial by alphabet
     *
     * @return ilLDAPResult
     */
    private function runReadAllUsersPartial(string $dn) : ilLDAPResult
    {
        $filter = $this->settings->getFilter();
        $page_filter = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','-');
        $chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $tmp_result = new ilLDAPResult($this->lh);

        foreach ($page_filter as $letter) {
            $new_filter = '(&';
            $new_filter .= $filter;

            switch ($letter) {
                case '-':
                    $new_filter .= ('(!(|');
                    foreach ($chars as $char) {
                        $new_filter .= ('(' . $this->settings->getUserAttribute() . '=' . $char . '*)');
                    }
                    $new_filter .= ')))';
                    break;

                default:
                    $new_filter .= ('(' . $this->settings->getUserAttribute() . '=' . $letter . '*))');
                    break;
            }

            $this->logger->info('Searching with ldap search and filter ' . $new_filter . ' in ' . $dn);
            $res = $this->queryByScope(
                $this->settings->getUserScope(),
                $dn,
                $new_filter,
                array($this->settings->getUserAttribute())
            );
            $tmp_result->setResult($res);
            $tmp_result->run();
        }

        return $tmp_result;
    }

    /**
     * check group membership
     * @param string login name
     * @param array user data
     * @return bool
     */
    public function checkGroupMembership(string $a_ldap_user_name, array $ldap_user_data) : bool
    {
        $group_names = $this->getServer()->getGroupNames();
        
        if (!count($group_names)) {
            $this->logger()->debug('No LDAP group restrictions found');
            return true;
        }
        
        $group_dn = $this->getServer()->getGroupDN();
        if (
            $group_dn &&
            (substr($group_dn, -1) != ',')
        ) {
            $group_dn .= ',';
        }
        $group_dn .= $this->getServer()->getBaseDN();
        
        foreach ($group_names as $group) {
            $user = $a_ldap_user_name;
            if ($this->getServer()->enabledGroupMemberIsDN()) {
                if ($this->getServer()->enabledEscapeDN()) {
                    $user = ldap_escape($ldap_user_data['dn'], "", LDAP_ESCAPE_FILTER);
                } else {
                    $user = $ldap_user_data['dn'];
                }
            }
            
            $filter = sprintf(
                '(&(%s=%s)(%s=%s)%s)',
                $this->getServer()->getGroupAttribute(),
                $group,
                $this->getServer()->getGroupMember(),
                $user,
                $this->getServer()->getGroupFilter()
            );
            $this->logger->debug('Current group search base: ' . $group_dn);
            $this->logger->debug('Current group filter: ' . $filter);
            
            $res = $this->queryByScope(
                $this->getServer()->getGroupScope(),
                $group_dn,
                $filter,
                [$this->getServer()->getGroupMember()]
            );
            
            $this->logger->dump($res);
            
            $tmp_result = new ilLDAPResult($this->lh, $res);
            $tmp_result->run();
            $group_result = $tmp_result->getRows();
            
            $this->logger->debug('Group query returned: ');
            $this->logger->dump($group_result, ilLogLevel::DEBUG);
            
            if (count($group_result)) {
                return true;
            }
        }
        
        // group restrictions failed check optional membership
        if ($this->getServer()->isMembershipOptional()) {
            $this->logger->debug('Group restrictions failed, checking user filter.');
            if ($this->readUserData($a_ldap_user_name, true, true)) {
                $this->logger->debug('User filter matches.');
                return true;
            }
        }
        $this->logger->debug('Group restrictions failed.');
        return false;
    }
    

    /**
     * Fetch group member ids
     */
    private function fetchGroupMembers($a_name = '') : bool
    {
        $group_name = strlen($a_name) ? $a_name : $this->settings->getGroupName();
        
        // Build filter
        $filter = sprintf(
            '(&(%s=%s)%s)',
            $this->settings->getGroupAttribute(),
            $group_name,
            $this->settings->getGroupFilter()
        );
        
        
        // Build search base
        if (($gdn = $this->settings->getGroupDN()) && substr($gdn, -1) != ',') {
            $gdn .= ',';
        }
        $gdn .= $this->settings->getBaseDN();
        
        $this->logger->debug('Using filter ' . $filter);
        $this->logger->debug('Using DN ' . $gdn);
        $res = $this->queryByScope(
            $this->settings->getGroupScope(),
            $gdn,
            $filter,
            array($this->settings->getGroupMember())
        );
            
        $tmp_result = new ilLDAPResult($this->lh, $res);
        $tmp_result->run();
        $group_data = $tmp_result->getRows();
        
        
        if (!$tmp_result->numRows()) {
            $this->logger->info('No group found.');
            return false;
        }
                
        $attribute_name = strtolower($this->settings->getGroupMember());
        
        // All groups
        foreach ($group_data as $data) {
            if (is_array($data[$attribute_name])) {
                $this->logger->debug('Found ' . count($data[$attribute_name]) . ' group members for group ' . $data['dn']);
                foreach ($data[$attribute_name] as $name) {
                    $this->readUserData($name, true, true);
                }
            } else {
                $this->readUserData($data[$attribute_name], true, true);
            }
        }
        unset($tmp_result);
        return true;
    }
    
    /**
     * Read user data
     * @param bool $a_check_dn check dn
     * @param bool $a_try_group_user_filter use group filter
     */
    private function readUserData(string $a_name, bool $a_check_dn = false, bool $a_try_group_user_filter = false) : bool
    {
        $filter = $this->settings->getFilter();
        if ($a_try_group_user_filter) {
            if ($this->settings->isMembershipOptional()) {
                $filter = $this->settings->getGroupUserFilter();
            }
        }
        
        // Build filter
        if ($this->settings->enabledGroupMemberIsDN() and $a_check_dn) {
            $dn = $a_name;

            $fields = array_merge($this->user_fields, array('useraccountcontrol'));
            $res = $this->queryByScope(ilLDAPServer::LDAP_SCOPE_BASE, strtolower($dn), $filter, $fields);
        } else {
            $filter = sprintf(
                '(&(%s=%s)%s)',
                $this->settings->getUserAttribute(),
                $a_name,
                $filter
            );

            // Build search base
            if (($dn = $this->settings->getSearchBase()) && substr($dn, -1) != ',') {
                $dn .= ',';
            }
            $dn .= $this->settings->getBaseDN();
            $fields = array_merge($this->user_fields, array('useraccountcontrol'));
            $res = $this->queryByScope($this->settings->getUserScope(), strtolower($dn), $filter, $fields);
        }
        
        
        $tmp_result = new ilLDAPResult($this->lh, $res);
        $tmp_result->run();
        if (!$tmp_result->numRows()) {
            $this->logger->info('LDAP: No user data found for: ' . $a_name);
            unset($tmp_result);
            return false;
        }
        
        if ($user_data = $tmp_result->get()) {
            if (isset($user_data['useraccountcontrol'])) {
                if (($user_data['useraccountcontrol'] & 0x02)) {
                    $this->logger->notice('LDAP: ' . $a_name . ' account disabled.');
                    return false;
                }
            }
            
            $account = $user_data[strtolower($this->settings->getUserAttribute())];
            if (is_array($account)) {
                $user_ext = strtolower(array_shift($account));
            } else {
                $user_ext = strtolower($account);
            }
            
            // auth mode depends on ldap server settings
            $auth_mode = $this->settings->getAuthenticationMappingKey();
            $user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount($auth_mode, $user_ext);
            $this->users[$user_ext] = $user_data;
        }
        return true;
    }

    /**
     * Parse authentication mode
     * @return string auth mode
     */
    private function parseAuthMode() : string
    {
        return $this->settings->getAuthenticationMappingKey();
    }
    
    /**
     * Query by scope
     * IL_SCOPE_SUB => ldap_search
     * IL_SCOPE_ONE => ldap_list
     */
    private function queryByScope(int $a_scope, string $a_base_dn, string $a_filter, array $a_attributes)
    {
        $a_filter = $a_filter ? $a_filter : "(objectclass=*)";

        switch ($a_scope) {
            case ilLDAPServer::LDAP_SCOPE_SUB:
                $res = @ldap_search($this->lh, $a_base_dn, $a_filter, $a_attributes);
                break;
                
            case ilLDAPServer::LDAP_SCOPE_ONE:
                $res = @ldap_list($this->lh, $a_base_dn, $a_filter, $a_attributes);
                break;
            
            case ilLDAPServer::LDAP_SCOPE_BASE:

                $res = @ldap_read($this->lh, $a_base_dn, $a_filter, $a_attributes);
                break;

            default:
                $this->logger->warning("LDAP: LDAPQuery: Unknown search scope");
        }
        
        $error = ldap_error($this->lh);
        if (strcmp('Success', $error) !== 0) {
            $this->logger->warning($error);
            $this->logger->warning('Base DN:' . $a_base_dn);
            $this->logger->warning('Filter: ' . $a_filter);
        }
        
        return $res;
    }
    
    /**
     * Connect to LDAP server
     *
     * @throws ilLDAPQueryException
     *
     */
    private function connect() : void
    {
        $this->lh = @ldap_connect($this->ldap_server_url);
        
        // LDAP Connect
        if (!$this->lh) {
            throw new ilLDAPQueryException("LDAP: Cannot connect to LDAP Server: " . $this->settings->getUrl());
        }
        // LDAP Version
        if (!ldap_set_option($this->lh, LDAP_OPT_PROTOCOL_VERSION, $this->settings->getVersion())) {
            throw new ilLDAPQueryException("LDAP: Cannot set version to: " . $this->settings->getVersion());
        }
        // Switch on referrals
        if ($this->settings->isActiveReferrer()) {
            if (!ldap_set_option($this->lh, LDAP_OPT_REFERRALS, true)) {
                throw new ilLDAPQueryException("LDAP: Cannot switch on LDAP referrals");
            }
        } else {
            ldap_set_option($this->lh, LDAP_OPT_REFERRALS, false);
            $this->logger->debug('Switching referrals to false.');
        }
        // Start TLS
        if ($this->settings->isActiveTLS()) {
            if (!ldap_start_tls($this->lh)) {
                throw new ilLDAPQueryException("LDAP: Cannot start LDAP TLS");
            }
        }
    }
    
    /**
     * Bind to LDAP server
     *
     * @access public
     * @param int binding_type ilLDAPQuery::LDAP_BIND_DEFAULT || ilLDAPQuery::LDAP_BIND_ADMIN
     * @throws ilLDAPQueryException on connection failure.
     *
     */
    public function bind(int $a_binding_type = ilLDAPQuery::LDAP_BIND_DEFAULT, string $a_user_dn = '', string $a_password = '')
    {
        switch ($a_binding_type) {
            case ilLDAPQuery::LDAP_BIND_TEST:
                ldap_set_option($this->lh, LDAP_OPT_NETWORK_TIMEOUT, ilLDAPServer::DEFAULT_NETWORK_TIMEOUT);
                // fall through
                // no break
            case ilLDAPQuery::LDAP_BIND_DEFAULT:
                // Now bind anonymously or as user
                if (
                ilLDAPServer::LDAP_BIND_USER == $this->settings->getBindingType() &&
                    strlen($this->settings->getBindUser())
                ) {
                    $user = $this->settings->getBindUser();
                    $pass = $this->settings->getBindPassword();

                    define('IL_LDAP_REBIND_USER', $user);
                    define('IL_LDAP_REBIND_PASS', $pass);
                    $this->logger->debug('Bind as ' . $user);
                } else {
                    $user = $pass = '';
                    $this->logger->debug('Bind anonymous');
                }
                break;
                
            case ilLDAPQuery::LDAP_BIND_ADMIN:
                $user = $this->settings->getRoleBindDN();
                $pass = $this->settings->getRoleBindPassword();
                
                if (!strlen($user) or !strlen($pass)) {
                    $user = $this->settings->getBindUser();
                    $pass = $this->settings->getBindPassword();
                }

                define('IL_LDAP_REBIND_USER', $user);
                define('IL_LDAP_REBIND_PASS', $pass);
                break;
                
            case ilLDAPQuery::LDAP_BIND_AUTH:
                $this->logger->debug('Trying to bind as: ' . $a_user_dn);
                $user = $a_user_dn;
                $pass = $a_password;
                break;
                
                
            default:
                throw new ilLDAPQueryException('LDAP: unknown binding type in: ' . __METHOD__);
        }
        
        if (!@ldap_bind($this->lh, $user, $pass)) {
            throw new ilLDAPQueryException('LDAP: Cannot bind as ' . $user . ' with message: ' . ldap_err2str(ldap_errno($this->lh)) . ' Trying fallback...', ldap_errno($this->lh));
        } else {
            $this->logger->debug('Bind successful.');
        }
    }
    
    /**
     * fetch required fields of user profile data
     *
     * @access private
     * @param
     *
     */
    private function fetchUserProfileFields() : array
    {
        $this->user_fields = array_merge(
            array($this->settings->getUserAttribute()),
            array('dn'),
            $this->mapping->getFields(),
            ilLDAPRoleAssignmentRules::getAttributeNames($this->getServer()->getServerId())
        );
    }
    
    
    /**
     * Unbind
     */
    private function unbind() : void
    {
        if ($this->lh) {
            @ldap_unbind($this->lh);
        }
    }
    
    
    /**
     * Destructor unbind from ldap server
     *
     */
    public function __destruct()
    {
        if ($this->lh) {
            @ldap_unbind($this->lh);
        }
    }

    /**
     * Check if pagination is enabled (rfc: 2696)
     * @return bool
     */
    public function checkPaginationEnabled() : bool
    {
        if ($this->getServer()->getVersion() != 3) {
            $this->logger->info('Pagination control unavailable for ldap v' . $this->getServer()->getVersion());
            return false;
        }

        $result = ldap_read($this->lh, '', '(objectClass=*)', [self::IL_LDAP_SUPPORTED_CONTROL]);
        if ($result === false) {
            $this->logger->warning('Failed to query for pagination control');
            return false;
        }
        $entries = (array) (ldap_get_entries($this->lh, $result)[0] ?? []);
        if (
            array_key_exists(strtolower(self::IL_LDAP_SUPPORTED_CONTROL), $entries) &&
            is_array($entries[strtolower(self::IL_LDAP_SUPPORTED_CONTROL)]) &&
            in_array(self::IL_LDAP_CONTROL_PAGEDRESULTS, $entries[strtolower(self::IL_LDAP_SUPPORTED_CONTROL)])
        ) {
            $this->logger->info('Using paged control');
            return true;
        }
        $this->logger->info('Paged control disabled');
        return false;
    }
}
