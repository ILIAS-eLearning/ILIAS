<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderAccountMigrationInterface.php';


require_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php');
require_once('include/Unicode/UtfNormal.php');
require_once('./Services/AuthShibboleth/classes/class.ilShibbolethPluginWrapper.php');
require_once('./Services/AuthShibboleth/classes/Config/class.shibConfig.php');
require_once('./Services/AuthShibboleth/classes/ServerData/class.shibServerData.php');
require_once('./Services/AuthShibboleth/classes/User/class.shibUser.php');

/**
 * Shibboleth authentication provider
 *
 */
class ilAuthProviderShibboleth extends ilAuthProvider implements ilAuthProviderInterface
{
    private $migration_account = '';
    
    
    /**
     * Constructor
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(\ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
    }
    
    /**
     * Do apache auth
     * @param \ilAuthStatus $status
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        global $DIC; // for backword compatibility of hook environment variables
        $ilias = $DIC['ilias'];
        $ilSetting = $DIC['ilSetting'];
        $shibServerData = shibServerData::getInstance();
        
        //$this->getLogger()->dump($shibServerData);
        
        if ($shibServerData->getLogin()) {
            $shibUser = shibUser::buildInstance($shibServerData);
            // for backword compatibility of hook environment variables
            $userObj = &$shibUser; // For shib_data_conv included Script
            $newUser = $shibUser->isNew(); // For shib_data_conv included Script
            if ($shibUser->isNew()) {
                $shibUser->createFields();
                $shibUser->setPref('hits_per_page', $ilSetting->get('hits_per_page'));

                // Modify user data before creating the user
                // Include custom code that can be used to further modify
                // certain Shibboleth user attributes
                if ($ilias->getSetting('shib_data_conv') and $ilias->getSetting('shib_data_conv') != ''
                    and is_readable($ilias->getSetting('shib_data_conv'))
                ) {
                    include($ilias->getSetting('shib_data_conv'));
                }
                $shibUser = ilShibbolethPluginWrapper::getInstance()->beforeCreateUser($shibUser);
                $shibUser->create();
                $shibUser->updateOwner();
                $shibUser->saveAsNew();
                $shibUser->writePrefs();
                $shibUser = ilShibbolethPluginWrapper::getInstance()->afterCreateUser($shibUser);
                ilShibbolethRoleAssignmentRules::doAssignments($shibUser->getId(), $_SERVER);
            } else {
                $shibUser->updateFields();
                // Include custom code that can be used to further modify
                // certain Shibboleth user attributes
                if ($ilias->getSetting('shib_data_conv') and $ilias->getSetting('shib_data_conv') != ''
                    and is_readable($ilias->getSetting('shib_data_conv'))
                ) {
                    include($ilias->getSetting('shib_data_conv'));
                }
                //				$shibUser->update();
                $shibUser = ilShibbolethPluginWrapper::getInstance()->beforeUpdateUser($shibUser);
                $shibUser->update();
                $shibUser = ilShibbolethPluginWrapper::getInstance()->afterUpdateUser($shibUser);
                ilShibbolethRoleAssignmentRules::updateAssignments($shibUser->getId(), $_SERVER);
            }

            $c = shibConfig::getInstance();
            if (($newUser && !$c->isActivateNew()) || !$newUser) {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
                $status->setAuthenticatedUserId(ilObjUser::_lookupId($shibUser->getLogin()));
            } elseif ($newUser && $c->isActivateNew()) {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
                $status->setReason('err_inactive');
            }
        } else {
            $this->getLogger()->info('Shibboleth authentication failed.');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }
    }
}
