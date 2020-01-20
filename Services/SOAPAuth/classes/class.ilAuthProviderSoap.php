<?php
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAuthProviderSoap
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthProviderSoap extends ilAuthProvider implements ilAuthProviderInterface 
{
    protected $server_host	= '';
    protected $server_port	= '';
    protected $server_uri	= '';
    protected $server_https	= '';
    protected $server_nms	= '';
    protected $use_dot_net	= false;
    protected $uri = '';
    protected $client = null;

    /**
     * @inheritDoc
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
    }

    /**
     * Init soap client
     * @return
     */
    public function initClient()
    {
        global $DIC;

        $this->server_host = $DIC->settings()->get('soap_auth_server');
        $this->server_port = $DIC->settings()->get('soap_auth_port');
        $this->server_uri = $DIC->settings()->get('soap_auth_uri');
        $this->server_https = $DIC->settings()->get('soap_auth_use_https');
        $this->server_nms = $DIC->settings()->get('soap_auth_namespace');
        $this->use_dot_net = (bool) $DIC->settings()->get('use_dotnet');

        $this->uri  = $this->server_https ? 'https://' : 'http://';
        $this->uri .= $this->server_host;

        if ($this->server_port > 0) {
            $this->uri .= (':' . $this->server_port);
        }
        if ($this->server_uri) {
            $this->uri .= ('/' . $this->server_uri);
        }

        include_once './webservice/soap/lib/nusoap.php';
        $this->client = new nusoap_client($this->uri);
    }

    /**
     * @inheritDoc
     */
    public function doAuthentication(ilAuthStatus $status)
    {
        try {
            $this->initClient();

            return $this->handleSoapAuth($status);
        } catch (\ilException $e) {
            $this->getLogger()->warning($e->getMessage());
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }
    }

    /**
     * @param ilAuthStatus $status
     */
    private function handleSoapAuth(ilAuthStatus $status)
    {
        global $DIC;

        ilLoggerFactory::getLogger('auth')->debug(sprintf(
            'Login observer called for SOAP authentication request of ext_account "%s" and auth_mode "%s".',
            $this->getCredentials()->getUsername(),
            'soap'
        ));
        ilLoggerFactory::getLogger('auth')->debug(sprintf(
            'Trying to find ext_account "%s" for auth_mode "%s".',
            $this->getCredentials()->getUsername(),
            'soap'
        ));

        $local_user = ilObjUser::_checkExternalAuthAccount(
            'soap',
            $this->getCredentials()->getUsername()
        );

        if ('' === $local_user || false === $local_user) {
            $new_user = true;
        } else {
            $new_user = false;
        }

        $soapAction = "";
        $nspref = "";
        if ($this->use_dot_net) {
            $soapAction = $this->server_nms . "/isValidSession";
            $nspref = "ns1:";
        }
        $valid = $this->client->call(
            'isValidSession',
            [
                $nspref . 'ext_uid' => $this->getCredentials()->getUsername(),
                $nspref . 'soap_pw' => $this->getCredentials()->getPassword(),
                $nspref . 'new_user' => $new_user
            ],
            $this->server_nms,
            $soapAction
        );

        if ($valid["valid"] !== true) {
            $valid["valid"] = false;
        }

        if (!$valid['valid']) {
            throw new ilException("Authentication failed");
        }

        if (!$new_user) {
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId(ilObjUser::_lookupId($local_user));
            ilSession::set('used_external_auth', true);
            return true;
        } elseif (!$DIC->settings()->get("soap_auth_create_users")) {
            throw new ilException("Authentication succeeded, but creation of new accounts is disabled in the administration");
        }

        $userObj = new ilObjUser();
        $local_user = ilAuthUtils::_generateLogin($this->getCredentials()->getUsername());

        $newUser["firstname"] = $valid["firstname"];
        $newUser["lastname"] = $valid["lastname"];
        $newUser["email"] = $valid["email"];
        $newUser["login"] = $local_user;

        // to do: set valid password and send mail
        $newUser["passwd"] = "";
        $newUser["passwd_type"] = IL_PASSWD_CRYPTED;

        // generate password, if local authentication is allowed
        // and account mail is activated
        $pw = "";

        if ($DIC->settings()->get("soap_auth_allow_local") &&
            $DIC->settings()->get("soap_auth_account_mail")) {
            $pw = ilUtil::generatePasswords(1);
            $pw = $pw[0];
            $newUser["passwd"] = $pw;
            $newUser["passwd_type"] = IL_PASSWD_PLAIN;
        }

        //$newUser["gender"] = "m";
        $newUser["auth_mode"] = "soap";
        $newUser["ext_account"] = $this->getCredentials()->getUsername();
        $newUser["profile_incomplete"] = 1;

        // system data
        $userObj->assignData($newUser);
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());

        // set user language to system language
        $userObj->setLanguage($DIC->language()->getDefaultLanguage());

        // Time limit
        $userObj->setTimeLimitOwner(7);
        $userObj->setTimeLimitUnlimited(1);
        $userObj->setTimeLimitFrom(time());
        $userObj->setTimeLimitUntil(time());

        // Create user in DB
        $userObj->setOwner(0);
        $userObj->create();
        $userObj->setActive(1);

        $userObj->updateOwner();

        //insert user data in table user_data
        $userObj->saveAsNew(false);

        // setup user preferences
        $userObj->writePrefs();

        // to do: test this
        $DIC->rbac()->admin()->assignUser($DIC->settings()->get('soap_auth_user_default_role'), $userObj->getId());

        // send account mail
        if ($DIC->settings()->get("soap_auth_account_mail")) {
            include_once('./Services/User/classes/class.ilObjUserFolder.php');
            $amail = ilObjUserFolder::_lookupNewAccountMail($DIC->settings()->get("language"));
            if (trim($amail["body"]) != "" && trim($amail["subject"]) != "") {
                include_once("Services/Mail/classes/class.ilAccountMail.php");
                $acc_mail = new ilAccountMail();

                if ($pw != "") {
                    $acc_mail->setUserPassword($pw);
                }
                $acc_mail->setUser($userObj);
                $acc_mail->send();
            }
        }

        $this->getLogger()->debug('Successfully authenticated user: ' . $this->getCredentials()->getUsername());
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId($userObj->getId());
    }
}