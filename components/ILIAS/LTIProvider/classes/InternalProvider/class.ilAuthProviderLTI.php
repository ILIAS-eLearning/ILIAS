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

/**
 * OAuth based lti authentication
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author Stefan Schneider
 */
class ilAuthProviderLTI extends \ilAuthProvider implements \ilAuthProviderInterface
{
    public const AUTH_MODE_PREFIX = 'lti';
    private ?ilLTIDataConnector $dataConnector = null;
    private string $lti_context_id = "";
    private int $ref_id = 0;
    private ?ilLTITool $provider = null;
    private ?array $messageParameters = null;

    protected string $launchReturnUrl = "";

    private ?ilLogger $logger = null;

    /**
     * Constructor
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        parent::__construct($credentials);
        $this->logger = ilLoggerFactory::getLogger('ltis');
    }

    /**
     * Get auth mode by key
     * @param string $a_auth_mode
     * @return string auth_mode
     */
    public static function getAuthModeByKey(string $a_auth_key): string
    {
        $auth_arr = explode('_', $a_auth_key);
        if (count($auth_arr) > 1) {
            return 'lti_' . $auth_arr[1];
        }
        return 'lti';
    }

    /**
     * Get auth id by auth mode
     * @param string $a_auth_mode
     * @return int|string auth_mode
     */
    public static function getKeyByAuthMode(string $a_auth_mode)
    {
        $auth_arr = explode('_', $a_auth_mode);
        if (count($auth_arr) > 1) {
            return ilAuthUtils::AUTH_PROVIDER_LTI . '_' . $auth_arr[1];
        }
        return ilAuthUtils::AUTH_PROVIDER_LTI;
    }

    /**
     * get all active authmode server ids
     * @return array
     */
    public static function getActiveAuthModes(): array
    {
        global $ilDB;

        // move to connector
        $query = 'SELECT consumer_pk from lti2_consumer where enabled = ' . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);

        $sids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sids[] = $row->consumer_pk;
        }
        return $sids;
    }

    /**
     * @return array
     */
    public static function getAuthModes(): array
    {
        global $ilDB;

        // move to connector
        $query = 'SELECT distinct(consumer_pk) consumer_pk from lti2_consumer';
        $res = $ilDB->query($query);

        $sids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sids[] = $row->consumer_pk;
        }
        return $sids;
    }

    /**
     * Lookup consumer title
     * @param int $a_sid
     * @return string
     */
    public static function lookupConsumer(int $a_sid): string
    {
        $connector = new ilLTIDataConnector();
        $consumer = ilLTIPlatform::fromRecordId($a_sid, $connector);

        $object_ref = $consumer->getRefId();
        $object_title = ilObject2::_lookupTitle(ilObject2::_lookupObjectId($object_ref));
        return $consumer->getTitle() . " / " . $object_title;
    }

    /**
     * Get auth id by auth mode
     * @param string $a_auth_mode
     * @return int|null
     */
    public static function getServerIdByAuthMode(string $a_auth_mode): ?int
    {
        if (self::isAuthModeLTI($a_auth_mode)) {
            $auth_arr = explode('_', $a_auth_mode);
            return (int) $auth_arr[1];
        }
        return null;
    }

    /**
     * Check if user auth mode is LTI
     * @param string $a_auth_mode
     * @return bool
     */
    public static function isAuthModeLTI(string $a_auth_mode): bool
    {
        if (!$a_auth_mode) {
            ilLoggerFactory::getLogger('ltis')->warning('No auth mode given.');
            return false;
        }
        $auth_arr = explode('_', $a_auth_mode);
        return ($auth_arr[0] == ilAuthUtils::AUTH_PROVIDER_LTI) and $auth_arr[1];
    }

    /**
     * find consumer key id
     * @param string $a_oauth_consumer_key
     * @return int
     */
    protected function findAuthKeyId(string $a_oauth_consumer_key): int
    {
        global $ilDB;

        $query = 'SELECT consumer_pk from lti2_consumer where consumer_key = ' . $ilDB->quote(
            $a_oauth_consumer_key,
            'text'
        );
        // $query = 'SELECT id from lti_ext_consumer where consumer_key = '.$ilDB->quote($a_oauth_consumer_key,'text');
        $this->getLogger()->debug($query);
        $res = $ilDB->query($query);

        $lti_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lti_id = $row->consumer_pk;
            // $lti_id = $row->id;
        }
        $this->getLogger()->debug('External consumer key is: ' . (int) $lti_id);
        return $lti_id;
    }

    /**
     * find lti id
     * @param int $a_lti_id
     * @return string
     */
    protected function findAuthPrefix(int $a_lti_id): string
    {
        global $ilDB;

        $query = 'SELECT prefix from lti_ext_consumer where id = ' . $ilDB->quote($a_lti_id, 'integer');
        $this->getLogger()->debug($query);
        $res = $ilDB->query($query);

        // $prefix = 'lti'.$a_lti_id.'_';
        $prefix = '';
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $prefix = $row->prefix;
        }
        $this->getLogger()->debug('LTI prefix: ' . $prefix);
        return $prefix;
    }

    /**
     * find global role of consumer
     * @param int $a_lti_id
     * @return int|null
     */
    protected function findGlobalRole(int $a_lti_id): ?int
    {
        global $ilDB;

        $query = 'SELECT role from lti_ext_consumer where id = ' . $ilDB->quote($a_lti_id, 'integer');
        $this->getLogger()->debug($query);
        $res = $ilDB->query($query);

        $role = null;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $role = (int) $row->role;
        }
        $this->getLogger()->debug('LTI role: ' . $role);
        return $role;
    }

    /**
     * Do authentication
     * @param ilAuthStatus $status
     * @return bool
     * @throws ilPasswordException
     * @throws ilUserException
     */
    public function doAuthentication(\ilAuthStatus $status): bool
    {
        global $DIC;
        $post = [];

        $lti_provider = new ilLTITool(new ilLTIDataConnector());

        if ($DIC->http()->wrapper()->post()->has('launch_presentation_return_url')) {
            $this->launchReturnUrl = $DIC->http()->wrapper()->post()->retrieve('launch_presentation_return_url', $DIC->refinery()->kindlyTo()->string());
            setcookie("launch_presentation_return_url", $this->launchReturnUrl, time() + 86400, "/", "", true, true);
            $this->logger->info("Setting launch_presentation_return_url in cookie storage " . $this->launchReturnUrl);
        }
        $lti_provider->handleRequest();
        $this->provider = $lti_provider;
        $this->messageParameters = $this->provider->getMessageParameters();

        if (!$DIC->http()->wrapper()->post()->has('launch_presentation_return_url')) {
            $this->launchReturnUrl = $_COOKIE['launch_presentation_return_url'] ?? "";
            $this->logger->info("Catching launch_presentation_return_url from cookies" . $this->launchReturnUrl);
            $post["launch_presentation_return_url"] = $this->launchReturnUrl;
        }

        if (!$lti_provider->ok) {
            $this->getLogger()->info('LTI authentication failed with message: ' . $lti_provider->reason);
            $status->setReason($lti_provider->reason);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        } else {
            $this->getLogger()->debug('LTI authentication success');
        }

        if (empty($this->messageParameters)) {
            $status->setReason('empty_lti_message_parameters');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        $platform = ilLTIPlatform::fromConsumerKey($this->provider->platform->getKey(), $this->provider->platform->getDataConnector());
        ilSession::clear("lti_context_ids");
        $this->ref_id = $platform->getRefId();

        $lti_context_ids = ilSession::get('lti_context_ids');

        if (isset($lti_context_ids) && is_array($lti_context_ids)) {
            if (!in_array($this->ref_id, $lti_context_ids)) {
                $this->getLogger()->debug("push new lti ref_id: " . $this->ref_id);
                $lti_context_ids[] = $this->ref_id;
                ilSession::set('lti_context_ids', $lti_context_ids);
                $this->getLogger()->debug((string) var_export(ilSession::get('lti_context_ids'), true));
            }
        } else {
            $this->getLogger()->debug("lti_context_ids is not set. Create new array...");
            ilSession::set('lti_context_ids', [$this->ref_id]);
            $this->getLogger()->debug((string) var_export(ilSession::get('lti_context_ids'), true));
        }

        if (!empty($this->messageParameters['launch_presentation_return_url'])) {
            $post['launch_presentation_return_url'] = $this->messageParameters['launch_presentation_return_url'];
        }
        if (!empty($this->messageParameters['launch_presentation_css_url'])) {
            $post['launch_presentation_css_url'] = $this->messageParameters['launch_presentation_css_url'];
        }
        if (!empty($this->messageParameters['resource_link_title'])) {
            $post['resource_link_title'] = $this->messageParameters['resource_link_title'];
        }

        ilSession::set('lti_' . $this->ref_id . '_post_data', $post);

        /** @var ilObjectDefinition $obj_definition */
        $obj_definition = $DIC["objDefinition"];

        ilSession::set('lti_init_target', $obj_definition->getClassName(ilObject::_lookupType($this->ref_id, true)) . '_' . $this->ref_id);

        if (!$platform->enabled) {
            $this->getLogger()->warning('Consumer is not enabled');
            $status->setReason('lti_consumer_inactive');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        if (!$platform->getActive()) {
            $this->getLogger()->warning('Consumer is not active');
            $status->setReason('lti_consumer_inactive');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        $lti_id = $platform->getExtConsumerId();
        if (!$lti_id) {
            $status->setReason('lti_auth_failed_invalid_key');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        $this->getLogger()->debug('Using prefix:' . $platform->getPrefix());

        $this->getCredentials()->setUsername($this->messageParameters['user_id']);

        $internal_account = $this->findUserId(
            $this->getCredentials()->getUsername(),
            (string) $lti_id,
            $platform->getPrefix()
        );

        if ($internal_account) {
            $this->updateUser($internal_account, $platform);
        } else {
            $internal_account = $this->createUser($platform);
        }

        $this->handleLocalRoleAssignments($internal_account, $platform, $this->ref_id);

        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
        $status->setAuthenticatedUserId($internal_account);

        return true;
    }

    /**
     * Find user by auth mode and lti id
     * @param string $a_oauth_user
     * @param string $a_oauth_id
     * @param string $a_user_prefix
     * @return int
     */
    protected function findUserId(string $a_oauth_user, string $a_oauth_id, string $a_user_prefix): int
    {
        $user_name = ilObjUser::_checkExternalAuthAccount(
            self::AUTH_MODE_PREFIX . '_' . $a_oauth_id,
            $a_oauth_user
        );
        $user_id = 0;
        if ($user_name) {
            $user_id = ilObjUser::_lookupId($user_name);
        }
        $this->getLogger()->debug('Found user with auth mode lti_' . $a_oauth_id . ' with user_id: ' . $user_id);
        return $user_id;
    }

    /**
     * update existing user
     * @access protected
     * @param int           $a_local_user_id
     * @param ilLTIPlatform $consumer
     * @return int
     */
    protected function updateUser(int $a_local_user_id, ilLTIPlatform $consumer): int
    {
        global $ilClientIniFile, $DIC;
        //        if (empty($this->messageParameters)) {
        //            $status->setReason('empty_lti_message_parameters');
        //            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
        //            return false;
        //        }
        $user_obj = new ilObjUser($a_local_user_id);
        if (isset($this->messageParameters['lis_person_name_given'])) {
            $user_obj->setFirstname($this->messageParameters['lis_person_name_given']);
        } else {
            $user_obj->setFirstname('-');
        }
        if (isset($this->messageParameters['lis_person_name_family'])) {
            $user_obj->setLastname($this->messageParameters['lis_person_name_family']);
        } else {
            $user_obj->setLastname('-');
        }
        $user_obj->setEmail($this->messageParameters['lis_person_contact_email_primary']);

        $user_obj->setActive(true);

        $until = $user_obj->getTimeLimitUntil();

        if ($until < (time() + (int) $ilClientIniFile->readVariable('session', 'expire'))) {
            $user_obj->setTimeLimitFrom(time() - 60);
            $user_obj->setTimeLimitUntil(time() + (int) $ilClientIniFile->readVariable("session", "expire"));
        }
        $user_obj->refreshLogin();
        $user_obj->update();

        $GLOBALS['DIC']->rbac()->admin()->assignUser($consumer->getRole(), $user_obj->getId());
        $this->getLogger()->debug('Assigned user to: ' . $consumer->getRole());

        $this->getLogger()->info('Update of lti user with uid: ' . $user_obj->getId() . ' and login: ' . $user_obj->getLogin());
        return $user_obj->getId();
    }

    /**
     * create new user
     * @access protected
     * @param ilLTIPlatform $consumer
     * @return int
     * @throws ilPasswordException
     * @throws ilUserException
     */
    protected function createUser(ilLTIPlatform $consumer): int
    {
        global $ilClientIniFile, $DIC;
        //        if (empty($this->messageParameters)) {
        //            $status->setReason('empty_lti_message_parameters');
        //            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
        //            return false;
        //        }
        $userObj = new ilObjUser();
        $local_user = ilAuthUtils::_generateLogin($consumer->getPrefix() . '_' . $this->getCredentials()->getUsername());

        $newUser["login"] = $local_user;
        if (isset($this->messageParameters['lis_person_name_given'])) {
            $newUser["firstname"] = $this->messageParameters['lis_person_name_given'];
        } else {
            $newUser["firstname"] = '-';
        }
        if (isset($this->messageParameters['lis_person_name_family'])) {
            $newUser["lastname"] = $this->messageParameters['lis_person_name_family'];
        } else {
            $newUser["lastname"] = '-';
        }
        $newUser['email'] = $this->messageParameters['lis_person_contact_email_primary'];

        // set "plain md5" password (= no valid password)
        //        $newUser["passwd"] = "";
        $newUser["passwd_type"] = ilObjUser::PASSWD_CRYPTED;

        $newUser["auth_mode"] = 'lti_' . $consumer->getExtConsumerId();
        $newUser['ext_account'] = $this->getCredentials()->getUsername();
        $newUser["profile_incomplete"] = 0;

        // ILIAS 8
        //check
        $newUser["gender"] = 'n';
        $newUser["title"] = null;
        $newUser["birthday"] = null;
        $newUser["institution"] = null;
        $newUser["department"] = null;
        $newUser["street"] = null;
        $newUser["city"] = null;
        $newUser["zipcode"] = null;
        $newUser["country"] = null;
        $newUser["sel_country"] = null;
        $newUser["phone_office"] = null;
        $newUser["phone_home"] = null;
        $newUser["phone_mobile"] = null;
        $newUser["fax"] = null;
        $newUser["matriculation"] = null;
        $newUser["second_email"] = null;
        $newUser["hobby"] = null;
        $newUser["client_ip"] = null;
        $newUser["passwd_salt"] = null;//$newUser->getPasswordSalt();
        $newUser["latitude"] = null;
        $newUser["longitude"] = null;
        $newUser["loc_zoom"] = null;
        $newUser["last_login"] = null;
        $newUser["first_login"] = null;
        $newUser["last_profile_prompt"] = null;
        $newUser["last_update"] = ilUtil::now();
        $newUser["create_date"] = ilUtil::now();
        $newUser["referral_comment"] = null;
        $newUser["approve_date"] = null;
        $newUser["agree_date"] = null;
        $newUser["inactivation_date"] = null;
        $newUser["time_limit_from"] = null;
        $newUser["time_limit_until"] = null;
        $newUser["is_self_registered"] = null;
        //end to check

        $newUser["passwd_enc_type"] = "";
        $newUser["active"] = true;
        $newUser["time_limit_owner"] = 7;
        $newUser["time_limit_unlimited"] = 0;
        $newUser["time_limit_message"] = 0;
        $newUser["passwd"] = " ";
        //        $newUser["last_update"]

        // system data
        $userObj->assignData($newUser);
        $userObj->setTitle($userObj->getFullname());
        $userObj->setDescription($userObj->getEmail());

        // set user language
        $userObj->setLanguage($consumer->getLanguage());

        // Time limit
        $userObj->setTimeLimitOwner(7);
        $userObj->setTimeLimitUnlimited(false);
        $userObj->setTimeLimitFrom(time() - 5);
        //        todo ?
        $userObj->setTimeLimitUntil(time() + (int) $ilClientIniFile->readVariable("session", "expire"));

        // Create user in DB
        $userObj->setOwner(6);
        $userObj->create();
        $userObj->setActive(true);
        //        $userObj->updateOwner();
        $userObj->setLastPasswordChangeTS(time());
        $userObj->saveAsNew();
        $userObj->writePrefs();

        $GLOBALS['DIC']->rbac()->admin()->assignUser($consumer->getRole(), $userObj->getId());

        $this->getLogger()->info('Created new lti user with uid: ' . $userObj->getId() . ' and login: ' . $userObj->getLogin());
        return $userObj->getId();
    }

    protected function handleLocalRoleAssignments(int $user_id, ilLTIPlatform $consumer, int $target_ref_id, ?int $default_rol_id = null): bool
    {
        global $DIC;
        $this->getLogger()->info('$target_ref_id: ' . $target_ref_id);
        if (!$target_ref_id) {
            $this->getLogger()->warning('No target id given');
            return false;
        }

        $obj_settings = new ilLTIProviderObjectSetting($target_ref_id, $consumer->getExtConsumerId());

        $roles = $this->messageParameters['roles'] ?? '';

        if (!is_string($roles) || empty($roles)) {
            $this->getLogger()->warning('No role information given or invalid role format.');
            return false;
        }

        $this->getLogger()->info("Deassigning all roles for user: " . $user_id);
        $DIC->rbac()->admin()->deassignUser($obj_settings->getTutorRole(), $user_id);
        $DIC->rbac()->admin()->deassignUser($obj_settings->getMemberRole(), $user_id);
        $DIC->rbac()->admin()->deassignUser($obj_settings->getAdminRole(), $user_id);

        $role_arr = is_array($roles) ? $roles : explode(',', $roles);

        $this->getLogger()->info('Recieved roles: ' . implode(', ', $role_arr));

        $tree = $DIC->repositoryTree();
        $parent = $tree->getParentId($target_ref_id);
        if ($parent != 1) {
            $this->handleLocalRoleAssignments($user_id, $consumer, $parent, $obj_settings->getMemberRole());
        }
        foreach ($role_arr as $role) {
            $role = trim($role);
            $local_role_id = $this->mapLTIRoleToLocalRole($role, $obj_settings) == 0 && $default_rol_id != null ? $default_rol_id : $this->mapLTIRoleToLocalRole($role, $obj_settings);
            if (isset($local_role_id)) {
                $this->getLogger()->info('Assigning local role ID: ' . $local_role_id . ' for LTI role: ' . $role . ' to user ID: ' . $user_id);
                $DIC->rbac()->admin()->assignUser($local_role_id, $user_id);
            } else {
                $this->getLogger()->info('No local role mapping found for LTI role: ' . $role);
            }
        }

        return true;
    }

    /**
     * Maps an LTI role (URI or simple name) to a local ILIAS role ID.
     *
     * @param string $lti_role
     * @param ilLTIProviderObjectSetting $settings
     * @return int|null The ILIAS role ID, or null if no mapping is found.
     */
    protected function mapLTIRoleToLocalRole(string $lti_role, ilLTIProviderObjectSetting $settings): ?int
    {
        // Prioritize more specific roles (sub-roles)
        $role_map = [
            // System Roles
            'http://purl.imsglobal.org/vocab/lti/system/person#TestUser' => null, // Example: No mapping for TestUser
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#Administrator' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#None' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#AccountAdmin' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#Creator' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#SysAdmin' => null,  // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#SysSupport' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/system/person#User' => null, // No direct mapping

            // Institution Roles
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Administrator' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Faculty' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Guest' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#None' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Other' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Staff' => null,  // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Student' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Alumni' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Instructor' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Learner' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Member' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Mentor' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#Observer' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/institution/person#ProspectiveStudent' => null, // No direct mapping

            // Context Roles (Main)
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Administrator' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership#ContentDeveloper' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Mentor' => null, // No direct mapping
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Manager' => $settings->getAdminRole(), // Potentially map to admin
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Member' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership#Officer' => null, // No direct mapping

            // Context Sub-Roles (TeachingAssistant)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistant' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantGroup' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantOffering' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantSection' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantSectionAssociation' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#TeachingAssistantTemplate' => $settings->getTutorRole(),
            // Context Sub-Roles (Grader)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#Grader' => $settings->getTutorRole(), // Map Grader to Tutor
            // Context Sub-Roles (GuestInstructor, Lecturer, PrimaryInstructor, SecondaryInstructor)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#GuestInstructor' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#Lecturer' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#PrimaryInstructor' => $settings->getTutorRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#SecondaryInstructor' => $settings->getTutorRole(),
            // Context Sub-Roles (ExternalInstructor)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Instructor#ExternalInstructor' => $settings->getTutorRole(),

            // Context Sub-Roles (ExternalLearner, GuestLearner, Learner, NonCreditLearner)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#ExternalLearner' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#GuestLearner' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#Learner' => $settings->getMemberRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Learner#NonCreditLearner' => $settings->getMemberRole(),

            // Context Sub-Roles (AreaManager, CourseCoordinator, ExternalObserver, Manager, Observer)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#AreaManager' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#CourseCoordinator' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#ExternalObserver' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#Manager' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Manager#Observer' => null,

            // Context Sub-Roles (Advisor, Auditor, ExternalAdvisor, ExternalAuditor, ExternalLearningFacilitator, ExternalMentor, ExternalReviewer, ExternalTutor, LearningFacilitator, Mentor, Reviewer, Tutor)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Advisor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Auditor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalAdvisor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalAuditor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalLearningFacilitator' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalMentor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalReviewer' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#ExternalTutor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#LearningFacilitator' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Mentor' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Reviewer' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Mentor#Tutor' => $settings->getTutorRole(), // Map Tutor to Tutor

            // Context Sub-Roles (Chair, Communications, Secretary, Treasurer, Vice-Chair)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Chair' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Communications' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Secretary' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Treasurer' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Officer#Vice-Chair' => null,

            // Context Sub-Roles (ContentDeveloper, ContentExpert, ExternalContentExpert, Librarian)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentDeveloper' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ContentExpert' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#ExternalContentExpert' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/ContentDeveloper#Librarian' => null,

            // Context Sub-Roles (Member)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Member#Member' => $settings->getMemberRole(),

            // Context Sub-Roles (Administrator, Developer, ExternalDeveloper, ExternalSupport, ExternalSystemAdministrator, Support, SystemAdministrator)
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Administrator' => $settings->getAdminRole(),
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Developer' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalDeveloper' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalSupport' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#ExternalSystemAdministrator' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#Support' => null,
            'http://purl.imsglobal.org/vocab/lis/v2/membership/Administrator#SystemAdministrator' => null,
        ];

        // LTI 1.0/1.1 simple names (supported for backward compatibility)
        $simple_name_map = [
            'Instructor' => $settings->getTutorRole(),
            'Learner' => $settings->getMemberRole(),
            'ContentDeveloper' => null,
            'Administrator' => $settings->getAdminRole(),
            'Mentor' => null,
            'Manager' => $settings->getAdminRole(),
            'Member' => $settings->getMemberRole(),
            'Officer' => null,
        ];


        if (isset($role_map[$lti_role])) {
            return $role_map[$lti_role];
        } elseif (isset($simple_name_map[$lti_role])) {
            // Check for simple names
            return $simple_name_map[$lti_role];
        }

        return null;
    }

}
