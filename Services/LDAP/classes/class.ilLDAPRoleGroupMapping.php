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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPRoleGroupMapping
{
    private static ?ilLDAPRoleGroupMapping $instance = null;
    private ilLogger $log;
    private ilRbacReview $rbacreview;
    private ilObjectDataCache $ilObjDataCache;

    private array $servers;
    private array $mappings;
    private array $mapping_members;
    private array $mapping_info;
    private array $mapping_info_strict;
    private array $query;
    private array $users;
    private ?array $user_dns;
    private bool $active_servers = false;
    
    /**
     * Singleton contructor
     */
    private function __construct()
    {
        global $DIC;

        $this->log = $DIC->logger()->auth();
        $this->rbacreview = $DIC->rbac()->review();
        $this->ilObjDataCache = $DIC['ilObjDataCache'];

        $this->initServers();
    }
    
    /**
     * Get singleton instance of this class
     */
    public static function _getInstance() : ?ilLDAPRoleGroupMapping
    {
        if (is_object(self::$instance)) {
            return self::$instance;
        }
        return self::$instance = new ilLDAPRoleGroupMapping();
    }
    
    /**
     * Get info string for object
     * If check info type is enabled this function will check if the info string is visible in the repository.
     *
     * @param int object id
     * @param bool check info type
     * @return string[]
     */
    public function getInfoStrings(int $a_obj_id, bool $a_check_type = false) : array
    {
        if (!$this->active_servers) {
            return [];
        }

        if ($a_check_type) {
            if (isset($this->mapping_info_strict[$a_obj_id]) && is_array($this->mapping_info_strict[$a_obj_id])) {
                return $this->mapping_info_strict[$a_obj_id];
            }
        } elseif (isset($this->mapping_info[$a_obj_id]) && is_array($this->mapping_info[$a_obj_id])) {
            return $this->mapping_info[$a_obj_id];
        }

        return [];
    }
    
    
    /**
     * This method is typically called from class RbacAdmin::assignUser()
     * It checks if there is a role mapping and if the user has auth mode LDAP
     * After these checks the user is assigned to the LDAP group
     */
    public function assign($a_role_id, $a_usr_id) : bool
    {
        // return if there nothing to do
        if (!$this->active_servers) {
            return false;
        }
        
        if (!$this->isHandledRole($a_role_id)) {
            return false;
        }
        if (!$this->isHandledUser($a_usr_id)) {
            $this->log->write('LDAP assign: User ID: ' . $a_usr_id . ' has no LDAP account');
            return false;
        }
        $this->log->write('LDAP assign: User ID: ' . $a_usr_id . ' Role Id: ' . $a_role_id);
        $this->assignToGroup($a_role_id, $a_usr_id);

        return true;
    }
    
    /**
     * Delete role.
     * This function triggered from ilRbacAdmin::deleteRole
     * It deassigns all user from the mapped ldap group.
     *
     * @param int role id
     *
     */
    public function deleteRole(int $a_role_id) : bool
    {
        // return if there nothing to do
        if (!$this->active_servers) {
            return false;
        }
        
        if (!$this->isHandledRole($a_role_id)) {
            return false;
        }
        
        foreach ($this->rbacreview->assignedUsers($a_role_id) as $usr_id) {
            $this->deassign($a_role_id, $usr_id);
        }
        return true;
    }
    
    
    /**
     * This method is typically called from class RbacAdmin::deassignUser()
     * It checks if there is a role mapping and if the user has auth mode LDAP
     * After these checks the user is deassigned from the LDAP group
     */
    public function deassign($a_role_id, $a_usr_id) : bool
    {
        // return if there notzing to do
        if (!$this->active_servers) {
            return false;
        }
        if (!$this->isHandledRole($a_role_id)) {
            return false;
        }
        if (!$this->isHandledUser($a_usr_id)) {
            return false;
        }
        $this->log->write('LDAP deassign: User ID: ' . $a_usr_id . ' Role Id: ' . $a_role_id);
        $this->deassignFromGroup($a_role_id, $a_usr_id);
        
        return true;
    }
    
    /**
     * Delete user => deassign from all ldap groups
     *
     * @param int user id
     */
    public function deleteUser($a_usr_id) : bool
    {
        foreach ($this->mappings as $role_id) {
            $this->deassign($role_id, $a_usr_id);
        }
        return true;
    }
    
    
    /**
     * Check if there is any active server with
     */
    private function initServers() : void
    {
        $server_ids = ilLDAPServer::_getRoleSyncServerIds();
        
        if (!count($server_ids)) {
            return;
        }

        // Init servers
        $this->active_servers = true;
        $this->servers = [];
        $this->mappings = [];
        foreach ($server_ids as $server_id) {
            $this->servers[$server_id] = new ilLDAPServer($server_id);
            $this->mappings = ilLDAPRoleGroupMappingSettings::_getAllActiveMappings();
        }
        $this->mapping_info = [];
        $this->mapping_info_strict = [];
        foreach ($this->mappings as $mapping) {
            foreach ($mapping as $data) {
                if ($data['info'] !== '' && $data['object_id']) {
                    $this->mapping_info[$data['object_id']][] = $data['info'];
                }
                if ($data['info'] !== '' && ($data['info_type'] === ilLDAPRoleGroupMappingSettings::MAPPING_INFO_ALL)) {
                    $this->mapping_info_strict[$data['object_id']][] = $data['info'];
                }
            }
        }
        $this->users = ilObjUser::_getExternalAccountsByAuthMode('ldap', true);
    }
    
    /**
     * Check if a role is handled or not
     *
     * @param int role_id
     * @return bool server id or 0 if mapping exists
     *
     */
    private function isHandledRole($a_role_id) : bool
    {
        return array_key_exists($a_role_id, $this->mappings);
    }
    
    /**
     * Check if user is ldap user
     */
    private function isHandledUser($a_usr_id) : bool
    {
        return array_key_exists($a_usr_id, $this->users);
    }
    
    
    /**
     * Assign user to group
     *
     * @param int role_id
     * @param int user_id
     */
    private function assignToGroup($a_role_id, $a_usr_id) : void
    {
        foreach ($this->mappings[$a_role_id] as $data) {
            try {
                if ($data['isdn']) {
                    $external_account = $this->readDN($a_usr_id, $data['server_id']);
                } else {
                    $external_account = $this->users[$a_usr_id];
                }
                
                // Forcing modAdd since Active directory is too slow and i cannot check if a user is member or not.
                #if($this->isMember($external_account,$data))
                #{
                #	$this->log->write("LDAP assign: User already assigned to group '".$data['dn']."'");
                #}
                #else
                {
                    // Add user
                    $query_obj = $this->getLDAPQueryInstance($data['server_id'], $data['url']);
                    $query_obj->modAdd($data['dn'], array($data['member'] => $external_account));
                    $this->log->write('LDAP assign: Assigned ' . $external_account . ' to group ' . $data['dn']);
                }
            } catch (ilLDAPQueryException $exc) {
                $this->log->write($exc->getMessage());
                // try next mapping
                continue;
            }
        }
    }
    
    /**
     * Deassign user from group
     *
     * @param int role_id
     * @param int user_id
     *
     */
    private function deassignFromGroup($a_role_id, $a_usr_id) : void
    {
        foreach ($this->mappings[$a_role_id] as $data) {
            try {
                if ($data['isdn']) {
                    $external_account = $this->readDN($a_usr_id, $data['server_id']);
                } else {
                    $external_account = $this->users[$a_usr_id];
                }
                
                // Check for other role membership
                if ($role_id = $this->checkOtherMembership($a_usr_id, $a_role_id, $data)) {
                    $this->log->write('LDAP deassign: User is still assigned to role "' . $role_id . '".');
                    continue;
                }
                /*
                if(!$this->isMember($external_account,$data))
                 {
                    $this->log->write("LDAP deassign: User not assigned to group '".$data['dn']."'");
                    continue;
                 }
                */
                // Deassign user
                $query_obj = $this->getLDAPQueryInstance($data['server_id'], $data['url']);
                $query_obj->modDelete($data['dn'], array($data['member'] => $external_account));
                $this->log->write('LDAP deassign: Deassigned ' . $external_account . ' from group ' . $data['dn']);
                
                // Delete from cache
                if (is_array($this->mapping_members[$data['mapping_id']])) {
                    $key = array_search($external_account, $this->mapping_members[$data['mapping_id']], true);
                    if ($key || $key === 0) {
                        unset($this->mapping_members[$data['mapping_id']]);
                    }
                }
            } catch (ilLDAPQueryException $exc) {
                $this->log->write($exc->getMessage());
                // try next mapping
                continue;
            }
        }
    }
    
    /**
     * Check other membership
     *
     * @return string|false role name
     *
     */
    private function checkOtherMembership(int $a_usr_id, int $a_role_id, array $a_data)
    {
        foreach ($this->mappings as $role_id => $tmp_data) {
            foreach ($tmp_data as $data) {
                if ($role_id === $a_role_id) {
                    continue;
                }
                if ($data['server_id'] !== $a_data['server_id']) {
                    continue;
                }
                if ($data['dn'] !== $a_data['dn']) {
                    continue;
                }
                if ($this->rbacreview->isAssigned($a_usr_id, $role_id)) {
                    return $this->ilObjDataCache->lookupTitle((int) $role_id);
                }
            }
        }
        return false;
    }
    
    /**
     * Read DN of user
     *
     * @param int user id
     * @param int server id
     * @throws ilLDAPQueryException
     */
    private function readDN(int $a_usr_id, int $a_server_id)
    {
        if ($this->user_dns === null) {
            $this->user_dns = [];
        }
        if (isset($this->user_dns[$a_usr_id])) {
            return $this->user_dns[$a_usr_id];
        }
        
        $external_account = $this->users[$a_usr_id];
        
        $server = $this->servers[$a_server_id];
        $query_obj = $this->getLDAPQueryInstance($a_server_id, $server->getUrl());

        if ($search_base = $server->getSearchBase()) {
            $search_base .= ',';
        }
        $search_base .= $server->getBaseDN();

        // try optional group user filter first
        if ($server->isMembershipOptional() && $server->getGroupUserFilter()) {
            $userFilter = $server->getGroupUserFilter();
        } else {
            $userFilter = $server->getFilter();
        }

        $filter = sprintf(
            '(&(%s=%s)%s)',
            $server->getUserAttribute(),
            $external_account,
            $userFilter
        );

        $res = $query_obj->query($search_base, $filter, $server->getUserScope(), array('dn'));

        if (!$res->numRows()) {
            throw new ilLDAPQueryException(__METHOD__ . ' cannot find dn for user ' . $external_account);
        }
        if ($res->numRows() > 1) {
            throw new ilLDAPQueryException(__METHOD__ . ' found multiple distinguished name for: ' . $external_account);
        }

        $data = $res->get();
        $this->user_dns[$a_usr_id] = $data['dn'];
        return $this->user_dns[$a_usr_id];
    }
    
    /**
     * Get LDAPQueryInstance
     *
     * @throws ilLDAPQueryException
     */
    private function getLDAPQueryInstance($a_server_id, $a_url)
    {
        if (array_key_exists($a_server_id, $this->query) &&
            array_key_exists($a_url, $this->query[$a_server_id]) &&
            is_object($this->query[$a_server_id][$a_url])) {
            return $this->query[$a_server_id][$a_url];
        }
        $tmp_query = new ilLDAPQuery($this->servers[$a_server_id], $a_url);
        $tmp_query->bind(ilLDAPQuery::LDAP_BIND_ADMIN);

        return $this->query[$a_server_id][$a_url] = $tmp_query;
    }
}
