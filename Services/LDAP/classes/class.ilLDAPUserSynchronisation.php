<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/LDAP/classes/class.ilLDAPServer.php';
include_once './Services/LDAP/exceptions/class.ilLDAPSynchronisationForbiddenException.php';
include_once './Services/LDAP/exceptions/class.ilLDAPAccountMigrationRequiredException.php';

/**
 * Synchronization of user accounts used in auth container ldap, radius , cas,...
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesLDAP
 */
class ilLDAPUserSynchronisation
{
    private $authmode = 0;

    private $server = null;

    private $extaccount = '';
    private $intaccount = '';

    private $user_data = array();
    
    private $force_creation = false;
    private $force_read_ldap_data = false;


    /**
     * Constructor
     *
     * @param string $a_auth_mode
     */
    public function __construct($a_authmode, $a_server_id)
    {
        $this->initServer($a_authmode, $a_server_id);
    }

    /**
     * Get current ldap server
     * @return ilLDAPServer $server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get Auth Mode
     * @return int authmode
     */
    public function getAuthMode()
    {
        return $this->authmode;
    }

    /**
     * Set external account (unique for each auth mode)
     * @param string $a_ext
     */
    public function setExternalAccount($a_ext)
    {
        $this->extaccount = $a_ext;
    }

    /**
     * Get external accocunt
     * @return <type>
     */
    public function getExternalAccount()
    {
        return $this->extaccount;
    }

    /**
     * Get ILIAS unique internal account name
     * @return string internal account
     */
    public function getInternalAccount()
    {
        return $this->intaccount;
    }
    
    /**
     * Force cration of user accounts (Account migration enabled)
     * @param bool $a_force
     */
    public function forceCreation($a_force)
    {
        $this->force_creation = $a_force;
    }
    
    public function forceReadLdapData($a_status)
    {
        $this->force_read_ldap_data = $a_status;
    }

    /**
     * Check if creation of user account is forced (account migration)
     * @return bool
     */
    public function isCreationForced()
    {
        return (bool) $this->force_creation;
    }

    /**
     * Get user data
     * @return array $user_data
     */
    public function getUserData()
    {
        return (array) $this->user_data;
    }

    /**
     * Set user data
     * @param array $a_data
     */
    public function setUserData($a_data)
    {
        $this->user_data = (array) $a_data;
    }

    /**
     * Synchronize user account
     * @todo Redirects to account migration if required
     * @throws UnexpectedValueException missing or wrong external account given
     * @throws ilLDAPSynchronisationForbiddenException if user synchronisation is disabled
     */
    public function sync()
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
    protected function handleCreation()
    {
        // Disabled sync on login
        if (!$this->getServer()->enabledSyncOnLogin()) {
            throw new ilLDAPSynchronisationForbiddenException('User synchronisation forbidden.');
        }
        // Account migration
        if ($this->getServer()->isAccountMigrationEnabled() and !$this->isCreationForced()) {
            $this->readUserData();
            throw new ilLDAPAccountMigrationRequiredException('Account migration check required.');
        }
    }

    /**
     * Update user account and role assignments
     * @return bool
     */
    protected function performUpdate()
    {
        include_once './Services/User/classes/class.ilUserCreationContext.php';
        ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

        include_once 'Services/LDAP/classes/class.ilLDAPAttributeToUser.php';
        $update = new ilLDAPAttributeToUser($this->getServer());
        if ($this->isCreationForced()) {
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
     */
    protected function readUserData()
    {
        // Add internal account to user data
        $this->user_data['ilInternalAccount'] = $this->getInternalAccount();

        if (!$this->force_read_ldap_data) {
            if (substr($this->getAuthMode(), 0, 4) == 'ldap') {
                return true;
            }
        }
        
        include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
        $query = new ilLDAPQuery($this->getServer());
        $user = $query->fetchUser($this->getExternalAccount());
        
        ilLoggerFactory::getLogger('auth')->dump($user, ilLogLevel::DEBUG);

        $this->user_data = (array) $user[$this->getExternalAccount()];
    }


    /**
     * Read internal account of user
     * @throws UnexpectedValueException
     */
    protected function readInternalAccount()
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
     * @return bool
     */
    protected function isUpdateRequired()
    {
        if ($this->isCreationForced()) {
            return true;
        }
        if (!$this->getInternalAccount()) {
            return true;
        }

        // Check attribute mapping on login
        include_once './Services/LDAP/classes/class.ilLDAPAttributeMapping.php';
        if (ilLDAPAttributeMapping::hasRulesForUpdate($this->getServer()->getServerId())) {
            return true;
        }

        // Check if there is any change in role assignments
        include_once './Services/LDAP/classes/class.ilLDAPRoleAssignmentRule.php';
        if (ilLDAPRoleAssignmentRule::hasRulesForUpdate()) {
            return true;
        }
        return false;
    }


    /**
     * Init LDAP server
     * @param int $a_server_id
     */
    protected function initServer($a_auth_mode, $a_server_id)
    {
        $this->authmode = $a_auth_mode;
        $this->server = ilLDAPServer::getInstanceByServerId($a_server_id);
    }
}
