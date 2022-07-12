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
 * Synchronization of user accounts used in auth container ldap, cas,...
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLDAPUserSynchronisation
{
    private string $authmode;
    private ilLDAPServer $server;
    private string $extaccount = '';
    private string $intaccount = '';

    private array $user_data = array();
    private bool $force_creation = false;
    private bool $force_read_ldap_data = false;
    private ilLogger $logger;

    public function __construct(string $a_authmode, int $a_server_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->auth();
        $this->initServer($a_authmode, $a_server_id);
    }

    /**
     * Get current ldap server
     * @return ilLDAPServer $server
     */
    public function getServer() : ilLDAPServer
    {
        return $this->server;
    }

    /**
     * Get Auth Mode
     */
    public function getAuthMode() : string
    {
        return $this->authmode;
    }

    /**
     * Set external account (unique for each auth mode)
     */
    public function setExternalAccount(string $a_ext) : void
    {
        $this->extaccount = $a_ext;
    }

    /**
     * Get external accocunt
     */
    public function getExternalAccount() : string
    {
        return $this->extaccount;
    }

    /**
     * Get ILIAS unique internal account name
     * @return string internal account
     */
    public function getInternalAccount() : ?string
    {
        return $this->intaccount;
    }
    
    /**
     * Force cration of user accounts (Account migration enabled)
     */
    public function forceCreation(bool $a_force) : void
    {
        $this->force_creation = $a_force;
    }
    
    public function forceReadLdapData(bool $a_status) : void
    {
        $this->force_read_ldap_data = $a_status;
    }

    /**
     * Get user data
     * @return array $user_data
     */
    public function getUserData() : array
    {
        return $this->user_data;
    }

    /**
     * Set user data
     */
    public function setUserData(array $a_data) : void
    {
        $this->user_data = $a_data;
    }

    /**
     * Synchronize user account
     * @todo Redirects to account migration if required
     * @throws UnexpectedValueException missing or wrong external account given
     * @throws ilLDAPSynchronisationForbiddenException if user synchronisation is disabled
     * @throws ilLDAPSynchronisationFailedException bind failure
     */
    public function sync() : string
    {
        $this->readInternalAccount();
        
        if (!$this->getInternalAccount()) {
            ilLoggerFactory::getLogger('auth')->debug('Creating new account');
            $this->handleCreation();
        }

        // Nothing to do if sync on login is disabled
        if (!$this->getServer()->enabledSyncOnLogin()) {
            return $this->getInternalAccount();
        }

        // For performance reasons, check if (an update is required)
        if ($this->isUpdateRequired()) {
            ilLoggerFactory::getLogger('auth')->debug('Perform update of user data');
            $this->readUserData();
            $this->performUpdate();
        }
        return $this->getInternalAccount();
    }

    /**
     * Handle creation of user accounts
     * @throws ilLDAPSynchronisationForbiddenException
     * @throws ilLDAPAccountMigrationRequiredException
     */
    protected function handleCreation() : void
    {
        // Disabled sync on login
        if (!$this->getServer()->enabledSyncOnLogin()) {
            throw new ilLDAPSynchronisationForbiddenException('User synchronisation forbidden.');
        }
        // Account migration
        if (!$this->force_creation && $this->getServer()->isAccountMigrationEnabled()) {
            $this->readUserData();
            throw new ilLDAPAccountMigrationRequiredException('Account migration check required.');
        }
    }

    /**
     * Update user account and role assignments
     */
    protected function performUpdate() : bool
    {
        ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

        $update = new ilLDAPAttributeToUser($this->getServer());
        if ($this->force_creation) {
            $update->addMode(ilLDAPAttributeToUser::MODE_INITIALIZE_ROLES);
        }
        $update->setNewUserAuthMode($this->getAuthMode());
        $update->setUserData(
            array(
                $this->getExternalAccount() => $this->getUserData()
            )
        );

        $update->refresh();

        // User has been created, now read internal account again
        $this->readInternalAccount();
        return true;
    }

    /**
     * Read user data.
     * In case of auth mode != 'ldap' start a query with external account name against ldap server
     * @throws ilLDAPSynchronisationFailedException
     */
    protected function readUserData() : bool
    {
        // Add internal account to user data
        $this->user_data['ilInternalAccount'] = $this->getInternalAccount();
        if (!$this->force_read_ldap_data && strpos($this->getAuthMode(), 'ldap') === 0) {
            return true;
        }

        try {
            $query = new ilLDAPQuery($this->getServer());
            $query->bind(ilLDAPQuery::LDAP_BIND_DEFAULT);
            $user = $query->fetchUser($this->getExternalAccount());
            $this->logger->dump($user, ilLogLevel::DEBUG);
            $this->user_data = (array) $user[$this->getExternalAccount()];
        } catch (ilLDAPQueryException $e) {
            $this->logger->error('LDAP bind failed with message: ' . $e->getMessage());
            throw new ilLDAPSynchronisationFailedException($e->getMessage());
        }

        return true;
    }


    /**
     * Read internal account of user
     * @throws UnexpectedValueException
     */
    protected function readInternalAccount() : void
    {
        if (!$this->getExternalAccount()) {
            throw new UnexpectedValueException('No external account given.');
        }
        $this->intaccount = ilObjUser::_checkExternalAuthAccount(
            $this->getAuthMode(),
            $this->getExternalAccount()
        );
    }

    /**
     * Check if an update is required
     */
    protected function isUpdateRequired() : bool
    {
        if ($this->force_creation) {
            return true;
        }
        if (!$this->getInternalAccount()) {
            return true;
        }

        // Check attribute mapping on login
        if (ilLDAPAttributeMapping::hasRulesForUpdate($this->getServer()->getServerId())) {
            return true;
        }

        // Check if there is any change in role assignments
        if (ilLDAPRoleAssignmentRule::hasRulesForUpdate()) {
            return true;
        }
        return false;
    }


    /**
     * Init LDAP server
     */
    protected function initServer(string $a_auth_mode, int $a_server_id) : void
    {
        $this->authmode = $a_auth_mode;
        $this->server = ilLDAPServer::getInstanceByServerId($a_server_id);
    }
}
