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
 * Apache auth provider
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthProviderApache extends ilAuthProvider implements ilAuthProviderAccountMigrationInterface
{
    public const APACHE_AUTH_TYPE_DIRECT_MAPPING = 1;
    public const APACHE_AUTH_TYPE_EXTENDED_MAPPING = 2;
    public const APACHE_AUTH_TYPE_BY_FUNCTION = 3;

    private ilSetting $settings;
    private string $migration_account = '';
    private bool $force_new_account = false;

    public function __construct(ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
        $this->settings = new ilSetting('apache_auth');
    }

    protected function getSettings() : ilSetting
    {
        return $this->settings;
    }

    public function doAuthentication(ilAuthStatus $status) : bool
    {
        if (!$this->getSettings()->get('apache_enable_auth', '0')) {
            $this->getLogger()->info('Apache auth disabled.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_disabled');
            return false;
        }

        if (
            !$this->getSettings()->get('apache_auth_indicator_name', '') ||
            !$this->getSettings()->get('apache_auth_indicator_value', '')
        ) {
            $this->getLogger()->warning('Apache auth indicator match failure.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_indicator_match_failure');
            return false;
        }

        $validIndicatorValues = array_filter(array_map(
            'trim',
            str_getcsv($this->getSettings()->get('apache_auth_indicator_value', ''))
        ));
        //TODO PHP8-REVIEW: $DIC->http()->request()->getServerParams()['apache_auth_indicator_name']
        if (
            !isset($_SERVER[$this->getSettings()->get('apache_auth_indicator_name', '')]) ||
            !in_array($_SERVER[$this->getSettings()->get('apache_auth_indicator_name', '')], $validIndicatorValues, true)
        ) {
            $this->getLogger()->warning('Apache authentication failed (indicator name <-> value');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        if (!ilUtil::isLogin($this->getCredentials()->getUsername())) {
            $this->getLogger()->warning('Invalid login name given: ' . $this->getCredentials()->getUsername());
            $this->handleAuthenticationFail($status, 'apache_auth_err_invalid_login');
            return false;
        }

        if ($this->getCredentials()->getUsername() === '') {
            $this->getLogger()->info('No username given');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        // Apache with ldap as data source
        if ($this->getSettings()->get('apache_enable_ldap', '0')) {
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

    public function migrateAccount(ilAuthStatus $status) : void
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap', '0')) {
            $this->handleLDAPDataSource($status);
        }
    }

    public function createNewAccount(ilAuthStatus $status) : void
    {
        $this->force_new_account = true;
        if ($this->getSettings()->get('apache_enable_ldap', '0')) {
            $this->handleLDAPDataSource($status);
        }
    }

    public function getExternalAccountName() : string
    {
        return $this->migration_account;
    }

    public function setExternalAccountName(string $name) : void
    {
        $this->migration_account = $name;
    }

    public function getTriggerAuthMode() : string
    {
        return (string) ilAuthUtils::AUTH_APACHE;
    }

    public function getUserAuthModeName() : string
    {
        if ($this->getSettings()->get('apache_ldap_sid', '0')) {
            return 'ldap_' . $this->getSettings()->get('apache_ldap_sid', '');
        }

        return 'apache';
    }

    protected function handleLDAPDataSource(ilAuthStatus $status) : bool
    {
        $server = ilLDAPServer::getInstanceByServerId(
            (int) $this->getSettings()->get('apache_ldap_sid', '0')
        );

        $this->getLogger()->debug('Using ldap data source with server configuration: ' . $server->getName());

        $sync = new ilLDAPUserSynchronisation('ldap_' . $server->getServerId(), $server->getServerId());
        $sync->setExternalAccount($this->getCredentials()->getUsername());
        $sync->setUserData([]);
        $sync->forceCreation($this->force_new_account);
        $sync->forceReadLdapData(true);

        try {
            $internal_account = $sync->sync();
            $this->getLogger()->debug('Internal account: ' . $internal_account);
        } catch (UnexpectedValueException $e) {
            $this->getLogger()->info('Login failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        } catch (ilLDAPSynchronisationFailedException $e) {
            $this->handleAuthenticationFail($status, 'err_auth_ldap_failed');
            return false;
        } catch (ilLDAPSynchronisationForbiddenException $e) {
            // No syncronisation allowed => create Error
            $this->getLogger()->info('Login failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_auth_ldap_no_ilias_user');
            return false;
        } catch (ilLDAPAccountMigrationRequiredException $e) {
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
