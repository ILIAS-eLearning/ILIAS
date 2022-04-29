<?php declare(strict_types=1);

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * OAuth based lti authentication
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author Stefan Schneider
 */
class ilAuthProviderLTI extends \ilAuthProvider implements \ilAuthProviderInterface
{
    const AUTH_MODE_PREFIX = 'lti';
    private ?ilLTIDataConnector $dataConnector = null;
    private string $lti_context_id = "";
    private int $ref_id = 0;

    /**
     * Get auth mode by key
     * @param string $a_auth_mode
     * @return string auth_mode
     */
    public static function getAuthModeByKey(string $a_auth_key) : string
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
    // TODO PHP8 Review: Union Types are not supported by PHP 7.4! int|string
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
    public static function getActiveAuthModes() : array
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
    public static function getAuthModes() : array
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
    public static function lookupConsumer(int $a_sid) : string
    {
        $connector = new ilLTIDataConnector();
        $consumer = ilLTIToolConsumer::fromRecordId($a_sid, $connector);
        return $consumer->getTitle();
    }

    /**
     * Get auth id by auth mode
     * @param string $a_auth_mode
     * @return int|null
     */
    public static function getServerIdByAuthMode(string $a_auth_mode) : ?int
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
    public static function isAuthModeLTI(string $a_auth_mode) : bool
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
    protected function findAuthKeyId(string $a_oauth_consumer_key) : int
    {
        global $ilDB;

        $query = 'SELECT consumer_pk from lti2_consumer where consumer_key256 = ' . $ilDB->quote(
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
    protected function findAuthPrefix(int $a_lti_id) : string
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
    protected function findGlobalRole(int $a_lti_id) : ?int
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
     * @param \ilAuthStatus $status
     * @return bool
     */
    public function doAuthentication(\ilAuthStatus $status) : bool
    {
        global $DIC;
        //fix for Ilias Consumer
        if ($DIC->http()->wrapper()->post()->has('launch_presentation_document_target') &&
            $DIC->http()->wrapper()->post()->retrieve(
                'launch_presentation_document_target',
                $DIC->refinery()->kindlyTo()->string()
            )) {
            // TODO PHP8 Review: Remove/Replace SuperGlobals
            $_POST['launch_presentation_document_target'] = 'window';
        }

        $this->dataConnector = new ilLTIDataConnector();

        $lti_provider = new ilLTIToolProvider($this->dataConnector);
        // $lti_provider = new ToolProvider\ToolProvider($this->dataConnector);
        $ok = true;
        $lti_provider->handleRequest();

        if (!$ok) {
            $this->getLogger()->info('LTI authentication failed with message: ' . $lti_provider->reason);
            $status->setReason($lti_provider->reason);
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        } else {
            $this->getLogger()->debug('LTI authentication success');
        }
        // if ($lti_provider->reason != "") die($lti_provider->reason);//ACHTUNG später Rückgabe prüfen

        // sm: this does only load the standard lti date connector, not the ilLTIToolConsumer with extended data, like prefix.
        $consumer = new ilLTIToolConsumer(
            $DIC->http()->wrapper()->post()->retrieve('oauth_consumer_key', $DIC->refinery()->kindlyTo()->string()),
            $this->dataConnector
        );

        /**
         * @var ilLTIToolConsumer
         */
        $consumer = ilLTIToolConsumer::fromRecordId(
            $consumer->getRecordId(),
            $this->dataConnector
        );

        $this->ref_id = $consumer->getRefId();
        // stores ref_ids of all lti consumer within active LTI User Session
        $lti_context_ids = ilSession::get('lti_context_ids');
        // if session object exists only add ref_id if not already exists
        if (isset($lti_context_ids) && is_array($lti_context_ids)) {
            if (!in_array($this->ref_id, $lti_context_ids)) {
                $this->getLogger()->debug("push new lti ref_id: " . $this->ref_id);
                $lti_context_ids[] = $this->ref_id;
                ilSession::set('lti_context_ids', $lti_context_ids);
                $this->getLogger()->debug((string) var_export(true), ilSession::get('lti_context_ids'));
            }
        } else {
            $this->getLogger()->debug("lti_context_ids is not set. Create new array...");
            ilSession::set('lti_context_ids', [$this->ref_id]);
            $this->getLogger()->debug((string) var_export(true), ilSession::get('lti_context_ids'));
        }

        // for testing external css

        // store POST into Consumer Session
        $post = (array) $DIC->http()->wrapper()->post();
        ilSession::set('lti_' . $this->ref_id . '_post_data', $post);
        ilSession::set('lti_init_target', ilObject::_lookupType($this->ref_id, true) . '_' . $this->ref_id);

        // lti service activation
        if (!$consumer->enabled) {
            $this->getLogger()->warning('Consumer is not enabled');
            $status->setReason('lti_consumer_inactive');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }
        // global activation status
        if (!$consumer->getActive()) {
            $this->getLogger()->warning('Consumer is not active');
            $status->setReason('lti_consumer_inactive');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        $lti_id = $consumer->getExtConsumerId();
        if (!$lti_id) {
            $status->setReason('lti_auth_failed_invalid_key');
            $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
            return false;
        }

        $this->getLogger()->debug('Using prefix:' . $consumer->getPrefix());

        $internal_account = $this->findUserId(
            $this->getCredentials()->getUsername(),
            (string) $lti_id,
            $consumer->getPrefix()
        );

        if ($internal_account) {
            $this->updateUser($internal_account, $consumer);
        } else {
            $internal_account = $this->createUser($consumer);
        }

        $this->handleLocalRoleAssignments($internal_account, $consumer);

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
    protected function findUserId(string $a_oauth_user, string $a_oauth_id, string $a_user_prefix) : int
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
     * @param int               $a_local_user_id
     * @param ilLTIToolConsumer $consumer
     * @return int
     */
    protected function updateUser(int $a_local_user_id, ilLTIToolConsumer $consumer) : int
    {
        global $ilClientIniFile, $DIC;

        $user_obj = new ilObjUser($a_local_user_id);
        $user_obj->setFirstname($DIC->http()->wrapper()->post()->retrieve(
            'lis_person_name_given',
            $DIC->refinery()->kindlyTo()->string()
        ));
        $user_obj->setLastname($DIC->http()->wrapper()->post()->retrieve(
            'lis_person_name_family',
            $DIC->refinery()->kindlyTo()->string()
        ));
        $user_obj->setEmail($DIC->http()->wrapper()->post()->retrieve(
            'lis_person_contact_email_primary',
            $DIC->refinery()->kindlyTo()->string()
        ));
        $user_obj->setActive(true);

        $until = $user_obj->getTimeLimitUntil();

        if ($until < (time() + (int) $ilClientIniFile->readVariable('session', 'expire'))) {
            $user_obj->setTimeLimitFrom(time() - 60);
            $user_obj->setTimeLimitUntil(time() + (int) $ilClientIniFile->readVariable("session", "expire"));
        }
        $user_obj->update();
        $user_obj->refreshLogin();

        $GLOBALS['DIC']->rbac()->admin()->assignUser($consumer->getRole(), $user_obj->getId());
        $this->getLogger()->debug('Assigned user to: ' . $consumer->getRole());

        $this->getLogger()->info('Update of lti user with uid: ' . $user_obj->getId() . ' and login: ' . $user_obj->getLogin());
        return $user_obj->getId();
    }

    /**
     * create new user
     * @access protected
     * @param ilLTIToolConsumer $consumer
     * @return int
     * @throws ilPasswordException
     * @throws ilUserException
     */
    protected function createUser(ilLTIToolConsumer $consumer) : int
    {
        global $ilClientIniFile, $DIC;

        $userObj = new ilObjUser();
        $local_user = ilAuthUtils::_generateLogin($consumer->getPrefix() . '_' . $this->getCredentials()->getUsername());

        $newUser["login"] = $local_user;
        $newUser["firstname"] = $DIC->http()->wrapper()->post()->retrieve(
            'lis_person_name_given',
            $DIC->refinery()->kindlyTo()->string()
        );
        $newUser["lastname"] = $DIC->http()->wrapper()->post()->retrieve(
            'lis_person_name_family',
            $DIC->refinery()->kindlyTo()->string()
        );
        $newUser['email'] = $DIC->http()->wrapper()->post()->retrieve(
            'lis_person_contact_email_primary',
            $DIC->refinery()->kindlyTo()->string()
        );

        // set "plain md5" password (= no valid password)
//        $newUser["passwd"] = "";
        $newUser["passwd_type"] = ilObjUser::PASSWD_CRYPTED;

        $newUser["auth_mode"] = 'lti_' . $consumer->getExtConsumerId();
        $newUser['ext_account'] = $this->getCredentials()->getUsername();
        $newUser["profile_incomplete"] = 0;

        // ILIAS 8
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

    protected function handleLocalRoleAssignments(int $user_id, ilLTIToolConsumer $consumer) : bool
    {
        global $DIC;
        //$target_ref_id = $_SESSION['lti_current_context_id'];
        $target_ref_id = $this->ref_id;
        $this->getLogger()->info('$target_ref_id: ' . $target_ref_id);
        if (!$target_ref_id) {
            $this->getLogger()->warning('No target id given');
            return false;
        }

        $obj_settings = new ilLTIProviderObjectSetting($target_ref_id, $consumer->getExtConsumerId());

        // @todo read from lti data
        $roles = $DIC->http()->wrapper()->post()->retrieve('roles', $DIC->refinery()->kindlyTo()->string());
        if (!strlen($roles)) {
            $this->getLogger()->warning('No role information given');
            return false;
        }
        $role_arr = explode(',', $roles);
        foreach ($role_arr as $role_name) {
            $role_name = trim($role_name);
            switch ($role_name) {
                case 'Administrator':
                    $this->getLogger()->info('Administrator role handling');
                    if ($obj_settings->getAdminRole()) {
                        $GLOBALS['DIC']->rbac()->admin()->assignUser(
                            $obj_settings->getAdminRole(),
                            $user_id
                        );
                    }
                    break;

                case 'Instructor':
                    $this->getLogger()->info('Instructor role handling');
                    $this->getLogger()->info('Tutor role for request: ' . $obj_settings->getTutorRole());
                    if ($obj_settings->getTutorRole()) {
                        $GLOBALS['DIC']->rbac()->admin()->assignUser(
                            $obj_settings->getTutorRole(),
                            $user_id
                        );
                    }
                    break;

                case 'Member':
                case 'Learner':
                    $this->getLogger()->info('Member role handling');
                    if ($obj_settings->getMemberRole()) {
                        $GLOBALS['DIC']->rbac()->admin()->assignUser(
                            $obj_settings->getMemberRole(),
                            $user_id
                        );
                    }
                    break;
            }
        }
        return true;
    }
}
