<?php

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
 * Shibboleth authentication provider
 *
 */
class ilAuthProviderShibboleth extends ilAuthProvider
{
    private ILIAS $ilias;
    private ilSetting $settings;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        $this->ilias = $DIC['ilias'];
        $this->settings = $DIC->settings();
        parent::__construct($credentials);
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilSystemStyleException
     * @throws ilPasswordException
     * @throws ilObjectTypeMismatchException
     * @throws ilUserException
     */
    public function doAuthentication(ilAuthStatus $status): bool
    {
        $shib_server_data = shibServerData::getInstance();

        if ($shib_server_data->getLogin() !== '' && $shib_server_data->getLogin() !== '0') {
            $shib_user = shibUser::buildInstance($shib_server_data);
            // for backword compatibility of hook environment variables
            $new_user = $shib_user->isNew(); // For shib_data_conv included Script
            $settings = new ilShibbolethSettings();
            $account_creation = $settings->getAccountCreation();
            if (!$new_user) {
                $shib_user->updateFields();
                // Include custom code that can be used to further modify
                // certain Shibboleth user attributes
                if (
                    $this->ilias->getSetting('shib_data_conv') &&
                    $this->ilias->getSetting('shib_data_conv') !== '' &&
                    is_readable($this->ilias->getSetting('shib_data_conv'))
                ) {
                    /** @noRector */
                    include($this->ilias->getSetting('shib_data_conv'));
                }
                $shib_user = ilShibbolethPluginWrapper::getInstance()->beforeUpdateUser($shib_user);
                $shib_user->update();
                $shib_user = ilShibbolethPluginWrapper::getInstance()->afterUpdateUser($shib_user);
                ilShibbolethRoleAssignmentRules::updateAssignments($shib_user->getId(), $_SERVER);
            } elseif ($account_creation !== ilShibbolethSettings::ACCOUNT_CREATION_DISABLED) {
                $shib_user->createFields();
                $shib_user->setPref('hits_per_page', $this->settings->get('hits_per_page'));

                // Modify user data before creating the user
                // Include custom code that can be used to further modify
                // certain Shibboleth user attributes
                if (
                    $this->ilias->getSetting('shib_data_conv') &&
                    $this->ilias->getSetting('shib_data_conv', '') !== '' &&
                    is_readable($this->ilias->getSetting('shib_data_conv'))
                ) {
                    /** @noRector */
                    include($this->ilias->getSetting('shib_data_conv'));
                }
                $shib_user = ilShibbolethPluginWrapper::getInstance()->beforeCreateUser($shib_user);
                if ($account_creation === ilShibbolethSettings::ACCOUNT_CREATION_WITH_APPROVAL) {
                    $shib_user->setActive(false);
                }
                $shib_user->create();
                $shib_user->saveAsNew();
                $shib_user->updateOwner();
                $shib_user->writePrefs();
                $shib_user = ilShibbolethPluginWrapper::getInstance()->afterCreateUser($shib_user);
                ilShibbolethRoleAssignmentRules::doAssignments($shib_user->getId(), $_SERVER);
            }

            if (!$new_user || $account_creation === ilShibbolethSettings::ACCOUNT_CREATION_ENABLED) {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
                $status->setAuthenticatedUserId(ilObjUser::_lookupId($shib_user->getLogin()));
            } elseif ($account_creation === ilShibbolethSettings::ACCOUNT_CREATION_WITH_APPROVAL) {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
                $status->setReason('err_inactive');
            } else {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
                $status->setReason('err_disabled');
            }

        } else {
            $this->getLogger()->info('Shibboleth authentication failed.');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        return true;
    }
}
