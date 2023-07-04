<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjUserGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjUserGUI: ilLearningProgressGUI, ilObjectOwnershipManagementGUI
*
* @ingroup ServicesUser
*/
class ilObjUserGUI extends ilObjectGUI
{
    /** @var ILIAS\UI\Factory */
    protected $uiFactory;

    /** @var ILIAS\UI\Renderer */
    protected $uiRenderer;

    public $ilCtrl;

    /**
    * array of gender abbreviations
    * @var		array
    * @access	public
    */
    public $gender;

    /**
    * ILIAS3 object type abbreviation
    * @var		string
    * @access	public
    */
    public $type;

    /**
    * userfolder ref_id where user is assigned to
    * @var		string
    * @access	public
    */
    public $user_ref_id;

    /**
     * ilObjUserGUI constructor.
     * @param $a_data
     * @param $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     * @param \ILIAS\UI\Factory $uiFactory
     * @param \ILIAS\UI\Renderer $uiRenderer
     */
    public function __construct(
        $a_data,
        $a_id,
        $a_call_by_reference = false,
        $a_prepare_output = true,
        ILIAS\UI\Factory $uiFactory = null,
        ILIAS\UI\Renderer $uiRenderer = null
    ) {
        global $DIC;

        if (null === $uiFactory) {
            $uiFactory = $DIC->ui()->factory();
        }
        $this->uiFactory = $uiFactory;

        if (null === $uiRenderer) {
            $uiRenderer = $DIC->ui()->renderer();
        }
        $this->uiRenderer = $uiRenderer;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        define('USER_FOLDER_ID', 7);

        $this->type = "usr";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->usrf_ref_id = &$this->ref_id;

        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array('obj_id', 'letter'));
        $this->ctrl->setParameterByClass("ilobjuserfoldergui", "letter", $_GET["letter"]);
        $this->ctrl->setContext($this->object->getId(), 'usr');
        $lng->loadLanguageModule('user');

        // for gender selection. don't change this
        // maybe deprecated
        $this->gender = array(
                              'n' => "salutation_n",
                              'm' => "salutation_m",
                              'f' => "salutation_f",
                              );
    }

    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        switch ($next_class) {
            case "illearningprogressgui":
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_USER_FOLDER, USER_FOLDER_ID, $this->object->getId());
                $this->ctrl->forwardCommand($new_gui);
                break;

            case "ilobjectownershipmanagementgui":
                include_once("Services/Object/classes/class.ilObjectOwnershipManagementGUI.php");
                $gui = new ilObjectOwnershipManagementGUI($this->object->getId());
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                if ($cmd == "" || $cmd == "view") {
                    $cmd = "edit";
                }
                $cmd .= "Object";
                $return = $this->$cmd();

                break;
        }
        return $return;
    }

    /* Overwritten from base class
    */
    public function setTitleAndDescription()
    {
        if (strtolower(get_class($this->object)) == 'ilobjuser') {
            $this->tpl->setTitle('[' . $this->object->getLogin() . '] ' . $this->object->getTitle());
            $this->tpl->setDescription($this->object->getLongDescription());
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_" . $this->object->getType() . ".svg"), $this->lng->txt("obj_" . $this->object->getType()));
        } else {
            parent::setTitleAndDescription();
        }
    }



    public function cancelObject()
    {
        ilSession::clear("saved_post");

        if (strtolower($_GET["baseClass"]) == 'iladministrationgui') {
            $this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
        //$return_location = $_GET["cmd_return_location"];
            //ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }

    /**
    * admin and normal tabs are equal for roles
    */
    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    */
    public function getTabs()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilHelp = $DIC['ilHelp'];

        $this->tabs_gui->clearTargets();

        $ilHelp->setScreenIdComponent("usr");

        if ($_GET["search"]) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("search_results"),
                $_SESSION["usr_search_link"]
            );

            $this->tabs_gui->addTarget(
                "properties",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit","","view"),
                get_class($this),
                "",
                true
            );
        } else {
            $this->tabs_gui->addTarget(
                "properties",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit","","view"),
                get_class($this)
            );
        }

        $this->tabs_gui->addTarget(
            "role_assignment",
            $this->ctrl->getLinkTarget($this, "roleassignment"),
            array("roleassignment"),
            get_class($this)
        );

        // learning progress
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if ($rbacsystem->checkAccess('read', $this->ref_id) and
            ilObjUserTracking::_enabledLearningProgress() and
            ilObjUserTracking::_enabledUserRelatedData()) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass('illearningprogressgui', ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        $this->tabs_gui->addTarget(
            'user_ownership',
            $this->ctrl->getLinkTargetByClass('ilobjectownershipmanagementgui', ''),
            '',
            'ilobjectownershipmanagementgui'
        );
    }

    /**
    * set back tab target
    */
    public function setBackTarget($a_text, $a_link)
    {
        $this->back_target = array("text" => $a_text,
            "link" => $a_link);
    }

    /**
    * display user create form
    */

    public function __checkUserDefinedRequiredFields()
    {
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $this->user_defined_fields = &ilUserDefinedFields::_getInstance();

        foreach ($this->user_defined_fields->getDefinitions() as $field_id => $definition) {
            if ($definition['required'] and !strlen($_POST['udf'][$field_id])) {
                return false;
            }
        }
        return true;
    }


    public function __showUserDefinedFields()
    {
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $this->user_defined_fields = &ilUserDefinedFields::_getInstance();

        if ($this->object->getType() == 'usr') {
            $user_defined_data = $this->object->getUserDefinedData();
        }
        foreach ($this->user_defined_fields->getDefinitions() as $field_id => $definition) {
            $old = isset($_SESSION["error_post_vars"]["udf"][$field_id]) ?
                $_SESSION["error_post_vars"]["udf"][$field_id] : $user_defined_data[$field_id];

            if ($definition['field_type'] == UDF_TYPE_TEXT) {
                $this->tpl->setCurrentBlock("field_text");
                $this->tpl->setVariable("FIELD_NAME", 'udf[' . $definition['field_id'] . ']');
                $this->tpl->setVariable("FIELD_VALUE", ilUtil::prepareFormOutput($old));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("field_select");
                $this->tpl->setVariable("SELECT_BOX", ilUtil::formSelect(
                    $old,
                    'udf[' . $definition['field_id'] . ']',
                    $this->user_defined_fields->fieldValuesToSelectArray(
                        $definition['field_values']
                    ),
                    false,
                    true
                ));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("user_defined");

            if ($definition['required']) {
                $name = $definition['field_name'] . "<span class=\"asterisk\">*</span>";
            } else {
                $name = $definition['field_name'];
            }
            $this->tpl->setVariable("TXT_FIELD_NAME", $name);
            $this->tpl->parseCurrentBlock();
        }
        return true;
    }

    public function initCreate()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];

        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            $this->tabs_gui->clearTargets();
        }

        // role selection
        $obj_list = $rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
        $rol = array();
        foreach ($obj_list as $obj_data) {
            // allow only 'assign_users' marked roles if called from category
            if ($this->object->getRefId() != USER_FOLDER_ID and !in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
                include_once './Services/AccessControl/classes/class.ilObjRole.php';

                if (!ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])) {
                    continue;
                }
            }
            // exclude anonymous role from list
            if ($obj_data["obj_id"] != ANONYMOUS_ROLE_ID) {
                // do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
                if ($obj_data["obj_id"] != SYSTEM_ROLE_ID or in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
                    $rol[$obj_data["obj_id"]] = $obj_data["title"];
                }
            }
        }

        // raise error if there is no global role user can be assigned to
        if (!count($rol)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_roles_users_can_be_assigned_to"), $this->ilias->error_obj->MESSAGE);
        }

        $keys = array_keys($rol);

        // set pre defined user role to default
        if (in_array(4, $keys)) {
            $this->default_role = 4;
        } else {
            if (count($keys) > 1 and in_array(2, $keys)) {
                // remove admin role as preselectable role
                foreach ($keys as $key => $val) {
                    if ($val == 2) {
                        unset($keys[$key]);
                        break;
                    }
                }
            }

            $this->default_role = array_shift($keys);
        }
        $this->selectable_roles = $rol;
    }

    /**
    * Display user create form
    */
    public function createObject()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $rbacsystem = $DIC['rbacsystem'];
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];

        if (!$rbacsystem->checkAccess('create_usr', $this->usrf_ref_id) and
            !$rbacsystem->checkAccess('cat_administrate_users', $this->usrf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->initCreate();
        $this->initForm("create");
        return $tpl->setContent($this->form_gui->getHtml());
    }

    /**
    * save user data
    * @access	public
    */
    public function saveObject()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilSetting = $DIC['ilSetting'];
        $tpl = $DIC['tpl'];
        $ilUser = $DIC['ilUser'];
        $rbacadmin = $DIC['rbacadmin'];
        $rbacsystem = $DIC['rbacsystem'];

        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');

        // User folder
        if (!$rbacsystem->checkAccess('create_usr', $this->usrf_ref_id) &&
            !$ilAccess->checkAccess('cat_administrate_users', "", $this->usrf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->initCreate();
        $this->initForm("create");

        // Manipulate form so ignore required fields are no more required. This has to be done before ilPropertyFormGUI::checkInput() is called.
        $profileMaybeIncomplete = false;
        if ($this->form_gui->getInput('ignore_rf', false)) {
            $profileMaybeIncomplete = $this->handleIgnoredRequiredFields();
        }

        if ($this->form_gui->checkInput()) {
            // @todo: external account; time limit check and savings

            // checks passed. save user
            $userObj = $this->loadValuesFromForm();

            $userObj->setPasswd($this->form_gui->getInput('passwd'), IL_PASSWD_PLAIN);
            $userObj->setTitle($userObj->getFullname());
            $userObj->setDescription($userObj->getEmail());

            $udf = array();
            foreach ($_POST as $k => $v) {
                if (substr($k, 0, 4) == "udf_") {
                    $udf[substr($k, 4)] = $v;
                }
            }
            $userObj->setUserDefinedData($udf);

            $userObj->create();

            include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
            if (ilAuthUtils::_isExternalAccountEnabled()) {
                $userObj->setExternalAccount($_POST["ext_account"]);
            }

            // set a timestamp for last_password_change
            // this ts is needed by ilSecuritySettings
            $userObj->setLastPasswordChangeTS(time());

            //insert user data in table user_data
            $userObj->saveAsNew();

            // setup user preferences
            if ($this->isSettingChangeable('language')) {
                $userObj->setLanguage($_POST["language"]);
            }

            if ($this->isSettingChangeable('skin_style')) {
                //set user skin and style
                $sknst = explode(":", $_POST["skin_style"]);

                if ($userObj->getPref("style") != $sknst[1] ||
                    $userObj->getPref("skin") != $sknst[0]) {
                    $userObj->setPref("skin", $sknst[0]);
                    $userObj->setPref("style", $sknst[1]);
                }
            }
            if ($this->isSettingChangeable('hits_per_page')) {
                $userObj->setPref("hits_per_page", $_POST["hits_per_page"]);
            }
            /*if($this->isSettingChangeable('show_users_online'))
            {
                $userObj->setPref("show_users_online", $_POST["show_users_online"]);
            }*/
            if ($this->isSettingChangeable('hide_own_online_status')) {
                $userObj->setPref("hide_own_online_status", $_POST["hide_own_online_status"]);
            }
            if ($this->isSettingChangeable('bs_allow_to_contact_me')) {
                $userObj->setPref('bs_allow_to_contact_me', $_POST['bs_allow_to_contact_me'] ? 'y' : 'n');
            }
            if ($this->isSettingChangeable('chat_osc_accept_msg')) {
                $userObj->setPref('chat_osc_accept_msg', $_POST['chat_osc_accept_msg'] ? 'y' : 'n');
            }
            if ((int) $ilSetting->get('session_reminder_enabled')) {
                $userObj->setPref('session_reminder_enabled', (int) $_POST['session_reminder_enabled']);
            }
            $userObj->writePrefs();

            //set role entries
            $rbacadmin->assignUser($_POST["default_role"], $userObj->getId(), true);

            $msg = $this->lng->txt("user_added");

            $ilUser->setPref('send_info_mails', ($_POST['send_mail'] == 'y') ? 'y' : 'n');
            $ilUser->writePrefs();

            $this->object = $userObj;

            if ($this->isSettingChangeable('upload')) {
                $this->uploadUserPictureObject();
            }

            if ($profileMaybeIncomplete) {
                include_once 'Services/User/classes/class.ilUserProfile.php';
                if (ilUserProfile::isProfileIncomplete($this->object)) {
                    $this->object->setProfileIncomplete(true);
                    $this->object->update();
                }
            }

            // send new account mail
            if ($_POST['send_mail'] == 'y') {
                include_once('Services/Mail/classes/class.ilAccountMail.php');
                $acc_mail = new ilAccountMail();
                $acc_mail->useLangVariablesAsFallback(true);
                $acc_mail->setAttachConfiguredFiles(true);
                $acc_mail->setUserPassword($_POST['passwd']);
                $acc_mail->setUser($userObj);

                if ($acc_mail->send()) {
                    $msg = $msg . '<br />' . $this->lng->txt('mail_sent');
                    ilUtil::sendSuccess($msg, true);
                } else {
                    $msg = $msg . '<br />' . $this->lng->txt('mail_not_sent');
                    ilUtil::sendInfo($msg, true);
                }
            } else {
                ilUtil::sendSuccess($msg, true);
            }


            if (strtolower($_GET["baseClass"]) == 'iladministrationgui') {
                $this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
            } else {
                $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
            }
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHtml());
        }
    }

    /**
    * Display user edit form
    *
    * @access	public
    */
    public function editObject()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $rbacsystem = $DIC->rbac()->system();
        $access = $DIC->access();

        // User folder
        // User folder && access granted by rbac or by org unit positions
        if ($this->usrf_ref_id == USER_FOLDER_ID &&
            (
                !$rbacsystem->checkAccess('visible,read', $this->usrf_ref_id) ||
                !$access->checkRbacOrPositionPermissionAccess('write', \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $this->usrf_ref_id) ||
                !in_array(
                    $this->object->getId(),
                    $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'write',
                        \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                        USER_FOLDER_ID,
                        [$this->object->getId()]
                    )
                )
            )
        ) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
        }

        if ($this->usrf_ref_id == USER_FOLDER_ID and !$rbacsystem->checkAccess('visible,read', $this->usrf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
        }
        // if called from local administration $this->usrf_ref_id is category id
        // Todo: this has to be fixed. Do not mix user folder id and category id
        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            // check if user is assigned to category
            if (!$rbacsystem->checkAccess('cat_administrate_users', $this->object->getTimeLimitOwner())) {
                $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
            }
        }

        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            $this->tabs_gui->clearTargets();
        }

        // get form
        $this->initForm("edit");
        $this->getValues();
        $this->showAcceptedTermsOfService();
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * @param object $a_mode [optional]
     * @return object ilObjUser
     */
    protected function loadValuesFromForm($a_mode = 'create')
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];

        switch ($a_mode) {
            case 'create':
                $user = new ilObjUser();
                break;

            case 'update':
                $user = $this->object;
                break;
        }

        $from = $this->form_gui->getItemByPostVar('time_limit_from')->getDate();
        $user->setTimeLimitFrom($from
            ? $from->get(IL_CAL_UNIX)
            : null);

        $until = $this->form_gui->getItemByPostVar('time_limit_until')->getDate();
        $user->setTimeLimitUntil($until
            ? $until->get(IL_CAL_UNIX)
            : null);

        $user->setTimeLimitUnlimited($this->form_gui->getInput('time_limit_unlimited'));

        if ($a_mode == 'create') {
            $user->setTimeLimitOwner($this->usrf_ref_id);
        }

        // Birthday
        if ($this->isSettingChangeable('birthday')) {
            $bd = $this->form_gui->getItemByPostVar('birthday');
            $bd = $bd->getDate();
            $user->setBirthday($bd
                ? $bd->get(IL_CAL_DATE)
                : null);
        }

        // Login
        $user->setLogin($this->form_gui->getInput('login'));


        // Gender
        if ($this->isSettingChangeable('gender')) {
            $user->setGender($this->form_gui->getInput('gender'));
        }

        // Title
        if ($this->isSettingChangeable('title')) {
            $user->setUTitle($this->form_gui->getInput('title'));
        }

        // Firstname
        if ($this->isSettingChangeable('firstname')) {
            $user->setFirstname($this->form_gui->getInput('firstname'));
        }
        // Lastname
        if ($this->isSettingChangeable('lastname')) {
            $user->setLastname($this->form_gui->getInput('lastname'));
        }
        $user->setFullname();

        // Institution
        if ($this->isSettingChangeable('institution')) {
            $user->setInstitution($this->form_gui->getInput('institution'));
        }

        // Department
        if ($this->isSettingChangeable('department')) {
            $user->setDepartment($this->form_gui->getInput('department'));
        }
        // Street
        if ($this->isSettingChangeable('street')) {
            $user->setStreet($this->form_gui->getInput('street'));
        }
        // City
        if ($this->isSettingChangeable('city')) {
            $user->setCity($this->form_gui->getInput('city'));
        }
        // Zipcode
        if ($this->isSettingChangeable('zipcode')) {
            $user->setZipcode($this->form_gui->getInput('zipcode'));
        }
        // Country
        if ($this->isSettingChangeable('country')) {
            $user->setCountry($this->form_gui->getInput('country'));
        }
        // Selected Country
        if ($this->isSettingChangeable('sel_country')) {
            $user->setSelectedCountry($this->form_gui->getInput('sel_country'));
        }
        // Phone Office
        if ($this->isSettingChangeable('phone_office')) {
            $user->setPhoneOffice($this->form_gui->getInput('phone_office'));
        }
        // Phone Home
        if ($this->isSettingChangeable('phone_home')) {
            $user->setPhoneHome($this->form_gui->getInput('phone_home'));
        }
        // Phone Mobile
        if ($this->isSettingChangeable('phone_mobile')) {
            $user->setPhoneMobile($this->form_gui->getInput('phone_mobile'));
        }
        // Fax
        if ($this->isSettingChangeable('fax')) {
            $user->setFax($this->form_gui->getInput('fax'));
        }
        // Matriculation
        if ($this->isSettingChangeable('matriculation')) {
            $user->setMatriculation($this->form_gui->getInput('matriculation'));
        }
        // Email
        if ($this->isSettingChangeable('email')) {
            $user->setEmail($this->form_gui->getInput('email'));
        }
        // Second Email
        if ($this->isSettingChangeable('second_email')) {
            $user->setSecondEmail($this->form_gui->getInput('second_email'));
        }
        // Hobby
        if ($this->isSettingChangeable('hobby')) {
            $user->setHobby($this->form_gui->getInput('hobby'));
        }
        // Referral Comment
        if ($this->isSettingChangeable('referral_comment')) {
            $user->setComment($this->form_gui->getInput('referral_comment'));
        }

        // interests
        $user->setGeneralInterests($this->form_gui->getInput('interests_general'));
        $user->setOfferingHelp($this->form_gui->getInput('interests_help_offered'));
        $user->setLookingForHelp($this->form_gui->getInput('interests_help_looking'));

        // ClientIP
        $user->setClientIP($this->form_gui->getInput('client_ip'));

        // Google maps
        $user->setLatitude($this->form_gui->getInput('latitude'));
        $user->setLongitude($this->form_gui->getInput('longitude'));
        $user->setLocationZoom($this->form_gui->getInput('loc_zoom'));

        // External account
        $user->setAuthMode($this->form_gui->getInput('auth_mode'));
        $user->setExternalAccount($this->form_gui->getInput('ext_account'));

        if ((int) $user->getActive() != (int) $this->form_gui->getInput('active')) {
            $user->setActive($this->form_gui->getInput('active'), $ilUser->getId());
        }

        return $user;
    }


    /**
    * Update user
    */
    public function updateObject()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $rbacsystem = $DIC->rbac()->system();
        $ilUser = $DIC->user();
        $access = $DIC->access();

        // User folder && access granted by rbac or by org unit positions
        if ($this->usrf_ref_id == USER_FOLDER_ID &&
            (
                !$rbacsystem->checkAccess('visible,read', USER_FOLDER_ID) ||
                !$access->checkRbacOrPositionPermissionAccess('write', \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, USER_FOLDER_ID) ||
                !in_array(
                    $this->object->getId(),
                    $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'write',
                        \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                        USER_FOLDER_ID,
                        [$this->object->getId()]
                    )
                )
            )
        ) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
        }
        // if called from local administration $this->usrf_ref_id is category id
        // Todo: this has to be fixed. Do not mix user folder id and category id
        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            // check if user is assigned to category
            if (!$rbacsystem->checkAccess('cat_administrate_users', $this->object->getTimeLimitOwner())) {
                $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
            }
        }
        $this->initForm("edit");

        // we do not want to store this dates, they are only printed out
        unset($_POST['approve_date']);
        $_POST['agree_date'] = $this->object->getAgreeDate();
        unset($_POST['last_login']);

        // Manipulate form so ignore required fields are no more required. This has to be done before ilPropertyFormGUI::checkInput() is called.
        $profileMaybeIncomplete = false;
        if ($this->form_gui->getInput('ignore_rf', false)) {
            $profileMaybeIncomplete = $this->handleIgnoredRequiredFields();
        }

        if ($this->form_gui->checkInput()) {
            // @todo: external account; time limit
            // if not allowed or empty -> do no change password
            if (ilAuthUtils::_allowPasswordModificationByAuthMode(ilAuthUtils::_getAuthMode($_POST['auth_mode']))
                && trim($_POST['passwd']) != "") {
                $this->object->setPasswd($_POST['passwd'], IL_PASSWD_PLAIN);
            }

            /*
             * reset counter for failed logins
             * if $_POST['active'] is set to 1
             */
            if ($_POST['active'] == 1) {
                ilObjUser::_resetLoginAttempts($this->object->getId());
            }

            #$this->object->assignData($_POST);
            $this->loadValuesFromForm('update');

            $udf = array();
            foreach ($_POST as $k => $v) {
                if (substr($k, 0, 4) == "udf_") {
                    $udf[substr($k, 4)] = $v;
                }
            }
            $this->object->setUserDefinedData($udf);

            try {
                $this->object->updateLogin($_POST['login']);
            } catch (ilUserException $e) {
                ilUtil::sendFailure($e->getMessage());
                $this->form_gui->setValuesByPost();
                return $tpl->setContent($this->form_gui->getHtml());
            }

            $this->object->setTitle($this->object->getFullname());
            $this->object->setDescription($this->object->getEmail());

            if ($this->isSettingChangeable('language')) {
                $this->object->setLanguage($this->form_gui->getInput('language'));
            }

            if ($this->isSettingChangeable('skin_style')) {
                //set user skin and style
                $sknst = explode(":", $_POST["skin_style"]);

                if ($this->object->getPref("style") != $sknst[1] ||
                    $this->object->getPref("skin") != $sknst[0]) {
                    $this->object->setPref("skin", $sknst[0]);
                    $this->object->setPref("style", $sknst[1]);
                }
            }
            if ($this->isSettingChangeable('hits_per_page')) {
                $this->object->setPref("hits_per_page", $_POST["hits_per_page"]);
            }
            /*if($this->isSettingChangeable('show_users_online'))
            {
                $this->object->setPref("show_users_online", $_POST["show_users_online"]);
            }*/
            if ($this->isSettingChangeable('hide_own_online_status')) {
                $this->object->setPref("hide_own_online_status", $_POST["hide_own_online_status"]);
            }
            if ($this->isSettingChangeable('bs_allow_to_contact_me')) {
                $this->object->setPref('bs_allow_to_contact_me', $_POST['bs_allow_to_contact_me'] ? 'y' : 'n');
            }
            if ($this->isSettingChangeable('chat_osc_accept_msg')) {
                $this->object->setPref('chat_osc_accept_msg', $_POST['chat_osc_accept_msg'] ? 'y' : 'n');
            }

            // set a timestamp for last_password_change
            // this ts is needed by ilSecuritySettings
            $this->object->setLastPasswordChangeTS(time());

            global $DIC;

            $ilSetting = $DIC['ilSetting'];
            if ((int) $ilSetting->get('session_reminder_enabled')) {
                $this->object->setPref('session_reminder_enabled', (int) $_POST['session_reminder_enabled']);
            }

            // #10054 - profile may have been completed, check below is only for incomplete
            $this->object->setProfileIncomplete(false);

            $this->update = $this->object->update();


            // If the current user is editing its own user account,
            // we update his preferences.
            if ($ilUser->getId() == $this->object->getId()) {
                $ilUser->readPrefs();
            }
            $ilUser->setPref('send_info_mails', ($_POST['send_mail'] == 'y') ? 'y' : 'n');
            $ilUser->writePrefs();

            $mail_message = $this->__sendProfileMail();
            $msg = $this->lng->txt('saved_successfully') . $mail_message;

            // same personal image
            if ($this->isSettingChangeable('upload')) {
                $this->uploadUserPictureObject();
            }

            if ($profileMaybeIncomplete) {
                include_once 'Services/User/classes/class.ilUserProfile.php';
                if (ilUserProfile::isProfileIncomplete($this->object)) {
                    $this->object->setProfileIncomplete(true);
                    $this->object->update();
                }
            }

            // feedback
            ilUtil::sendSuccess($msg, true);

            if (strtolower($_GET["baseClass"]) == 'iladministrationgui') {
                $this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
            } else {
                $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
            }
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHtml());
        }
    }

    /**
    * Get values from user object and put them into form
    */
    public function getValues()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        $data = array();

        // login data
        $data["auth_mode"] = $this->object->getAuthMode();
        $data["login"] = $this->object->getLogin();
        //$data["passwd"] = "********";
        //$data["passwd2"] = "********";
        $data["ext_account"] = $this->object->getExternalAccount();

        // system information
        $data["create_date"] = ilDatePresentation::formatDate(new ilDateTime($this->object->getCreateDate(), IL_CAL_DATETIME));
        $data["owner"] = ilObjUser::_lookupLogin($this->object->getOwner());
        $data["approve_date"] = ($this->object->getApproveDate() != "")
            ? ilDatePresentation::formatDate(new ilDateTime($this->object->getApproveDate(), IL_CAL_DATETIME))
            : null;
        $data["agree_date"] = ($this->object->getAgreeDate() != "")
            ? ilDatePresentation::formatDate(new ilDateTime($this->object->getAgreeDate(), IL_CAL_DATETIME))
            : null;
        $data["last_login"] = ($this->object->getLastLogin() != "")
             ? ilDatePresentation::formatDate(new ilDateTime($this->object->getLastLogin(), IL_CAL_DATETIME))
             : null;
        $data["active"] = $this->object->getActive();
        $data["time_limit_unlimited"] = $this->object->getTimeLimitUnlimited();

        $data["time_limit_from"] = $this->object->getTimeLimitFrom()
            ? new ilDateTime($this->object->getTimeLimitFrom(), IL_CAL_UNIX)
            : null;
        $data["time_limit_until"] = $this->object->getTimeLimitUntil()
            ? new ilDateTime($this->object->getTimeLimitUntil(), IL_CAL_UNIX)
            : null;

        // personal data
        $data["gender"] = $this->object->getGender();
        $data["firstname"] = $this->object->getFirstname();
        $data["lastname"] = $this->object->getLastname();
        $data["title"] = $this->object->getUTitle();
        $data['birthday'] = $this->object->getBirthday()
            ? new ilDate($this->object->getBirthday(), IL_CAL_DATE)
            : null;
        $data["institution"] = $this->object->getInstitution();
        $data["department"] = $this->object->getDepartment();
        $data["street"] = $this->object->getStreet();
        $data["city"] = $this->object->getCity();
        $data["zipcode"] = $this->object->getZipcode();
        $data["country"] = $this->object->getCountry();
        $data["sel_country"] = $this->object->getSelectedCountry();
        $data["phone_office"] = $this->object->getPhoneOffice();
        $data["phone_home"] = $this->object->getPhoneHome();
        $data["phone_mobile"] = $this->object->getPhoneMobile();
        $data["fax"] = $this->object->getFax();
        $data["email"] = $this->object->getEmail();
        $data["second_email"] = $this->object->getSecondEmail();
        $data["hobby"] = $this->object->getHobby();
        $data["referral_comment"] = $this->object->getComment();

        // interests
        $data["interests_general"] = $this->object->getGeneralInterests();
        $data["interests_help_offered"] = $this->object->getOfferingHelp();
        $data["interests_help_looking"] = $this->object->getLookingForHelp();

        // other data
        $data["matriculation"] = $this->object->getMatriculation();
        $data["client_ip"] = $this->object->getClientIP();

        // user defined fields
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $this->user_defined_fields = ilUserDefinedFields::_getInstance();
        $user_defined_data = $this->object->getUserDefinedData();
        foreach ($this->user_defined_fields->getDefinitions() as $field_id => $definition) {
            $data["udf_" . $field_id] = $user_defined_data["f_" . $field_id];
        }

        // settings
        $data["language"] = $this->object->getLanguage();
        $data["skin_style"] = $this->object->skin . ":" . $this->object->prefs["style"];
        $data["hits_per_page"] = $this->object->prefs["hits_per_page"];
        //$data["show_users_online"] = $this->object->prefs["show_users_online"];
        $data["hide_own_online_status"] = $this->object->prefs["hide_own_online_status"];
        $data['bs_allow_to_contact_me'] = $this->object->prefs['bs_allow_to_contact_me'] == 'y';
        $data['chat_osc_accept_msg'] = $this->object->prefs['chat_osc_accept_msg'] == 'y';
        $data["session_reminder_enabled"] = (int) $this->object->prefs["session_reminder_enabled"];

        $data["send_mail"] = ($this->object->prefs['send_info_mails'] == 'y');


        $this->form_gui->setValuesByArray($data);
    }

    /**
    * Init user form
    */
    public function initForm($a_mode)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $styleDefinition = $DIC['styleDefinition'];
        $ilSetting = $DIC['ilSetting'];
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $ilUser = $DIC['ilUser'];

        $settings = $ilSetting->getAll();

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
        if ($a_mode == "create") {
            $this->form_gui->setTitle($lng->txt("usr_new"));
        } else {
            $this->form_gui->setTitle($lng->txt("usr_edit"));
        }

        // login data
        $sec_l = new ilFormSectionHeaderGUI();
        $sec_l->setTitle($lng->txt("login_data"));
        $this->form_gui->addItem($sec_l);

        // authentication mode
        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
        $active_auth_modes = ilAuthUtils::_getActiveAuthModes();
        $am = new ilSelectInputGUI($lng->txt("auth_mode"), "auth_mode");
        $option = array();
        foreach ($active_auth_modes as $auth_name => $auth_key) {
            if ($auth_name == 'default') {
                $name = $this->lng->txt('auth_' . $auth_name) . " (" . $this->lng->txt('auth_' . ilAuthUtils::_getAuthModeName($auth_key)) . ")";
            } else {
                // begin-patch ldap_multiple
                #$name = $this->lng->txt('auth_'.$auth_name);
                include_once './Services/Authentication/classes/class.ilAuthUtils.php';
                $name = ilAuthUtils::getAuthModeTranslation($auth_key, $auth_name);
                // end-patch ldap_multiple
            }
            $option[$auth_name] = $name;
        }
        $am->setOptions($option);
        $this->form_gui->addItem($am);

        if ($a_mode == "edit") {
            $id = new ilNonEditableValueGUI($lng->txt("usr_id"), "id");
            $id->setValue($this->object->getId());
            $this->form_gui->addItem($id);
        }

        // login
        $lo = new ilUserLoginInputGUI($lng->txt("login"), "login");
        $lo->setRequired(true);
        if ($a_mode == "edit") {
            $lo->setCurrentUserId($this->object->getId());
            try {
                include_once 'Services/Calendar/classes/class.ilDate.php';

                $last_history_entry = ilObjUser::_getLastHistoryDataByUserId($this->object->getId());
                $lo->setInfo(
                    sprintf(
                        $this->lng->txt('usr_loginname_history_info'),
                        ilDatePresentation::formatDate(new ilDateTime($last_history_entry[1], IL_CAL_UNIX)),
                        $last_history_entry[0]
                    )
                );
            } catch (ilUserException $e) {
            }
        }

        $this->form_gui->addItem($lo);

        // passwords
        // @todo: do not show passwords, if there is not a single auth, that
        // allows password setting
        {
            $pw = new ilPasswordInputGUI($lng->txt("passwd"), "passwd");
            $pw->setUseStripSlashes(false);
            $pw->setSize(32);
            $pw->setMaxLength(80); // #17221
            $pw->setValidateAuthPost("auth_mode");
            if ($a_mode == "create") {
                $pw->setRequiredOnAuth(true);
            }
            $pw->setInfo(ilUtil::getPasswordRequirementsInfo());
            $this->form_gui->addItem($pw);
        }
        // @todo: invisible/hidden passwords

        // external account
        include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
        if (ilAuthUtils::_isExternalAccountEnabled()) {
            $ext = new ilTextInputGUI($lng->txt("user_ext_account"), "ext_account");
            $ext->setSize(40);
            $ext->setMaxLength(250);
            $ext->setInfo($lng->txt("user_ext_account_desc"));
            $this->form_gui->addItem($ext);
        }

        // login data
        $sec_si = new ilFormSectionHeaderGUI();
        $sec_si->setTitle($this->lng->txt("system_information"));
        $this->form_gui->addItem($sec_si);

        // create date, approve date, agreement date, last login
        if ($a_mode == "edit") {
            $sia = array("create_date", "approve_date", "agree_date", "last_login", "owner");
            foreach ($sia as $a) {
                $siai = new ilNonEditableValueGUI($lng->txt($a), $a);
                $this->form_gui->addItem($siai);
            }
        }

        // active
        $ac = new ilCheckboxInputGUI($lng->txt("active"), "active");
        $ac->setChecked(true);
        $this->form_gui->addItem($ac);

        // access	@todo: get fields right (names change)
        $lng->loadLanguageModule('crs');

        // access
        $radg = new ilRadioGroupInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
        $radg->setValue(1);
        $op1 = new ilRadioOption($lng->txt("user_access_unlimited"), 1);
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("user_access_limited"), 0);
        $radg->addOption($op2);

        //		$ac = new ilCheckboxInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
        //		$ac->setChecked(true);
        //		$ac->setOptionTitle($lng->txt("crs_unlimited"));

        // access.from
        $acfrom = new ilDateTimeInputGUI($this->lng->txt("crs_from"), "time_limit_from");
        $acfrom->setRequired(true);
        $acfrom->setShowTime(true);
        //		$ac->addSubItem($acfrom);
        $op2->addSubItem($acfrom);

        // access.to
        $acto = new ilDateTimeInputGUI($this->lng->txt("crs_to"), "time_limit_until");
        $acto->setRequired(true);
        $acto->setShowTime(true);
        //		$ac->addSubItem($acto);
        $op2->addSubItem($acto);

        //		$this->form_gui->addItem($ac);
        $this->form_gui->addItem($radg);

        // personal data
        if (
            $this->isSettingChangeable('gender') or
            $this->isSettingChangeable('firstname') or
            $this->isSettingChangeable('lastname') or
            $this->isSettingChangeable('title') or
            $this->isSettingChangeable('personal_image') or
            $this->isSettingChangeable('birhtday')
        ) {
            $sec_pd = new ilFormSectionHeaderGUI();
            $sec_pd->setTitle($this->lng->txt("personal_data"));
            $this->form_gui->addItem($sec_pd);
        }

        // gender
        if ($this->isSettingChangeable('gender')) {
            $gndr = new ilRadioGroupInputGUI($lng->txt("salutation"), "gender");
            $gndr->setRequired(isset($settings["require_gender"]) && $settings["require_gender"]);
            $neutral = new ilRadioOption($lng->txt("salutation_n"), "n");
            $gndr->addOption($neutral);
            $female = new ilRadioOption($lng->txt("salutation_f"), "f");
            $gndr->addOption($female);
            $male = new ilRadioOption($lng->txt("salutation_m"), "m");
            $gndr->addOption($male);
            $this->form_gui->addItem($gndr);
        }

        // firstname, lastname, title
        $fields = array("firstname" => true, "lastname" => true,
            "title" => isset($settings["require_title"]) && $settings["require_title"]);
        foreach ($fields as $field => $req) {
            if ($this->isSettingChangeable($field)) {
                // #18795
                $caption = ($field == "title")
                    ? "person_title"
                    : $field;
                $inp = new ilTextInputGUI($lng->txt($caption), $field);
                $inp->setSize(32);
                $inp->setMaxLength(32);
                $inp->setRequired($req);
                $this->form_gui->addItem($inp);
            }
        }

        // personal image
        if ($this->isSettingChangeable('upload')) {
            $pi = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
            if ($a_mode == "edit" || $a_mode == "upload") {
                $pi->setImage(ilObjUser::_getPersonalPicturePath(
                    $this->object->getId(),
                    "small",
                    true,
                    true
                ));
            }
            $this->form_gui->addItem($pi);
        }

        if ($this->isSettingChangeable('birthday')) {
            $birthday = new ilBirthdayInputGUI($lng->txt('birthday'), 'birthday');
            $birthday->setRequired(isset($settings["require_birthday"]) && $settings["require_birthday"]);
            $this->form_gui->addItem($birthday);
        }


        // institution, department, street, city, zip code, country, phone office
        // phone home, phone mobile, fax, e-mail
        $fields = array(
            array("institution", 40, 80),
            array("department", 40, 80),
            array("street", 40, 40),
            array("city", 40, 40),
            array("zipcode", 10, 10),
            array("country", 40, 40),
            array("sel_country"),
            array("phone_office", 30, 30),
            array("phone_home", 30, 30),
            array("phone_mobile", 30, 30),
            array("fax", 30, 30));

        $counter = 0;
        foreach ($fields as $field) {
            if (!$counter++ and $this->isSettingChangeable($field[0])) {
                // contact data
                $sec_cd = new ilFormSectionHeaderGUI();
                $sec_cd->setTitle($this->lng->txt("contact_data"));
                $this->form_gui->addItem($sec_cd);

                // org units
                if ($a_mode == "edit") {
                    $orgus = new ilNonEditableValueGUI($lng->txt('objs_orgu'), 'org_units');
                    $orgus->setValue($this->object->getOrgUnitsRepresentation());
                    $this->form_gui->addItem($orgus);
                }
            }
            if ($this->isSettingChangeable($field[0])) {
                if ($field[0] != "sel_country") {
                    $inp = new ilTextInputGUI($lng->txt($field[0]), $field[0]);
                    $inp->setSize($field[1]);
                    $inp->setMaxLength($field[2]);
                    $inp->setRequired(isset($settings["require_" . $field[0]]) &&
                        $settings["require_" . $field[0]]);
                    $this->form_gui->addItem($inp);
                } else {
                    // country selection
                    include_once("./Services/Form/classes/class.ilCountrySelectInputGUI.php");
                    $cs = new ilCountrySelectInputGUI($lng->txt($field[0]), $field[0]);
                    $cs->setRequired(isset($settings["require_" . $field[0]]) &&
                        $settings["require_" . $field[0]]);
                    $this->form_gui->addItem($cs);
                }
            }
        }

        // email
        if ($this->isSettingChangeable('email')) {
            $em = new ilEMailInputGUI($lng->txt("email"), "email");
            $em->setRequired(isset($settings["require_email"]) &&
                $settings["require_email"]);
            $this->form_gui->addItem($em);
        }

        // second email
        if ($this->isSettingChangeable('second_email')) {
            $em = new ilEMailInputGUI($lng->txt("second_email"), "second_email");

            $this->form_gui->addItem($em);
        }

        // interests/hobbies
        if ($this->isSettingChangeable('hobby')) {
            $hob = new ilTextAreaInputGUI($lng->txt("hobby"), "hobby");
            $hob->setRows(3);
            $hob->setCols(40);
            $hob->setRequired(isset($settings["require_hobby"]) &&
                $settings["require_hobby"]);
            $this->form_gui->addItem($hob);
        }

        // referral comment
        if ($this->isSettingChangeable('referral_comment')) {
            $rc = new ilTextAreaInputGUI($lng->txt("referral_comment"), "referral_comment");
            $rc->setRows(3);
            $rc->setCols(40);
            $rc->setRequired(isset($settings["require_referral_comment"]) &&
                $settings["require_referral_comment"]);
            $this->form_gui->addItem($rc);
        }


        // interests

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("interests"));
        $this->form_gui->addItem($sh);

        $multi_fields = array("interests_general", "interests_help_offered", "interests_help_looking");
        foreach ($multi_fields as $multi_field) {
            if ($this->isSettingChangeable($multi_field)) {
                // see ilUserProfile
                $ti = new ilTextInputGUI($lng->txt($multi_field), $multi_field);
                $ti->setMulti(true);
                $ti->setMaxLength(40);
                $ti->setSize(40);
                $ti->setRequired(isset($settings["require_" . $multi_field]) &&
                    $settings["require_" . $multi_field]);
                $this->form_gui->addItem($ti);
            }
        }


        // other information
        if ($this->isSettingChangeable('user_profile_other')) {
            $sec_oi = new ilFormSectionHeaderGUI();
            $sec_oi->setTitle($this->lng->txt("user_profile_other"));
            $this->form_gui->addItem($sec_oi);
        }

        // matriculation number
        if ($this->isSettingChangeable('matriculation')) {
            $mr = new ilTextInputGUI($lng->txt("matriculation"), "matriculation");
            $mr->setSize(40);
            $mr->setMaxLength(40);
            $mr->setRequired(isset($settings["require_matriculation"]) &&
                $settings["require_matriculation"]);
            $this->form_gui->addItem($mr);
        }

        // client IP
        $ip = new ilTextInputGUI($lng->txt("client_ip"), "client_ip");
        $ip->setSize(40);
        $ip->setMaxLength(255);
        $ip->setInfo($this->lng->txt("current_ip") . " " . $_SERVER["REMOTE_ADDR"] . " <br />" .
            '<span class="warning">' . $this->lng->txt("current_ip_alert") . "</span>");
        $this->form_gui->addItem($ip);

        // additional user defined fields
        include_once './Services/User/classes/class.ilUserDefinedFields.php';
        $user_defined_fields = ilUserDefinedFields::_getInstance();

        if ($this->usrf_ref_id == USER_FOLDER_ID) {
            $all_defs = $user_defined_fields->getDefinitions();
        } else {
            $all_defs = $user_defined_fields->getChangeableLocalUserAdministrationDefinitions();
        }

        foreach ($all_defs as $field_id => $definition) {
            include_once './Services/User/classes/class.ilCustomUserFieldsHelper.php';
            $f_property = ilCustomUserFieldsHelper::getInstance()->getFormPropertyForDefinition($definition, true);
            if ($f_property instanceof ilFormPropertyGUI) {
                $this->form_gui->addItem($f_property);
            }
        }

        // settings
        if (
            $a_mode == 'create' or
            $this->isSettingChangeable('language') or
            $this->isSettingChangeable('skin_style') or
            $this->isSettingChangeable('hits_per_page') or
            $this->isSettingChangeable('hide_own_online_status') or
            $this->isSettingChangeable('bs_allow_to_contact_me') or
            $this->isSettingChangeable('chat_osc_accept_msg')
        ) {
            $sec_st = new ilFormSectionHeaderGUI();
            $sec_st->setTitle($this->lng->txt("settings"));
            $this->form_gui->addItem($sec_st);
        }

        // role
        if ($a_mode == "create") {
            $role = new ilSelectInputGUI(
                $lng->txt("default_role"),
                'default_role'
            );
            $role->setRequired(true);
            $role->setValue($this->default_role);
            $role->setOptions($this->selectable_roles);
            $this->form_gui->addItem($role);
        }

        // language
        if ($this->isSettingChangeable('language')) {
            $lang = new ilSelectInputGUI(
                $lng->txt("language"),
                'language'
            );
            $languages = $lng->getInstalledLanguages();
            $lng->loadLanguageModule("meta");
            $options = array();
            foreach ($languages as $l) {
                $options[$l] = $lng->txt("meta_l_" . $l);
            }
            $lang->setOptions($options);
            $lang->setValue($ilSetting->get("language"));
            $this->form_gui->addItem($lang);
        }

        // skin/style
        if ($this->isSettingChangeable('skin_style')) {
            $sk = new ilSelectInputGUI(
                $lng->txt("skin_style"),
                'skin_style'
            );
            /**
             * @var ilStyleDefinition $styleDefinition
             */
            $skins = $styleDefinition->getAllSkins();

            $options = array();
            if (is_array($skins)) {
                $sk = new ilSelectInputGUI($this->lng->txt("skin_style"), "skin_style");

                $options = array();
                foreach ($skins as $skin) {
                    foreach ($skin->getStyles() as $style) {
                        include_once("./Services/Style/System/classes/class.ilSystemStyleSettings.php");
                        if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId())) {
                            continue;
                        }

                        $options[$skin->getId() . ":" . $style->getId()] = $skin->getName() . " / " . $style->getName();
                    }
                }
            }
            $sk->setOptions($options);
            $sk->setValue($ilClientIniFile->readVariable("layout", "skin") .
                    ":" . $ilClientIniFile->readVariable("layout", "style"));

            $this->form_gui->addItem($sk);
        }

        // hits per page
        if ($this->isSettingChangeable('hits_per_page')) {
            $hpp = new ilSelectInputGUI(
                $lng->txt("hits_per_page"),
                'hits_per_page'
            );
            $options = array(10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
                50 => 50, 100 => 100, 9999 => $this->lng->txt("no_limit"));
            $hpp->setOptions($options);
            $hpp->setValue($ilSetting->get("hits_per_page"));
            $this->form_gui->addItem($hpp);

            // users online
            /*$uo = new ilSelectInputGUI($lng->txt("users_online"),
                'show_users_online');
            $options = array(
                "y" => $lng->txt("users_online_show_y"),
                "associated" => $lng->txt("users_online_show_associated"),
                "n" => $lng->txt("users_online_show_n"));
            $uo->setOptions($options);
            $uo->setValue($ilSetting->get("show_users_online"));
            $this->form_gui->addItem($uo);*/
        }

        // hide online status
        if ($this->isSettingChangeable('hide_own_online_status')) {
            $lng->loadLanguageModule("awrn");

            $default = ($ilSetting->get('hide_own_online_status') == "n")
                ? $this->lng->txt("user_awrn_show")
                : $this->lng->txt("user_awrn_hide");

            $options = array(
                "" => $this->lng->txt("user_awrn_default") . " (" . $default . ")",
                "n" => $this->lng->txt("user_awrn_show"),
                "y" => $this->lng->txt("user_awrn_hide"));
            $os = new ilSelectInputGUI($lng->txt("awrn_user_show"), "hide_own_online_status");
            $os->setOptions($options);
            $os->setDisabled($ilSetting->get("usr_settings_disable_hide_own_online_status"));
            $os->setInfo($lng->txt("awrn_hide_from_awareness_info"));
            $this->form_gui->addItem($os);


            //$os = new ilCheckboxInputGUI($lng->txt("awrn_hide_from_awareness"), "hide_own_online_status");
            //$this->form_gui->addItem($os);
        }

        // allow to contact me
        if ($this->isSettingChangeable('bs_allow_to_contact_me')) {
            $lng->loadLanguageModule('buddysystem');
            $os = new ilCheckboxInputGUI($lng->txt('buddy_allow_to_contact_me'), 'bs_allow_to_contact_me');
            if ($a_mode == 'create') {
                $os->setChecked(ilUtil::yn2tf($ilSetting->get('bs_allow_to_contact_me', 'n')));
            }
            $this->form_gui->addItem($os);
        }
        if ($this->isSettingChangeable('chat_osc_accept_msg')) {
            $lng->loadLanguageModule('chatroom');
            $chat_osc_acm = new ilCheckboxInputGUI($lng->txt('chat_osc_accept_msg'), 'chat_osc_accept_msg');
            if ($a_mode == 'create') {
                $chat_osc_acm->setChecked(ilUtil::yn2tf($ilSetting->get('chat_osc_accept_msg', 'n')));
            }
            $this->form_gui->addItem($chat_osc_acm);
        }

        if ((int) $ilSetting->get('session_reminder_enabled')) {
            $cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
            $cb->setValue(1);
            $this->form_gui->addItem($cb);
        }

        // Options
        if ($this->isSettingChangeable('send_mail')) {
            $sec_op = new ilFormSectionHeaderGUI();
            $sec_op->setTitle($this->lng->txt("options"));
            $this->form_gui->addItem($sec_op);
        }

        // send email
        $se = new ilCheckboxInputGUI($lng->txt('inform_user_mail'), 'send_mail');
        $se->setInfo($lng->txt('inform_user_mail_info'));
        $se->setValue('y');
        $se->setChecked(($ilUser->getPref('send_info_mails') == 'y'));
        $this->form_gui->addItem($se);

        // ignore required fields
        $irf = new ilCheckboxInputGUI($lng->txt('ignore_required_fields'), 'ignore_rf');
        $irf->setInfo($lng->txt('ignore_required_fields_info'));
        $irf->setValue(1);
        $this->form_gui->addItem($irf);

        // @todo: handle all required fields

        // command buttons
        if ($a_mode == "create" || $a_mode == "save") {
            $this->form_gui->addCommandButton("save", $lng->txt("save"));
        }
        if ($a_mode == "edit" || $a_mode == "update") {
            $this->form_gui->addCommandButton("update", $lng->txt("save"));
        }
        $this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
    }

    /**
     * Check if setting is visible
     * This is the case when called from user folder.
     * Otherwise (category local user account depend on a setting)
     * @param array $settings
     * @param string $a_field
     * @return
     */
    protected function isSettingChangeable($a_field)
    {
        // TODO: Allow mixed field parameter to support checks against an array of field names.

        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        static $settings = null;



        if ($this->usrf_ref_id == USER_FOLDER_ID) {
            return true;
        }

        if ($settings == null) {
            $settings = $ilSetting->getAll();
        }
        return (bool) $settings['usr_settings_changeable_lua_' . $a_field];
    }


    /**
    * upload user image
    *
    * (original method by ratana ty)
    */
    public function uploadUserPictureObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacsystem = $DIC['rbacsystem'];

        // User folder
        if ($this->usrf_ref_id == USER_FOLDER_ID and
            !$rbacsystem->checkAccess('visible,read', $this->usrf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
        }
        // if called from local administration $this->usrf_ref_id is category id
        // Todo: this has to be fixed. Do not mix user folder id and category id
        if ($this->usrf_ref_id != USER_FOLDER_ID) {
            // check if user is assigned to category
            if (!$rbacsystem->checkAccess('cat_administrate_users', $this->object->getTimeLimitOwner())) {
                $this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_user"), $this->ilias->error_obj->MESSAGE);
            }
        }

        $userfile_input = $this->form_gui->getItemByPostVar("userfile");

        if ($_FILES["userfile"]["tmp_name"] == "") {
            if ($userfile_input->getDeletionFlag()) {
                $this->object->removeUserPicture();
            }
            return;
        }
        if ($_FILES["userfile"]["size"] == 0) {
            ilUtil::sendFailure($this->lng->txt("msg_no_file"));
        } else {
            $webspace_dir = ilUtil::getWebspaceDir();
            $image_dir = $webspace_dir . "/usr_images";
            $store_file = "usr_" . $this->object->getId() . "." . "jpg";

            // store filename
            $this->object->setPref("profile_image", $store_file);
            $this->object->update();

            // move uploaded file
            $pi = pathinfo($_FILES["userfile"]["name"]);
            $uploaded_file = $image_dir . "/upload_" . $this->object->getId() . "." . $pi["extension"];
            if (!ilUtil::moveUploadedFile(
                $_FILES["userfile"]["tmp_name"],
                $_FILES["userfile"]["name"],
                $uploaded_file,
                false
            )) {
                ilUtil::sendFailure($this->lng->txt("upload_error", true));
                $this->ctrl->redirect($this, "showProfile");
            }
            chmod($uploaded_file, 0770);

            // take quality 100 to avoid jpeg artefacts when uploading jpeg files
            // taking only frame [0] to avoid problems with animated gifs
            $show_file = "$image_dir/usr_" . $this->object->getId() . ".jpg";
            $thumb_file = "$image_dir/usr_" . $this->object->getId() . "_small.jpg";
            $xthumb_file = "$image_dir/usr_" . $this->object->getId() . "_xsmall.jpg";
            $xxthumb_file = "$image_dir/usr_" . $this->object->getId() . "_xxsmall.jpg";
            $uploaded_file = ilUtil::escapeShellArg($uploaded_file);
            $show_file = ilUtil::escapeShellArg($show_file);
            $thumb_file = ilUtil::escapeShellArg($thumb_file);
            $xthumb_file = ilUtil::escapeShellArg($xthumb_file);
            $xxthumb_file = ilUtil::escapeShellArg($xxthumb_file);

            if (ilUtil::isConvertVersionAtLeast("6.3.8-3")) {
                ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200^ -gravity center -extent 200x200 -quality 100 JPEG:" . $show_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100^ -gravity center -extent 100x100 -quality 100 JPEG:" . $thumb_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75^ -gravity center -extent 75x75 -quality 100 JPEG:" . $xthumb_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30^ -gravity center -extent 30x30 -quality 100 JPEG:" . $xxthumb_file);
            } else {
                ilUtil::execConvert($uploaded_file . "[0] -geometry 200x200 -quality 100 JPEG:" . $show_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 100x100 -quality 100 JPEG:" . $thumb_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 75x75 -quality 100 JPEG:" . $xthumb_file);
                ilUtil::execConvert($uploaded_file . "[0] -geometry 30x30 -quality 100 JPEG:" . $xxthumb_file);
            }
        }
    }

    /**
    * remove user image
    */
    public function removeUserPictureObject()
    {
        $webspace_dir = ilUtil::getWebspaceDir();
        $image_dir = $webspace_dir . "/usr_images";
        $file = $image_dir . "/usr_" . $this->object->getID() . "." . "jpg";
        $thumb_file = $image_dir . "/usr_" . $this->object->getID() . "_small.jpg";
        $xthumb_file = $image_dir . "/usr_" . $this->object->getID() . "_xsmall.jpg";
        $xxthumb_file = $image_dir . "/usr_" . $this->object->getID() . "_xxsmall.jpg";
        $upload_file = $image_dir . "/upload_" . $this->object->getID();

        // remove user pref file name
        $this->object->setPref("profile_image", "");
        $this->object->update();
        ilUtil::sendSuccess($this->lng->txt("user_image_removed"));

        if (@is_file($file)) {
            unlink($file);
        }
        if (@is_file($thumb_file)) {
            unlink($thumb_file);
        }
        if (@is_file($xthumb_file)) {
            unlink($xthumb_file);
        }
        if (@is_file($xxthumb_file)) {
            unlink($xxthumb_file);
        }
        if (@is_file($upload_file)) {
            unlink($upload_file);
        }

        $this->editObject();
    }

    /**
    * assign users to role
    *
    * @access	public
    */
    public function assignSaveObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];

        if (!$rbacsystem->checkAccess("edit_roleassignment", $this->usrf_ref_id)) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_role_to_user"), $this->ilias->error_obj->MESSAGE);
        }

        $selected_roles = $_POST["role_id"] ? $_POST["role_id"] : array();
        $posted_roles = $_POST["role_id_ctrl"] ? $_POST["role_id_ctrl"] : array();

        // prevent unassignment of system role from system user
        if ($this->object->getId() == SYSTEM_USER_ID and in_array(SYSTEM_ROLE_ID, $posted_roles)) {
            array_push($selected_roles, SYSTEM_ROLE_ID);
        }

        $global_roles_all = $rbacreview->getGlobalRoles();
        $assigned_roles_all = $rbacreview->assignedRoles($this->object->getId());
        $assigned_roles = array_intersect($assigned_roles_all, $posted_roles);
        $assigned_global_roles_all = array_intersect($assigned_roles_all, $global_roles_all);
        $assigned_global_roles = array_intersect($assigned_global_roles_all, $posted_roles);
        $posted_global_roles = array_intersect($selected_roles, $global_roles_all);

        if ((empty($selected_roles) and count($assigned_roles_all) == count($assigned_roles))
             or (empty($posted_global_roles) and count($assigned_global_roles_all) == count($assigned_global_roles))) {
            //$this->ilias->raiseError($this->lng->txt("msg_min_one_role")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
            // workaround. sometimes jumps back to wrong page
            ilUtil::sendFailure($this->lng->txt("msg_min_one_role") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, 'roleassignment');
        }

        foreach (array_diff($assigned_roles, $selected_roles) as $role) {
            $rbacadmin->deassignUser($role, $this->object->getId());
        }

        foreach (array_diff($selected_roles, $assigned_roles) as $role) {
            $rbacadmin->assignUser($role, $this->object->getId(), false);
        }

        include_once "./Services/AccessControl/classes/class.ilObjRole.php";

        // update object data entry (to update last modification date)
        $this->object->update();

        ilUtil::sendSuccess($this->lng->txt("msg_roleassignment_changed"), true);

        if (strtolower($_GET["baseClass"]) == 'iladministrationgui') {
            $this->ctrl->redirect($this, 'roleassignment');
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }

    /**
    * display roleassignment panel
    *
    * @access	public
    */
    public function roleassignmentObject()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilTabs = $DIC['ilTabs'];
        $access = $DIC->access();

        $ilTabs->activateTab("role_assignment");

        if (!$rbacsystem->checkAccess("edit_roleassignment", $this->usrf_ref_id) &&
            !$access->isCurrentUserBasedOnPositionsAllowedTo("read_users", array($this->object->getId()))
        ) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_role_to_user"), $this->ilias->error_obj->MESSAGE);
        }

        $_SESSION['filtered_roles'] = isset($_POST['filter']) ? $_POST['filter'] : $_SESSION['filtered_roles'];

        if ($_SESSION['filtered_roles'] > 5) {
            $_SESSION['filtered_roles'] = 0;
        }

        $this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.usr_role_assignment.html', 'Services/User');

        if (false) {
            $this->tpl->setCurrentBlock("form_filter");
            $this->tpl->setVariable("FILTER_TXT_FILTER", $this->lng->txt('filter'));
            $this->tpl->setVariable("SELECT_FILTER", $this->__buildFilterSelect());
            $this->tpl->setVariable("FILTER_ACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("FILTER_NAME", 'roleassignment');
            $this->tpl->setVariable("FILTER_VALUE", $this->lng->txt('apply_filter'));
            $this->tpl->parseCurrentBlock();
        }

        // init table
        include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
        $tab = new ilRoleAssignmentTableGUI($this, "roleassignment");

        $tab->parse($this->object->getId());
        $this->tpl->setVariable("ROLES_TABLE", $tab->getHTML());
    }

    /**
    * Apply filter
    */
    public function applyFilterObject()
    {
        include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
        $table_gui = new ilRoleAssignmentTableGUI($this, "roleassignment");
        $table_gui->writeFilterToSession();        // writes filter to session
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $this->roleassignmentObject();
    }

    /**
    * Reset filter
    */
    public function resetFilterObject()
    {
        include_once("./Services/User/classes/class.ilRoleAssignmentTableGUI.php");
        $table_gui = new ilRoleAssignmentTableGUI($this, "roleassignment");
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $table_gui->resetFilter();                // clears filter
        $this->roleassignmentObject();
    }

    public function __getDateSelect($a_type, $a_varname, $a_selected)
    {
        switch ($a_type) {
            case "minute":
                for ($i = 0;$i <= 60;$i++) {
                    $days[$i] = $i < 10 ? "0" . $i : $i;
                }
                return ilUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case "hour":
                for ($i = 0;$i < 24;$i++) {
                    $days[$i] = $i < 10 ? "0" . $i : $i;
                }
                return ilUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case "day":
                for ($i = 1;$i < 32;$i++) {
                    $days[$i] = $i < 10 ? "0" . $i : $i;
                }
                return ilUtil::formSelect($a_selected, $a_varname, $days, false, true);

            case "month":
                for ($i = 1;$i < 13;$i++) {
                    $month[$i] = $i < 10 ? "0" . $i : $i;
                }
                return ilUtil::formSelect($a_selected, $a_varname, $month, false, true);

            case "year":
                if ($a_selected < date('Y', time())) {
                    $start = $a_selected;
                } else {
                    $start = date('Y', time());
                }

                for ($i = $start;$i < date("Y", time()) + 11;++$i) {
                    $year[$i] = $i;
                }
                return ilUtil::formSelect($a_selected, $a_varname, $year, false, true);
        }
    }

    public function __toUnix($a_time_arr)
    {
        return mktime(
            $a_time_arr["hour"],
            $a_time_arr["minute"],
            $a_time_arr["second"],
            $a_time_arr["month"],
            $a_time_arr["day"],
            $a_time_arr["year"]
        );
    }




    public function __unsetSessionVariables()
    {
        unset($_SESSION["filtered_roles"]);
    }

    public function __buildFilterSelect()
    {
        $action[0] = $this->lng->txt('assigned_roles');
        $action[1] = $this->lng->txt('all_roles');
        $action[2] = $this->lng->txt('all_global_roles');
        $action[3] = $this->lng->txt('all_local_roles');
        $action[4] = $this->lng->txt('internal_local_roles_only');
        $action[5] = $this->lng->txt('non_internal_local_roles_only');

        return ilUtil::formSelect($_SESSION['filtered_roles'], "filter", $action, false, true);
    }

    public function hitsperpageObject()
    {
        parent::hitsperpageObject();
        $this->roleassignmentObject();
    }

    /**
    * should be overwritten to add object specific items
    * (repository items are preloaded)
    */
    public function addAdminLocatorItems($a_do_not_add_object = false)
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];

        $ilLocator->clearItems();

        if ($_GET["admin_mode"] == "settings") {	// system settings
            $this->ctrl->setParameterByClass(
                "ilobjsystemfoldergui",
                "ref_id",
                SYSTEM_FOLDER_ID
            );
            $ilLocator->addItem(
                $this->lng->txt("administration"),
                $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjsystemfoldergui"), ""),
                ilFrameTargetInfo::_getFrame("MainContent")
            );

            if ($_GET['ref_id'] == USER_FOLDER_ID) {
                $ilLocator->addItem(
                    $this->lng->txt("obj_" . ilObject::_lookupType(
                        ilObject::_lookupObjId($_GET["ref_id"])
                    )),
                    $this->ctrl->getLinkTargetByClass("ilobjuserfoldergui", "view")
                );
            } elseif ($_GET['ref_id'] == ROLE_FOLDER_ID) {
                $ilLocator->addItem(
                    $this->lng->txt("obj_" . ilObject::_lookupType(
                        ilObject::_lookupObjId($_GET["ref_id"])
                    )),
                    $this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view")
                );
            }

            if ($_GET["obj_id"] > 0) {
                $ilLocator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, "view")
                );
            }
        } else {							// repository administration
            // ?
        }
    }

    public function showUpperIcon()
    {
    }

    public function __sendProfileMail()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilias = $DIC['ilias'];

        if ($_POST['send_mail'] != 'y') {
            return '';
        }
        if (!strlen($this->object->getEmail())) {
            return '';
        }

        // Choose language of user
        $usr_lang = new ilLanguage($this->object->getLanguage());
        $usr_lang->loadLanguageModule('crs');
        $usr_lang->loadLanguageModule('registration');

        include_once "Services/Mail/classes/class.ilMimeMail.php";

        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS['DIC']["mail.mime.sender.factory"];

        $mmail = new ilMimeMail();
        $mmail->From($senderFactory->system());

        $mailOptions = new \ilMailOptions($this->object->getId());
        $mmail->To($mailOptions->getExternalEmailAddresses());

        // mail subject
        $subject = $usr_lang->txt("profile_changed");


        // mail body
        $body = $usr_lang->txt("reg_mail_body_salutation")
            . " " . $this->object->getFullname() . ",\n\n";

        $date = $this->object->getApproveDate();
        // Approve
        if ((time() - strtotime($date)) < 10) {
            $body .= $usr_lang->txt('reg_mail_body_approve') . "\n\n";
        } else {
            $body .= $usr_lang->txt('reg_mail_body_profile_changed') . "\n\n";
        }

        // Append login info only if password has been chacnged
        if ($_POST['passwd'] != '') {
            $body .= $usr_lang->txt("reg_mail_body_text2") . "\n" .
                ILIAS_HTTP_PATH . "/login.php?client_id=" . $ilias->client_id . "\n" .
                $usr_lang->txt("login") . ": " . $this->object->getLogin() . "\n" .
                $usr_lang->txt("passwd") . ": " . $_POST['passwd'] . "\n\n";
        }
        $body .= $usr_lang->txt("reg_mail_body_text3") . "\n";
        $body .= $this->object->getProfileAsString($usr_lang);
        $body .= ilMail::_getInstallationSignature();


        $mmail->Subject($subject, true);
        $mmail->Body($body);
        $mmail->Send();


        return "<br/>" . $this->lng->txt("mail_sent");
    }

    /**
     * Goto user profile screen
     */
    public static function _goto($a_target)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];

        // #10888
        if ($a_target == md5("usrdelown")) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID &&
                $ilUser->hasDeletionFlag()) {
                $ilCtrl->initBaseClass("ildashboardgui");
                $ilCtrl->redirectByClass(array("ildashboardgui", "ilpersonalsettingsgui"), "deleteOwnAccount3");
            }
            exit("This account is not flagged for deletion."); // #12160
        }

        // badges
        if (substr($a_target, -4) == "_bdg") {
            $_GET["baseClass"] = "ilDashboardGUI";
            $_GET["cmd"] = "jumpToBadges";
            include("ilias.php");
            exit();
        }

        if ('registration' == $a_target) {
            $_GET["baseClass"] = 'ilStartUpGUI';
            $ilCtrl->redirectByClass(array('ilStartUpGUI', 'ilAccountRegistrationGUI'), '');
        } elseif ('nameassist' == $a_target) {
            $_GET["baseClass"] = 'ilStartUpGUI';
            $ilCtrl->redirectByClass(array('ilStartUpGUI', 'ilPasswordAssistanceGUI'), 'showUsernameAssistanceForm');
        } elseif ('pwassist' == $a_target) {
            $_GET["baseClass"] = 'ilStartUpGUI';
            $ilCtrl->redirectByClass(array('ilStartUpGUI', 'ilPasswordAssistanceGUI'), '');
        } elseif ('agreement' == $a_target) {
            if ($ilUser->getId() > 0 && !$ilUser->isAnonymous()) {
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->initBaseClass('ildashboardgui');
                $ilCtrl->redirectByClass(array('ildashboardgui', 'ilpersonalprofilegui'), 'showUserAgreement');
            } else {
                $_GET['baseClass'] = 'ilStartUpGUI';
                $ilCtrl->setTargetScript('ilias.php');
                $ilCtrl->redirectByClass(array('ilStartUpGUI'), 'showTermsOfService');
            }
        }

        if (substr($a_target, 0, 1) == "n") {
            $a_target = ilObjUser::_lookupId(ilUtil::stripSlashes(substr($a_target, 1)));
        }

        if (strpos($a_target, 'contact_approved') !== false) {
            $_GET['cmd'] = 'approveContactRequest';
        } elseif (strpos($a_target, 'contact_ignored') !== false) {
            $_GET['cmd'] = 'ignoreContactRequest';
        } else {
            $_GET['cmd'] = 'view';
        }

        $_GET["user_id"] = (int) $a_target;
        $_GET["baseClass"] = "ilPublicUserProfileGUI";
        $_GET["cmdClass"] = "ilpublicuserprofilegui";
        include("ilias.php");
        exit;
    }

    /**
     *
     * Handles ignored required fields by changing the required flag of form elements
     *
     * @access	protected
     * @return	boolean	A flag whether the user profile is maybe incomplete after saving the form data
     *
     */
    protected function handleIgnoredRequiredFields()
    {
        $profileMaybeIncomplete = false;

        require_once 'Services/User/classes/class.ilUserProfile.php';

        foreach (ilUserProfile::getIgnorableRequiredSettings() as $fieldName) {
            $elm = $this->form_gui->getItemByPostVar($fieldName);

            if (!$elm) {
                continue;
            }

            if ($elm->getRequired()) {
                $profileMaybeIncomplete = true;

                // Flag as optional
                $elm->setRequired(false);
            }
        }

        include_once 'Services/User/classes/class.ilUserDefinedFields.php';
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        foreach ($user_defined_fields->getDefinitions() as $field_id => $definition) {
            $elm = $this->form_gui->getItemByPostVar('udf_' . $definition['field_id']);

            if (!$elm) {
                continue;
            }

            if ($elm->getRequired() && $definition['changeable'] && $definition['required'] && $definition['visible']) {
                $profileMaybeIncomplete = true;

                // Flag as optional
                $elm->setRequired(false);
            }
        }

        return $profileMaybeIncomplete;
    }

    /**
     *
     */
    protected function showAcceptedTermsOfService()
    {
        /** @var $agreeDate ilNonEditableValueGUI */
        $agreeDate = $this->form_gui->getItemByPostVar('agree_date');
        if ($agreeDate && $agreeDate->getValue()) {
            $this->lng->loadLanguageModule('tos');
            $helper = new \ilTermsOfServiceHelper();

            $entity = $helper->getCurrentAcceptanceForUser($this->object);
            if ($entity->getId()) {
                $modal = $this->uiFactory
                    ->modal()
                    ->lightbox([
                        $this->uiFactory->modal()->lightboxTextPage($entity->getText(), $entity->getTitle())
                    ]);

                $titleLink = $this->uiFactory
                    ->button()
                    ->shy($entity->getTitle(), '#')
                    ->withOnClick($modal->getShowSignal());

                $agreementDocument = new ilNonEditableValueGUI(
                    $this->lng->txt('tos_agreement_document'),
                    '',
                    true
                );
                $agreementDocument->setValue($this->uiRenderer->render([$titleLink, $modal]));
                $agreeDate->addSubItem($agreementDocument);
            }
        } elseif ($agreeDate) {
            $agreeDate->setValue($this->lng->txt('tos_not_accepted_yet'));
        }
    }
} // END class.ilObjUserGUI
