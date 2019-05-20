<?php



/**
 * LdapServerSettings
 */
class LdapServerSettings
{
    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var int
     */
    private $active = '0';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var int
     */
    private $version = '0';

    /**
     * @var string|null
     */
    private $baseDn;

    /**
     * @var int
     */
    private $referrals = '0';

    /**
     * @var int
     */
    private $tls = '0';

    /**
     * @var int
     */
    private $bindType = '0';

    /**
     * @var string|null
     */
    private $bindUser;

    /**
     * @var string|null
     */
    private $bindPass;

    /**
     * @var string|null
     */
    private $searchBase;

    /**
     * @var bool
     */
    private $userScope = '0';

    /**
     * @var string|null
     */
    private $userAttribute;

    /**
     * @var string|null
     */
    private $filter;

    /**
     * @var string|null
     */
    private $groupDn;

    /**
     * @var bool
     */
    private $groupScope = '0';

    /**
     * @var string|null
     */
    private $groupFilter;

    /**
     * @var string|null
     */
    private $groupMember;

    /**
     * @var bool
     */
    private $groupMemberisdn = '0';

    /**
     * @var string|null
     */
    private $groupName;

    /**
     * @var string|null
     */
    private $groupAttribute;

    /**
     * @var bool
     */
    private $groupOptional = '0';

    /**
     * @var string|null
     */
    private $groupUserFilter;

    /**
     * @var bool
     */
    private $syncOnLogin = '0';

    /**
     * @var bool
     */
    private $syncPerCron = '0';

    /**
     * @var bool
     */
    private $roleSyncActive = '0';

    /**
     * @var string|null
     */
    private $roleBindDn;

    /**
     * @var string|null
     */
    private $roleBindPass;

    /**
     * @var bool
     */
    private $migration = '0';

    /**
     * @var bool
     */
    private $authentication = '1';

    /**
     * @var bool
     */
    private $authenticationType = '0';

    /**
     * @var string|null
     */
    private $usernameFilter;


    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set active.
     *
     * @param int $active
     *
     * @return LdapServerSettings
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return LdapServerSettings
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return LdapServerSettings
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set version.
     *
     * @param int $version
     *
     * @return LdapServerSettings
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set baseDn.
     *
     * @param string|null $baseDn
     *
     * @return LdapServerSettings
     */
    public function setBaseDn($baseDn = null)
    {
        $this->baseDn = $baseDn;

        return $this;
    }

    /**
     * Get baseDn.
     *
     * @return string|null
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * Set referrals.
     *
     * @param int $referrals
     *
     * @return LdapServerSettings
     */
    public function setReferrals($referrals)
    {
        $this->referrals = $referrals;

        return $this;
    }

    /**
     * Get referrals.
     *
     * @return int
     */
    public function getReferrals()
    {
        return $this->referrals;
    }

    /**
     * Set tls.
     *
     * @param int $tls
     *
     * @return LdapServerSettings
     */
    public function setTls($tls)
    {
        $this->tls = $tls;

        return $this;
    }

    /**
     * Get tls.
     *
     * @return int
     */
    public function getTls()
    {
        return $this->tls;
    }

    /**
     * Set bindType.
     *
     * @param int $bindType
     *
     * @return LdapServerSettings
     */
    public function setBindType($bindType)
    {
        $this->bindType = $bindType;

        return $this;
    }

    /**
     * Get bindType.
     *
     * @return int
     */
    public function getBindType()
    {
        return $this->bindType;
    }

    /**
     * Set bindUser.
     *
     * @param string|null $bindUser
     *
     * @return LdapServerSettings
     */
    public function setBindUser($bindUser = null)
    {
        $this->bindUser = $bindUser;

        return $this;
    }

    /**
     * Get bindUser.
     *
     * @return string|null
     */
    public function getBindUser()
    {
        return $this->bindUser;
    }

    /**
     * Set bindPass.
     *
     * @param string|null $bindPass
     *
     * @return LdapServerSettings
     */
    public function setBindPass($bindPass = null)
    {
        $this->bindPass = $bindPass;

        return $this;
    }

    /**
     * Get bindPass.
     *
     * @return string|null
     */
    public function getBindPass()
    {
        return $this->bindPass;
    }

    /**
     * Set searchBase.
     *
     * @param string|null $searchBase
     *
     * @return LdapServerSettings
     */
    public function setSearchBase($searchBase = null)
    {
        $this->searchBase = $searchBase;

        return $this;
    }

    /**
     * Get searchBase.
     *
     * @return string|null
     */
    public function getSearchBase()
    {
        return $this->searchBase;
    }

    /**
     * Set userScope.
     *
     * @param bool $userScope
     *
     * @return LdapServerSettings
     */
    public function setUserScope($userScope)
    {
        $this->userScope = $userScope;

        return $this;
    }

    /**
     * Get userScope.
     *
     * @return bool
     */
    public function getUserScope()
    {
        return $this->userScope;
    }

    /**
     * Set userAttribute.
     *
     * @param string|null $userAttribute
     *
     * @return LdapServerSettings
     */
    public function setUserAttribute($userAttribute = null)
    {
        $this->userAttribute = $userAttribute;

        return $this;
    }

    /**
     * Get userAttribute.
     *
     * @return string|null
     */
    public function getUserAttribute()
    {
        return $this->userAttribute;
    }

    /**
     * Set filter.
     *
     * @param string|null $filter
     *
     * @return LdapServerSettings
     */
    public function setFilter($filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter.
     *
     * @return string|null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set groupDn.
     *
     * @param string|null $groupDn
     *
     * @return LdapServerSettings
     */
    public function setGroupDn($groupDn = null)
    {
        $this->groupDn = $groupDn;

        return $this;
    }

    /**
     * Get groupDn.
     *
     * @return string|null
     */
    public function getGroupDn()
    {
        return $this->groupDn;
    }

    /**
     * Set groupScope.
     *
     * @param bool $groupScope
     *
     * @return LdapServerSettings
     */
    public function setGroupScope($groupScope)
    {
        $this->groupScope = $groupScope;

        return $this;
    }

    /**
     * Get groupScope.
     *
     * @return bool
     */
    public function getGroupScope()
    {
        return $this->groupScope;
    }

    /**
     * Set groupFilter.
     *
     * @param string|null $groupFilter
     *
     * @return LdapServerSettings
     */
    public function setGroupFilter($groupFilter = null)
    {
        $this->groupFilter = $groupFilter;

        return $this;
    }

    /**
     * Get groupFilter.
     *
     * @return string|null
     */
    public function getGroupFilter()
    {
        return $this->groupFilter;
    }

    /**
     * Set groupMember.
     *
     * @param string|null $groupMember
     *
     * @return LdapServerSettings
     */
    public function setGroupMember($groupMember = null)
    {
        $this->groupMember = $groupMember;

        return $this;
    }

    /**
     * Get groupMember.
     *
     * @return string|null
     */
    public function getGroupMember()
    {
        return $this->groupMember;
    }

    /**
     * Set groupMemberisdn.
     *
     * @param bool $groupMemberisdn
     *
     * @return LdapServerSettings
     */
    public function setGroupMemberisdn($groupMemberisdn)
    {
        $this->groupMemberisdn = $groupMemberisdn;

        return $this;
    }

    /**
     * Get groupMemberisdn.
     *
     * @return bool
     */
    public function getGroupMemberisdn()
    {
        return $this->groupMemberisdn;
    }

    /**
     * Set groupName.
     *
     * @param string|null $groupName
     *
     * @return LdapServerSettings
     */
    public function setGroupName($groupName = null)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get groupName.
     *
     * @return string|null
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * Set groupAttribute.
     *
     * @param string|null $groupAttribute
     *
     * @return LdapServerSettings
     */
    public function setGroupAttribute($groupAttribute = null)
    {
        $this->groupAttribute = $groupAttribute;

        return $this;
    }

    /**
     * Get groupAttribute.
     *
     * @return string|null
     */
    public function getGroupAttribute()
    {
        return $this->groupAttribute;
    }

    /**
     * Set groupOptional.
     *
     * @param bool $groupOptional
     *
     * @return LdapServerSettings
     */
    public function setGroupOptional($groupOptional)
    {
        $this->groupOptional = $groupOptional;

        return $this;
    }

    /**
     * Get groupOptional.
     *
     * @return bool
     */
    public function getGroupOptional()
    {
        return $this->groupOptional;
    }

    /**
     * Set groupUserFilter.
     *
     * @param string|null $groupUserFilter
     *
     * @return LdapServerSettings
     */
    public function setGroupUserFilter($groupUserFilter = null)
    {
        $this->groupUserFilter = $groupUserFilter;

        return $this;
    }

    /**
     * Get groupUserFilter.
     *
     * @return string|null
     */
    public function getGroupUserFilter()
    {
        return $this->groupUserFilter;
    }

    /**
     * Set syncOnLogin.
     *
     * @param bool $syncOnLogin
     *
     * @return LdapServerSettings
     */
    public function setSyncOnLogin($syncOnLogin)
    {
        $this->syncOnLogin = $syncOnLogin;

        return $this;
    }

    /**
     * Get syncOnLogin.
     *
     * @return bool
     */
    public function getSyncOnLogin()
    {
        return $this->syncOnLogin;
    }

    /**
     * Set syncPerCron.
     *
     * @param bool $syncPerCron
     *
     * @return LdapServerSettings
     */
    public function setSyncPerCron($syncPerCron)
    {
        $this->syncPerCron = $syncPerCron;

        return $this;
    }

    /**
     * Get syncPerCron.
     *
     * @return bool
     */
    public function getSyncPerCron()
    {
        return $this->syncPerCron;
    }

    /**
     * Set roleSyncActive.
     *
     * @param bool $roleSyncActive
     *
     * @return LdapServerSettings
     */
    public function setRoleSyncActive($roleSyncActive)
    {
        $this->roleSyncActive = $roleSyncActive;

        return $this;
    }

    /**
     * Get roleSyncActive.
     *
     * @return bool
     */
    public function getRoleSyncActive()
    {
        return $this->roleSyncActive;
    }

    /**
     * Set roleBindDn.
     *
     * @param string|null $roleBindDn
     *
     * @return LdapServerSettings
     */
    public function setRoleBindDn($roleBindDn = null)
    {
        $this->roleBindDn = $roleBindDn;

        return $this;
    }

    /**
     * Get roleBindDn.
     *
     * @return string|null
     */
    public function getRoleBindDn()
    {
        return $this->roleBindDn;
    }

    /**
     * Set roleBindPass.
     *
     * @param string|null $roleBindPass
     *
     * @return LdapServerSettings
     */
    public function setRoleBindPass($roleBindPass = null)
    {
        $this->roleBindPass = $roleBindPass;

        return $this;
    }

    /**
     * Get roleBindPass.
     *
     * @return string|null
     */
    public function getRoleBindPass()
    {
        return $this->roleBindPass;
    }

    /**
     * Set migration.
     *
     * @param bool $migration
     *
     * @return LdapServerSettings
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;

        return $this;
    }

    /**
     * Get migration.
     *
     * @return bool
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * Set authentication.
     *
     * @param bool $authentication
     *
     * @return LdapServerSettings
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;

        return $this;
    }

    /**
     * Get authentication.
     *
     * @return bool
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * Set authenticationType.
     *
     * @param bool $authenticationType
     *
     * @return LdapServerSettings
     */
    public function setAuthenticationType($authenticationType)
    {
        $this->authenticationType = $authenticationType;

        return $this;
    }

    /**
     * Get authenticationType.
     *
     * @return bool
     */
    public function getAuthenticationType()
    {
        return $this->authenticationType;
    }

    /**
     * Set usernameFilter.
     *
     * @param string|null $usernameFilter
     *
     * @return LdapServerSettings
     */
    public function setUsernameFilter($usernameFilter = null)
    {
        $this->usernameFilter = $usernameFilter;

        return $this;
    }

    /**
     * Get usernameFilter.
     *
     * @return string|null
     */
    public function getUsernameFilter()
    {
        return $this->usernameFilter;
    }
}
