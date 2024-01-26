<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Jumbojett\OpenIDConnectClient;

/**
 * Class ilAuthProviderOpenIdConnect
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilAuthProviderOpenIdConnect extends ilAuthProvider implements ilAuthProviderInterface
{
    const OIDC_AUTH_IDTOKEN = "oidc_auth_idtoken";
    /**
     * @var ilOpenIdConnectSettings|null
     */
    private $settings = null;

    private $lng = null;


    /**
     * ilAuthProviderOpenIdConnect constructor.
     * @param ilAuthCredentials $credentials
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        parent::__construct($credentials);
        $this->settings = ilOpenIdConnectSettings::getInstance();
        $this->lng = $DIC->language();
    }

    /**
     * Handle logout event
     */
    public function handleLogout()
    {
        if ($this->settings->getLogoutScope() == ilOpenIdConnectSettings::LOGOUT_SCOPE_LOCAL) {
            return false;
        }

        $id_token = ilSession::get(self::OIDC_AUTH_IDTOKEN);
        $this->getLogger()->debug('Logging out with token: ' . $id_token);


        if (is_string($id_token) && $id_token !== '') {
            ilSession::set(self::OIDC_AUTH_IDTOKEN, '');
            $oidc = $this->initClient();
            try {
                $oidc->signOut(
                    $id_token,
                    ILIAS_HTTP_PATH . '/logout.php'
                );
            } catch (\Jumbojett\OpenIDConnectClientException $e) {
                $this->getLogger()->warning("Logging out of OIDC provider failed with: " . $e->getMessage());
            }
        }
    }

    /**
     * Do authentication
     * @param \ilAuthStatus $status Authentication status
     * @return bool
     */
    public function doAuthentication(\ilAuthStatus $status)
    {
        try {
            $oidc = $this->initClient();
            $oidc->setRedirectURL(ILIAS_HTTP_PATH . '/openidconnect.php');

            $proxy = ilProxySettings::_getInstance();
            if ($proxy->isActive()) {
                $host = $proxy->getHost();
                $port = $proxy->getPort();
                if ($port) {
                    $host .= ":" . $port;
                }
                $oidc->setHttpProxy($host);
            }

            $this->getLogger()->debug(
                'Redirect url is: ' .
                $oidc->getRedirectURL()
            );

            $oidc->addScope($this->settings->getAllScopes());
            switch ($this->settings->getLoginPromptType()) {
                case ilOpenIdConnectSettings::LOGIN_ENFORCE:
                    $oidc->addAuthParam(['prompt' => 'login']);
                    break;
            }

            $oidc->authenticate();
            // user is authenticated, otherwise redirected to authorization endpoint or exception
            $this->getLogger()->dump($_REQUEST, \ilLogLevel::DEBUG);

            $claims = $oidc->getVerifiedClaims(null);
            $this->getLogger()->dump($claims, \ilLogLevel::DEBUG);
            $status = $this->handleUpdate($status, $claims);

            // @todo : provide a general solution for all authentication methods
            $_GET['target'] = (string) $this->getCredentials()->getRedirectionTarget();

            if ($this->settings->getLogoutScope() == ilOpenIdConnectSettings::LOGOUT_SCOPE_GLOBAL) {
                $token = $oidc->requestUserInfo();
                ilSession::set(self::OIDC_AUTH_IDTOKEN, $oidc->getIdToken());
            }
            return true;
        } catch (Exception $e) {
            $this->getLogger()->warning($e->getMessage());
            $this->getLogger()->warning($e->getCode());
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setTranslatedReason($this->lng->txt("auth_oidc_failed"));
            return false;
        }
    }


    /**
     * @param ilAuthStatus $status
     * @param array $user_info
     */
    private function handleUpdate(ilAuthStatus $status, $user_info)
    {
        if (!is_object($user_info)) {
            $this->getLogger()->error('Received invalid user credentials: ');
            $this->getLogger()->dump($user_info, ilLogLevel::ERROR);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason('err_wrong_login');
            return false;
        }

        $uid_field = $this->settings->getUidField();
        $ext_account = $user_info->$uid_field;

        $this->getLogger()->debug('Authenticated external account: ' . $ext_account);


        $int_account = ilObjUser::_checkExternalAuthAccount(
            ilOpenIdConnectUserSync::AUTH_MODE,
            $ext_account
        );

        try {
            $sync = new ilOpenIdConnectUserSync($this->settings, $user_info);
            if (!is_string($ext_account)) {
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
                $status->setReason('err_wrong_login');
                return $status;
            }
            $sync->setExternalAccount($ext_account);
            $sync->setInternalAccount($int_account);
            $sync->updateUser();

            $user_id = $sync->getUserId();
            ilSession::set('used_external_auth', true);
            $status->setAuthenticatedUserId($user_id);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);

            // @todo : provide a general solution for all authentication methods
            $_GET['target'] = (string) $this->getCredentials()->getRedirectionTarget();
        } catch (ilOpenIdConnectSyncForbiddenException $e) {
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason('err_wrong_login');
        }

        return $status;
    }

    /**
     * @return OpenIDConnectClient
     */
    private function initClient() : OpenIDConnectClient
    {
        $oidc = new OpenIDConnectClient(
            $this->settings->getProvider(),
            $this->settings->getClientId(),
            $this->settings->getSecret()
        );
        return $oidc;
    }
}
