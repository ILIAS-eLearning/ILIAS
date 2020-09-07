<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderAccountMigrationInterface.php';

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderLDAP extends ilAuthProvider implements ilAuthProviderInterface, ilAuthProviderAccountMigrationInterface
{
    private $server = null;
    private $migration_account = '';
    private $force_new_account = false;
    
    /**
     * Constructor
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(\ilAuthCredentials $credentials, $a_server_id = 0)
    {
        parent::__construct($credentials);
        $this->initServer($a_server_id);
    }
    
    /**
     * Get server
     * @return \ilLDAPServer
     */
    public function getServer()
    {
        return $this->server;
    }
    
    
    /**
     * Do authentication
     * @param \ilAuthStatus $status
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        try {
            // bind
            include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
            $query = new ilLDAPQuery($this->getServer());
            $query->bind(IL_LDAP_BIND_DEFAULT);
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot bind to LDAP server... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return false;
        }
        try {
            // Read user data, which does ensure a sucessful authentication.
            $users = $query->fetchUser(
                $this->getCredentials()->getUsername()
            );
            
            if (!$users) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return false;
            }
            if (!trim($this->getCredentials()->getPassword())) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return false;
            }
            if (!array_key_exists($this->changeKeyCase($this->getCredentials()->getUsername()), $users)) {
                $this->getLogger()->warning('Cannot find user: ' . $this->changeKeyCase($this->getCredentials()->getUsername()));
                $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
                return false;
            }
            
            // check group membership
            if (!$query->checkGroupMembership(
                $this->getCredentials()->getUsername(),
                $users[$this->changeKeyCase($this->getCredentials()->getUsername())]
            )) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return false;
            }
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot fetch LDAP user data... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return false;
        }
        try {
            // now bind with login credentials
            $query->bind(IL_LDAP_BIND_AUTH, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]['dn'], $this->getCredentials()->getPassword());
        } catch (ilLDAPQueryException $e) {
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }
        
        // authentication success update profile
        return $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
    }
    
    /**
     * Update Account
     * @param array $user
     * @return bool
     */
    protected function updateAccount(ilAuthStatus $status, array $user)
    {
        $user = array_change_key_case($user, CASE_LOWER);
        $this->getLogger()->dump($user, ilLogLevel::DEBUG);
        
        include_once './Services/LDAP/classes/class.ilLDAPUserSynchronisation.php';
        $sync = new ilLDAPUserSynchronisation('ldap_' . $this->getServer()->getServerId(), $this->getServer()->getServerId());
        $sync->setExternalAccount($this->getCredentials()->getUsername());
        $sync->setUserData($user);
        $sync->forceCreation($this->force_new_account);

        try {
            $internal_account = $sync->sync();
            $this->getLogger()->debug('Internal account: ' . $internal_account);
        } catch (UnexpectedValueException $e) {
            $this->getLogger()->info('Login failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        } catch (ilLDAPSynchronisationForbiddenException $e) {
            // No syncronisation allowed => create Error
            $this->getLogger()->info('Login failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_auth_ldap_no_ilias_user');
            return false;
        } catch (ilLDAPAccountMigrationRequiredException $e) {
            // Account migration required
            $this->setExternalAccountName($this->getCredentials()->getUsername());
            $this->getLogger()->info('Authentication failed: account migration required for external account: ' . $this->getCredentials()->getUsername());
            $status->setStatus(ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED);
            return false;
        }
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId(ilObjUser::_lookupId($internal_account));
        return true;
    }
    

    
    /**
     * Init Server
     */
    protected function initServer($a_server_id)
    {
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        $this->server = new ilLDAPServer($a_server_id);
    }

    // Account migration
    
    /**
     * @inheritdoc
     */
    public function createNewAccount(ilAuthStatus $status)
    {
        $this->force_new_account = true;
        
        try {
            include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
            $query = new ilLDAPQuery($this->getServer());
            $query->bind(IL_LDAP_BIND_DEFAULT);
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot bind to LDAP server... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return false;
        }
        try {
            // fetch user
            $users = $query->fetchUser(
                $this->getCredentials()->getUsername()
            );
            if (!$users) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return false;
            }
            if (!array_key_exists($this->changeKeyCase($this->getCredentials()->getUsername()), $users)) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return false;
            }
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot fetch LDAP user data... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return false;
        }

        // authentication success update profile
        $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
    }
    
    

    /**
     * @inheritdoc
     */
    public function migrateAccount(ilAuthStatus $status)
    {
        $this->force_new_account = true;
        
        try {
            include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
            $query = new ilLDAPQuery($this->getServer());
            $query->bind(IL_LDAP_BIND_DEFAULT);
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot bind to LDAP server... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return false;
        }
        
        $users = $query->fetchUser($this->getCredentials()->getUsername());
        $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
        return true;
    }

    /**
     * Get trigger auth mode
     */
    public function getTriggerAuthMode()
    {
        return AUTH_LDAP . '_' . $this->getServer()->getServerId();
    }

    /**
     * Get user auth mode name
     */
    public function getUserAuthModeName()
    {
        return 'ldap_' . $this->getServer()->getServerId();
    }

    /**
     * Get external account name
     * @return string
     */
    public function getExternalAccountName()
    {
        return $this->migration_account;
    }
    
    /**
     * Set external account name
     * @param string $a_name
     */
    public function setExternalAccountName($a_name)
    {
        $this->migration_account = $a_name;
    }
    
    /**
     * Change case similar to array_change_key_case, to avoid further encoding problems.
     * @param string $a_string
     * @return string
     */
    protected function changeKeyCase($a_string)
    {
        $as_array = array_change_key_case(array($a_string => $a_string));
        foreach ($as_array as $key => $string) {
            return $key;
        }
    }
}
