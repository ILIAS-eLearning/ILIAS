<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjUserFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilUserTableGUI
* @ilCtrl_Calls ilObjUserFolderGUI: ilAccountCodesGUI, ilCustomUserFieldsGUI, ilRepositorySearchGUI, ilUserStartingPointGUI
* @ilCtrl_Calls ilObjUserFolderGUI: ilUserProfileInfoSettingsGUI
*
* @ingroup ServicesUser
*/
class ilObjUserFolderGUI extends ilObjectGUI
{
    public $ctrl;

    protected $log;

    /** @var ilObjUserFolder */
    public $object;

    /**
     * @var ilUserSettingsConfig
     */
    protected $user_settings_config;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        // TODO: move this to class.ilias.php
        define('USER_FOLDER_ID', 7);
        $this->type = "usrf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        
        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule("user");

        $ilCtrl->saveParameter($this, "letter");

        $this->user_settings_config = new ilUserSettingsConfig();

        $this->log = ilLoggerFactory::getLogger("user");
    }

    public function setUserOwnerId($a_id)
    {
        $this->user_owner_id = $a_id;
    }
    public function getUserOwnerId()
    {
        return $this->user_owner_id ? $this->user_owner_id : USER_FOLDER_ID;
    }

    public function executeCommand()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $access = $DIC->access();
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilusertablegui':
                include_once("./Services/User/classes/class.ilUserTableGUI.php");
                $u_table = new ilUserTableGUI($this, "view");
                $u_table->initFilter();
                $this->ctrl->setReturn($this, 'view');
                $this->ctrl->forwardCommand($u_table);
                break;

            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
                
            case 'ilrepositorysearchgui':

                if (!$access->checkRbacOrPositionPermissionAccess("read_users", \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, USER_FOLDER_ID)) {
                    $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
                }

                include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($this->lng->txt("search_user_extended")); // #17502
                $user_search->enableSearchableCheck(false);
                $user_search->setUserLimitations(false);
                $user_search->setCallback(
                    $this,
                    'searchResultHandler',
                    $this->getUserMultiCommands(true)
                );
                $user_search->addUserAccessFilterCallable(array($this, "searchUserAccessFilterCallable"));
                $this->tabs_gui->setTabActive('search_user_extended');
                $this->ctrl->setReturn($this, 'view');
                $ret = &$this->ctrl->forwardCommand($user_search);
                break;
            
            case 'ilaccountcodesgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("account_codes");
                include_once("./Services/User/classes/class.ilAccountCodesGUI.php");
                $acc = new ilAccountCodesGUI($this->ref_id);
                $this->ctrl->forwardCommand($acc);
                break;
            
            case 'ilcustomuserfieldsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("user_defined_fields");
                include_once("./Services/User/classes/class.ilCustomUserFieldsGUI.php");
                $cf = new ilCustomUserFieldsGUI();
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserstartingpointgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("starting_points");
                include_once("./Services/User/classes/class.ilUserStartingPointGUI.php");
                $cf = new ilUserStartingPointGUI($this->ref_id);
                $this->ctrl->forwardCommand($cf);
                break;

            case 'iluserprofileinfosettingsgui':
                $this->tabs_gui->setTabActive('settings');
                $this->setSubTabs("settings");
                $ilTabs->activateSubTab("user_profile_info");
                $ps = new ilUserProfileInfoSettingsGUI();
                $this->ctrl->forwardCommand($ps);
                break;

            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }

    /**
     * @param string $a_permission
     */
    protected function checkAccess($a_permission)
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];

        if (!$this->checkAccessBool($a_permission)) {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->WARNING);
        }
    }

    /**
     * @param string $a_permission
     * @return bool
     */
    protected function checkAccessBool($a_permission)
    {
        return $this->access->checkAccess($a_permission, '', $this->ref_id);
    }

    public function learningProgressObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tpl = $DIC['tpl'];
        
        // deprecated JF 27 May 2013
        exit();

        if (!$rbacsystem->checkAccess("read", $this->object->getRefId()) ||
            !ilObjUserTracking::_enabledLearningProgress() ||
            !ilObjUserTracking::_enabledUserRelatedData()) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        
        include_once "Services/User/classes/class.ilUserLPTableGUI.php";
        $tbl = new ilUserLPTableGUI($this, "learningProgress", $this->object->getRefId());
        
        $tpl->setContent($tbl->getHTML());
    }
    
    /**
    * Reset filter
    * (note: this function existed before data table filter has been introduced
    */
    public function resetFilterObject()
    {
        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "view");
        $utab->resetOffset();
        $utab->resetFilter();

        // from "old" implementation
        $this->viewObject(true);
    }

    /**
    * Add new user;
    */
    public function addUserObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->setParameterByClass("ilobjusergui", "new_type", "usr");
        $ilCtrl->redirectByClass(array("iladministrationgui", "ilobjusergui"), "create");
    }
    
    
    /**
    * Apply filter
    */
    public function applyFilterObject()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "view");
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->viewObject();
        $ilTabs->activateTab("usrf");
    }

    /**
    * list users
    *
    * @access	public
    */
    public function viewObject($reset_filter = false)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilToolbar = $DIC->toolbar();
        $tpl = $DIC['tpl'];
        $ilSetting = $DIC['ilSetting'];
        $access = $DIC->access();
        $user_filter = null;
        
        include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";

        if ($rbacsystem->checkAccess('create_usr', $this->object->getRefId()) ||
            $rbacsystem->checkAccess('cat_administrate_users', $this->object->getRefId())) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("usr_add");
            $button->setUrl($this->ctrl->getLinkTarget($this, "addUser"));
            $ilToolbar->addButtonInstance($button);

            $button = ilLinkButton::getInstance();
            $button->setCaption("import_users");
            $button->setUrl($this->ctrl->getLinkTarget($this, "importUserForm"));
            $ilToolbar->addButtonInstance($button);
        }

        if (
            !$access->checkAccess('read_users', '', USER_FOLDER_ID) &&
            $access->checkRbacOrPositionPermissionAccess(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID
            )) {
            $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
            $user_filter = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $users
            );
        }

        // alphabetical navigation
        if ((int) $ilSetting->get('user_adm_alpha_nav')) {
            if (count($ilToolbar->getItems()) > 0) {
                $ilToolbar->addSeparator();
            }

            // alphabetical navigation
            include_once("./Services/Form/classes/class.ilAlphabetInputGUI.php");
            $ai = new ilAlphabetInputGUI("", "first");
            include_once("./Services/User/classes/class.ilObjUser.php");
            $ai->setLetters(ilObjUser::getFirstLettersOfLastnames($user_filter));
            $ai->setParentCommand($this, "chooseLetter");
            $ai->setHighlighted($_GET["letter"]);
            $ilToolbar->addInputItem($ai, true);
        }

        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "view", ilUserTableGUI::MODE_USER_FOLDER, false);
        $utab->addFilterItemValue('user_ids', $user_filter);
        $utab->getItems();

        $tpl->setContent($utab->getHTML());
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoCompleteObject()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->addUserAccessFilterCallable([$this,'filterUserIdsByRbacOrPositionOfCurrentUser']);
        $auto->setSearchFields(array('login','firstname','lastname','email', 'second_email'));
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    /**
     * @param int[] $user_ids
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $user_ids)
    {
        global $DIC;

        $access = $DIC->access();
        return $access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'read_users',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID,
            $user_ids
        );
    }

    /**
     * Choose first letter
     *
     * @param
     * @return
     */
    public function chooseLetterObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->redirect($this, "view");
    }

    
    /**
    * show possible action (form buttons)
    *
    * @param	boolean
    * @access	public
    */
    public function showActions($with_subobjects = false)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $operations = array();
        //var_dump($this->actions);
        if ($this->actions == "") {
            $d = array(
                "delete" => array("name" => "delete", "lng" => "delete"),
                "activate" => array("name" => "activate", "lng" => "activate"),
                "deactivate" => array("name" => "deactivate", "lng" => "deactivate"),
                "accessRestrict" => array("name" => "accessRestrict", "lng" => "accessRestrict"),
                "accessFree" => array("name" => "accessFree", "lng" => "accessFree"),
                "export" => array("name" => "export", "lng" => "export")
            );
        } else {
            $d = $this->actions;
        }
        foreach ($d as $row) {
            if ($rbacsystem->checkAccess($row["name"], $this->object->getRefId())) {
                $operations[] = $row;
            }
        }

        if (count($operations) > 0) {
            $select = "<select name=\"selectedAction\">\n";
            foreach ($operations as $val) {
                $select .= "<option value=\"" . $val["name"] . "\"";
                if (strcmp($_POST["selectedAction"], $val["name"]) == 0) {
                    $select .= " selected=\"selected\"";
                }
                $select .= ">";
                $select .= $this->lng->txt($val["lng"]);
                $select .= "</option>";
            }
            $select .= "</select>";
            $this->tpl->setCurrentBlock("tbl_action_select");
            $this->tpl->setVariable("SELECT_ACTION", $select);
            $this->tpl->setVariable("BTN_NAME", "userAction");
            $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("submit"));
            $this->tpl->parseCurrentBlock();
        }

        if ($with_subobjects === true) {
            $subobjs = $this->showPossibleSubObjects();
        }

        if ((count($operations) > 0) or $subobjs === true) {
            $this->tpl->setCurrentBlock("tbl_action_row");
            $this->tpl->setVariable("COLUMN_COUNTS", count($this->data["cols"]));
            $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
            $this->tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
    * show possible subobjects (pulldown menu)
    * overwritten to prevent displaying of role templates in local role folders
    *
    * @access	public
    */
    public function showPossibleSubObjects()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
        
        if (!$rbacsystem->checkAccess('create_usr', $this->object->getRefId())) {
            unset($d["usr"]);
        }

        if (count($d) > 0) {
            foreach ($d as $row) {
                $count = 0;
                if ($row["max"] > 0) {
                    //how many elements are present?
                    for ($i = 0; $i < count($this->data["ctrl"]); $i++) {
                        if ($this->data["ctrl"][$i]["type"] == $row["name"]) {
                            $count++;
                        }
                    }
                }
                if ($row["max"] == "" || $count < $row["max"]) {
                    $subobj[] = $row["name"];
                }
            }
        }

        if (is_array($subobj)) {
            //build form
            $opts = ilUtil::formSelect(12, "new_type", $subobj);
            $this->tpl->setCurrentBlock("add_object");
            $this->tpl->setVariable("SELECT_OBJTYPE", $opts);
            $this->tpl->setVariable("BTN_NAME", "create");
            $this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
            $this->tpl->parseCurrentBlock();
            
            return true;
        }

        return false;
    }

    public function cancelUserFolderActionObject()
    {
        $this->ctrl->redirect($this, 'view');
    }
    
    public function cancelSearchActionObject()
    {
        $this->ctrl->redirectByClass('ilrepositorysearchgui', 'showSearchResults');
    }

    /**
    * Set the selected users active
    *
    * @access	public
    */
    public function confirmactivateObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->WARNING);
        }
        
        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId($id, false);
            if ($obj instanceof \ilObjUser) {
                $obj->setActive(true, $ilUser->getId());
                $obj->update();
            }
        }

        ilUtil::sendSuccess($this->lng->txt("user_activated"), true);

        if ($_POST["frsrch"]) {
            $this->ctrl->redirectByClass('ilRepositorySearchGUI', 'show');
        } else {
            $this->ctrl->redirect($this, "view");
        }
    }

    /**
    * Set the selected users inactive
    *
    * @access	public
    */
    public function confirmdeactivateObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->WARNING);
        }
        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId($id, false);
            if ($obj instanceof \ilObjUser) {
                $obj->setActive(false, $ilUser->getId());
                $obj->update();
            }
        }

        // Feedback
        ilUtil::sendSuccess($this->lng->txt("user_deactivated"), true);

        if ($_POST["frsrch"]) {
            $this->ctrl->redirectByClass('ilRepositorySearchGUI', 'show');
        } else {
            $this->ctrl->redirect($this, "view");
        }
    }
    
    /**
     * "access free"
     */
    protected function confirmaccessFreeObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->WARNING);
        }
        
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId($id, false);
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(1);
                $obj->setTimeLimitFrom("");
                $obj->setTimeLimitUntil("");
                $obj->setTimeLimitMessage(0);
                $obj->update();
            }
        }

        // Feedback
        ilUtil::sendSuccess($this->lng->txt("access_free_granted"), true);

        if ($_POST["frsrch"]) {
            $this->ctrl->redirectByClass('ilRepositorySearchGUI', 'show');
        } else {
            $this->ctrl->redirect($this, "view");
        }
    }
    
    public function setAccessRestrictionObject($a_form = null, $a_from_search = false)
    {
        if (!$a_form) {
            $a_form = $this->initAccessRestrictionForm($a_from_search);
        }
        $this->tpl->setContent($a_form->getHTML());
        
        // #10963
        return true;
    }
    
    /**
     * @param bool $a_from_search
     * @return \ilPropertyFormGUI|void
     */
    protected function initAccessRestrictionForm($a_from_search = false)
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            return $this->viewObject();
        }
                        
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("time_limit_add_time_limit_for_selected"));
        $form->setFormAction($this->ctrl->getFormAction($this, "confirmaccessRestrict"));
        
        $from = new ilDateTimeInputGUI($this->lng->txt("access_from"), "from");
        $from->setShowTime(true);
        $from->setRequired(true);
        $form->addItem($from);
        
        $to = new ilDateTimeInputGUI($this->lng->txt("access_until"), "to");
        $to->setRequired(true);
        $to->setShowTime(true);
        $form->addItem($to);
        
        $form->addCommandButton("confirmaccessRestrict", $this->lng->txt("confirm"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));
        
        foreach ($user_ids as $user_id) {
            $ufield = new ilHiddenInputGUI("id[]");
            $ufield->setValue($user_id);
            $form->addItem($ufield);
        }
        
        // return to search?
        if ($a_from_search || $_POST["frsrch"]) {
            $field = new ilHiddenInputGUI("frsrch");
            $field->setValue(1);
            $form->addItem($field);
        }
        
        return $form;
    }

    /**
     * @return bool
     * @throws \ilDatabaseException
     * @throws \ilObjectNotFoundException
     */
    protected function confirmaccessRestrictObject()
    {
        global $DIC;

        $ilUser = $DIC->user();

        $form = $this->initAccessRestrictionForm();
        if (!$form->checkInput()) {
            return $this->setAccessRestrictionObject($form);
        }
        
        $timefrom = $form->getItemByPostVar("from")->getDate()->get(IL_CAL_UNIX);
        $timeuntil = $form->getItemByPostVar("to")->getDate()->get(IL_CAL_UNIX);
        if ($timeuntil <= $timefrom) {
            ilUtil::sendFailure($this->lng->txt("time_limit_not_valid"));
            return $this->setAccessRestrictionObject($form);
        }


        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->WARNING);
        }
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId($id, false);
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(0);
                $obj->setTimeLimitFrom($timefrom);
                $obj->setTimeLimitUntil($timeuntil);
                $obj->setTimeLimitMessage(0);
                $obj->update();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("access_restricted"), true);

        if ($_POST["frsrch"]) {
            $this->ctrl->redirectByClass('ilRepositorySearchGUI', 'show');
        } else {
            $this->ctrl->redirect($this, "view");
        }
    }

    /**
    * confirm delete Object
    *
    * @access	public
    */
    public function confirmdeleteObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        // FOR NON_REF_OBJECTS WE CHECK ACCESS ONLY OF PARENT OBJECT ONCE
        if (!$rbacsystem->checkAccess('delete', $this->object->getRefId())) {
            ilUtil::sendFailure($this->lng->txt("msg_no_perm_delete"), true);
            $ilCtrl->redirect($this, "view");
        }
        
        if (in_array($ilUser->getId(), $_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("msg_no_delete_yourself"), $this->ilias->error_obj->WARNING);
        }

        // FOR ALL SELECTED OBJECTS
        foreach ($_POST["id"] as $id) {
            // instatiate correct object class (usr)
            $obj = &$this->ilias->obj_factory->getInstanceByObjId($id);
            $obj->delete();
        }

        // Feedback
        ilUtil::sendSuccess($this->lng->txt("user_deleted"), true);
                
        if ($_POST["frsrch"]) {
            $this->ctrl->redirectByClass('ilRepositorySearchGUI', 'show');
        } else {
            $this->ctrl->redirect($this, "view");
        }
    }
    
    /**
     * Get selected items for table action
     *
     * @return int[]
     */
    protected function getActionUserIds()
    {
        global $DIC;
        $access = $DIC->access();

        if ($_POST["select_cmd_all"]) {
            include_once("./Services/User/classes/class.ilUserTableGUI.php");
            $utab = new ilUserTableGUI($this, "view", ilUserTableGUI::MODE_USER_FOLDER, false);

            if (!$access->checkAccess('read_users', '', USER_FOLDER_ID) &&
                $access->checkRbacOrPositionPermissionAccess(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
                $filtered_users = $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                    'read_users',
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID,
                    $users
                );

                $utab->addFilterItemValue("user_ids", $filtered_users);
            }

            return $utab->getUserIdsForFilter();
        } else {
            return $access->filterUserIdsByRbacOrPositionOfCurrentUser(
                'read_users',
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                (array) $_POST['id']
            );
        }
    }

    /**
     * Check if current user has access to manipulate user data
     * @return bool
     */
    private function checkUserManipulationAccessBool()
    {
        global $DIC;

        $access = $DIC->access();
        return $access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        );
    }

    /**
    * display activation confirmation screen
    */
    public function showActionConfirmation($action, $a_from_search = false)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }
        
        if (!$a_from_search) {
            $ilTabs->activateTab("obj_usrf");
        } else {
            $ilTabs->activateTab("search_user_extended");
        }
                
        if (strcmp($action, "accessRestrict") == 0) {
            return $this->setAccessRestrictionObject(null, $a_from_search);
        }
        if (strcmp($action, "mail") == 0) {
            return $this->mailObject();
        }

        unset($this->data);
        
        if (!$a_from_search) {
            $cancel = "cancelUserFolderAction";
        } else {
            $cancel = "cancelSearchAction";
        }
        
        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_" . $action . "_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), $cancel);
        $cgui->setConfirm($this->lng->txt("confirm"), "confirm" . $action);
        
        if ($a_from_search) {
            $cgui->addHiddenItem("frsrch", 1);
        }

        foreach ($user_ids as $id) {
            $user = new ilObjUser($id);

            $login = $user->getLastLogin();
            if (!$login) {
                $login = $this->lng->txt("never");
            } else {
                $login = ilDatePresentation::formatDate(new ilDateTime($login, IL_CAL_DATETIME));
            }

            $caption = $user->getFullname() . " (" . $user->getLogin() . ")" . ", " .
                $user->getEmail() . " -  " . $this->lng->txt("last_login") . ": " . $login;

            $cgui->addItem("id[]", $id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());

        return true;
    }

    /**
    * Delete users
    */
    public function deleteUsersObject()
    {
        $_POST["selectedAction"] = "delete";
        $this->showActionConfirmation($_POST["selectedAction"]);
    }
    
    /**
    * Activate users
    */
    public function activateUsersObject()
    {
        $_POST["selectedAction"] = "activate";
        $this->showActionConfirmation($_POST["selectedAction"]);
    }
    
    /**
    * Deactivate users
    */
    public function deactivateUsersObject()
    {
        $_POST["selectedAction"] = "deactivate";
        $this->showActionConfirmation($_POST["selectedAction"]);
    }

    /**
    * Restrict access
    */
    public function restrictAccessObject()
    {
        $_POST["selectedAction"] = "accessRestrict";
        $this->showActionConfirmation($_POST["selectedAction"]);
    }

    /**
    * Free access
    */
    public function freeAccessObject()
    {
        $_POST["selectedAction"] = "accessFree";
        $this->showActionConfirmation($_POST["selectedAction"]);
    }

    public function userActionObject()
    {
        $this->showActionConfirmation($_POST["selectedAction"]);
    }

    /**
    * display form for user import
    */
    public function importUserFormObject()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilCtrl = $DIC->ctrl();
        $access = $DIC->access();

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('usrf'), $ilCtrl->getLinkTarget($this, 'view'));

        if (
            !$rbacsystem->checkAccess('create_usr', $this->object->getRefId()) &&
            !$access->checkAccess('cat_administrate_users', '', $this->object->getRefId())
        ) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $this->initUserImportForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
    * Init user import form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initUserImportForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // Import File
        include_once("./Services/Form/classes/class.ilFileInputGUI.php");
        $fi = new ilFileInputGUI($lng->txt("import_file"), "importFile");
        $fi->setSuffixes(array("xml", "zip"));
        $fi->setRequired(true);
        //$fi->enableFileNameSelection();
        //$fi->setInfo($lng->txt(""));
        $this->form->addItem($fi);

        $this->form->addCommandButton("importUserRoleAssignment", $lng->txt("import"));
        $this->form->addCommandButton("importCancelled", $lng->txt("cancel"));
                    
        $this->form->setTitle($lng->txt("import_users"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
    * import cancelled
    *
    * @access private
    */
    public function importCancelledObject()
    {
        global $DIC;
        $filesystem = $DIC->filesystem()->storage();

        // purge user import directory
        $import_dir = $this->getImportDir();
        if ($filesystem->hasDir($import_dir)) {
            $filesystem->deleteDir($import_dir);
        }

        if (strtolower($_GET["baseClass"]) == 'iladministrationgui') {
            $this->ctrl->redirect($this, "view");
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }

    /**
     * get user import directory name with new FileSystem implementation
     */
    public function getImportDir()
    {
        // For each user session a different directory must be used to prevent
        // that one user session overwrites the import data that another session
        // is currently importing.
        global $DIC;

        $ilUser = $DIC->user();

        $importDir = 'user_import/usr_' . $ilUser->getId() . '_' . session_id();

        return $importDir;
    }


    /**
     * display form for user import with new FileSystem implementation
     */
    public function importUserRoleAssignmentObject()
    {
        global $DIC;

        $tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC->ctrl();
        $renderer = $DIC->ui()->renderer();


        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget($this->lng->txt('usrf'), $ilCtrl->getLinkTarget($this, 'view'));

        $this->initUserImportForm();
        if ($this->form->checkInput()) {
            $xml_file = $this->handleUploadedFiles();
            //importParser needs the full path to xml file
            $xml_file_full_path = ilUtil::getDataDir() . '/' . $xml_file;

            $form = $this->initUserRoleAssignmentForm($xml_file_full_path);

            $tpl->setContent($renderer->render($form));
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }

    private function initUserRoleAssignmentForm($xml_file_full_path)
    {
        global $DIC;

        $ilUser = $DIC->user();
        $rbacreview = $DIC->rbac()->review();
        $rbacsystem = $DIC->rbac()->system();
        $ui = $DIC->ui()->factory();

        $importParser = new ilUserImportParser($xml_file_full_path, IL_VERIFY);
        $importParser->startParsing();

        $this->verifyXmlData($importParser);

        $xml_file_name = explode("/", $xml_file_full_path);
        $roles_import_filename = $ui->input()->field()->text($this->lng->txt("import_file"))
                                                                ->withDisabled(true)
                                                                ->withValue(end($xml_file_name));


        $roles_import_count = $ui->input()->field()->numeric($this->lng->txt("num_users"))
                                                            ->withDisabled(true)
                                                            ->withValue($importParser->getUserCount());

        $importParser = new ilUserImportParser($xml_file_full_path, IL_EXTRACT_ROLES);
        $importParser->startParsing();
        // Extract the roles
        $roles = $importParser->getCollectedRoles();

        // get global roles
        $all_gl_roles = $rbacreview->getRoleListByObject(ROLE_FOLDER_ID);
        $gl_roles = array();
        $roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
        foreach ($all_gl_roles as $obj_data) {
            // check assignment permission if called from local admin
            if ($this->object->getRefId() != USER_FOLDER_ID) {
                if (!in_array(SYSTEM_ROLE_ID, $roles_of_user) && !ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])) {
                    continue;
                }
            }
            // exclude anonymous role from list
            if ($obj_data["obj_id"] != ANONYMOUS_ROLE_ID) {
                // do not allow to assign users to administrator role if current user does not has SYSTEM_ROLE_ID
                if ($obj_data["obj_id"] != SYSTEM_ROLE_ID or in_array(SYSTEM_ROLE_ID, $roles_of_user)) {
                    $gl_roles[$obj_data["obj_id"]] = $obj_data["title"];
                }
            }
        }

        // global roles
        $got_globals = false;
        $global_selects = array();
        foreach ($roles as $role_id => $role) {
            if ($role["type"] == "Global") {
                if (!$got_globals) {
                    $got_globals = true;

                    $global_roles_assignment_info = $ui->input()->field()->text($this->lng->txt("roles_of_import_global"))
                                                ->withDisabled(true)
                                                ->withValue($this->lng->txt("assign_global_role"));
                }

                //select options for new form input to still have both ids
                $select_options = array();
                foreach ($gl_roles as $key => $value) {
                    $select_options[$role_id . "-" . $key] = $value;
                }

                // pre selection for role
                $pre_select = array_search($role["name"], $select_options);
                if (!$pre_select) {
                    switch ($role["name"]) {
                        case "Administrator":	// ILIAS 2/3 Administrator
                            $pre_select = array_search("Administrator", $select_options);
                            break;

                        case "Autor":			// ILIAS 2 Author
                            $pre_select = array_search("User", $select_options);
                            break;

                        case "Lerner":			// ILIAS 2 Learner
                            $pre_select = array_search("User", $select_options);
                            break;

                        case "Gast":			// ILIAS 2 Guest
                            $pre_select = array_search("Guest", $select_options);
                            break;

                        default:
                            $pre_select = array_search("User", $select_options);
                            break;
                    }
                }

                $select = $ui->input()->field()->select($role["name"], $select_options)
                             ->withValue($pre_select)
                             ->withRequired(true);
                array_push($global_selects, $select);
            }
        }

        // Check if local roles need to be assigned
        $got_locals = false;
        foreach ($roles as $role_id => $role) {
            if ($role["type"] == "Local") {
                $got_locals = true;
                break;
            }
        }

        if ($got_locals) {
            $local_roles_assignment_info = $ui->input()->field()->text($this->lng->txt("roles_of_import_local"))
                                        ->withDisabled(true)
                                        ->withValue($this->lng->txt("assign_local_role"));


            // get local roles
            if ($this->object->getRefId() == USER_FOLDER_ID) {
                // The import function has been invoked from the user folder
                // object. In this case, we show only matching roles,
                // because the user folder object is considered the parent of all
                // local roles and may contains thousands of roles on large ILIAS
                // installations.
                $loc_roles = array();

                $roleMailboxSearch = new \ilRoleMailboxSearch(new \ilMailRfc822AddressParserFactory());
                foreach ($roles as $role_id => $role) {
                    if ($role["type"] == "Local") {
                        $searchName = (substr($role['name'], 0, 1) == '#') ? $role['name'] : '#' . $role['name'];
                        $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                        foreach ($matching_role_ids as $mid) {
                            if (!in_array($mid, $loc_roles)) {
                                $loc_roles[] = $mid;
                            }
                        }
                    }
                }
            } else {
                // The import function has been invoked from a locally
                // administrated category. In this case, we show all roles
                // contained in the subtree of the category.
                $loc_roles = $rbacreview->getAssignableRolesInSubtree($this->object->getRefId());
            }
            $l_roles = array();

            // create a search array with  .
            $l_roles_mailbox_searcharray = array();
            foreach ($loc_roles as $key => $loc_role) {
                // fetch context path of role
                $rolf = $rbacreview->getFoldersAssignedToRole($loc_role, true);

                // only process role folders that are not set to status "deleted"
                // and for which the user has write permissions.
                // We also don't show the roles which are in the ROLE_FOLDER_ID folder.
                // (The ROLE_FOLDER_ID folder contains the global roles).
                if (
                    !$rbacreview->isDeleted($rolf[0]) &&
                    $rbacsystem->checkAccess('write', $rolf[0]) &&
                    $rolf[0] != ROLE_FOLDER_ID
                ) {
                    // A local role is only displayed, if it is contained in the subtree of
                    // the localy administrated category. If the import function has been
                    // invoked from the user folder object, we show all local roles, because
                    // the user folder object is considered the parent of all local roles.
                    // Thus, if we start from the user folder object, we initialize the
                    // isInSubtree variable with true. In all other cases it is initialized
                    // with false, and only set to true if we find the object id of the
                    // locally administrated category in the tree path to the local role.
                    $isInSubtree = $this->object->getRefId() == USER_FOLDER_ID;

                    $path_array = array();
                    if ($this->tree->isInTree($rolf[0])) {
                        // Create path. Paths which have more than 4 segments
                        // are truncated in the middle.
                        $tmpPath = $this->tree->getPathFull($rolf[0]);
                        $tmpPath[] = $rolf[0];//adds target item to list

                        for ($i = 1, $n = count($tmpPath) - 1; $i < $n; $i++) {
                            if ($i < 3 || $i > $n - 3) {
                                $path_array[] = $tmpPath[$i]['title'];
                            } elseif ($i == 3 || $i == $n - 3) {
                                $path_array[] = '...';
                            }

                            $isInSubtree |= $tmpPath[$i]['obj_id'] == $this->object->getId();
                        }
                        //revert this path for a better readability in dropdowns #18306
                        $path = implode(" < ", array_reverse($path_array));
                    } else {
                        $path = "<b>Rolefolder " . $rolf[0] . " not found in tree! (Role " . $loc_role . ")</b>";
                    }
                    $roleMailboxAddress = (new \ilRoleMailboxAddress($loc_role))->value();
                    $l_roles[$loc_role] = $roleMailboxAddress . ', ' . $path;
                }
            } //foreach role

            natcasesort($l_roles);
            $l_roles["ignore"] = $this->lng->txt("usrimport_ignore_role");

            $roleMailboxSearch = new \ilRoleMailboxSearch(new \ilMailRfc822AddressParserFactory());
            $local_selects = [];
            foreach ($roles as $role_id => $role) {
                if ($role["type"] == "Local") {
                    /*$this->tpl->setCurrentBlock("local_role");
                    $this->tpl->setVariable("TXT_IMPORT_LOCAL_ROLE", $role["name"]);*/
                    $searchName = (substr($role['name'], 0, 1) == '#') ? $role['name'] : '#' . $role['name'];
                    $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                    $pre_select = count($matching_role_ids) == 1 ? $role_id . "-" . $matching_role_ids[0] : "ignore";

                    if ($this->object->getRefId() == USER_FOLDER_ID) {
                        // There are too many roles in a large ILIAS installation
                        // that's why whe show only a choice with the the option "ignore",
                        // and the matching roles.
                        $selectable_roles = array();
                        $selectable_roles["ignore"] = $this->lng->txt("usrimport_ignore_role");
                        foreach ($matching_role_ids as $id) {
                            $selectable_roles[$role_id . "-" . $id] = $l_roles[$id];
                        }

                        $select = $ui->input()->field()->select($role["name"], $selectable_roles)
                                     ->withValue($pre_select)
                                     ->withRequired(true);
                        array_push($local_selects, $select);
                    } else {
                        $selectable_roles = array();
                        foreach ($l_roles as $local_role_id => $value) {
                            if ($local_role_id !== "ignore") {
                                $selectable_roles[$role_id . "-" . $local_role_id] = $value;
                            }
                        }
                        if (count($selectable_roles)) {
                            $select = $ui->input()->field()->select($role["name"], $selectable_roles)
                                         ->withRequired(true);
                            array_push($local_selects, $select);
                        }
                    }
                }
            }
        }


        $handlers = array(
            IL_IGNORE_ON_CONFLICT => $this->lng->txt("ignore_on_conflict"),
            IL_UPDATE_ON_CONFLICT => $this->lng->txt("update_on_conflict")
        );

        $conflict_action_select = $ui->input()->field()->select($this->lng->txt("conflict_handling"), $handlers, str_replace('\n', '<br>', $this->lng->txt("usrimport_conflict_handling_info")))
                                     ->withValue(IL_IGNORE_ON_CONFLICT)
                                     ->withRequired(true);

        // new account mail
        $this->lng->loadLanguageModule("mail");
        $amail = ilObjUserFolder::_lookupNewAccountMail($this->lng->getDefaultLanguage());
        if (trim($amail["body"]) != "" && trim($amail["subject"]) != "") {
            $send_checkbox = $ui->input()->field()->checkbox($this->lng->txt("user_send_new_account_mail"))
                                             ->withValue(true);

            $mail_section = $ui->input()->field()->section([$send_checkbox], $this->lng->txt("mail_account_mail"));
        }

        $file_info_section = $ui->input()->field()->section(
            [
                "filename" => $roles_import_filename,
                "import_count" => $roles_import_count,
            ],
            $this->lng->txt("file_info")
        );

        $global_role_info_section = $ui->input()->field()->section([$global_roles_assignment_info], $this->lng->txt("global_role_assignment"));
        $global_role_selection_section = $ui->input()->field()->section($global_selects, "");
        $conflict_action_section = $ui->input()->field()->section([$conflict_action_select], "");
        $form_action = $DIC->ctrl()->getFormActionByClass('ilObjUserFolderGui', 'importUsers');

        $form_elements = array(
            "file_info" => $file_info_section,
            "global_role_info" => $global_role_info_section,
            "global_role_selection" => $global_role_selection_section
        );


        if (!empty($local_selects)) {
            $local_role_info_section = $ui->input()->field()->section([$local_roles_assignment_info], $this->lng->txt("local_role_assignment"));
            $local_role_selection_section = $ui->input()->field()->section($local_selects, "");

            $form_elements["local_role_info"] = $local_role_info_section;
            $form_elements["local_role_selection"] = $local_role_selection_section;
        }

        $form_elements["conflict_action"] = $conflict_action_section;

        if (!empty($mail_section)) {
            $form_elements["send_mail"] = $mail_section;
        }

        return $ui->input()->container()->form()->standard(
            $form_action,
            $form_elements
        );
    }

    /**
     * Handles uploaded zip/xmp files with Filesystem implementation
     */
    private function handleUploadedFiles() : string
    {
        global $DIC;

        $ilUser = $DIC->user();

        $upload = $DIC->upload();

        $filesystem = $DIC->filesystem()->storage();
        $import_dir = $this->getImportDir();

        if (!$upload->hasBeenProcessed()) {
            $upload->process();
        }

        // recreate user import directory
        if ($filesystem->hasDir($import_dir)) {
            $filesystem->deleteDir($import_dir);
        }
        $filesystem->createDir($import_dir);


        foreach ($upload->getResults() as $single_file_upload) {
            $file_name = $single_file_upload->getName();
            $parts = pathinfo($file_name);

            //check if upload status is ok
            if ($single_file_upload->getStatus() != \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError($this->lng->txt("no_import_file_found"), $this->ilias->error_obj->MESSAGE);
            }

            // move uploaded file to user import directory
            $upload->moveFilesTo(
                $import_dir,
                \ILIAS\FileUpload\Location::STORAGE
            );

            // handle zip file
            if ($single_file_upload->getMimeType() == "application/zip") {
                // Workaround: unzip function needs full path to file. Should be replaced once Filesystem has own unzip implementation
                $full_path = ilUtil::getDataDir() . '/user_import/usr_' . $ilUser->getId() . '_' . session_id() . "/" . $file_name;
                ilUtil::unzip($full_path);

                $xml_file = null;
                $file_list = $filesystem->listContents($import_dir);

                foreach ($file_list as $key => $a_file) {
                    if (substr($a_file->getPath(), -4) == '.xml') {
                        unset($file_list[$key]);
                        $xml_file = $a_file->getPath();
                        break;
                    }
                }

                //Removing all files except the one to be imported, to make sure to get the right one in import-function
                foreach ($file_list as $a_file) {
                    $filesystem->delete($a_file->getPath());
                }

                if (is_null($xml_file)) {
                    $subdir = basename($parts["basename"], "." . $parts["extension"]);
                    $xml_file = $import_dir . "/" . $subdir . "/" . $subdir . ".xml";
                }
            }
            // handle xml file
            else {
                $a = $filesystem->listContents($import_dir);
                $file = end($a);
                $xml_file = $file->getPath();
            }

            // check xml file
            if (!$filesystem->has($xml_file)) {
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError($this->lng->txt("no_xml_file_found_in_zip")
                    . " " . $subdir . "/" . $subdir . ".xml", $this->ilias->error_obj->MESSAGE);
            }
        }

        return $xml_file;
    }

    public function verifyXmlData($importParser)
    {
        global $DIC;

        $filesystem = $DIC->filesystem()->storage();

        $import_dir = $this->getImportDir();
        switch ($importParser->getErrorLevel()) {
            case IL_IMPORT_SUCCESS:
                break;
            case IL_IMPORT_WARNING:
                $this->tpl->setVariable("IMPORT_LOG", $importParser->getProtocolAsHTML($this->lng->txt("verification_warning_log")));
                break;
            case IL_IMPORT_FAILURE:
                $filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt("verification_failed") . $importParser->getProtocolAsHTML($this->lng->txt("verification_failure_log")),
                    $this->ilias->error_obj->MESSAGE
                );
            return;
        }
    }

    /**
     * Import Users with new form implementation
     */
    public function importUsersObject()
    {
        global $DIC;

        $ilUser = $DIC->user();
        $request = $DIC->http()->request();
        $rbacreview = $DIC->rbac()->review();
        $rbacsystem = $DIC->rbac()->system();
        $filesystem = $DIC->filesystem()->storage();
        $import_dir = $this->getImportDir();


        $file_list = $filesystem->listContents($import_dir);

        //Make sure there's only one file in the import directory at this point
        if (count($file_list) > 1) {
            $filesystem->deleteDir($import_dir);
            $this->ilias->raiseError(
                $this->lng->txt("usrimport_wrong_file_count"),
                $this->ilias->error_obj->MESSAGE
            );
            if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
                $this->ctrl->redirect($this, "view");
            } else {
                $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
            }
        } else {
            $xml_file = $file_list[0]->getPath();
        }


        //Need full path to xml file to initialise form
        $xml_path = ilUtil::getDataDir() . '/' . $xml_file;


        if ($request->getMethod() == "POST") {
            $form = $this->initUserRoleAssignmentForm($xml_path)->withRequest($request);
            $result = $form->getData();
        } else {
            $this->ilias->raiseError(
                $this->lng->txt("usrimport_form_not_evaluabe"),
                $this->ilias->error_obj->MESSAGE
            );
            if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
                $this->ctrl->redirect($this, "view");
            } else {
                $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
            }
        }

        $rule = $result["conflict_action"][0];

        //If local roles exist, merge the roles that are to be assigned, otherwise just take the array that has global roles
        $roles = isset($result["local_role_selection"]) ? array_merge($result["global_role_selection"], $result["local_role_selection"]) : $result["global_role_selection"];

        $role_assignment = array();
        foreach ($roles as $value) {
            $keys = explode("-", $value);
            $role_assignment[$keys[0]] = $keys[1];
        }

        $importParser = new ilUserImportParser($xml_path, IL_USER_IMPORT, $rule);
        $importParser->setFolderId($this->getUserOwnerId());

        // Catch hack attempts
        // We check here again, if the role folders are in the tree, and if the
        // user has permission on the roles.
        if (!empty($role_assignment)) {
            $global_roles = $rbacreview->getGlobalRoles();
            $roles_of_user = $rbacreview->assignedRoles($ilUser->getId());
            foreach ($role_assignment as $role_id) {
                if ($role_id != "") {
                    if (in_array($role_id, $global_roles)) {
                        if (!in_array(SYSTEM_ROLE_ID, $roles_of_user)) {
                            if ($role_id == SYSTEM_ROLE_ID && !in_array(SYSTEM_ROLE_ID, $roles_of_user)
                                || ($this->object->getRefId() != USER_FOLDER_ID
                                    && !ilObjRole::_getAssignUsersStatus($role_id))
                            ) {
                                $filesystem->deleteDir($import_dir);
                                $this->ilias->raiseError(
                                    $this->lng->txt("usrimport_with_specified_role_not_permitted"),
                                    $this->ilias->error_obj->MESSAGE
                                );
                            }
                        }
                    } else {
                        $rolf = $rbacreview->getFoldersAssignedToRole($role_id, true);
                        if ($rbacreview->isDeleted($rolf[0])
                            || !$rbacsystem->checkAccess('write', $rolf[0])) {
                            $filesystem->deleteDir($import_dir);
                            $this->ilias->raiseError(
                                $this->lng->txt("usrimport_with_specified_role_not_permitted"),
                                $this->ilias->error_obj->MESSAGE
                            );
                            return;
                        }
                    }
                }
            }
        }

        if (isset($result['send_mail'])) {
            $importParser->setSendMail($result['send_mail'][0]);
        }

        $importParser->setRoleAssignment($role_assignment);
        $importParser->startParsing();

        // purge user import directory
        $filesystem->deleteDir($import_dir);

        switch ($importParser->getErrorLevel()) {
            case IL_IMPORT_SUCCESS:
                ilUtil::sendSuccess($this->lng->txt("user_imported"), true);
                break;
            case IL_IMPORT_WARNING:
                ilUtil::sendSuccess($this->lng->txt("user_imported_with_warnings") . $importParser->getProtocolAsHTML($this->lng->txt("import_warning_log")), true);
                break;
            case IL_IMPORT_FAILURE:
                $this->ilias->raiseError(
                    $this->lng->txt("user_import_failed")
                    . $importParser->getProtocolAsHTML($this->lng->txt("import_failure_log")),
                    $this->ilias->error_obj->MESSAGE
                );
                break;
        }

        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            $this->ctrl->redirect($this, "view");
        //ilUtil::redirect($this->ctrl->getLinkTarget($this));
        } else {
            $this->ctrl->redirectByClass('ilobjcategorygui', 'listUsers');
        }
    }
    
    public function hitsperpageObject()
    {
        parent::hitsperpageObject();
        $this->viewObject();
    }

    /**
     * Show user account general settings
     * @return
     */
    protected function generalSettingsObject()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->initFormGeneralSettings();
        
        include_once './Services/User/classes/class.ilUserAccountSettings.php';
        $aset = ilUserAccountSettings::getInstance();
        
        $show_blocking_time_in_days = $ilSetting->get('loginname_change_blocking_time') / 86400;
        $show_blocking_time_in_days = (float) $show_blocking_time_in_days;
        
        include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
        $security = ilSecuritySettings::_getInstance();

        $settings = [
            'lua' => $aset->isLocalUserAdministrationEnabled(),
            'lrua' => $aset->isUserAccessRestricted(),
            'allow_change_loginname' => (bool) $ilSetting->get('allow_change_loginname'),
            'create_history_loginname' => (bool) $ilSetting->get('create_history_loginname'),
            'reuse_of_loginnames' => (bool) $ilSetting->get('reuse_of_loginnames'),
            'loginname_change_blocking_time' => (float) $show_blocking_time_in_days,
            'user_adm_alpha_nav' => (int) $ilSetting->get('user_adm_alpha_nav'),
            // 'user_ext_profiles' => (int)$ilSetting->get('user_ext_profiles')
            'user_reactivate_code' => (int) $ilSetting->get('user_reactivate_code'),
            'user_own_account' => (int) $ilSetting->get('user_delete_own_account'),
            'user_own_account_email' => $ilSetting->get('user_delete_own_account_email'),

            'session_handling_type' => $ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED),
            'session_reminder_enabled' => $ilSetting->get('session_reminder_enabled'),
            'session_max_count' => $ilSetting->get('session_max_count', ilSessionControl::DEFAULT_MAX_COUNT),
            'session_min_idle' => $ilSetting->get('session_min_idle', ilSessionControl::DEFAULT_MIN_IDLE),
            'session_max_idle' => $ilSetting->get('session_max_idle', ilSessionControl::DEFAULT_MAX_IDLE),
            'session_max_idle_after_first_request' => $ilSetting->get(
                'session_max_idle_after_first_request',
                ilSessionControl::DEFAULT_MAX_IDLE_AFTER_FIRST_REQUEST
            ),

            'login_max_attempts' => $security->getLoginMaxAttempts(),
            'ps_prevent_simultaneous_logins' => (int) $security->isPreventionOfSimultaneousLoginsEnabled(),
            'password_assistance' => (bool) $ilSetting->get("password_assistance"),
            'letter_avatars' => (int) $ilSetting->get('letter_avatars'),
            'password_change_on_first_login_enabled' => $security->isPasswordChangeOnFirstLoginEnabled() ? 1 : 0,
            'password_max_age' => $security->getPasswordMaxAge()
        ];

        $passwordPolicySettings = $this->getPasswordPolicySettingsMap($security);
        $this->form->setValuesByArray(array_merge(
            $settings,
            $passwordPolicySettings,
            ['pw_policy_hash' => md5(implode('', $passwordPolicySettings))]
        ));

        $this->tpl->setContent($this->form->getHTML());
    }


    /**
     * @param ilSecuritySettings $security
     * @return array
     */
    private function getPasswordPolicySettingsMap(\ilSecuritySettings $security) : array
    {
        return [
            'password_must_not_contain_loginame' => $security->getPasswordMustNotContainLoginnameStatus() ? 1 : 0,
            'password_chars_and_numbers_enabled' => $security->isPasswordCharsAndNumbersEnabled() ? 1 : 0,
            'password_special_chars_enabled' => $security->isPasswordSpecialCharsEnabled() ? 1 : 0,
            'password_min_length' => $security->getPasswordMinLength(),
            'password_max_length' => $security->getPasswordMaxLength(),
            'password_ucase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
            'password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
        ];
    }
    
    
    /**
     * Save user account settings
     * @return
     */
    public function saveGeneralSettingsObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->initFormGeneralSettings();
        if ($this->form->checkInput()) {
            $valid = true;
            
            if (!strlen($this->form->getInput('loginname_change_blocking_time'))) {
                $valid = false;
                $this->form->getItemByPostVar('loginname_change_blocking_time')
                                        ->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
            }
                                            
            include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
            $security = ilSecuritySettings::_getInstance();

            // account security settings
            $security->setPasswordCharsAndNumbersEnabled((bool) $_POST["password_chars_and_numbers_enabled"]);
            $security->setPasswordSpecialCharsEnabled((bool) $_POST["password_special_chars_enabled"]);
            $security->setPasswordMinLength((int) $_POST["password_min_length"]);
            $security->setPasswordMaxLength((int) $_POST["password_max_length"]);
            $security->setPasswordNumberOfUppercaseChars((int) $_POST['password_ucase_chars_num']);
            $security->setPasswordNumberOfLowercaseChars((int) $_POST['password_lowercase_chars_num']);
            $security->setPasswordMaxAge((int) $_POST["password_max_age"]);
            $security->setLoginMaxAttempts((int) $_POST["login_max_attempts"]);
            $security->setPreventionOfSimultaneousLogins((bool) $_POST['ps_prevent_simultaneous_logins']);
            $security->setPasswordChangeOnFirstLoginEnabled((bool) $_POST['password_change_on_first_login_enabled']);
            $security->setPasswordMustNotContainLoginnameStatus((int) $_POST['password_must_not_contain_loginame']);
                
            if (!$security->validate($this->form)) {
                $valid = false;
            }
            
            if ($valid) {
                $security->save();
                
                include_once './Services/User/classes/class.ilUserAccountSettings.php';
                ilUserAccountSettings::getInstance()->enableLocalUserAdministration($this->form->getInput('lua'));
                ilUserAccountSettings::getInstance()->restrictUserAccess($this->form->getInput('lrua'));
                ilUserAccountSettings::getInstance()->update();

                $ilSetting->set('allow_change_loginname', (int) $this->form->getInput('allow_change_loginname'));
                $ilSetting->set('create_history_loginname', (int) $this->form->getInput('create_history_loginname'));
                $ilSetting->set('reuse_of_loginnames', (int) $this->form->getInput('reuse_of_loginnames'));
                $save_blocking_time_in_seconds = (int) ($this->form->getInput('loginname_change_blocking_time') * 86400);
                $ilSetting->set('loginname_change_blocking_time', (int) $save_blocking_time_in_seconds);
                $ilSetting->set('user_adm_alpha_nav', (int) $this->form->getInput('user_adm_alpha_nav'));
                $ilSetting->set('user_reactivate_code', (int) $this->form->getInput('user_reactivate_code'));
                
                $ilSetting->set('user_delete_own_account', (int) $this->form->getInput('user_own_account'));
                $ilSetting->set('user_delete_own_account_email', $this->form->getInput('user_own_account_email'));
                
                $ilSetting->set("password_assistance", $this->form->getInput("password_assistance"));
                
                // BEGIN SESSION SETTINGS
                $ilSetting->set(
                    'session_handling_type',
                    (int) $this->form->getInput('session_handling_type')
                );

                if ($this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_FIXED) {
                    $ilSetting->set(
                        'session_reminder_enabled',
                        $this->form->getInput('session_reminder_enabled')
                    );
                } elseif ($this->form->getInput('session_handling_type') == ilSession::SESSION_HANDLING_LOAD_DEPENDENT) {
                    require_once 'Services/Authentication/classes/class.ilSessionControl.php';
                    if (
                        $ilSetting->get(
                            'session_allow_client_maintenance',
                            ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE
                        )
                      ) {
                        // has to be done BEFORE updating the setting!
                        include_once "Services/Authentication/classes/class.ilSessionStatistics.php";
                        ilSessionStatistics::updateLimitLog((int) $this->form->getInput('session_max_count'));

                        $ilSetting->set(
                            'session_max_count',
                            (int) $this->form->getInput('session_max_count')
                        );
                        $ilSetting->set(
                            'session_min_idle',
                            (int) $this->form->getInput('session_min_idle')
                        );
                        $ilSetting->set(
                            'session_max_idle',
                            (int) $this->form->getInput('session_max_idle')
                        );
                        $ilSetting->set(
                            'session_max_idle_after_first_request',
                            (int) $this->form->getInput('session_max_idle_after_first_request')
                        );
                    }
                }
                // END SESSION SETTINGS
                $ilSetting->set('letter_avatars', (int) $this->form->getInput('letter_avatars'));

                $requestPasswordReset = false;
                if ($this->form->getInput('pw_policy_hash')) {
                    $oldSettingsHash = $this->form->getInput('pw_policy_hash');
                    $currentSettingsHash = md5(implode('', $this->getPasswordPolicySettingsMap($security)));

                    $requestPasswordReset = ($oldSettingsHash !== $currentSettingsHash);
                }

                if ($requestPasswordReset) {
                    $this->ctrl->redirect($this, 'askForUserPasswordReset');
                } else {
                    ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
                }
            } else {
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            }
        } else {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     *
     */
    protected function forceUserPasswordResetObject()
    {
        \ilUserPasswordManager::getInstance()->resetLastPasswordChangeForLocalUsers();
        $this->lng->loadLanguageModule('ps');

        \ilUtil::sendSuccess($this->lng->txt('ps_passwd_policy_change_force_user_reset_succ'), true);
        $this->ctrl->redirect($this, 'generalSettings');
    }

    /**
     *
     */
    protected function askForUserPasswordResetObject()
    {
        $this->lng->loadLanguageModule('ps');

        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'askForUserPasswordReset'));
        $confirmation->setHeaderText($this->lng->txt('ps_passwd_policy_changed_force_user_reset'));
        $confirmation->setConfirm($this->lng->txt('yes'), 'forceUserPasswordReset');
        $confirmation->setCancel($this->lng->txt('no'), 'generalSettings');

        $this->tpl->setContent($confirmation->getHTML());
    }
    
    
    /**
     * init general settings form
     * @return
     */
    protected function initFormGeneralSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('general_settings');
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveGeneralSettings'));
        
        $this->form->setTitle($this->lng->txt('general_settings'));
        
        $lua = new ilCheckboxInputGUI($this->lng->txt('enable_local_user_administration'), 'lua');
        $lua->setInfo($this->lng->txt('enable_local_user_administration_info'));
        $lua->setValue(1);
        $this->form->addItem($lua);
        
        $lrua = new ilCheckboxInputGUI($this->lng->txt('restrict_user_access'), 'lrua');
        $lrua->setInfo($this->lng->txt('restrict_user_access_info'));
        $lrua->setValue(1);
        $this->form->addItem($lrua);

        // enable alphabetical navigation in user administration
        $alph = new ilCheckboxInputGUI($this->lng->txt('user_adm_enable_alpha_nav'), 'user_adm_alpha_nav');
        //$alph->setInfo($this->lng->txt('restrict_user_access_info'));
        $alph->setValue(1);
        $this->form->addItem($alph);

        // account codes
        $code = new ilCheckboxInputGUI($this->lng->txt("user_account_code_setting"), "user_reactivate_code");
        $code->setInfo($this->lng->txt('user_account_code_setting_info'));
        $this->form->addItem($code);
        
        // delete own account
        $own = new ilCheckboxInputGUI($this->lng->txt("user_allow_delete_own_account"), "user_own_account");
        $this->form->addItem($own);
        $own_email = new ilEMailInputGUI($this->lng->txt("user_delete_own_account_notification_email"), "user_own_account_email");
        $own->addSubItem($own_email);
        
        
        // BEGIN SESSION SETTINGS
        
        // create session handling radio group
        $ssettings = new ilRadioGroupInputGUI($this->lng->txt('sess_mode'), 'session_handling_type');
    
        // first option, fixed session duration
        $fixed = new ilRadioOption($this->lng->txt('sess_fixed_duration'), ilSession::SESSION_HANDLING_FIXED);
        
        // create session reminder subform
        $cb = new ilCheckboxInputGUI($this->lng->txt("session_reminder"), "session_reminder_enabled");
        $expires = ilSession::getSessionExpireValue();
        $time = ilDatePresentation::secondsToString($expires, true);
        $cb->setInfo($this->lng->txt("session_reminder_info") . "<br />" .
            sprintf($this->lng->txt('session_reminder_session_duration'), $time));
        $fixed->addSubItem($cb);
        
        // add session handling to radio group
        $ssettings->addOption($fixed);
        
        // second option, session control
        $ldsh = new ilRadioOption($this->lng->txt('sess_load_dependent_session_handling'), ilSession::SESSION_HANDLING_LOAD_DEPENDENT);

        // add session control subform
        require_once('Services/Authentication/classes/class.ilSessionControl.php');
        
        // this is the max count of active sessions
        // that are getting started simlutanously
        $sub_ti = new ilTextInputGUI($this->lng->txt('session_max_count'), 'session_max_count');
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_count_info'));
        if (!$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE)) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);
        
        // after this (min) idle time the session can be deleted,
        // if there are further requests for new sessions,
        // but max session count is reached yet
        $sub_ti = new ilTextInputGUI($this->lng->txt('session_min_idle'), 'session_min_idle');
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_min_idle_info'));
        if (!$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE)) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);
        
        // after this (max) idle timeout the session expires
        // and become invalid, so it is not considered anymore
        // when calculating current count of active sessions
        $sub_ti = new ilTextInputGUI($this->lng->txt('session_max_idle'), 'session_max_idle');
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_idle_info'));
        if (!$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE)) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);

        // this is the max duration that can elapse between the first and the secnd
        // request to the system before the session is immidietly deleted
        $sub_ti = new ilTextInputGUI(
            $this->lng->txt('session_max_idle_after_first_request'),
            'session_max_idle_after_first_request'
        );
        $sub_ti->setMaxLength(5);
        $sub_ti->setSize(5);
        $sub_ti->setInfo($this->lng->txt('session_max_idle_after_first_request_info'));
        if (!$ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE)) {
            $sub_ti->setDisabled(true);
        }
        $ldsh->addSubItem($sub_ti);
        
        // add session control to radio group
        $ssettings->addOption($ldsh);
        
        // add radio group to form
        if ($ilSetting->get('session_allow_client_maintenance', ilSessionControl::DEFAULT_ALLOW_CLIENT_MAINTENANCE)) {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $this->form->addItem($ssettings);
        } else {
            // just shows the status wether the session
            //setting maintenance is allowed by setup
            $ti = new ilNonEditableValueGUI($this->lng->txt('session_config'), "session_config");
            $ti->setValue($this->lng->txt('session_config_maintenance_disabled'));
            $ssettings->setDisabled(true);
            $ti->addSubItem($ssettings);
            $this->form->addItem($ti);
        }
        
        // END SESSION SETTINGS
                                
        
        $this->lng->loadLanguageModule('ps');
        
        $pass = new ilFormSectionHeaderGUI();
        $pass->setTitle($this->lng->txt('ps_password_settings'));
        $this->form->addItem($pass);
         
        $check = new ilCheckboxInputGUI($this->lng->txt('ps_password_change_on_first_login_enabled'), 'password_change_on_first_login_enabled');
        $check->setInfo($this->lng->txt('ps_password_change_on_first_login_enabled_info'));
        $this->form->addItem($check);
        
        include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');

        $check = new ilCheckboxInputGUI($this->lng->txt('ps_password_must_not_contain_loginame'), 'password_must_not_contain_loginame');
        $check->setInfo($this->lng->txt('ps_password_must_not_contain_loginame_info'));
        $this->form->addItem($check);
        
        $check = new ilCheckboxInputGUI($this->lng->txt('ps_password_chars_and_numbers_enabled'), 'password_chars_and_numbers_enabled');
        //$check->setOptionTitle($this->lng->txt('ps_password_chars_and_numbers_enabled'));
        $check->setInfo($this->lng->txt('ps_password_chars_and_numbers_enabled_info'));
        $this->form->addItem($check);

        $check = new ilCheckboxInputGUI($this->lng->txt('ps_password_special_chars_enabled'), 'password_special_chars_enabled');
        //$check->setOptionTitle($this->lng->txt('ps_password_special_chars_enabled'));
        $check->setInfo($this->lng->txt('ps_password_special_chars_enabled_info'));
        $this->form->addItem($check);

        $text = new ilNumberInputGUI($this->lng->txt('ps_password_min_length'), 'password_min_length');
        $text->setInfo($this->lng->txt('ps_password_min_length_info'));
        $text->setSize(1);
        $text->setMaxLength(2);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI($this->lng->txt('ps_password_max_length'), 'password_max_length');
        $text->setInfo($this->lng->txt('ps_password_max_length_info'));
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI($this->lng->txt('ps_password_uppercase_chars_num'), 'password_ucase_chars_num');
        $text->setInfo($this->lng->txt('ps_password_uppercase_chars_num_info'));
        $text->setMinValue(0);
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI($this->lng->txt('ps_password_lowercase_chars_num'), 'password_lowercase_chars_num');
        $text->setInfo($this->lng->txt('ps_password_lowercase_chars_num_info'));
        $text->setMinValue(0);
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);

        $text = new ilNumberInputGUI($this->lng->txt('ps_password_max_age'), 'password_max_age');
        $text->setInfo($this->lng->txt('ps_password_max_age_info'));
        $text->setSize(2);
        $text->setMaxLength(3);
        $this->form->addItem($text);
                            
        // password assistance
        $cb = new ilCheckboxInputGUI($this->lng->txt("enable_password_assistance"), "password_assistance");
        $cb->setInfo($this->lng->txt("password_assistance_info"));
        $this->form->addItem($cb);
                
        $pass = new ilFormSectionHeaderGUI();
        $pass->setTitle($this->lng->txt('ps_security_protection'));
        $this->form->addItem($pass);
        
        $text = new ilNumberInputGUI($this->lng->txt('ps_login_max_attempts'), 'login_max_attempts');
        $text->setInfo($this->lng->txt('ps_login_max_attempts_info'));
        $text->setSize(1);
        $text->setMaxLength(2);
        $this->form->addItem($text);
        
        // prevent login from multiple pcs at the same time
        $objCb = new ilCheckboxInputGUI($this->lng->txt('ps_prevent_simultaneous_logins'), 'ps_prevent_simultaneous_logins');
        $objCb->setValue(1);
        $objCb->setInfo($this->lng->txt('ps_prevent_simultaneous_logins_info'));
        $this->form->addItem($objCb);
        

        

        $log = new ilFormSectionHeaderGUI();
        $log->setTitle($this->lng->txt('loginname_settings'));
        $this->form->addItem($log);
        
        $chbChangeLogin = new ilCheckboxInputGUI($this->lng->txt('allow_change_loginname'), 'allow_change_loginname');
        $chbChangeLogin->setValue(1);
        $this->form->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI($this->lng->txt('history_loginname'), 'create_history_loginname');
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue(1);
        
        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI($this->lng->txt('reuse_of_loginnames_contained_in_history'), 'reuse_of_loginnames');
        $chbReuseLoginnames->setValue(1);
        $chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));
        
        $chbChangeLogin->addSubItem($chbReuseLoginnames);
        $chbChangeBlockingTime = new ilNumberInputGUI($this->lng->txt('loginname_change_blocking_time'), 'loginname_change_blocking_time');
        $chbChangeBlockingTime->allowDecimals(true);
        $chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
        $chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
        $chbChangeBlockingTime->setSize(10);
        $chbChangeBlockingTime->setMaxLength(10);
        $chbChangeLogin->addSubItem($chbChangeBlockingTime);

        $la = new ilCheckboxInputGUI($this->lng->txt('usr_letter_avatars'), 'letter_avatars');
        $la->setValue(1);
        $la->setInfo($this->lng->txt('usr_letter_avatars_info'));
        $this->form->addItem($la);

        $passwordPolicySettingsHash = new \ilHiddenInputGUI('pw_policy_hash');
        $this->form->addItem($passwordPolicySettingsHash);

        $this->form->addCommandButton('saveGeneralSettings', $this->lng->txt('save'));
    }




    /**
    * Global user settings
    *
    * Allows to define global settings for user accounts
    *
    * Note: The Global user settings form allows to specify default values
    *       for some user preferences. To avoid redundant implementations,
    *       specification of default values can be done elsewhere in ILIAS
    *       are not supported by this form.
    */
    public function settingsObject()
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];
        $ilTabs = $DIC['ilTabs'];

        include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("mail");
        $lng->loadLanguageModule("chatroom");
        $this->setSubTabs('settings');
        $ilTabs->activateTab('settings');
        $ilTabs->activateSubTab('standard_fields');

        include_once("./Services/User/classes/class.ilUserFieldSettingsTableGUI.php");
        $tab = new ilUserFieldSettingsTableGUI($this, "settings");
        if ($this->confirm_change) {
            $tab->setConfirmChange();
        }
        $tpl->setContent($tab->getHTML());
    }
    
    public function confirmSavedObject()
    {
        $this->saveGlobalUserSettingsObject("save");
    }
    
    public function saveGlobalUserSettingsObject($action = "")
    {
        include_once 'Services/Search/classes/class.ilUserSearchOptions.php';
        include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';

        global $DIC;

        $ilias = $DIC['ilias'];
        $ilSetting = $DIC['ilSetting'];

        $user_settings_config = $this->user_settings_config;
        
        // see ilUserFieldSettingsTableGUI
        include_once("./Services/User/classes/class.ilUserProfile.php");
        $up = new ilUserProfile();
        $up->skipField("username");
        $field_properties = $up->getStandardFields();
        $profile_fields = array_keys($field_properties);
                
        $valid = true;
        foreach ($profile_fields as $field) {
            if ($_POST["chb"]["required_" . $field] &&
                    !(int) $_POST['chb']['visib_reg_' . $field]
            ) {
                $valid = false;
                break;
            }
        }
        
        if (!$valid) {
            global $DIC;

            $lng = $DIC['lng'];
            ilUtil::sendFailure($lng->txt('invalid_visible_required_options_selected'));
            $this->confirm_change = 1;
            $this->settingsObject();
            return;
        }

        // For the following fields, the required state can not be changed
        $fixed_required_fields = array(
            "firstname" => 1,
            "lastname" => 1,
            "upload" => 0,
            "password" => 0,
            "language" => 0,
            "skin_style" => 0,
            "hits_per_page" => 0,
            /*"show_users_online" => 0,*/
            "hide_own_online_status" => 0
        );
        
        // check if a course export state of any field has been added
        $privacy = ilPrivacySettings::_getInstance();
        if ($privacy->enabledCourseExport() == true &&
            $privacy->courseConfirmationRequired() == true &&
            $action != "save") {
            foreach ($profile_fields as $field) {
                if (!$ilias->getSetting("usr_settings_course_export_" . $field) && $_POST["chb"]["course_export_" . $field] == "1") {
                    #ilUtil::sendQuestion($this->lng->txt('confirm_message_course_export'));
                    #$this->confirm_change = 1;
                    #$this->settingsObject();
                    #return;
                }
            }
        }
        // Reset user confirmation
        if ($action == 'save') {
            include_once('Services/Membership/classes/class.ilMemberAgreement.php');
            ilMemberAgreement::_reset();
        }

        foreach ($profile_fields as $field) {
            // Enable disable searchable
            if (ilUserSearchOptions::_isSearchable($field)) {
                ilUserSearchOptions::_saveStatus($field, (bool) $_POST['chb']['searchable_' . $field]);
            }
        
            if (!$_POST["chb"]["visible_" . $field] && !$field_properties[$field]["visible_hide"]) {
                $user_settings_config->setVisible($field, false);
            } else {
                $user_settings_config->setVisible($field, true);
            }

            if (!$_POST["chb"]["changeable_" . $field] && !$field_properties[$field]["changeable_hide"]) {
                $user_settings_config->setChangeable($field, false);
            } else {
                $user_settings_config->setChangeable($field, true);
            }

            // registration visible
            if ((int) $_POST['chb']['visib_reg_' . $field] && !$field_properties[$field]["visib_reg_hide"]) {
                $ilSetting->set('usr_settings_visib_reg_' . $field, '1');
            } else {
                $ilSetting->set('usr_settings_visib_reg_' . $field, '0');
            }

            if ((int) $_POST['chb']['visib_lua_' . $field]) {
                $ilSetting->set('usr_settings_visib_lua_' . $field, '1');
            } else {
                $ilSetting->set('usr_settings_visib_lua_' . $field, '0');
            }

            if ((int) $_POST['chb']['changeable_lua_' . $field]) {
                $ilSetting->set('usr_settings_changeable_lua_' . $field, '1');
            } else {
                $ilSetting->set('usr_settings_changeable_lua_' . $field, '0');
            }

            if ($_POST["chb"]["export_" . $field] && !$field_properties[$field]["export_hide"]) {
                $ilias->setSetting("usr_settings_export_" . $field, "1");
            } else {
                $ilias->deleteSetting("usr_settings_export_" . $field);
            }
            
            // Course export/visibility
            if ($_POST["chb"]["course_export_" . $field] && !$field_properties[$field]["course_export_hide"]) {
                $ilias->setSetting("usr_settings_course_export_" . $field, "1");
            } else {
                $ilias->deleteSetting("usr_settings_course_export_" . $field);
            }
            
            // Group export/visibility
            if ($_POST["chb"]["group_export_" . $field] && !$field_properties[$field]["group_export_hide"]) {
                $ilias->setSetting("usr_settings_group_export_" . $field, "1");
            } else {
                $ilias->deleteSetting("usr_settings_group_export_" . $field);
            }

            $is_fixed = array_key_exists($field, $fixed_required_fields);
            if ($is_fixed && $fixed_required_fields[$field] || !$is_fixed && $_POST["chb"]["required_" . $field]) {
                $ilias->setSetting("require_" . $field, "1");
            } else {
                $ilias->deleteSetting("require_" . $field);
            }
        }

        if ($_POST["select"]["default_hits_per_page"]) {
            $ilias->setSetting("hits_per_page", $_POST["select"]["default_hits_per_page"]);
        }

        /*if ($_POST["select"]["default_show_users_online"])
        {
            $ilias->setSetting("show_users_online",$_POST["select"]["default_show_users_online"]);
        }*/
        
        if ($_POST["chb"]["export_preferences"]) {
            $ilias->setSetting("usr_settings_export_preferences", $_POST["chb"]["export_preferences"]);
        } else {
            $ilias->deleteSetting("usr_settings_export_preferences");
        }
        
        $ilias->setSetting('mail_incoming_mail', (int) $_POST['select']['default_mail_incoming_mail']);
        $ilias->setSetting('chat_osc_accept_msg', ilUtil::stripSlashes($_POST['select']['default_chat_osc_accept_msg']));
        $ilias->setSetting('bs_allow_to_contact_me', ilUtil::stripSlashes($_POST['select']['default_bs_allow_to_contact_me']));
        $ilias->setSetting('hide_own_online_status', ilUtil::stripSlashes($_POST['select']['default_hide_own_online_status']));

        ilUtil::sendSuccess($this->lng->txt("usr_settings_saved"));
        $this->settingsObject();
    }
    
    
    /**
    *	build select form to distinguish between active and non-active users
    */
    public function __buildUserFilterSelect()
    {
        $action[-1] = $this->lng->txt('all_users');
        $action[1] = $this->lng->txt('usr_active_only');
        $action[0] = $this->lng->txt('usr_inactive_only');
        $action[2] = $this->lng->txt('usr_limited_access_only');
        $action[3] = $this->lng->txt('usr_without_courses');
        $action[4] = $this->lng->txt('usr_filter_lastlogin');
        $action[5] = $this->lng->txt("usr_filter_coursemember");
        $action[6] = $this->lng->txt("usr_filter_groupmember");
        $action[7] = $this->lng->txt("usr_filter_role");

        return  ilUtil::formSelect($_SESSION['user_filter'], "user_filter", $action, false, true);
    }

    /**
    * Download selected export files
    *
    * Sends a selected export file for download
    *
    */
    public function downloadExportFileObject()
    {
        if (!isset($_POST["file"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        if (count($_POST["file"]) > 1) {
            $this->ilias->raiseError($this->lng->txt("select_max_one_item"), $this->ilias->error_obj->MESSAGE);
        }

        $file = basename($_POST["file"][0]);

        $export_dir = $this->object->getExportDirectory();
        ilUtil::deliverFile($export_dir . "/" . $file, $file);
    }
    
    /**
    * confirmation screen for export file deletion
    */
    public function confirmDeleteExportFileObject()
    {
        if (!isset($_POST["file"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeleteExportFile");
        $cgui->setConfirm($this->lng->txt("confirm"), "deleteExportFile");

        // BEGIN TABLE DATA
        foreach ($_POST["file"] as $file) {
            $cgui->addItem("file[]", $file, $file, ilObject::_getIcon($this->object->getId()), $this->lng->txt("obj_usrf"));
        }

        $this->tpl->setContent($cgui->getHTML());
    }


    /**
    * cancel deletion of export files
    */
    public function cancelDeleteExportFileObject()
    {
        $this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
    }


    /**
    * delete export files
    */
    public function deleteExportFileObject()
    {
        $export_dir = $this->object->getExportDirectory();
        foreach ($_POST["file"] as $file) {
            $file = basename($file);
            
            $exp_file = $export_dir . "/" . $file;
            if (@is_file($exp_file)) {
                unlink($exp_file);
            }
        }
        $this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
    }

    /**
     * @throws ilObjectException
     */
    protected function performExportObject()
    {
        $this->checkPermission("write,read_users");

        $this->object->buildExportFile($_POST["export_type"]);
        $this->ctrl->redirect($this, 'export');
    }

    /**
     *
     */
    public function exportObject()
    {
        global $DIC;

        $this->checkPermission("write,read_users");

        $button = ilSubmitButton::getInstance();
        $button->setCaption('create_export_file');
        $button->setCommand('performExport');
        $toolbar = $DIC->toolbar();
        $toolbar->setFormAction($this->ctrl->getFormAction($this));

        $export_types = array(
            "userfolder_export_excel_x86",
            "userfolder_export_csv",
            "userfolder_export_xml"
        );
        $options = [];
        foreach ($export_types as $type) {
            $options[$type] = $this->lng->txt($type);
        }
        $type_selection = new \ilSelectInputGUI('', 'export_type');
        $type_selection->setOptions($options);

        $toolbar->addInputItem($type_selection, true);
        $toolbar->addButtonInstance($button);

        $table = new \ilUserExportFileTableGUI($this, 'export');
        $table->init();
        $table->parse($this->object->getExportFiles());

        $this->tpl->setContent($table->getHTML());
    }


    protected function initNewAccountMailForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $lng->loadLanguageModule("meta");
        $lng->loadLanguageModule("mail");
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        $form->setTitleIcon(ilUtil::getImagePath("icon_mail.svg"));
        $form->setTitle($lng->txt("user_new_account_mail"));
        $form->setDescription($lng->txt("user_new_account_mail_desc"));
                
        $langs = $lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            $amail = $this->object->_lookupNewAccountMail($lang_key);
            
            $title = $lng->txt("meta_l_" . $lang_key);
            if ($lang_key == $lng->getDefaultLanguage()) {
                $title .= " (" . $lng->txt("default") . ")";
            }
            
            $header = new ilFormSectionHeaderGUI();
            $header->setTitle($title);
            $form->addItem($header);
                                                    
            $subj = new ilTextInputGUI($lng->txt("subject"), "subject_" . $lang_key);
            // $subj->setRequired(true);
            $subj->setValue($amail["subject"]);
            $form->addItem($subj);
            
            $salg = new ilTextInputGUI($lng->txt("mail_salutation_general"), "sal_g_" . $lang_key);
            // $salg->setRequired(true);
            $salg->setValue($amail["sal_g"]);
            $form->addItem($salg);
            
            $salf = new ilTextInputGUI($lng->txt("mail_salutation_female"), "sal_f_" . $lang_key);
            // $salf->setRequired(true);
            $salf->setValue($amail["sal_f"]);
            $form->addItem($salf);
            
            $salm = new ilTextInputGUI($lng->txt("mail_salutation_male"), "sal_m_" . $lang_key);
            // $salm->setRequired(true);
            $salm->setValue($amail["sal_m"]);
            $form->addItem($salm);
        
            $body = new ilTextAreaInputGUI($lng->txt("message_content"), "body_" . $lang_key);
            // $body->setRequired(true);
            $body->setValue($amail["body"]);
            $body->setRows(10);
            $body->setCols(100);
            $form->addItem($body);
            
            $att = new ilFileInputGUI($lng->txt("attachment"), "att_" . $lang_key);
            $att->setAllowDeletion(true);
            if ($amail["att_file"]) {
                $att->setValue($amail["att_file"]);
            }
            $form->addItem($att);
        }
    
        $form->addCommandButton("saveNewAccountMail", $lng->txt("save"));
        $form->addCommandButton("cancelNewAccountMail", $lng->txt("cancel"));
                
        return $form;
    }
    
    /**
    * new account mail administration
    */
    public function newAccountMailObject()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('user_new_account_mail');

        $form = $this->initNewAccountMailForm();

        $ftpl = new ilTemplate('tpl.usrf_new_account_mail.html', true, true, 'Services/User');
        $ftpl->setVariable("FORM", $form->getHTML());
        unset($form);

        // placeholder help text
        $ftpl->setVariable("TXT_USE_PLACEHOLDERS", $lng->txt("mail_nacc_use_placeholder"));
        $ftpl->setVariable("TXT_MAIL_SALUTATION", $lng->txt("mail_nacc_salutation"));
        $ftpl->setVariable("TXT_FIRST_NAME", $lng->txt("firstname"));
        $ftpl->setVariable("TXT_LAST_NAME", $lng->txt("lastname"));
        $ftpl->setVariable("TXT_EMAIL", $lng->txt("email"));
        $ftpl->setVariable("TXT_LOGIN", $lng->txt("mail_nacc_login"));
        $ftpl->setVariable("TXT_PASSWORD", $lng->txt("password"));
        $ftpl->setVariable("TXT_PASSWORD_BLOCK", $lng->txt("mail_nacc_pw_block"));
        $ftpl->setVariable("TXT_NOPASSWORD_BLOCK", $lng->txt("mail_nacc_no_pw_block"));
        $ftpl->setVariable("TXT_ADMIN_MAIL", $lng->txt("mail_nacc_admin_mail"));
        $ftpl->setVariable("TXT_ILIAS_URL", $lng->txt("mail_nacc_ilias_url"));
        $ftpl->setVariable("TXT_CLIENT_NAME", $lng->txt("mail_nacc_client_name"));
        $ftpl->setVariable("TXT_TARGET", $lng->txt("mail_nacc_target"));
        $ftpl->setVariable("TXT_TARGET_TITLE", $lng->txt("mail_nacc_target_title"));
        $ftpl->setVariable("TXT_TARGET_TYPE", $lng->txt("mail_nacc_target_type"));
        $ftpl->setVariable("TXT_TARGET_BLOCK", $lng->txt("mail_nacc_target_block"));
        $ftpl->setVariable("TXT_IF_TIMELIMIT", $lng->txt("mail_nacc_if_timelimit"));
        $ftpl->setVariable("TXT_TIMELIMIT", $lng->txt("mail_nacc_timelimit"));

        $this->tpl->setContent($ftpl->get());
    }

    public function cancelNewAccountMailObject()
    {
        $this->ctrl->redirect($this, "settings");
    }

    public function saveNewAccountMailObject()
    {
        global $DIC;

        $lng = $DIC['lng'];
                
        $langs = $lng->getInstalledLanguages();
        foreach ($langs as $lang_key) {
            $this->object->_writeNewAccountMail(
                $lang_key,
                ilUtil::stripSlashes($_POST["subject_" . $lang_key]),
                ilUtil::stripSlashes($_POST["sal_g_" . $lang_key]),
                ilUtil::stripSlashes($_POST["sal_f_" . $lang_key]),
                ilUtil::stripSlashes($_POST["sal_m_" . $lang_key]),
                ilUtil::stripSlashes($_POST["body_" . $lang_key])
            );
                        
            if ($_FILES["att_" . $lang_key]["tmp_name"]) {
                $this->object->_updateAccountMailAttachment(
                    $lang_key,
                    $_FILES["att_" . $lang_key]["tmp_name"],
                    $_FILES["att_" . $lang_key]["name"]
                );
            }

            if ($_POST["att_" . $lang_key . "_delete"]) {
                $this->object->_deleteAccountMailAttachment($lang_key);
            }
        }
        
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "newAccountMail");
    }

    public function getAdminTabs()
    {
        $this->getTabs();
    }

    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $access = $DIC->access();
        
        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "usrf",
                $this->ctrl->getLinkTarget($this, "view"),
                array("view","delete","resetFilter", "userAction", ""),
                "",
                ""
            );
        }

        if ($access->checkRbacOrPositionPermissionAccess("read_users", \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, USER_FOLDER_ID)) {
            $this->tabs_gui->addTarget(
                "search_user_extended",
                $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', ''),
                array(),
                "ilrepositorysearchgui",
                ""
            );
        }
        
        
        if ($rbacsystem->checkAccess("write,read_users", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "generalSettings"),
                array('askForUserPasswordReset', 'forceUserPasswordReset', 'settings','generalSettings','listUserDefinedField','newAccountMail')
            );
                
            $this->tabs_gui->addTarget(
                "export",
                $this->ctrl->getLinkTarget($this, "export"),
                "export",
                "",
                ""
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }


    /**
    * set sub tabs
    */
    public function setSubTabs($a_tab)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        
        switch ($a_tab) {
            case "settings":
                $this->tabs_gui->addSubTabTarget(
                    'general_settings',
                    $this->ctrl->getLinkTarget($this, 'generalSettings'),
                    'generalSettings',
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "standard_fields",
                    $this->ctrl->getLinkTarget($this, 'settings'),
                    array("settings", "saveGlobalUserSettings"),
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "user_defined_fields",
                    $this->ctrl->getLinkTargetByClass("ilcustomuserfieldsgui", "listUserDefinedFields"),
                    "listUserDefinedFields",
                    get_class($this)
                );
                $this->tabs_gui->addSubTabTarget(
                    "user_new_account_mail",
                    $this->ctrl->getLinkTarget($this, 'newAccountMail'),
                    "newAccountMail",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "starting_points",
                    $this->ctrl->getLinkTargetByClass("iluserstartingpointgui", "startingPoints"),
                    "startingPoints",
                    get_class($this)
                );


                $this->tabs_gui->addSubTabTarget(
                    "user_profile_info",
                    $this->ctrl->getLinkTargetByClass("ilUserProfileInfoSettingsGUI", ''),
                    "",
                    "ilUserProfileInfoSettingsGUI"
                );

                #$this->tabs_gui->addSubTab("account_codes", $this->lng->txt("user_account_codes"),
                #							 $this->ctrl->getLinkTargetByClass("ilaccountcodesgui"));
                break;
        }
    }
    
    public function showLoginnameSettingsObject()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $show_blocking_time_in_days = (int) $ilSetting->get('loginname_change_blocking_time') / 86400;
        
        $this->initLoginSettingsForm();
        $this->loginSettingsForm->setValuesByArray(array(
            'allow_change_loginname' => (bool) $ilSetting->get('allow_change_loginname'),
            'create_history_loginname' => (bool) $ilSetting->get('create_history_loginname'),
            'reuse_of_loginnames' => (bool) $ilSetting->get('reuse_of_loginnames'),
            'loginname_change_blocking_time' => (float) $show_blocking_time_in_days
        ));
        
        $this->tpl->setVariable('ADM_CONTENT', $this->loginSettingsForm->getHTML());
    }
    
    private function initLoginSettingsForm()
    {
        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('loginname_settings');
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->loginSettingsForm = new ilPropertyFormGUI;
        $this->loginSettingsForm->setFormAction($this->ctrl->getFormAction($this, 'saveLoginnameSettings'));
        $this->loginSettingsForm->setTitle($this->lng->txt('loginname_settings'));
        
        $chbChangeLogin = new ilCheckboxInputGUI($this->lng->txt('allow_change_loginname'), 'allow_change_loginname');
        $chbChangeLogin->setValue(1);
        $this->loginSettingsForm->addItem($chbChangeLogin);
        $chbCreateHistory = new ilCheckboxInputGUI($this->lng->txt('history_loginname'), 'create_history_loginname');
        $chbCreateHistory->setInfo($this->lng->txt('loginname_history_info'));
        $chbCreateHistory->setValue(1);
        $chbChangeLogin->addSubItem($chbCreateHistory);
        $chbReuseLoginnames = new ilCheckboxInputGUI($this->lng->txt('reuse_of_loginnames_contained_in_history'), 'reuse_of_loginnames');
        $chbReuseLoginnames->setValue(1);
        $chbReuseLoginnames->setInfo($this->lng->txt('reuse_of_loginnames_contained_in_history_info'));
        $chbChangeLogin->addSubItem($chbReuseLoginnames);
        $chbChangeBlockingTime = new ilNumberInputGUI($this->lng->txt('loginname_change_blocking_time'), 'loginname_change_blocking_time');
        $chbChangeBlockingTime->allowDecimals(true);
        $chbChangeBlockingTime->setSuffix($this->lng->txt('days'));
        $chbChangeBlockingTime->setInfo($this->lng->txt('loginname_change_blocking_time_info'));
        $chbChangeBlockingTime->setSize(10);
        $chbChangeBlockingTime->setMaxLength(10);
        $chbChangeLogin->addSubItem($chbChangeBlockingTime);
        
        $this->loginSettingsForm->addCommandButton('saveLoginnameSettings', $this->lng->txt('save'));
    }
    
    public function saveLoginnameSettingsObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->initLoginSettingsForm();
        if ($this->loginSettingsForm->checkInput()) {
            $valid = true;
            
            if (!strlen($this->loginSettingsForm->getInput('loginname_change_blocking_time'))) {
                $valid = false;
                $this->loginSettingsForm->getItemByPostVar('loginname_change_blocking_time')
                                        ->setAlert($this->lng->txt('loginname_change_blocking_time_invalidity_info'));
            }
            
            if ($valid) {
                $save_blocking_time_in_seconds = (int) $this->loginSettingsForm->getInput('loginname_change_blocking_time') * 86400;
                
                $ilSetting->set('allow_change_loginname', (int) $this->loginSettingsForm->getInput('allow_change_loginname'));
                $ilSetting->set('create_history_loginname', (int) $this->loginSettingsForm->getInput('create_history_loginname'));
                $ilSetting->set('reuse_of_loginnames', (int) $this->loginSettingsForm->getInput('reuse_of_loginnames'));
                $ilSetting->set('loginname_change_blocking_time', (int) $save_blocking_time_in_seconds);
                
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            } else {
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            }
        } else {
            ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        }
        $this->loginSettingsForm->setValuesByPost();
    
        $this->tpl->setVariable('ADM_CONTENT', $this->loginSettingsForm->getHTML());
    }

    /**
     * goto target group
     */
    public static function _goto($a_user)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];

        $a_target = USER_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI&ref_id=" . $a_target . "&jmpToUser=" . $a_user);
            exit;
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                ilUtil::sendFailure(sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     * Jump to edit screen for user
     */
    public function jumpToUserObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        if (((int) $_GET["jmpToUser"]) > 0 && ilObject::_lookupType((int) $_GET["jmpToUser"]) == "usr") {
            $ilCtrl->setParameterByClass("ilobjusergui", "obj_id", (int) $_GET["jmpToUser"]);
            $ilCtrl->redirectByClass("ilobjusergui", "view");
        }
    }

    /**
     * @param array $a_user_ids
     * @return array
     */
    public function searchUserAccessFilterCallable(array $a_user_ids) : array
    {
        global $DIC;
        $access = $DIC->access();

        if (!$this->checkPermissionBool("read_users")) {
            $a_user_ids = $access->filterUserIdsByPositionOfCurrentUser(
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $a_user_ids
            );
        }

        return $a_user_ids;
    }

    /**
     * Handles multi command from repository search gui
     */
    public function searchResultHandler($a_usr_ids, $a_cmd)
    {
        if (!count((array) $a_usr_ids)) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            return false;
        }
        
        $_POST['id'] = $a_usr_ids;
        
        // no real confirmation here
        if (stristr($a_cmd, "export")) {
            $cmd = $a_cmd . "Object";
            return $this->$cmd();
        }
        
        $_POST['selectedAction'] = $a_cmd;
        return $this->showActionConfirmation($a_cmd, true);
    }
    
    public function getUserMultiCommands($a_search_form = false)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        $cmds = array();
        // see searchResultHandler()
        if ($a_search_form) {
            if ($this->checkAccessBool('write')) {
                $cmds = array(
                    'activate' => $this->lng->txt('activate'),
                    'deactivate' => $this->lng->txt('deactivate'),
                    'accessRestrict' => $this->lng->txt('accessRestrict'),
                    'accessFree' => $this->lng->txt('accessFree')
                    );
            }

            if ($this->checkAccessBool('delete')) {
                $cmds["delete"] = $this->lng->txt("delete");
            }
        }
        // show confirmation
        else {
            if ($this->checkAccessBool('write')) {
                $cmds = array(
                    'activateUsers' => $this->lng->txt('activate'),
                    'deactivateUsers' => $this->lng->txt('deactivate'),
                    'restrictAccess' => $this->lng->txt('accessRestrict'),
                    'freeAccess' => $this->lng->txt('accessFree')
                );
            }
            
            if ($this->checkAccessBool('delete')) {
                $cmds["deleteUsers"] = $this->lng->txt("delete");
            }
        }
        
        if ($this->checkAccessBool('write')) {
            $export_types = array("userfolder_export_excel_x86", "userfolder_export_csv", "userfolder_export_xml");
            foreach ($export_types as $type) {
                $cmd = explode("_", $type);
                $cmd = array_pop($cmd);
                $cmds['usrExport' . ucfirst($cmd)] = $this->lng->txt('export') . ' - ' .
                    $this->lng->txt($type);
            }
        }
        
        // check if current user may send mails
        include_once "Services/Mail/classes/class.ilMail.php";
        $mail = new ilMail($ilUser->getId());
        if ($rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
            $cmds["mail"] = $this->lng->txt("send_mail");
        }
        
        $cmds['addToClipboard'] = $this->lng->txt('clipboard_add_btn');
                        
        return $cmds;
    }
    
    /**
     * Export excel
     */
    protected function usrExportX86Object()
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            return $this->ctrl->redirect($this, 'view');
        }

        if ($this->checkPermissionBool('write,read_users')) {
            $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_EXCEL, $user_ids);
            $this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_EXCEL, $user_ids, true);
            ilUtil::deliverFile($fullname . '.xlsx', $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_EXCEL) . '.xlsx', '', false, true);
        }
    }
    
    /**
     * Export csv
     */
    protected function usrExportCsvObject()
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            return $this->ctrl->redirect($this, 'view');
        }

        if ($this->checkPermissionBool("write,read_users")) {
            $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_CSV, $user_ids);
            $this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_CSV, $user_ids, true);
            ilUtil::deliverFile($fullname, $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_CSV), '', false, true);
        }
    }
    
    /**
     * Export xml
     */
    protected function usrExportXmlObject()
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            return $this->ctrl->redirect($this, 'view');
        }
        if ($this->checkPermissionBool("write,read_users")) {
            $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_XML, $user_ids);
            $this->ctrl->redirectByClass("ilobjuserfoldergui", "export");
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(ilObjUserFolder::FILE_TYPE_XML, $user_ids, true);
            ilUtil::deliverFile($fullname, $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_XML), '', false, true);
        }
    }
    
    /**
     *
     */
    protected function mailObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            return $this->ctrl->redirect($this, 'view');
        }
        
        // remove existing (temporary) lists
        include_once "Services/Contact/classes/class.ilMailingLists.php";
        $list = new ilMailingLists($ilUser);
        $list->deleteTemporaryLists();
        
        // create (temporary) mailing list
        include_once "Services/Contact/classes/class.ilMailingList.php";
        $list = new ilMailingList($ilUser);
        $list->setMode(ilMailingList::MODE_TEMPORARY);
        $list->setTitle("-TEMPORARY SYSTEM LIST-");
        $list->setDescription("-USER ACCOUNTS MAIL-");
        $list->setCreateDate(date("Y-m-d H:i:s"));
        $list->insert();
        $list_id = $list->getId();
        
        // after list has been saved...
        foreach ($user_ids as $user_id) {
            $list->assignUser($user_id);
        }
        
        include_once "Services/Mail/classes/class.ilFormatMail.php";
        $umail = new ilFormatMail($ilUser->getId());
        $mail_data = $umail->getSavedData();
        
        if (!is_array($mail_data)) {
            $mail_data = array("user_id" => $ilUser->getId());
        }
                
        // ???
        // $mail_data = $umail->appendSearchResult(array('#il_ml_'.$list_id), 'to');
        
        $umail->savePostData(
            $mail_data['user_id'],
            $mail_data['attachments'],
            '#il_ml_' . $list_id, // $mail_data['rcp_to'],
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_type'],
            $mail_data['m_email'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
        ilUtil::redirect(
            ilMailFormCall::getRedirectTarget(
                $this,
                '',
                array(),
                array(
                    'type' => 'search_res'
                )
            )
        );
    }
    
    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:
                
                include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
                $security = ilSecuritySettings::_getInstance();
                
                $fields = array();
                
                $subitems = array(
                    'ps_password_change_on_first_login_enabled' => array($security->isPasswordChangeOnFirstLoginEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'ps_password_must_not_contain_loginame' => array((bool) $security->getPasswordMustNotContainLoginnameStatus(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'ps_password_chars_and_numbers_enabled' => array($security->isPasswordCharsAndNumbersEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'ps_password_special_chars_enabled' => array($security->isPasswordSpecialCharsEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'ps_password_min_length' => (int) $security->getPasswordMinLength(),
                    'ps_password_max_length' => (int) $security->getPasswordMaxLength(),
                    'ps_password_uppercase_chars_num' => (int) $security->getPasswordNumberOfUppercaseChars(),
                    'ps_password_lowercase_chars_num' => (int) $security->getPasswordNumberOfLowercaseChars(),
                    'ps_password_max_age' => (int) $security->getPasswordMaxAge()
                );
                $fields['ps_password_settings'] = array(null, null, $subitems);
                
                $subitems = array(
                    'ps_login_max_attempts' => (int) $security->getLoginMaxAttempts(),
                    'ps_prevent_simultaneous_logins' => array($security->isPreventionOfSimultaneousLoginsEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );
                $fields['ps_security_protection'] = array(null, null, $subitems);
                
                return array(array("generalSettings", $fields));
        }
    }

    /**
     * Add users to clipboard
     */
    protected function addToClipboardObject()
    {
        $users = $this->getActionUserIds();
        if (!count($users)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'view');
        }
        include_once './Services/User/classes/class.ilUserClipboard.php';
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->add($users);
        $clip->save();
        
        ilUtil::sendSuccess($this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect($this, 'view');
    }
} // END class.ilObjUserFolderGUI
