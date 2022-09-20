<?php

declare(strict_types=1);

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
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
//TODO check why authfront does not include frontendInterface
class ilAuthFrontend
{
    public const MIG_EXTERNAL_ACCOUNT = 'mig_ext_account';
    public const MIG_TRIGGER_AUTHMODE = 'mig_trigger_auth_mode';
    public const MIG_DESIRED_AUTHMODE = 'mig_desired_auth_mode';

    private ilLogger $logger;
    private ilSetting $settings;
    private ilLanguage $lng;

    private ilAuthCredentials $credentials;
    private ilAuthStatus $status;
    /** @var ilAuthProvider[] */
    private array $providers;
    private ilAuthSession $auth_session;
    private ilAppEventHandler $ilAppEventHandler;

    private bool $authenticated = false;

    /**
     * @param ilAuthSession $session
     * @param ilAuthStatus $status
     * @param ilAuthCredentials $credentials
     * @param ilAuthProvider[] $providers
     */
    public function __construct(ilAuthSession $session, ilAuthStatus $status, ilAuthCredentials $credentials, array $providers)
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        $this->ilAppEventHandler = $DIC->event();

        $this->auth_session = $session;
        $this->credentials = $credentials;
        $this->status = $status;
        $this->providers = $providers;
    }

    /**
     * Get auth session
     */
    public function getAuthSession(): ilAuthSession
    {
        return $this->auth_session;
    }

    /**
     * Get auth credentials
     */
    public function getCredentials(): ilAuthCredentials
    {
        return $this->credentials;
    }

    /**
     * Get providers
     * @return ilAuthProviderInterface[] $provider
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return \ilAuthStatus
     */
    public function getStatus(): ilAuthStatus
    {
        return $this->status;
    }

    /**
     * Reset status
     */
    public function resetStatus(): void
    {
        $this->getStatus()->setStatus(ilAuthStatus::STATUS_UNDEFINED);
        $this->getStatus()->setReason('');
        $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
    }

    /**
     * Migrate Account to existing user account
     * @throws \InvalidArgumentException if current auth provider does not support account migration
     */
    public function migrateAccount(ilAuthSession $session): bool
    {
        if (!$session->isAuthenticated()) {
            $this->logger->warning('Desired user account is not authenticated');
            return false;
        }
        $user = ilObjectFactory::getInstanceByObjId($session->getUserId(), false);

        if (!$user instanceof ilObjUser) {
            $this->logger->info('Cannot instantiate user account for account migration: ' . $session->getUserId());
            return false;
        }

        $user->setAuthMode(ilSession::get(static::MIG_DESIRED_AUTHMODE));

        $this->logger->debug('new auth mode is: ' . ilSession::get(self::MIG_DESIRED_AUTHMODE));

        $user->setExternalAccount(ilSession::get(static::MIG_EXTERNAL_ACCOUNT));
        $user->update();

        foreach ($this->getProviders() as $provider) {
            if (!$provider instanceof ilAuthProviderAccountMigrationInterface) {
                $this->logger->warning('Provider: ' . get_class($provider) . ' does not support account migration.');
                throw new InvalidArgumentException('Invalid auth provider given.');
            }
            $this->getCredentials()->setUsername(ilSession::get(static::MIG_EXTERNAL_ACCOUNT));
            $provider->migrateAccount($this->getStatus());
            if ($this->getStatus()->getStatus() === ilAuthStatus::STATUS_AUTHENTICATED) {
                return $this->handleAuthenticationSuccess($provider);
            }
        }
        return $this->handleAuthenticationFail();
    }

    /**
     * Create new user account
     */
    public function migrateAccountNew(): bool
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof ilAuthProviderAccountMigrationInterface) {
                $this->logger->warning('Provider: ' . get_class($provider) . ' does not support account migration.');
                throw new InvalidArgumentException('Invalid auth provider given.');
            }
            $provider->createNewAccount($this->getStatus());

            if ($provider instanceof ilAuthProviderInterface &&
                $this->getStatus()->getStatus() === ilAuthStatus::STATUS_AUTHENTICATED) {
                return $this->handleAuthenticationSuccess($provider);
            }
        }
        return $this->handleAuthenticationFail();
    }



    /**
     * Try to authenticate user
     */
    public function authenticate(): bool
    {
        foreach ($this->getProviders() as $provider) {
            $this->resetStatus();

            $this->logger->debug('Trying authentication against: ' . get_class($provider));

            $provider->doAuthentication($this->getStatus());

            $this->logger->debug('Authentication user id: ' . $this->getStatus()->getAuthenticatedUserId());

            switch ($this->getStatus()->getStatus()) {
                case ilAuthStatus::STATUS_AUTHENTICATED:
                    return $this->handleAuthenticationSuccess($provider);

                case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
                    $this->logger->notice("Account migration required.");
                    if ($provider instanceof ilAuthProviderAccountMigrationInterface) {
                        return $this->handleAccountMigration($provider);
                    }

                    $this->logger->error('Authentication migratittion required but provider does not support interface' . get_class($provider));
                    break;
                case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                default:
                    $this->logger->debug('Authentication failed against: ' . get_class($provider));
                    break;
            }
        }
        return $this->handleAuthenticationFail();
    }

    /**
     * Handle account migration
     * @param ilAuthProvider $provider
     */
    protected function handleAccountMigration(ilAuthProviderAccountMigrationInterface $provider): bool
    {
        $this->logger->debug('Trigger auth mode: ' . $provider->getTriggerAuthMode());
        $this->logger->debug('Desired auth mode: ' . $provider->getUserAuthModeName());
        $this->logger->debug('External account: ' . $provider->getExternalAccountName());

        $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
        #$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);

        ilSession::set(static::MIG_TRIGGER_AUTHMODE, $provider->getTriggerAuthMode());
        ilSession::set(static::MIG_DESIRED_AUTHMODE, $provider->getUserAuthModeName());
        ilSession::set(static::MIG_EXTERNAL_ACCOUNT, $provider->getExternalAccountName());

        $this->logger->dump(ilSession::dumpToString(), ilLogLevel::DEBUG);

        return true;
    }

    /**
     * Handle successful authentication
     */
    protected function handleAuthenticationSuccess(ilAuthProviderInterface $provider): bool
    {
        $user = ilObjectFactory::getInstanceByObjId($this->getStatus()->getAuthenticatedUserId(), false);

        // reset expired status
        $this->getAuthSession()->setExpired(false);

        if (!$user instanceof ilObjUser) {
            $this->logger->error('Cannot instantiate user account with id: ' . $this->getStatus()->getAuthenticatedUserId());
            $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
            $this->getStatus()->setReason('auth_err_invalid_user_account');
            return false;
        }

        if (!$this->checkExceededLoginAttempts($user)) {
            $this->logger->info('Authentication failed for inactive user with id and too may login attempts: ' . $this->getStatus()->getAuthenticatedUserId());
            $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
            $this->getStatus()->setReason('auth_err_login_attempts_deactivation');
            return false;
        }

        if (!$this->checkActivation($user)) {
            $this->logger->info('Authentication failed for inactive user with id: ' . $this->getStatus()->getAuthenticatedUserId());
            $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
            $this->getStatus()->setReason('err_inactive');
            return false;
        }

        // time limit
        if (!$this->checkTimeLimit($user)) {
            $this->logger->info('Authentication failed (time limit restriction) for user with id: ' . $this->getStatus()->getAuthenticatedUserId());

            if ($this->settings->get('user_reactivate_code')) {
                $this->logger->debug('Accout reactivation codes are active');
                $this->getStatus()->setStatus(ilAuthStatus::STATUS_CODE_ACTIVATION_REQUIRED);
            } else {
                $this->logger->debug('Accout reactivation codes are inactive');
                $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
                $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
            }
            $this->getStatus()->setReason('time_limit_reached');
            return false;
        }

        // ip check
        if (!$this->checkIp($user)) {
            $this->logger->info('Authentication failed (wrong ip) for user with id: ' . $this->getStatus()->getAuthenticatedUserId());
            $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);

            $this->getStatus()->setTranslatedReason(
                sprintf(
                    $this->lng->txt('wrong_ip_detected'),
                    $_SERVER['REMOTE_ADDR']
                )
            );
            return false;
        }

        // check simultaneos logins
        $this->logger->debug('Check simutaneous login');
        if (!$this->checkSimultaneousLogins($user)) {
            $this->logger->info('Authentication failed: simultaneous logins forbidden for user: ' . $this->getStatus()->getAuthenticatedUserId());
            $this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            $this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
            $this->getStatus()->setReason('simultaneous_login_detected');
            return false;
        }

        // check if profile is complete
        if (
            ilUserProfile::isProfileIncomplete($user) &&
            ilAuthFactory::getContext() !== ilAuthFactory::CONTEXT_ECS &&
            ilContext::getType() !== ilContext::CONTEXT_LTI_PROVIDER
        ) {
            ilLoggerFactory::getLogger('auth')->info('User profile is incomplete.');
            $user->setProfileIncomplete(true);
            $user->update();
        }

        // redirects in case of error (session pool limit reached)
        ilSessionControl::handleLoginEvent($user->getLogin(), $this->getAuthSession());


        // @todo move to event handling
        ilOnlineTracking::addUser($user->getId());

        // @todo move to event handling
        ilObjForum::_updateOldAccess($user->getId());

        $security_settings = ilSecuritySettings::_getInstance();

        // determine first login of user for setting an indicator
        // which still is available in PersonalDesktop, Repository, ...
        // (last login date is set to current date in next step)
        if (
            $security_settings->isPasswordChangeOnFirstLoginEnabled() &&
            $user->getLastLogin() === ''
        ) {
            $user->resetLastPasswordChange();
        }
        $user->refreshLogin();

        // reset counter for failed logins
        ilObjUser::_resetLoginAttempts($user->getId());


        $this->logger->info('Successfully authenticated: ' . ilObjUser::_lookupLogin($this->getStatus()->getAuthenticatedUserId()));
        $this->getAuthSession()->setAuthenticated(true, $this->getStatus()->getAuthenticatedUserId());

        ilInitialisation::initUserAccount();

        ilSession::set('orig_request_target', '');
        $user->hasToAcceptTermsOfServiceInSession(true);


        // --- anonymous/registered user
        if (PHP_SAPI !=="cli") {
            $this->logger->info(
                'logged in as ' . $user->getLogin() .
            ', remote:' . $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'] .
            ', server:' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']
            );
        } else {
            $this->logger->info(
                'logged in as ' . $user->getLogin() . ' from CLI'
            );
        }

        // finally raise event
        $this->ilAppEventHandler->raise(
            'Services/Authentication',
            'afterLogin',
            array(
                'username' => $user->getLogin())
        );

        return true;
    }

    /**
     * Check activation
     */
    protected function checkActivation(ilObjUser $user): bool
    {
        return $user->getActive();
    }

    protected function checkExceededLoginAttempts(ilObjUser $user): bool
    {
        if ($user->getId() === ANONYMOUS_USER_ID) {
            return true;
        }

        $isInactive = !$user->getActive();
        if (!$isInactive) {
            return true;
        }

        $security = ilSecuritySettings::_getInstance();
        $maxLoginAttempts = $security->getLoginMaxAttempts();

        if (!$maxLoginAttempts) {
            return true;
        }

        $numLoginAttempts = \ilObjUser::_getLoginAttempts($user->getId());

        return $numLoginAttempts < $maxLoginAttempts;
    }

    /**
     * Check time limit
     */
    protected function checkTimeLimit(ilObjUser $user): bool
    {
        return $user->checkTimeLimit();
    }

    /**
     * Check ip
     */
    protected function checkIp(ilObjUser $user): bool
    {
        $clientip = $user->getClientIP();
        if (trim($clientip) !== "") {
            $clientip = preg_replace("/[^0-9.?*,:]+/", "", $clientip);
            $clientip = str_replace([".", "?", "*", ","], ["\\.", "[0-9]", "[0-9]*", "|"], $clientip);

            ilLoggerFactory::getLogger('auth')->debug('Check ip ' . $clientip . ' against ' . $_SERVER['REMOTE_ADDR']);

            if (!preg_match("/^" . $clientip . "$/", $_SERVER["REMOTE_ADDR"])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check simultaneous logins
     */
    protected function checkSimultaneousLogins(ilObjUser $user): bool
    {
        $this->logger->debug('Setting prevent simultaneous session is: ' . $this->settings->get('ps_prevent_simultaneous_logins'));
        return !($this->settings->get('ps_prevent_simultaneous_logins') &&
            ilObjUser::hasActiveSession($user->getId(), $this->getAuthSession()->getId()));
    }

    /**
     * Handle failed authenication
     */
    protected function handleAuthenticationFail(): bool
    {
        $this->logger->debug('Authentication failed for all authentication methods.');

        $user_id = ilObjUser::_lookupId($this->getCredentials()->getUsername());
        if (is_int($user_id) && $user_id !== ANONYMOUS_USER_ID) {
            ilObjUser::_incrementLoginAttempts($user_id);
            $login_attempts = ilObjUser::_getLoginAttempts($user_id);

            $this->logger->notice('Increased login attempts for user: ' . $this->getCredentials()->getUsername());

            $security = ilSecuritySettings::_getInstance();
            $max_attempts = $security->getLoginMaxAttempts();

            if ($max_attempts && $login_attempts >= $max_attempts) {
                $this->getStatus()->setReason('auth_err_login_attempts_deactivation');
                $this->logger->warning('User account set to inactive due to exceeded login attempts.');
                ilObjUser::_setUserInactive($user_id);
            }
        }
        return false;
    }
}
