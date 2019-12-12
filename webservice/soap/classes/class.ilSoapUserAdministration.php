<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


  /**
   * Soap user administration methods
   *
   * @author Stefan Meyer <meyer@leifos.com>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapUserAdministration extends ilSoapAdministration
{

    /**
     * Soap login
     * @global type $ilUser
     * @param type $client
     * @param type $username
     * @param type $password
     * @return type
     */
    public function login($client, $username, $password)
    {
        /**
         * @var $ilUser ilObjUser
         */
        global $DIC;

        $ilUser = $DIC['ilUser'];

        unset($_COOKIE[session_name()]);
        $_COOKIE['ilClientId'] = $client;

        try {
            $this->initIlias();
        } catch (Exception $e) {
            return $this->__raiseError($e->getMessage(), 'Server');
        }
        
        // now try authentication
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
        $credentials = new ilAuthFrontendCredentials();
        $credentials->setUsername($username);
        $credentials->setPassword($password);
        
        include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
        $provider_factory = new ilAuthProviderFactory();
        $providers = $provider_factory->getProviders($credentials);
            
        include_once './Services/Authentication/classes/class.ilAuthStatus.php';
        $status = ilAuthStatus::getInstance();
            
        include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
        $frontend_factory = new ilAuthFrontendFactory();
        $frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_CLI);
        $frontend = $frontend_factory->getFrontend(
            $GLOBALS['DIC']['ilAuthSession'],
            $status,
            $credentials,
            $providers
        );
            
        $frontend->authenticate();
            
        switch ($status->getStatus()) {
            case ilAuthStatus::STATUS_AUTHENTICATED:
                ilLoggerFactory::getLogger('auth')->debug('Authentication successful.');
                return $GLOBALS['DIC']['ilAuthSession']->getId() . '::' . $client;
                

            default:
            case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
                return $this->__raiseError(
                    $status->getReason(),
                    'Server'
                );
        }
        return true;
    }

    // Service methods
    public function loginCAS($client, $PT, $username)
    {
        $this->__initAuthenticationObject(AUTH_CAS);
        $this->sauth->setClient($client);
        $this->sauth->setUsername($username);
        $this->sauth->setPT($PT);
        $authenticated = true;
        //include_once("./Services/CAS/classes/class.ilCASAuth.php");
        //include_once("./Services/CAS/phpcas/source/CAS/CAS.php");
        if (!$this->sauth->authenticate()) {
            $authenticated = false;
        }
        if (!$authenticated) {
            return $this->__raiseError($this->sauth->getMessage(), $this->sauth->getMessageCode());
        }
        return $this->sauth->getSid() . '::' . $client;
    }

    // Service methods
    /**
     * Not required anymode. This method is a simple alias for login()
     * @param type $client
     * @param type $username
     * @param type $password
     * @return type
     * @deprecated since version 5.2
     */
    public function loginLDAP($client, $username, $password)
    {
        return $this->login($client, $username, $password);
    }

    /**
     * login as user from Stud.IP
     * @deprecated
     * @param string $sid
     * @param int $user_id
     * @return string $sid
     */
    public function loginStudipUser($sid, $user_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $ilIliasIniFile = $DIC['ilIliasIniFile'];

        $this->initAuth($sid);
        $this->initIlias();
        list($admin_sid, $client) = $this->__explodeSid($sid);
         
        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!$ilIliasIniFile->readVariable('server', 'studip')) {
            return $this->__raiseError('Stud.IP mode not active.', 'Server');
        }

        if (!$rbacreview->isAssigned($ilUser->getId(), SYSTEM_ROLE_ID)) {
            return $this->__raiseError('No permission to initialize user session.', 'Server');
        }

        if ($ilUser->getLoginByUserId($user_id)) {
            // logout admin
            include_once './Services/Authentication/classes/class.ilSession.php';
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
            $GLOBALS['DIC']['ilAuthSession']->logout();
            unset($_COOKIE[session_name()]);

            // init session and set user as authenticated
            $_COOKIE['ilClientId'] = $client;
            $GLOBALS['DIC']['ilAuthSession']->init();
            $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, $user_id);
            return (session_id() . '::' . $client);
        }
        return $this->__raiseError('User does not exist', 'Client');
    }

    /**
     * Logout user destroy session
     * @param string $sid
     * @return type
     */
    public function logout($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        
        include_once './Services/Authentication/classes/class.ilSession.php';
        ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
        $GLOBALS['DIC']['ilAuthSession']->logout();
        return true;
    }

    public function lookupUser($sid, $user_name)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        $user_name = trim($user_name);

        if (!strlen($user_name)) {
            return $this->__raiseError('No username given. Aborting', 'Client');
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        if (strcasecmp($ilUser->getLogin(), $user_name) != 0 && !$rbacsystem->checkAccess('read', USER_FOLDER_ID)) {
            return $this->__raiseError('Check access failed. ' . USER_FOLDER_ID, 'Server');
        }

        $user_id = ilObjUser::getUserIdByLogin($user_name);


        return $user_id ? $user_id : "0";
    }

    public function getUser($sid, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        if (!$rbacsystem->checkAccess('read', USER_FOLDER_ID)) {
            return $this->__raiseError('Check access failed.', 'Server');
        }

        if ($ilUser->getLoginByUserId($user_id)) {
            $tmp_user =&ilObjectFactory::getInstanceByObjId($user_id);
            $usr_data = $this->__readUserData($tmp_user);

            return $usr_data;
        }
        return $this->__raiseError('User does not exist', 'Client');
    }


    /**
     * @deprecated
     */
    public function deleteUser($sid, $user_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        if (!isset($user_id)) {
            return $this->__raiseError('No user_id given. Aborting', 'Client');
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $log = $DIC['log'];

        if (!$rbacsystem->checkAccess('delete', USER_FOLDER_ID)) {
            return $this->__raiseError('Check access failed.', 'Server');
        }

        if (!$ilUser->getLoginByUserId($user_id)) {
            return $this->__raiseError('User id: ' . $user_id . ' is not a valid identifier. Aborting', 'Client');
        }
        if ($ilUser->getId() == $user_id) {
            return $this->__raiseError('Cannot delete myself. Aborting', 'Client');
        }
        if ($user_id == SYSTEM_USER_ID) {
            return $this->__raiseError('Cannot delete root account. Aborting', 'Client');
        }
        // Delete him
        $log->write('SOAP: deleteUser()');
        $delete_user =&ilObjectFactory::getInstanceByObjId($user_id, false);
        $delete_user->delete();

        return true;
    }

    public function __readUserData(\ilObjUser &$usr_obj)
    {
        $usr_data['usr_id'] = $usr_obj->getId();
        $usr_data['login'] = $usr_obj->getLogin();
        $usr_data['passwd'] = $usr_obj->getPasswd();
        $usr_data['passwd_type'] = $usr_obj->getPasswdType();
        $usr_data['firstname'] = $usr_obj->getFirstname();
        $usr_data['lastname'] = $usr_obj->getLastname();
        $usr_data['title'] = $usr_obj->getUTitle();
        $usr_data['gender'] = $usr_obj->getGender();
        $usr_data['email'] = $usr_obj->getEmail();
        $usr_data['second_email'] = $usr_obj->getSecondEmail();
        $usr_data['institution'] = $usr_obj->getInstitution();
        $usr_data['street'] = $usr_obj->getStreet();
        $usr_data['city'] = $usr_obj->getCity();
        $usr_data['zipcode'] = $usr_obj->getZipcode();
        $usr_data['country'] = $usr_obj->getCountry();
        $usr_data['phone_office'] = $usr_obj->getPhoneOffice();
        $usr_data['last_login'] = $usr_obj->getLastLogin();
        $usr_data['last_update'] = $usr_obj->getLastUpdate();
        $usr_data['create_date'] = $usr_obj->getCreateDate();
        $usr_data['hobby'] = $usr_obj->getHobby();
        $usr_data['department'] = $usr_obj->getDepartment();
        $usr_data['phone_home'] = $usr_obj->getPhoneHome();
        $usr_data['phone_mobile'] = $usr_obj->getPhoneMobile();
        $usr_data['fax'] = $usr_obj->getFax();
        $usr_data['time_limit_owner'] = $usr_obj->getTimeLimitOwner();
        $usr_data['time_limit_unlimited'] = $usr_obj->getTimeLimitUnlimited();
        $usr_data['time_limit_from'] = $usr_obj->getTimeLimitFrom();
        $usr_data['time_limit_until'] = $usr_obj->getTimeLimitUntil();
        $usr_data['time_limit_message'] = $usr_obj->getTimeLimitMessage();
        $usr_data['referral_comment'] = $usr_obj->getComment();
        $usr_data['matriculation'] = $usr_obj->getMatriculation();
        $usr_data['active'] = $usr_obj->getActive();
        $usr_data['approve_date'] = $usr_obj->getApproveDate();
        $usr_data['user_skin'] = $usr_obj->getPref('skin');
        $usr_data['user_style'] = $usr_obj->getPref('style');
        $usr_data['user_language'] = $usr_obj->getLanguage();
        $usr_data['auth_mode'] = $usr_obj->getAuthMode();
        $usr_data['accepted_agreement'] = !$usr_obj->hasToAcceptTermsOfService();
        $usr_data['import_id'] = $usr_obj->getImportId();
        
        return $usr_data;
    }

    /**
    *
    * define ("IL_FAIL_ON_CONFLICT", 1);
    * define ("IL_UPDATE_ON_CONFLICT", 2);
    * define ("IL_IGNORE_ON_CONFLICT", 3);
    */
    public function importUsers($sid, $folder_id, $usr_xml, $conflict_rule, $send_account_mail)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }


        include_once './Services/User/classes/class.ilUserImportParser.php';
        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilLog = $DIC['ilLog'];

        // this takes time but is nescessary
        $error = false;


        // validate to prevent wrong XMLs
        $this->dom = @domxml_open_mem($usr_xml, DOMXML_LOAD_VALIDATING, $error);
        if ($error) {
            $msg = array();
            if (is_array($error)) {
                foreach ($error as $err) {
                    $msg []= "(" . $err["line"] . "," . $err["col"] . "): " . $err["errormessage"];
                }
            } else {
                $msg[] = $error;
            }
            $msg = join("\n", $msg);
            return $this->__raiseError($msg, "Client");
        }


        switch ($conflict_rule) {
            case 2:
                $conflict_rule = IL_UPDATE_ON_CONFLICT;
                break;
            case 3:
                $conflict_rule = IL_IGNORE_ON_CONFLICT;
                break;
            default:
                $conflict_rule = IL_FAIL_ON_CONFLICT;
        }


        // folder id 0, means to check permission on user basis!
        // must have create user right in time_limit_owner property (which is ref_id of container)
        if ($folder_id != 0) {
            // determine where to import
            if ($folder_id == -1) {
                $folder_id = USER_FOLDER_ID;
            }

            // get folder
            $import_folder = ilObjectFactory::getInstanceByRefId($folder_id, false);
            // id does not exist
            if (!$import_folder) {
                return $this->__raiseError('Wrong reference id.', 'Server');
            }

            // folder is not a folder, can also be a category
            if ($import_folder->getType() != "usrf" && $import_folder->getType() != "cat") {
                return $this->__raiseError('Folder must be a usr folder or a category.', 'Server');
            }

            // check access to folder
            if (!$rbacsystem->checkAccess('create_usr', $folder_id)) {
                return $this->__raiseError('Missing permission for creating users within ' . $import_folder->getTitle(), 'Server');
            }
        }

        // first verify


        $importParser = new ilUserImportParser("", IL_VERIFY, $conflict_rule);
        $importParser->setUserMappingMode(IL_USER_MAPPING_ID);
        $importParser->setXMLContent($usr_xml);
        $importParser->startParsing();

        switch ($importParser->getErrorLevel()) {
            case IL_IMPORT_SUCCESS:
                break;
            case IL_IMPORT_WARNING:
                return $this->__getImportProtocolAsXML($importParser->getProtocol("User Import Log - Warning"));
                break;
            case IL_IMPORT_FAILURE:
                return $this->__getImportProtocolAsXML($importParser->getProtocol("User Import Log - Failure"));
        }

        // verify is ok, so get role assignments

        $importParser = new ilUserImportParser("", IL_EXTRACT_ROLES, $conflict_rule);
        $importParser->setXMLContent($usr_xml);
        $importParser->setUserMappingMode(IL_USER_MAPPING_ID);
        $importParser->startParsing();

        $roles = $importParser->getCollectedRoles();

        //print_r($roles);



        // roles to be assigned, skip if one is not allowed!
        $permitted_roles = array();
        foreach ($roles as $role_id => $role) {
            if (!is_numeric($role_id)) {
                // check if internal id
                $internalId = ilUtil::__extractId($role_id, IL_INST_ID);
                
                if (is_numeric($internalId)) {
                    $role_id = $internalId;
                    $role_name = $role_id;
                }
                /*				else // perhaps it is a rolename
                                {
                                    $role  = ilSoapUserAdministration::__getRoleForRolename ($role_id);
                                    $role_name = $role->title;
                                    $role_id = $role->role_id;
                                }*/
            }
            
            if ($this->isPermittedRole($folder_id, $role_id)) {
                $permitted_roles[$role_id] = $role_id;
            } else {
                $role_name = ilObject::_lookupTitle($role_id);
                return $this->__raiseError("Could not find role " . $role_name . ". Either you use an invalid/deleted role " .
                    "or you try to assign a local role into the non-standard user folder and this role is not in its subtree.", 'Server');
            }
        }

        $global_roles = $rbacreview->getGlobalRoles();

        //print_r ($global_roles);



        foreach ($permitted_roles as $role_id => $role_name) {
            if ($role_id != "") {
                if (in_array($role_id, $global_roles)) {
                    if ($role_id == SYSTEM_ROLE_ID && !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))
                        || ($folder_id != USER_FOLDER_ID && $folder_id != 0 && !ilObjRole::_getAssignUsersStatus($role_id))
                        ) {
                        return $this->__raiseError($lng->txt("usrimport_with_specified_role_not_permitted") . " $role_name ($role_id)", 'Server');
                    }
                } else {
                    $rolf = $rbacreview->getFoldersAssignedToRole($role_id, true);
                    if ($rbacreview->isDeleted($rolf[0])
                                || !$rbacsystem->checkAccess('write', $rolf[0])) {
                        return $this->__raiseError($lng->txt("usrimport_with_specified_role_not_permitted") . " $role_name ($role_id)", "Server");
                    }
                }
            }
        }

        //print_r ($permitted_roles);

        $importParser = new ilUserImportParser("", IL_USER_IMPORT, $conflict_rule);
        $importParser->setSendMail($send_account_mail);
        $importParser->setUserMappingMode(IL_USER_MAPPING_ID);
        $importParser->setFolderId($folder_id);
        $importParser->setXMLContent($usr_xml);

        $importParser->setRoleAssignment($permitted_roles);

        $importParser->startParsing();

        if ($importParser->getErrorLevel() != IL_IMPORT_FAILURE) {
            return $this->__getUserMappingAsXML($importParser->getUserMapping());
        }
        return $this->__getImportProtocolAsXML($importParser->getProtocol());
    }
    
    /**
     * check if assignment is allowed
     *
     * @access protected
     * @param
     * @return
     */
    protected function isPermittedRole($a_folder, $a_role)
    {
        static $checked_roles = array();
        static $global_roles = null;
        
        
        if (isset($checked_roles[$a_role])) {
            return $checked_roles[$a_role];
        }
        
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];
        
        $locations = $rbacreview->getFoldersAssignedToRole($a_role, true);
        $location = $locations[0];
        
        // global role
        if ($location == ROLE_FOLDER_ID) {
            $ilLog->write(__METHOD__ . ': Check global role');
            // check assignment permission if called from local admin
            
            
            if ($a_folder != USER_FOLDER_ID and $a_folder != 0) {
                $ilLog->write(__METHOD__ . ': ' . $a_folder);
                include_once './Services/AccessControl/classes/class.ilObjRole.php';
                if (!ilObjRole::_getAssignUsersStatus($a_role)) {
                    $ilLog->write(__METHOD__ . ': No assignment allowed');
                    $checked_roles[$a_role] = false;
                    return false;
                }
            }
            // exclude anonymous role from list
            if ($a_role == ANONYMOUS_ROLE_ID) {
                $ilLog->write(__METHOD__ . ': Anonymous role chosen.');
                $checked_roles[$a_role] = false;
                return false;
            }
            // do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
            if ($a_role == SYSTEM_ROLE_ID and !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
                $ilLog->write(__METHOD__ . ': System role assignment forbidden.');
                $checked_roles[$a_role] = false;
                return false;
            }
            
            // Global role assignment ok
            $ilLog->write(__METHOD__ . ': Assignment allowed.');
            $checked_roles[$a_role] = true;
            return true;
        } elseif ($location) {
            $ilLog->write(__METHOD__ . ': Check local role.');

            // It's a local role
            $rolfs = $rbacreview->getFoldersAssignedToRole($a_role, true);
            $rolf = $rolfs[0];


            // only process role folders that are not set to status "deleted"
            // and for which the user has write permissions.
            // We also don't show the roles which are in the ROLE_FOLDER_ID folder.
            // (The ROLE_FOLDER_ID folder contains the global roles).
            if ($rbacreview->isDeleted($rolf)
                || !$rbacsystem->checkAccess('edit_permission', $rolf)) {
                $ilLog->write(__METHOD__ . ': Role deleted or no permission.');
                $checked_roles[$a_role] = false;
                return false;
            }
            // A local role is only displayed, if it is contained in the subtree of
            // the localy administrated category. If the import function has been
            // invoked from the user folder object, we show all local roles, because
            // the user folder object is considered the parent of all local roles.
            // Thus, if we start from the user folder object, we initializ$isInSubtree = $folder_id == USER_FOLDER_ID || $folder_id == 0;e the
            // isInSubtree variable with true. In all other cases it is initialized
            // with false, and only set to true if we find the object id of the
            // locally administrated category in the tree path to the local role.
            if ($a_folder != USER_FOLDER_ID and $a_folder != 0 and !$tree->isGrandChild($a_folder, $rolf)) {
                $ilLog->write(__METHOD__ . ': Not in path of category.');
                $checked_roles[$a_role] = false;
                return false;
            }
            $ilLog->write(__METHOD__ . ': Assignment allowed.');
            $checked_roles[$a_role] = true;
            return true;
        }
    }


    /**
    * return list of users following dtd users_3_7
    */
    public function getUsersForContainer($sid, $ref_id, $attachRoles, $active)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];
        $tree = $DIC['tree'];
        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];

        if ($ref_id == -1) {
            $ref_id = USER_FOLDER_ID;
        }

        $object = $this->checkObjectAccess($ref_id, array("crs","cat","grp","usrf","sess"), "read", true);
        if ($this->isFault($object)) {
            return $object;
        }
            
        $data = array();
        switch ($object->getType()) {
            case "usrf":
                $data = ilObjUser::_getUsersForFolder(USER_FOLDER_ID, $active);
                break;
            case "cat":
                $data =  ilObjUser::_getUsersForFolder($ref_id, $active);
                break;
            case "crs":
            {
                // GET ALL MEMBERS
                $roles = $object->__getLocalRoles();

                foreach ($roles as $role_id) {
                    $data = array_merge($rbacreview->assignedUsers($role_id), $data);
                }

                break;
            }
            case "grp":
                $member_ids = $object->getGroupMemberIds();
                $data = ilObjUser::_getUsersForGroup($member_ids, $active);
                break;
            case "sess":
                $course_ref_id = $tree->checkForParentType($ref_id, 'crs');
                if (!$course_ref_id) {
                    return $this->__raiseError("No course for session", "Client");
                }

                $event_obj_id = ilObject::_lookupObjId($ref_id);
                include_once 'Modules/Session/classes/class.ilEventParticipants.php';
                $event_part = new ilEventParticipants($event_obj_id);
                $member_ids = array_keys($event_part->getParticipants());
                $data = ilObjUser::_getUsersForIds($member_ids, $active);
                break;
        }

        if (is_array($data)) {
            include_once './Services/User/classes/class.ilUserXMLWriter.php';

            $xmlWriter = new ilUserXMLWriter();
            $xmlWriter->setObjects($data);
            $xmlWriter->setAttachRoles($attachRoles);
            
            if ($xmlWriter->start()) {
                return $xmlWriter->getXML();
            }
        }
        return $this->__raiseError('Error in processing information. This is likely a bug.', 'Server');
    }


    /**
    * @return list of users of a specific role, following dtd users_3_7
    */
    public function getUserForRole($sid, $role_id, $attachRoles, $active)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];


        $global_roles = $rbacreview->getGlobalRoles();


        if (in_array($role_id, $global_roles)) {
            if ($role_id == SYSTEM_ROLE_ID && !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))
            ) {
                return $this->__raiseError("Role access not permitted. ($role_id)", "Server");
            }
        } else {
            $rolf = $rbacreview->getFoldersAssignedToRole($role_id, true);
            if ($rbacreview->isDeleted($rolf[0])
                    || !$rbacsystem->checkAccess('write', $rolf[0])) {
                return $this->__raiseError("Role access not permitted. ($role_id)", "Server");
            }
            include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
            $privacy = ilPrivacySettings::_getInstance();
            if (!$rbacsystem->checkAccess('read', SYSTEM_USER_ID) and
               !$rbacsystem->checkAccess('export_member_data', $privacy->getPrivacySettingsRefId())) {
                return $this->__raiseError("Export of local role members not permitted. ($role_id)", "Server");
            }
        }

        $data = ilObjUser::_getUsersForRole($role_id, $active);
        include_once './Services/User/classes/class.ilUserXMLWriter.php';

        $xmlWriter = new ilUserXMLWriter();
        $xmlWriter->setAttachRoles($attachRoles);
        
        $xmlWriter->setObjects($data);

        if ($xmlWriter->start()) {
            return $xmlWriter->getXML();
        }
        return $this->__raiseError('Error in getUsersForRole', 'Server');
    }



    /**
    *	Create XML ResultSet
    *
    **/
    public function __getImportProtocolAsXML($a_array)
    {
        include_once './webservice/soap/classes/class.ilXMLResultSet.php';
        include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("userid");
        $xmlResultSet->addColumn("login");
        $xmlResultSet->addColumn("action");
        $xmlResultSet->addColumn("message");

        foreach ($a_array as $username => $messages) {
            foreach ($messages as $message) {
                $xmlRow = new ilXMLResultSetRow();
                $xmlRow->setValue(0, 0);
                $xmlRow->setValue(1, $username);
                $xmlRow->setValue(2, "");
                $xmlRow->setValue(3, $message);

                $xmlResultSet->addRow($xmlRow);
            }
        }

        $xml_writer = new ilXMLResultSetWriter($xmlResultSet);

        if ($xml_writer->start()) {
            return $xml_writer->getXML();
        }

        return $this->__raiseError('Error in __getImportProtocolAsXML', 'Server');
    }

    /**
     * return user  mapping as xml
     *
     * @param array (user_id => login) $a_array
     * @return XML String, following resultset.dtd
     */
    public function __getUserMappingAsXML($a_array)
    {
        include_once './webservice/soap/classes/class.ilXMLResultSet.php';
        include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';

        $xmlResultSet = new ilXMLResultSet();
        $xmlResultSet->addColumn("userid");
        $xmlResultSet->addColumn("login");
        $xmlResultSet->addColumn("action");
        $xmlResultSet->addColumn("message");

        if (count($a_array)) {
            foreach ($a_array as $username => $message) {
                $xmlRow = new ilXMLResultSetRow();
                $xmlRow->setValue(0, $username);
                $xmlRow->setValue(1, $message["login"]);
                $xmlRow->setValue(2, $message["action"]);
                $xmlRow->setValue(3, $message["message"]);

                $xmlResultSet->addRow($xmlRow);
            }
        }

        $xml_writer = new ilXMLResultSetWriter($xmlResultSet);

        if ($xml_writer->start()) {
            return $xml_writer->getXML();
        }

        return $this->__raiseError('Error in __getUserMappingAsXML', 'Server');
    }

    /**
     * return user xml following dtd 3.7
     *
     * @param String $sid    session id
     * @param String array $a_keyfields    array of user fieldname, following dtd 3.7
     * @param String $queryOperator  any logical operator
     * @param String array $a_keyValues  values separated by space, at least 3 chars per search term
     */
    public function searchUser($sid, $a_keyfields, $query_operator, $a_keyvalues, $attach_roles, $active)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }
        
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $rbacsystem = $DIC['rbacsystem'];

        if (!$rbacsystem->checkAccess('read', USER_FOLDER_ID)) {
            return $this->__raiseError('Check access failed.', 'Server');
        }


        if (!count($a_keyfields)) {
            $this->__raiseError('At least one keyfield is needed', 'Client');
        }

        if (!count($a_keyvalues)) {
            $this->__raiseError('At least one keyvalue is needed', 'Client');
        }

        if (!strcasecmp($query_operator, "and")==0 || !strcasecmp($query_operator, "or") == 0) {
            $this->__raiseError('Query operator must be either \'and\' or \'or\'', 'Client');
        }


        $query = $this->__buildSearchQuery($a_keyfields, $query_operator, $a_keyvalues);

        $query = "SELECT usr_data.*, usr_pref.value AS language
		          FROM usr_data
		          LEFT JOIN usr_pref
		          ON usr_pref.usr_id = usr_data.usr_id AND usr_pref.keyword = " .
                  $ilDB->quote("language", "text") .
                  "'language'
		          WHERE 1 = 1 " . $query;

        if (is_numeric($active) && $active > -1) {
            $query .= " AND active = " . $ilDB->quote($active);
        }

        $query .= " ORDER BY usr_data.lastname, usr_data.firstname ";

        //echo $query;

        $r = $ilDB->query($query);

        $data = array();

        while ($row = $ilDB->fetchAssoc($r)) {
            $data[] = $row;
        }

        include_once './Services/User/classes/class.ilUserXMLWriter.php';

        $xmlWriter = new ilUserXMLWriter();
        $xmlWriter->setAttachRoles($attach_roles);
         
        $xmlWriter->setObjects($data);

        if ($xmlWriter->start()) {
            return $xmlWriter->getXML();
        }
        return $this->__raiseError('Error in searchUser', 'Server');
    }

    /**
     * create search term according to parameters
     *
     * @param array of string $a_keyfields
     * @param string $queryOperator
     * @param array of string $a_keyValues
     */

    public function __buildSearchQuery($a_keyfields, $queryOperator, $a_keyvalues)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = array();

        $allowed_fields = array("firstname","lastname","email","login","matriculation","institution","department","title","ext_account");

        foreach ($a_keyfields as $keyfield) {
            $keyfield = strtolower($keyfield);

            if (!in_array($keyfield, $allowed_fields)) {
                continue;
            }

            $field_query = array();
            foreach ($a_keyvalues as $keyvalue) {
                if (strlen($keyvalue) >= 3) {
                    $field_query []= $keyfield . " like '%" . $keyvalue . "%'";
                }
            }
            if (count($field_query)) {
                $query [] = join(" " . strtoupper($queryOperator) . " ", $field_query);
            }
        }

        return count($query) ? " AND ((" . join(") OR (", $query) . "))" : "AND 0";
    }


    /**
    *	return user xmls for given user ids (csv separated ids) as xml based on usr dtd.
    *	@param string sid	session id
    *	@param string a_userids array of user ids, may be numeric or ilias ids
    *	@param boolean attachRoles	if true, role assignments will be attached, nothing will be done otherwise
    *	@return	string	xml string based on usr dtd
    */
    public function getUserXML($sid, $a_user_ids, $attach_roles)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        // check if own account
        $is_self = false;
        if (is_array($a_user_ids) and count($a_user_ids) == 1) {
            if (end($a_user_ids) == $ilUser->getId()) {
                $is_self = true;
            }
        } elseif (is_numeric($a_user_ids)) {
            if ($a_user_ids == $ilUser->getId()) {
                $is_self = true;
            }
        }

        if (!$rbacsystem->checkAccess('read', USER_FOLDER_ID) and !$is_self) {
            return $this->__raiseError('Check access failed.', 'Server');
        }

        // begin-patch filemanager
        $data = ilObjUser::_getUserData((array) $a_user_ids);
        // end-patch filemanager

        include_once './Services/User/classes/class.ilUserXMLWriter.php';
        $xmlWriter = new ilUserXMLWriter();
        $xmlWriter->setAttachRoles($attach_roles);
        $xmlWriter->setObjects($data);

        if ($xmlWriter->start()) {
            return $xmlWriter->getXML();
        }

        return $this->__raiseError('User does not exist', 'Client');
    }


    // has new mail
    public function hasNewMail($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
        if (ilMailGlobalServices::getNumberOfNewMailsByUserId($ilUser->getId()) > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getUserIdBySid($sid)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->__checkSession($sid)) {
            return $this->__raiseError($this->__getMessage(), $this->__getMessageCode());
        }

        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $parts = explode('::', $sid);
        $query = "SELECT usr_id FROM usr_session "
               . "INNER JOIN usr_data ON usr_id = user_id WHERE session_id = %s";
        $res = $ilDB->queryF($query, array('text'), array($parts[0]));
        $data = $ilDB->fetchAssoc($res);

        if (!(int) $data['usr_id']) {
            $this->__raiseError('User does not exist', 'Client');
        }
        
        return (int) $data['usr_id'];
    }
}
