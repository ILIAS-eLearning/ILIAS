<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderAccountMigrationInterface.php';

/**
 * Apache auth provider
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderApache extends ilAuthProvider implements ilAuthProviderInterface, ilAuthProviderAccountMigrationInterface
{
    const APACHE_AUTH_TYPE_DIRECT_MAPPING = 1;
    const APACHE_AUTH_TYPE_EXTENDED_MAPPING = 2;
    const APACHE_AUTH_TYPE_BY_FUNCTION = 3;

    private $settings = null;

    private $migration_account = '';
    private $force_new_account = false;
    

    /**
     * Constructor
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(\ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
        
        include_once './Services/Administration/classes/class.ilSetting.php';
        $this->settings = new ilSetting('apache_auth');
    }

    /**
     * Get setings
     * @return \ilSetting
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param \ilAuthStatus $status
     * @return bool
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        if (!$this->getSettings()->get('apache_enable_auth')) {
            $this->getLogger()->info('Apache auth disabled.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_disabled');
            return false;
        }

        if (
            !$this->getSettings()->get('apache_auth_indicator_name') ||
            !$this->getSettings()->get('apache_auth_indicator_value')
            ) {
            $this->getLogger()->warning('Apache auth indicator match failure.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_indicator_match_failure');
            return false;
        }

        if (
            !in_array(
                $_SERVER[$this->getSettings()->get('apache_auth_indicator_name')],
                array_filter(array_map('trim', str_getcsv($this->getSettings()->get('apache_auth_indicator_value'))))
            )
        ) {
            $this->getLogger()->warning('Apache authentication failed (indicator name <-> value');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        include_once './Services/Utilities/classes/class.ilUtil.php';
        if (!ilUtil::isLogin($this->getCredentials()->getUsername())) {
            $this->getLogger()->warning('Invalid login name given: ' . $this->getCredentials()->getUsername());
            $this->handleAuthenticationFail($status, 'apache_auth_err_invalid_login');
            return false;
        }

        if (!strlen($this->getCredentials()->getUsername())) {
            $this->getLogger()->info('No username given');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }
        
        // Apache with ldap as data source
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }
        
        $login  = ilObjUser::_checkExternalAuthAccount('apache', $this->getCredentials()->getUsername());
        $usr_id = ilObjUser::_lookupId($login);
        if (!$usr_id) {
            $this->getLogger()->info('Cannot find user id for external account: ' . $this->getCredentials()->getUsername());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId($usr_id);
        return true;
    }

    /**
     * Migrate existing account
     * Maybe ldap sync has to be performed here
     * @param ilAuthStatus $status
     * @param int $a_usr_id
     */
    public function migrateAccount(\ilAuthStatus $status)
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }
    }

    /**
     * Create new account for account migration
     * @param \ilAuthStatus $status
     */
    public function createNewAccount(\ilAuthStatus $status)
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }
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
     * Get auth mode of current authentication type
     */
    public function getTriggerAuthMode()
    {
        return AUTH_APACHE;
    }

    /**
     * Get user auth mode name
     */
    public function getUserAuthModeName()
    {
        if ($this->getSettings()->get('apache_ldap_sid')) {
            return 'ldap_' . (string) $this->getSettings()->get('apache_ldap_sid');
        }
        return 'apache';
    }
    
    /**
     * Handle ldap as data source
     * @param Auth $auth
     * @param string $ext_account
     */
    protected function handleLDAPDataSource(ilAuthStatus $status)
    {
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        $server = ilLDAPServer::getInstanceByServerId(
            $this->getSettings()->get('apache_ldap_sid')
        );
        
        $this->getLogger()->debug('Using ldap data source with server configuration: ' . $server->getName());

        include_once './Services/LDAP/classes/class.ilLDAPUserSynchronisation.php';
        $sync = new ilLDAPUserSynchronisation('ldap_' . $server->getServerId(), $server->getServerId());
        $sync->setExternalAccount($this->getCredentials()->getUsername());
        $sync->setUserData(array());
        $sync->forceCreation($this->force_new_account);
        $sync->forceReadLdapData(true);
        
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
}
