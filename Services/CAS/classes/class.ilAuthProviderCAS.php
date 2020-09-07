<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * CAS authentication provider
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilAuthProviderCAS extends ilAuthProvider implements ilAuthProviderInterface
{
    /**
     * @var ilCASSettings
     */
    private $settings = null;

    /**
     * ilAuthProviderCAS constructor.
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;

        parent::__construct($credentials);
        include_once './Services/CAS/classes/class.ilCASSettings.php';
        $this->settings = ilCASSettings::getInstance();
    }

    /**
     * @return \ilCASSettings
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @inheritdoc
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        include_once './Services/CAS/lib/CAS.php';
        global $phpCAS;

        $this->getLogger()->debug('Starting cas authentication attempt... ');

        try {
            phpCAS::setDebug(false);
            phpCAS::setVerbose(true);
            phpCAS::client(
                CAS_VERSION_2_0,
                $this->getSettings()->getServer(),
                (int) $this->getSettings()->getPort(),
                $this->getSettings()->getUri()
            );

            phpCAS::setNoCasServerValidation();
            phpCAS::forceAuthentication();
        } catch (Exception $e) {
            $this->getLogger()->error('Cas authentication failed with message: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        if (!strlen(phpCAS::getUser())) {
            return $this->handleAuthenticationFail($status, 'err_wrong_login');
        }
        $this->getCredentials()->setUsername(phpCAS::getUser());

        // check and handle ldap data sources
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        if (ilLDAPServer::isDataSourceActive(AUTH_CAS)) {
            return $this->handleLDAPDataSource($status);
        }

        // Check account available
        $local_user = ilObjUser::_checkExternalAuthAccount("cas", $this->getCredentials()->getUsername());
        if (strlen($local_user)) {
            $this->getLogger()->debug('CAS authentication successful.');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId(ilObjUser::_lookupId($local_user));
            return true;
        }

        if (!$this->getSettings()->isUserCreationEnabled()) {
            $this->getLogger()->debug('User creation disabled. No valid local account found');
            $this->handleAuthenticationFail($status, 'err_auth_cas_no_ilias_user');
            return false;
        }


        include_once './Services/CAS/classes/class.ilCASAttributeToUser.php';
        $importer = new ilCASAttributeToUser($this->getSettings());
        $new_name = $importer->create($this->getCredentials()->getUsername());

        if (!strlen($new_name)) {
            $this->getLogger()->debug('User creation failed.');
            $this->handleAuthenticationFail($status, 'err_auth_cas_no_ilias_user');
            return false;
        }

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId(ilObjUser::_lookupId($new_name));
        return true;
    }

    /**
     * Handle user data synchonization by ldap data source.
     * @param \ilAuthStatus $status
     */
    protected function handleLDAPDataSource(\ilAuthStatus $status)
    {
        include_once './Services/LDAP/classes/class.ilLDAPServer.php';
        $server = ilLDAPServer::getInstanceByServerId(
            ilLDAPServer::getDataSource(AUTH_CAS)
        );

        $this->getLogger()->debug('Using ldap data source for user: ' . $this->getCredentials()->getUsername());

        include_once './Services/LDAP/classes/class.ilLDAPUserSynchronisation.php';
        $sync = new ilLDAPUserSynchronisation('cas', $server->getServerId());
        $sync->setExternalAccount($this->getCredentials()->getUsername());
        $sync->setUserData(array());
        $sync->forceCreation(true);

        try {
            $internal_account = $sync->sync();
        } catch (UnexpectedValueException $e) {
            $this->getLogger()->warning('Authentication failed with mesage: ' . $e->getMessage());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        } catch (ilLDAPSynchronisationForbiddenException $e) {

            // No syncronisation allowed => create Error
            $this->getLogger()->warning('User creation disabled. No valid local account found');
            $this->handleAuthenticationFail($status, 'err_auth_cas_no_ilias_user');
            return false;
        } catch (ilLDAPAccountMigrationRequiredException $e) {

            // No syncronisation allowed => create Error
            $this->getLogger()->warning('User creation disabled. No valid local account found');
            $this->handleAuthenticationFail($status, 'err_auth_cas_no_ilias_user');
            return false;
        }
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId(ilObjUser::_lookupId($internal_account));
        return true;
    }
}
