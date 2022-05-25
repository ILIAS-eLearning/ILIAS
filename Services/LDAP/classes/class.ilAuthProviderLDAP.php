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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderLDAP extends ilAuthProvider implements ilAuthProviderAccountMigrationInterface
{
    private ilLDAPServer $server;
    private string $migration_account = '';
    private bool $force_new_account = false;

    public function __construct(ilAuthCredentials $credentials, int $a_server_id = 0)
    {
        parent::__construct($credentials);
        $this->initServer($a_server_id);
    }

    public function getServer() : ilLDAPServer
    {
        return $this->server;
    }

    /**
     * @inheritDoc
     */
    public function doAuthentication(ilAuthStatus $status) : bool
    {
        try {
            // bind
            $query = new ilLDAPQuery($this->getServer());
            $query->bind();
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
            $query->bind(
                ilLDAPQuery::LDAP_BIND_AUTH,
                $users[$this->changeKeyCase($this->getCredentials()->getUsername())]['dn'],
                $this->getCredentials()->getPassword()
            );
        } catch (ilLDAPQueryException $e) {
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        // authentication success update profile
        return $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
    }

    /**
     * Update Account
     */
    protected function updateAccount(ilAuthStatus $status, array $user) : bool
    {
        $user = array_change_key_case($user, CASE_LOWER);
        $this->getLogger()->dump($user, ilLogLevel::DEBUG);

        $sync = new ilLDAPUserSynchronisation(
            'ldap_' . $this->getServer()->getServerId(),
            $this->getServer()->getServerId()
        );
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
        } catch (ilLDAPSynchronisationFailedException $e) {
            $this->handleAuthenticationFail($status, 'err_auth_ldap_failed');
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

    protected function initServer(int $a_server_id) : void
    {
        $this->server = new ilLDAPServer($a_server_id);
    }

    /**
     * @inheritDoc
     */
    public function createNewAccount(ilAuthStatus $status) : void
    {
        $this->force_new_account = true;

        try {
            $query = new ilLDAPQuery($this->getServer());
            $query->bind();
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot bind to LDAP server... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return;
        }
        try {
            // fetch user
            $users = $query->fetchUser(
                $this->getCredentials()->getUsername()
            );
            if (!$users) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return;
            }
            if (!array_key_exists($this->changeKeyCase($this->getCredentials()->getUsername()), $users)) {
                $this->handleAuthenticationFail($status, 'err_wrong_login');
                return;
            }
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot fetch LDAP user data... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return;
        }

        // authentication success update profile
        $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
    }

    /**
     * @inheritDoc
     */
    public function migrateAccount(ilAuthStatus $status) : void
    {
        $this->force_new_account = true;

        try {
            $query = new ilLDAPQuery($this->getServer());
            $query->bind();
        } catch (ilLDAPQueryException $e) {
            $this->getLogger()->error('Cannot bind to LDAP server... ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'auth_err_ldap_exception');
            return;
        }

        $users = $query->fetchUser($this->getCredentials()->getUsername());
        $this->updateAccount($status, $users[$this->changeKeyCase($this->getCredentials()->getUsername())]);
    }

    /**
     * @inheritDoc
     */
    public function getTriggerAuthMode() : string
    {
        return ilAuthUtils::AUTH_LDAP . '_' . $this->getServer()->getServerId();
    }

    /**
     * @inheritDoc
     */
    public function getUserAuthModeName() : string
    {
        return 'ldap_' . $this->getServer()->getServerId();
    }

    /**
     * @inheritDoc
     */
    public function getExternalAccountName() : string
    {
        return $this->migration_account;
    }

    public function setExternalAccountName(string $a_name) : void
    {
        $this->migration_account = $a_name;
    }

    /**
     * Change case similar to array_change_key_case, to avoid further encoding problems.
     * @param string $a_string
     * @return string
     */
    protected function changeKeyCase(string $a_string) : string
    {
        return array_key_first(array_change_key_case(array($a_string => $a_string)));
    }
}
