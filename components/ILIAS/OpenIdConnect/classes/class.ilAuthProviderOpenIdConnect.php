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

class ilAuthProviderOpenIdConnect extends ilAuthProvider
{
    private const OIDC_AUTH_IDTOKEN = 'oidc_auth_idtoken';
    private const OIDC_LOGOUT_STATE = 'oidc_logout_state';
    private const OIDC_LOGOUT_USER_LANGUAGE = 'oidc_logout_user_language';

    private const ERR_AUTH_FAILED = 'auth_oidc_failed';
    private const ERR_AUTH_WRONG_LOGIN = 'err_wrong_login';

    private readonly ilOpenIdConnectSettings $settings;
    private readonly ilLogger $logger;
    private readonly ilLanguage $lng;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        parent::__construct($credentials);

        $this->logger = $DIC->logger()->auth();
        $this->settings = ilOpenIdConnectSettings::getInstance();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('auth');
    }

    /**
     * Static post-logout redirect URI for OP registration (OpenID Connect RP-Initiated Logout).
     * Same endpoint as login; pending logout is detected via ilSession.
     */
    public function getPostLogoutRedirectUri(): string
    {
        return ILIAS_HTTP_PATH . '/openidconnect.php';
    }

    public function isPostLogoutPending(): bool
    {
        $state = ilSession::get(self::OIDC_LOGOUT_STATE);

        return is_string($state) && $state !== '';
    }

    public function handleLogout(ilObjUser $user): void
    {
        if ($this->settings->getLogoutScope() === ilOpenIdConnectSettings::LOGOUT_SCOPE_LOCAL) {
            return;
        }

        $id_token = ilSession::get(self::OIDC_AUTH_IDTOKEN);
        if (!isset($id_token) || $id_token === '') {
            return;
        }

        $this->logger->debug('Logging out with token: ' . $id_token);

        // Keep dynamic logout context in the ILIAS session; only a static URI is sent to the OP.
        $state = bin2hex(random_bytes(16));
        ilSession::set(self::OIDC_LOGOUT_STATE, $state);
        ilSession::set(self::OIDC_LOGOUT_USER_LANGUAGE, $user->getLanguage());
        ilSession::set(self::OIDC_AUTH_IDTOKEN, '');

        $oidc = $this->initClient();

        try {
            $oidc->signOutWithState(
                $id_token,
                $this->getPostLogoutRedirectUri(),
                $state
            );
        } catch (\Jumbojett\OpenIDConnectClientException $e) {
            ilSession::set(self::OIDC_LOGOUT_STATE, '');
            ilSession::set(self::OIDC_LOGOUT_USER_LANGUAGE, '');
            $this->logger->warning('Logging out of OIDC provider failed with: ' . $e->getMessage());
        }
    }

    public function validatePostLogoutState(string $state): bool
    {
        $expected_state = ilSession::get(self::OIDC_LOGOUT_STATE);

        if (!is_string($expected_state) || $expected_state === '' || !hash_equals($expected_state, $state)) {
            $this->logger->warning('OpenID Connect post-logout state validation failed.');

            return false;
        }

        ilSession::set(self::OIDC_LOGOUT_STATE, '');

        return true;
    }

    public function consumePostLogoutUserLanguage(): string
    {
        $language = ilSession::get(self::OIDC_LOGOUT_USER_LANGUAGE);
        ilSession::set(self::OIDC_LOGOUT_USER_LANGUAGE, '');

        return is_string($language) && $language !== '' ? $language : 'en';
    }

    public function doAuthentication(ilAuthStatus $status): bool
    {
        if (!$this->settings->getActive()) {
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setTranslatedReason($this->lng->txt(self::ERR_AUTH_FAILED));
            $this->logger->info('Authentication aborted, OIDC authentication is disabled');
            return false;
        }

        try {
            $oidc = $this->initClient();
            $oidc->setRedirectURL(ILIAS_HTTP_PATH . '/openidconnect.php');

            $proxy = ilProxySettings::_getInstance();
            if ($proxy->isActive()) {
                $host = $proxy->getHost();
                $port = $proxy->getPort();
                if ($port) {
                    $host .= ':' . $port;
                }
                $oidc->setHttpProxy($host);
            }

            $this->logger->debug(
                'Redirect url is: ' .
                $oidc->getRedirectURL()
            );

            $oidc->addScope($this->settings->getAllScopes());
            if ($this->settings->getLoginPromptType() === ilOpenIdConnectSettings::LOGIN_ENFORCE) {
                $oidc->addAuthParam(['prompt' => 'login']);
            }

            $oidc->authenticate();
            // user is authenticated, otherwise redirected to authorization endpoint or exception

            $claims = $oidc->getVerifiedClaims();
            $this->logger->dump($claims, ilLogLevel::DEBUG);
            $status = $this->handleUpdate($status, $claims);

            // @todo : provide a general solution for all authentication methods
            //$_GET['target'] = $this->getCredentials()->getRedirectionTarget();// TODO PHP8-REVIEW Please eliminate this. Mutating the request is not allowed and will not work in ILIAS 8.

            if ($this->settings->getLogoutScope() === ilOpenIdConnectSettings::LOGOUT_SCOPE_GLOBAL) {
                ilSession::set(self::OIDC_AUTH_IDTOKEN, $oidc->getIdToken());
            }
            return true;
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage());
            $this->logger->warning((string) $e->getCode());
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setTranslatedReason($this->lng->txt(self::ERR_AUTH_FAILED));
            return false;
        }
    }

    /**
     * @param stdClass $user_info
     */
    private function handleUpdate(ilAuthStatus $status, $user_info): ilAuthStatus
    {
        if (!is_object($user_info)) {
            $this->logger->error('Received invalid user credentials: ');
            $this->logger->dump($user_info, ilLogLevel::ERROR);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason(self::ERR_AUTH_WRONG_LOGIN);
            return $status;
        }

        $uid_field = $this->settings->getUidField();
        $ext_account = $user_info->{$uid_field} ?? '';

        if (!is_string($ext_account) || $ext_account === '') {
            $this->logger->error('Could not determine valid external account, value is empty or not a string.');
            $this->logger->dump($user_info, ilLogLevel::ERROR);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason(self::ERR_AUTH_WRONG_LOGIN);
            return $status;
        }

        $this->logger->debug('Authenticated external account: ' . $ext_account);

        $int_account = ilObjUser::_checkExternalAuthAccount(
            ilOpenIdConnectUserSync::AUTH_MODE,
            $ext_account
        );

        try {
            $sync = new ilOpenIdConnectUserSync($this->settings, $user_info);
            $sync->setExternalAccount($ext_account);
            $sync->setInternalAccount((string) $int_account);
            $sync->updateUser();

            $user_id = $sync->getUserId();
            ilSession::set('used_external_auth_mode', ilAuthUtils::AUTH_OPENID_CONNECT);
            $status->setAuthenticatedUserId($user_id);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            //$_GET['target'] = $this->getCredentials()->getRedirectionTarget();// TODO PHP8-REVIEW Please eliminate this. Mutating the request is not allowed and will not work in ILIAS 8.
        } catch (ilOpenIdConnectSyncForbiddenException) {
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $status->setReason(self::ERR_AUTH_WRONG_LOGIN);
        }

        return $status;
    }

    private function initClient(): ilOpenIdConnectClient
    {
        $oidc = new ilOpenIdConnectClient(
            $this->settings->getProvider(),
            $this->settings->getClientId(),
            $this->settings->getSecret()
        );

        $oidc->setCodeChallengeMethod('S256');

        return $oidc;
    }
}
