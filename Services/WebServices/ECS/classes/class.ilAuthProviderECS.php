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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Services as HTTPServices;

/**
 * Auth prvider for ecs auth
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAuthProviderECS extends ilAuthProvider
{
    private ilIniFile $clientIniFile;
    private ilRbacAdmin $rbacAdmin;
    private ilSetting $setting;
    private ilLanguage $lng;
    private Refinery $refinery;
    private HTTPServices $http;
    private ilAuthSession $authSession;
    private ilCtrlInterface $ctrl;

    protected ?int $mid = null;
    protected ?string $abreviation = null;

    protected ilECSSetting $currentServer;
    protected ilECSServerSettings $servers;


    /**
     * Constructor
     * @param \ilAuthCredentials $credentials
     */
    public function __construct(\ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);

        global $DIC;

        $this->clientIniFile = $DIC->clientIni();
        $this->rbacAdmin = $DIC->rbac()->admin();
        $this->setting = $DIC->settings();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->authSession = $DIC['ilAuthSession'];
        $this->ctrl = $DIC->ctrl();

        $this->initECSServices();
    }

    /**
     * get abbreviation
     */
    public function getAbreviation(): string
    {
        return $this->abreviation;
    }

    /**
     * get mid
     */
    public function getMID(): int
    {
        return $this->mid;
    }

    public function setMID(int $a_mid): void
    {
        $this->mid = $a_mid;
    }

    /**
     * Set current server
     */
    public function setCurrentServer(ilECSSetting $server): void
    {
        $this->currentServer = $server;
    }

    /**
     * Get current server
     */
    public function getCurrentServer(): ilECSSetting
    {
        return $this->currentServer;
    }

    /**
     * Get server settings
     */
    public function getServerSettings(): ilECSServerSettings
    {
        return $this->servers;
    }


    /**
     * Try ecs authentication
     */
    public function doAuthentication(\ilAuthStatus $status): bool
    {
        $this->getLogger()->debug('Starting ECS authentication');
        if (!$this->getServerSettings()->activeServerExists()) {
            $this->getLogger()->warning('No active ecs server found. Aborting');
            $this->handleAuthenticationFail($status, 'err_wrong_login');
            return false;
        }

        // Iterate through all active ecs instances
        foreach ($this->getServerSettings()->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            $this->setCurrentServer($server);
            if ($this->validateHash()) {
                return $this->handleLoginByAuthMode($status);
            }
        }
        $this->getLogger()->warning('Could not validate ecs hash for any active server.');
        $this->handleAuthenticationFail($status, 'err_wrong_login');
        return false;
    }

    /**
     * Redirects to shibboleth login; to standard login page for LDAP based authentication
     * or authenticates/creates a local account
     */
    protected function handleLoginByAuthMode(ilAuthStatus $status): bool
    {
        $is_external_account = false;
        if ($this->http->wrapper()->query()->has('ecs_external_account')) {
            $is_external_account = $this->http->wrapper()->query()->retrieve(
                'ecs_external_account',
                $this->refinery->kindlyTo()->bool()
            );
        }
        $redirection_target = '';
        if ($this->http->wrapper()->query()->has('target')) {
            $redirection_target = $this->http->wrapper()->query()->retrieve(
                'target',
                $this->refinery->kindlyTo()->string()
            );
        }
        $part_settings = new ilECSParticipantSetting(
            $this->getCurrentServer()->getServerId(),
            $this->getMID()
        );
        if ($this->resumeCurrentSession()) {
            $this->getLogger()->debug('Continuing current user session');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId($this->authSession->getUserId());
            return true;
        }
        if (
            $is_external_account &&
            $part_settings->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE
        ) {
            $this->getLogger()->info('ILIAS login page authentication required.');
            ilSession::set('success', $this->lng->txt('ecs_login_success_ilias'));
            $this->initRemoteUserWithRemoteId();
            $this->ctrl->redirectToURL('login.php?target=' . $redirection_target);
            return false;
        }
        if (
            $is_external_account &&
            $part_settings->getIncomingAuthType() === ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH
        ) {
            $this->getLogger()->info('Redirect to shibboleth authentication');
            $this->initRemoteUserWithRemoteId();
            $this->ctrl->redirectToURL('shib_login.php?target=' . $redirection_target);
        }
        if ($part_settings->areIncomingLocalAccountsSupported()) {
            // handle successful authentication
            $new_usr_id = $this->handleLogin();
            $this->getLogger()->info('ECS authentication successful.');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
            $status->setAuthenticatedUserId($new_usr_id);
            return true;
        }
        $this->handleAuthenticationFail($status, 'err_wrong_login');
        return false;
    }

    protected function resumeCurrentSession(): bool
    {
        $session_user_id = $this->authSession->getUserId();
        if (!$session_user_id || $session_user_id == ANONYMOUS_USER_ID) {
            $this->getLogger()->debug('No valid session found');
            $this->authSession->setAuthenticated(false, ANONYMOUS_USER_ID);
            return false;
        }
        $session_ext_account = ilObjUser::_lookupExternalAccount($session_user_id);
        $user = new ilECSUser($this->http->request()->getQueryParams());
        $this->getLogger()->debug('ECS user name: ' . $user->getLogin());
        $this->getLogger()->debug('Session external account: ' . $session_ext_account);
        if (!$session_ext_account || strcmp($user->getLogin(), $session_ext_account) !== 0) {
            $this->getLogger()->debug('No matching session found. Terminating current user session.');
            $this->authSession->setAuthenticated(false, ANONYMOUS_USER_ID);
            return false;
        } else {
            // assign to ECS global role
            $this->rbacAdmin->assignUser($this->getCurrentServer()->getGlobalRole(), $this->authSession->getUserId());
        }
        return true;
    }


    /**
     * Called from base class after successful login
     */
    public function handleLogin()
    {
        $user = new ilECSUser($this->http->request()->getQueryParams());

        if (!$usr_id = ilObject::_lookupObjIdByImportId($user->getImportId())) {
            $username = $this->createUser($user);
        } else {
            $username = $this->updateUser($user, $usr_id);
        }

        // set user imported
        $import = new ilECSImport($this->getCurrentServer()->getServerId(), $usr_id);
        $import->save();

        // Store remote user data
        $remoteUserRepository = new ilECSRemoteUserRepository();
        $remoteUserRepository->createIfNotExisting(
            $this->getCurrentServer()->getServerId(),
            $this->getMID(),
            ilObjUser::_lookupId($username),
            $user->getImportId()
        );

        $this->getLogger()->info('Current user is: ' . $username);

        return ilObjUser::_lookupId($username);
    }

    public function initRemoteUserWithRemoteId(): void
    {
        $user = new ilECSUser($this->http->request()->getQueryParams());

        // Store remote user data
        $remoteUserRepository = new ilECSRemoteUserRepository();
        $remoteUserRepository->createIfRemoteUserNotExisting(
            $this->getCurrentServer()->getServerId(),
            $this->getMID(),
            0,
            $user->getLogin()
        );
    }

    /**
     * Validate ECS hash
     */
    public function validateHash(): bool
    {
        // fetch hash
        $hash = "";
        if ($this->http->wrapper()->query()->has('ecs_hash')) {
            $hash = $this->http->wrapper()->query()->retrieve(
                'ecs_hash',
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($this->http->wrapper()->query()->has('ecs_hash_url')) {
            $hashurl = urldecode(
                $this->http->wrapper()->query()->retrieve(
                    'ecs_hash_url',
                    $this->refinery->kindlyTo()->string()
                )
            );
            $hash = basename(parse_url($hashurl, PHP_URL_PATH));
        }

        $this->getLogger()->info('Using ecs hash: ' . $hash);
        // Check if hash is valid ...
        try {
            $connector = new ilECSConnector($this->getCurrentServer());
            $res = $connector->getAuth($hash);
            $auths = $res->getResult();

            $this->getLogger()->dump($auths, ilLogLevel::DEBUG);

            if ($auths->pid) {
                try {
                    $reader = ilECSCommunityReader::getInstanceByServerId($this->getCurrentServer()->getServerId());
                    foreach ($reader->getParticipantsByPid($auths->pid) as $participant) {
                        if ($participant->getOrganisation() instanceof \ilECSOrganisation) {
                            $this->abreviation = $participant->getOrganisation()->getAbbreviation();
                            break;
                        }
                    }
                    if (!$this->abreviation) {
                        $this->abreviation = $auths->abbr;
                    }
                } catch (Exception $e) {
                    $this->getLogger()->warning('Authentication failed with message: ' . $e->getMessage());
                    return false;
                }
            } else {
                $this->abreviation = $auths->abbr;
            }

            $this->getLogger()->debug('Got abbreviation: ' . $this->abreviation);
        } catch (ilECSConnectorException $e) {
            $this->getLogger()->warning('Authentication failed with message: ' . $e->getMessage());
            return false;
        }

        // read current mid
        try {
            $connector = new ilECSConnector($this->getCurrentServer());
            $details = $connector->getAuth($hash, true);

            $this->getLogger()->dump($details, ilLogLevel::DEBUG);
            $this->getLogger()->debug('Token create for mid: ' . $details->getFirstSender());

            $this->setMID($details->getFirstSender());
        } catch (ilECSConnectorException $e) {
            $this->getLogger()->warning('Receiving mid failed with message: ' . $e->getMessage());
            return false;
        }
        return true;
    }


    /**
     * Init ECS Services
     */
    private function initECSServices(): void
    {
        $this->servers = ilECSServerSettings::getInstance();
    }

    /**
     * create new user
     */
    protected function createUser(ilECSUser $user): string
    {
        $userObj = new ilObjUser();
        $userObj->setOwner(SYSTEM_USER_ID);

        $local_user = ilAuthUtils::_generateLogin($this->getAbreviation() . '_' . $user->getLogin());

        $newUser["login"] = $local_user;
        $newUser["firstname"] = $user->getFirstname();
        $newUser["lastname"] = $user->getLastname();
        $newUser['email'] = $user->getEmail();
        $newUser['institution'] = $user->getInstitution();

        // set "plain md5" password (= no valid password)
        $newUser["passwd"] = "";
        $newUser["passwd_type"] = ilObjUser::PASSWD_CRYPTED;

        $newUser["auth_mode"] = "ecs";
        $newUser["profile_incomplete"] = 0;

        // system data
        $userObj->assignData($newUser);
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());

        // set user language to system language
        $userObj->setLanguage($this->setting->get("language"));

        // Time limit
        $userObj->setTimeLimitOwner(7);
        $userObj->setTimeLimitUnlimited(false);
        $userObj->setTimeLimitFrom(time() - 5);
        $userObj->setTimeLimitUntil(time() + (int) $this->clientIniFile->readVariable("session", "expire"));

        // Create user in DB
        $userObj->setOwner(6);
        $userObj->create();
        $userObj->setActive(true);
        $userObj->updateOwner();
        $userObj->saveAsNew();
        $userObj->writePrefs();

        if ($this->getCurrentServer()->getGlobalRole()) {
            $this->rbacAdmin->assignUser($this->getCurrentServer()->getGlobalRole(), $userObj->getId());
        }
        ilObject::_writeImportId($userObj->getId(), $user->getImportId());

        $this->getLogger()->info('Created new remote user with usr_id: ' . $user->getImportId());

        // Send Mail
        #$this->sendNotification($userObj);
        $this->resetMailOptions($userObj->getId());

        return $userObj->getLogin();
    }

    /**
     * update existing user
     */
    protected function updateUser(ilECSUser $user, int $a_local_user_id): string
    {
        $user_obj = new ilObjUser($a_local_user_id);
        $user_obj->setFirstname($user->getFirstname());
        $user_obj->setLastname($user->getLastname());
        $user_obj->setEmail($user->getEmail());
        $user_obj->setInstitution($user->getInstitution());
        $user_obj->setActive(true);

        $until = $user_obj->getTimeLimitUntil();

        if ($until < (time() + (int) $this->clientIniFile->readVariable('session', 'expire'))) {
            $user_obj->setTimeLimitFrom(time() - 60);
            $user_obj->setTimeLimitUntil(time() + (int) $this->clientIniFile->readVariable("session", "expire"));
        }
        $user_obj->update();
        $user_obj->refreshLogin();

        if ($this->getCurrentServer()->getGlobalRole()) {
            $this->rbacAdmin->assignUser(
                $this->getCurrentServer()->getGlobalRole(),
                $user_obj->getId()
            );
        }

        $this->resetMailOptions($a_local_user_id);

        $this->getLogger()->debug('Finished update of remote user with usr_id: ' . $user->getImportId());
        return $user_obj->getLogin();
    }

    /**
     * Reset mail options to "local only"
     *
     */
    protected function resetMailOptions(int $a_usr_id): void
    {
        $options = new ilMailOptions($a_usr_id);
        $options->setIncomingType(ilMailOptions::INCOMING_LOCAL);
        $options->updateOptions();
    }
}
