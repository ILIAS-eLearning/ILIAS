<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use Jumbojett\OpenIDConnectClient;

/**
 * Class ilAuthProviderOpenIdConnect
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthProviderOpenIdConnect extends ilAuthProvider
{
    private ilOpenIdConnectSettings $settings;
    /** @var array $body */
    private $body;
    private ilLogger $logger;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        parent::__construct($credentials);

        $this->logger = $DIC->logger()->auth();
        $this->settings = ilOpenIdConnectSettings::getInstance();
        $this->body = $DIC->http()->request()->getParsedBody();
    }

    public function handleLogout(): void
    {
        if ($this->settings->getLogoutScope() === ilOpenIdConnectSettings::LOGOUT_SCOPE_LOCAL) {
            return;
        }

        $auth_token = ilSession::get('oidc_auth_token');
        $this->logger->debug('Using token: ' . $auth_token);

        if (isset($auth_token) && $auth_token !== '') {
            ilSession::set('oidc_auth_token', '');
            $oidc = $this->initClient();
            $oidc->signOut(
                $auth_token,
                ILIAS_HTTP_PATH . '/logout.php'
            );
        }
    }

    public function doAuthentication(ilAuthStatus $status): bool
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

            $this->logger->debug(
                'Redirect url is: ' .
                $oidc->getRedirectURL()
            );

            $oidc->setResponseTypes(
                [
                    'id_token'
                ]
            );


            $oidc->addScope($this->settings->getAllScopes());
            $oidc->addAuthParam(['response_mode' => 'form_post']);
            if ($this->settings->getLoginPromptType() === ilOpenIdConnectSettings::LOGIN_ENFORCE) {
                $oidc->addAuthParam(['prompt' => 'login']);
            }
            $oidc->setAllowImplicitFlow(true);

            $oidc->authenticate();
            // user is authenticated, otherwise redirected to authorization endpoint or exception
            $this->logger->dump($this->body, ilLogLevel::DEBUG);

            $claims = $oidc->getVerifiedClaims(null);
            $this->logger->dump($claims, ilLogLevel::DEBUG);
            $status = $this->handleUpdate($status, $claims);

            // @todo : provide a general solution for all authentication methods
            //$_GET['target'] = $this->getCredentials()->getRedirectionTarget();// TODO PHP8-REVIEW Please eliminate this. Mutating the request is not allowed and will not work in ILIAS 8.

            //TODO fix this. There is a PR and it is broken in 7 as well
            //if ($this->settings->getLogoutScope() === ilOpenIdConnectSettings::LOGOUT_SCOPE_GLOBAL) {
            //$token = $oidc->requestClientCredentialsToken();
            //ilSession::set('oidc_auth_token', $token->access_token);
            //}
            return true;
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
            $this->logger->warning((string) $e->getCode());
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setTranslatedReason($e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param ilAuthStatus $status
     * @param stdClass $user_info
     * @return ilAuthStatus
     */
    private function handleUpdate(ilAuthStatus $status, $user_info): ilAuthStatus
    {
        if (!is_object($user_info)) {
            $this->logger->error('Received invalid user credentials: ');
            $this->logger->dump($user_info, ilLogLevel::ERROR);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason('err_wrong_login');
            return $status;
        }

        $uid_field = $this->settings->getUidField();
        $ext_account = $user_info->{$uid_field};

        $this->logger->debug('Authenticated external account: ' . $ext_account);


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

            //$_GET['target'] = $this->getCredentials()->getRedirectionTarget();// TODO PHP8-REVIEW Please eliminate this. Mutating the request is not allowed and will not work in ILIAS 8.
        } catch (ilOpenIdConnectSyncForbiddenException $e) {
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason('err_wrong_login');
        }

        return $status;
    }

    private function initClient(): OpenIDConnectClient
    {
        $oidc = new OpenIDConnectClient(
            $this->settings->getProvider(),
            $this->settings->getClientId(),
            $this->settings->getSecret()
        );

        return $oidc;
    }
}
