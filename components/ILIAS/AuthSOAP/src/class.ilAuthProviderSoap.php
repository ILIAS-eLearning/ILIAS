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

declare(strict_types=1);

use ILIAS\AuthSOAP\SessionValidationClient;
use ILIAS\AuthSOAP\SoapAuthEndpoint;

class ilAuthProviderSoap extends ilAuthProvider
{
    protected SessionValidationClient $client;
    protected ilLogger $logger;
    protected ilSetting $settings;
    protected ilLanguage $language;
    protected ilRbacAdmin $rbac_admin;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->logger = $DIC->logger()->auth();
        $this->language = $DIC->language();
        $this->rbac_admin = $DIC->rbac()->admin();

        parent::__construct($credentials);
    }

    private function initClient(): void
    {
        $this->client = (new SoapAuthEndpoint($this->settings))->createValidationClient();
    }

    /**
     * @inheritDoc
     */
    public function doAuthentication(ilAuthStatus $status): bool
    {
        try {
            $this->initClient();
            $this->handleSoapAuth($status);
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->error($e->getTraceAsString());

            $this->handleAuthenticationFail($status, 'err_wrong_login');

            return false;
        }

        if ($status->getAuthenticatedUserId() > 0 && $status->getAuthenticatedUserId() !== ANONYMOUS_USER_ID) {
            $this->logger->info('Successfully authenticated user via SOAP: ' . $this->getCredentials()->getUsername());
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            ilSession::set('used_external_auth_mode', ilAuthUtils::AUTH_SOAP);

            return true;
        }

        $this->handleAuthenticationFail($status, 'err_wrong_login');

        return false;
    }

    private function handleSoapAuth(ilAuthStatus $status): bool
    {
        $this->logger->debug(sprintf(
            'Login observer called for SOAP authentication request of ext_account "%s" and auth_mode "%s".',
            $this->getCredentials()->getUsername(),
            'soap'
        ));
        $this->logger->debug(sprintf(
            'Trying to find ext_account "%s" for auth_mode "%s".',
            $this->getCredentials()->getUsername(),
            'soap'
        ));

        $internal_login = ilObjUser::_checkExternalAuthAccount(
            'soap',
            $this->getCredentials()->getUsername()
        );

        $is_new_user = false;
        if ($internal_login === '' || $internal_login === null) {
            $is_new_user = true;
        }

        $valid = $this->client->validateSession(
            $this->getCredentials()->getUsername(),
            $this->getCredentials()->getPassword(),
            $is_new_user
        )->validation;

        if ($valid['valid'] !== true) {
            $valid['valid'] = false;
        }

        if (!$valid['valid']) {
            $status->setReason('err_wrong_login');
            return false;
        }

        if (!$is_new_user) {
            $status->setAuthenticatedUserId(ilObjUser::_lookupId($internal_login));
            return true;
        }

        if (!$this->settings->get('soap_auth_create_users')) {
            $status->setTranslatedReason($this->language->txt('err_valid_login_account_creation_disabled'));
            return false;
        }

        $user_obj = new ilObjUser();
        $internal_login = ilAuthUtils::_generateLogin($this->getCredentials()->getUsername());

        $usr_data = [];
        $usr_data['firstname'] = $valid['firstname'];
        $usr_data['lastname'] = $valid['lastname'];
        $usr_data['email'] = $valid['email'];
        $usr_data['login'] = $internal_login;
        $usr_data['passwd'] = '';
        $usr_data['passwd_type'] = ilObjUser::PASSWD_CRYPTED;

        $password = '';
        if ($this->settings->get('soap_auth_allow_local')) {
            $passwords = ilSecuritySettingsChecker::generatePasswords(1);
            $password = $passwords[0];
            $usr_data['passwd'] = $password;
            $usr_data['passwd_type'] = ilObjUser::PASSWD_PLAIN;
        }

        $usr_data['auth_mode'] = 'soap';
        $usr_data['ext_account'] = $this->getCredentials()->getUsername();
        $usr_data['profile_incomplete'] = 1;

        $user_obj->assignData($usr_data);
        $user_obj->setTitle($user_obj->getFullname());
        $user_obj->setDescription($user_obj->getEmail());
        $user_obj->setLanguage($this->language->getDefaultLanguage());

        $user_obj->setTimeLimitOwner(USER_FOLDER_ID);
        $user_obj->setTimeLimitUnlimited(true);
        $user_obj->setTimeLimitFrom(time());
        $user_obj->setTimeLimitUntil(time());
        $user_obj->setOwner(0);
        $user_obj->create();
        $user_obj->setActive(true);
        $user_obj->saveAsNew();
        $user_obj->updateOwner();
        $user_obj->writePrefs();

        $this->rbac_admin->assignUser(
            (int) $this->settings->get('soap_auth_user_default_role', '4'),
            $user_obj->getId()
        );

        if ($this->settings->get('soap_auth_account_mail', '0')) {
            $registration_settings = new ilRegistrationSettings();
            $registration_settings->setPasswordGenerationStatus(true);

            $account_mail = new ilAccountRegistrationMail(
                $registration_settings,
                $this->language,
                $this->logger
            );
            $account_mail
                ->withDirectRegistrationMode()
                ->send($user_obj, $password, false);
        }

        $status->setAuthenticatedUserId($user_obj->getId());
        return true;
    }
}
