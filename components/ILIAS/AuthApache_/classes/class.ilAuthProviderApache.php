<?php

declare(strict_types=1);

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
final class ilAuthProviderApache extends ilAuthProvider implements ilAuthProviderAccountMigrationInterface
{
    public const APACHE_AUTH_TYPE_DIRECT_MAPPING = 1;
    public const APACHE_AUTH_TYPE_EXTENDED_MAPPING = 2;
    public const APACHE_AUTH_TYPE_BY_FUNCTION = 3;

    private const ENV_APACHE_AUTH_INDICATOR_NAME = 'apache_auth_indicator_name';

    private const ERR_WRONG_LOGIN = 'err_wrong_login';

    private const APACHE_ENABLE_LDAP = 'apache_enable_ldap';
    private const APACHE_LDAP_SID = 'apache_ldap_sid';

    private ilSetting $settings;
    private string $migration_account = '';
    private bool $force_new_account = false;

    public function __construct(ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
        $this->settings = new ilSetting('apache_auth');
    }

    public function doAuthentication(ilAuthStatus $status): bool
    {
        if (!$this->settings->get('apache_enable_auth', '0')) {
            $this->getLogger()->info('Apache auth disabled.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_disabled');
            return false;
        }

        if (
            !$this->settings->get(self::ENV_APACHE_AUTH_INDICATOR_NAME, '') ||
            !$this->settings->get('apache_auth_indicator_value', '')
        ) {
            $this->getLogger()->warning('Apache auth indicator match failure.');
            $this->handleAuthenticationFail($status, 'apache_auth_err_indicator_match_failure');
            return false;
        }

        $validIndicatorValues = array_filter(array_map(
            'trim',
            str_getcsv($this->settings->get('apache_auth_indicator_value', ''))
        ));
        //TODO PHP8-REVIEW: $DIC->http()->request()->getServerParams()['apache_auth_indicator_name']
        if (
            !isset($_SERVER[$this->settings->get(self::ENV_APACHE_AUTH_INDICATOR_NAME, '')]) ||
            !in_array($_SERVER[$this->settings->get(self::ENV_APACHE_AUTH_INDICATOR_NAME, '')], $validIndicatorValues, true)
        ) {
            $this->getLogger()->warning('Apache authentication failed (indicator name <-> value');
            $this->handleAuthenticationFail($status, self::ERR_WRONG_LOGIN);
            return false;
        }

        if (!ilUtil::isLogin($this->getCredentials()->getUsername())) {
            $this->getLogger()->warning('Invalid login name given: ' . $this->getCredentials()->getUsername());
            $this->handleAuthenticationFail($status, 'apache_auth_err_invalid_login');
            return false;
        }

        if ($this->getCredentials()->getUsername() === '') {
            $this->getLogger()->info('No username given');
            $this->handleAuthenticationFail($status, self::ERR_WRONG_LOGIN);
            return false;
        }

        // Apache with ldap as data source
        if ($this->settings->get(self::APACHE_ENABLE_LDAP, '0')) {
            return $this->handleLDAPDataSource($status);
        }

        $login = ilObjUser::_checkExternalAuthAccount('apache', $this->getCredentials()->getUsername());
        $usr_id = ilObjUser::_lookupId($login);
        if (!$usr_id) {
            $this->getLogger()->info('Cannot find user id for external account: ' . $this->getCredentials()->getUsername());
            $this->handleAuthenticationFail($status, self::ERR_WRONG_LOGIN);
            return false;
        }

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId($usr_id);
        return true;
    }

    public function migrateAccount(ilAuthStatus $status): void
    {
        $this->force_new_account = true;
        if ($this->settings->get(self::APACHE_ENABLE_LDAP, '0')) {
            $this->handleLDAPDataSource($status);
        }
    }

    public function createNewAccount(ilAuthStatus $status): void
    {
        $this->force_new_account = true;
        if ($this->settings->get(self::APACHE_ENABLE_LDAP, '0')) {
            $this->handleLDAPDataSource($status);
        }
    }

    public function getExternalAccountName(): string
    {
        return $this->migration_account;
    }

    public function setExternalAccountName(string $name): void
    {
        $this->migration_account = $name;
    }

    public function getTriggerAuthMode(): string
    {
        return (string) ilAuthUtils::AUTH_APACHE;
    }

    public function getUserAuthModeName(): string
    {
        if ($this->settings->get(self::APACHE_LDAP_SID, '0')) {
            return 'ldap_' . $this->settings->get(self::APACHE_LDAP_SID, '');
        }

        return 'apache';
    }

    private function handleLDAPDataSource(ilAuthStatus $status): bool
    {
        $server = ilLDAPServer::getInstanceByServerId(
            (int) $this->settings->get(self::APACHE_LDAP_SID, '0')
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
            $this->handleAuthenticationFail($status, self::ERR_WRONG_LOGIN);
            return false;
        } catch (ilLDAPSynchronisationFailedException $e) {
            $this->handleAuthenticationFail($status, 'err_auth_ldap_failed');
            return false;
        } catch (ilLDAPSynchronisationForbiddenException $e) {
            $this->getLogger()->info('Login failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_auth_ldap_no_ilias_user');
            return false;
        } catch (ilLDAPAccountMigrationRequiredException) {
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
