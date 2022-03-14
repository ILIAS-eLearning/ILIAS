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
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilAuthProviderSoap
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthProviderSoap extends ilAuthProvider implements ilAuthProviderInterface
{
    protected string $server_host = '';
    protected string $server_port = '';
    protected string $server_uri = '';
    protected string $server_https = '';
    protected string $server_nms = '';
    protected bool $use_dot_net = false;
    protected string $uri = '';
    /** @var nusoap_client */
    protected $client;
    protected ilLogger $logger;
    protected ilSetting $settings;
    protected ilLanguage $language;
    protected ilRbacAdmin $rbacAdmin;

    /**
     * @inheritDoc
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        $this->logger = $DIC->logger()->auth();
        $this->language = $DIC->language();
        $this->rbacAdmin = $DIC->rbac()->admin();

        parent::__construct($credentials);
    }

    /**
     *
     */
    private function initClient() : void
    {
        $this->server_host = (string) $this->settings->get('soap_auth_server', '');
        $this->server_port = (string) $this->settings->get('soap_auth_port', '');
        $this->server_uri = (string) $this->settings->get('soap_auth_uri', '');
        $this->server_nms = (string) $this->settings->get('soap_auth_namespace', '');
        $this->server_https = (bool) $this->settings->get('soap_auth_use_https', false);
        $this->use_dot_net = (bool) $this->settings->get('use_dotnet', false);

        $this->uri = $this->server_https ? 'https://' : 'http://';
        $this->uri .= $this->server_host;

        if ($this->server_port > 0) {
            $this->uri .= (':' . $this->server_port);
        }
        if ($this->server_uri) {
            $this->uri .= ('/' . $this->server_uri);
        }
        $this->client = new nusoap_client($this->uri);
    }

    /**
     * @inheritDoc
     */
    public function doAuthentication(ilAuthStatus $status) : bool
    {
        try {
            $this->initClient();
            $this->handleSoapAuth($status);
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $status->setTranslatedReason($e->getMessage());
        }

        if ($status->getAuthenticatedUserId() > 0) {
            $this->logger->info('Successfully authenticated user via SOAP: ' . $this->getCredentials()->getUsername());
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            ilSession::set('used_external_auth', true);

            return true;
        }

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);

        return false;
    }

    private function handleSoapAuth(ilAuthStatus $status) : bool
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
        if ('' === $internalLogin || false === $internalLogin) {
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
        } elseif (!$this->settings->get('soap_auth_create_users')) {
            // Translate the reasons, otherwise the default failure is displayed
            $status->setTranslatedReason($this->language->txt('err_valid_login_account_creation_disabled'));
            return false;
        }

        $userObj = new ilObjUser();
        $internalLogin = ilAuthUtils::_generateLogin($this->getCredentials()->getUsername());

        $usrData = [];
        $usrData['firstname'] = $valid['firstname'];
        $usrData['lastname'] = $valid['lastname'];
        $usrData['email'] = $valid['email'];
        $usrData['login'] = $internalLogin;
        $usrData['passwd'] = '';
        $usrData['passwd_type'] = ilObjUser::PASSWD_CRYPTED;

        $password = '';
        if ($this->settings->get('soap_auth_allow_local')) {
            $passwords = ilSecuritySettingsChecker::generatePasswords(1);
            $password = $passwords[0];
            $usrData['passwd'] = $password;
            $usrData['passwd_type'] = ilObjUser::PASSWD_PLAIN;
        }

        $usrData['auth_mode'] = 'soap';
        $usrData['ext_account'] = $this->getCredentials()->getUsername();
        $usrData['profile_incomplete'] = 1;

        $userObj->assignData($usrData);
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());
        $userObj->setLanguage($this->language->getDefaultLanguage());

        $userObj->setTimeLimitOwner(USER_FOLDER_ID);
        $userObj->setTimeLimitUnlimited(1);
        $userObj->setTimeLimitFrom(time());
        $userObj->setTimeLimitUntil(time());
        $userObj->setOwner(0);
        $userObj->create();
        $userObj->setActive(1);
        $userObj->updateOwner();
        $userObj->saveAsNew(false);
        $userObj->writePrefs();

        $this->rbacAdmin->assignUser(
            $this->settings->get('soap_auth_user_default_role', 4),
            $userObj->getId()
        );

        if ($this->settings->get('soap_auth_account_mail', false)) {
            $registrationSettings = new ilRegistrationSettings();
            $registrationSettings->setPasswordGenerationStatus(true);

            $accountMail = new ilAccountRegistrationMail(
                $registrationSettings,
                $this->language,
                $this->logger
            );
            $accountMail
                ->withDirectRegistrationMode()
                ->send($userObj, $password, false);
        }

        $status->setAuthenticatedUserId($userObj->getId());
        return true;
    }
}
