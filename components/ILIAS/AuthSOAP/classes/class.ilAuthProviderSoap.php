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

use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;

class ilAuthProviderSoap extends ilAuthProvider
{
    protected string $server_host = '';
    protected string $server_port = '';
    protected string $server_uri = '';
    protected bool $server_https = false;
    protected string $server_nms = '';
    protected bool $use_dot_net = false;
    protected string $uri = '';
    protected nusoap_client $client;
    protected ilLogger $logger;
    protected ilSetting $settings;
    protected ilLanguage $language;
    protected ilRbacAdmin $rbacAdmin;
    protected ilDBInterface $db;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->logger = $DIC->logger()->auth();
        $this->language = $DIC->language();
        $this->rbacAdmin = $DIC->rbac()->admin();
        $this->db = $DIC->database();

        parent::__construct($credentials);
    }

    private function initClient(): void
    {
        $this->server_host = (string) $this->settings->get('soap_auth_server', '');
        $this->server_port = (string) $this->settings->get('soap_auth_port', '');
        $this->server_uri = (string) $this->settings->get('soap_auth_uri', '');
        $this->server_nms = (string) $this->settings->get('soap_auth_namespace', '');
        $this->server_https = (bool) $this->settings->get('soap_auth_use_https', '0');
        $this->use_dot_net = (bool) $this->settings->get('use_dotnet', '0');

        $this->uri = $this->server_https ? 'https://' : 'http://';
        $this->uri .= $this->server_host;

        if ($this->server_port > 0) {
            $this->uri .= (':' . $this->server_port);
        }
        if ($this->server_uri) {
            $this->uri .= ('/' . $this->server_uri);
        }

        require_once __DIR__ . '/../../soap/lib/nusoap.php';
        $this->client = new nusoap_client($this->uri);
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

        $internalLogin = ilObjUser::_checkExternalAuthAccount(
            'soap',
            $this->getCredentials()->getUsername()
        );

        $isNewUser = false;
        if ('' === $internalLogin || null === $internalLogin) {
            $isNewUser = true;
        }

        $soapAction = '';
        $nspref = '';
        if ($this->use_dot_net) {
            $soapAction = $this->server_nms . '/isValidSession';
            $nspref = 'ns1:';
        }

        $valid = $this->client->call(
            'isValidSession',
            [
                $nspref . 'ext_uid' => $this->getCredentials()->getUsername(),
                $nspref . 'soap_pw' => $this->getCredentials()->getPassword(),
                $nspref . 'new_user' => $isNewUser
            ],
            $this->server_nms,
            $soapAction
        );

        if (!is_array($valid)) {
            $valid = ['valid' => false];
        }

        if ($valid['valid'] !== true) {
            $valid['valid'] = false;
        }

        if (!$valid['valid']) {
            $status->setReason('err_wrong_login');
            return false;
        }

        if (!$isNewUser) {
            $status->setAuthenticatedUserId(ilObjUser::_lookupId($internalLogin));
            return true;
        }

        if (!$this->settings->get('soap_auth_create_users')) {
            // Translate the reasons, otherwise the default failure is displayed
            $status->setTranslatedReason($this->language->txt('err_valid_login_account_creation_disabled'));
            return false;
        }

        $userObj = new ilObjUser();
        $internalLogin = ilAuthUtils::_generateLogin($this->getCredentials()->getUsername());

        $password = '';
        $password_type = ilObjUser::PASSWD_CRYPTED;
        if ($this->settings->get('soap_auth_allow_local')) {
            $passwords = ilSecuritySettingsChecker::generatePasswords(1);
            $password = $passwords[0];
            $password_type = ilObjUser::PASSWD_PLAIN;
        }

        $userObj->setLogin($internalLogin);
        $userObj->setFirstname($user->getFirstname());
        $userObj->setLastname($user->getLastname());
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());
        $userObj->setEmail($user->getEmail());
        $userObj->setPasswd($password, $password_type);
        $userObj->setAuthMode('soap');
        $userObj->setExternalAccount($this->getCredentials()->getUsername());
        $userObj->setLanguage($this->language->getDefaultLanguage());
        $userObj->setProfileIncomplete(true);

        $userObj->setTimeLimitUnlimited(true);
        $userObj->setTimeLimitFrom(time());
        $userObj->setTimeLimitUntil(time());
        $userObj->setOwner(0);
        $userObj->create();
        $userObj->setActive(true);
        $userObj->updateOwner();
        $userObj->saveAsNew();
        $userObj->writePrefs();

        $this->rbacAdmin->assignUser(
            (int) $this->settings->get('soap_auth_user_default_role', '4'),
            $userObj->getId()
        );

        if ($this->settings->get('soap_auth_account_mail', '0')) {
            $registrationSettings = new ilRegistrationSettings();
            $registrationSettings->setPasswordGenerationStatus(true);

            $accountMail = new ilAccountRegistrationMail(
                $registrationSettings,
                $this->logger,
                new NewAccountMailRepository($this->db)
            );
            $accountMail
                ->withDirectRegistrationMode()
                ->send($userObj, $password, false);
        }

        $status->setAuthenticatedUserId($userObj->getId());
        return true;
    }
}
