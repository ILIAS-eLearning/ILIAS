<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Apache auth provider
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
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
     * @param ilAuthCredentials $credentials
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
        $this->settings = new ilSetting('apache_auth');
    }

    /**
     * Get setings
     * @return ilSetting
     */
    protected function getSettings() : ilSetting
    {
        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function doAuthentication(ilAuthStatus $status)
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

        $validIndicatorValues = array_filter(array_map(
            'trim',
            str_getcsv($this->getSettings()->get('apache_auth_indicator_value'))
        ));
        if (!in_array($_SERVER[$this->getSettings()->get('apache_auth_indicator_name')], $validIndicatorValues)) {
            $this->getLogger()->warning('Apache authentication failed (indicator name <-> value');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

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
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }

        $login = ilObjUser::_checkExternalAuthAccount('apache', $this->getCredentials()->getUsername());
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
     * @inheritDoc
     */
    public function migrateAccount(ilAuthStatus $status)
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }
    }

    /**
     * @inheritDoc
     */
    public function createNewAccount(ilAuthStatus $status)
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap')) {
            return $this->handleLDAPDataSource($status);
        }
    }

    /**
     * @inheritDoc
     */
    public function getExternalAccountName()
    {
        return $this->migration_account;
    }

    /**
     * @param string $name
     */
    public function setExternalAccountName(string $name) : void
    {
        $this->migration_account = $name;
    }

    /**
     * @inheritDoc
     */
    public function getTriggerAuthMode()
    {
        return AUTH_APACHE;
    }

    /**
     * @inheritDoc
     */
    public function getUserAuthModeName()
    {
        if ($this->getSettings()->get('apache_ldap_sid')) {
            return 'ldap_' . (string) $this->getSettings()->get('apache_ldap_sid');
        }

        return 'apache';
    }

    /**
     * @inheritDoc
     */
    protected function handleLDAPDataSource(ilAuthStatus $status) : bool
    {
        $server = ilLDAPServer::getInstanceByServerId(
            $this->getSettings()->get('apache_ldap_sid')
        );

        $this->getLogger()->debug('Using ldap data source with server configuration: ' . $server->getName());

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