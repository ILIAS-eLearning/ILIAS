<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjOrgUnit GUI class
 *
 * @author Oskar Truffer <fs@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 * Date: 4/07/13
 * Time: 1:09 PM
 *
 * @ilCtrl_IsCalledBy ilObjOrgUnitGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjOrgUnitGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI, ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls ilObjOrgUnitGUI: ilInfoScreenGUI, ilObjStyleSheetGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjOrgUnitGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI, ilDidacticTemplateGUI, ilExportGUI, illearningprogressgui
 * @ilCtrl_Calls ilObjOrgUnitGUI: ilOrgUnitTranslationGUI, ilRepositorySearchGUI
 */

//TODO:
//- Move some methods in seperate Classes
//- Add Comments to the methods

require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitStaffTableGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitOtherRolesTableGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExporter.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Search/classes/class.ilRepositorySearchGUI.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

class ilObjOrgUnitGUI extends ilContainerGUI {

	/** @var  ilTabsGUI */
	public $tabs_gui;

	protected $active_subtab;

	function __construct()
    {
		parent::ilContainerGUI(array(), $_GET["ref_id"], true, false);

        global $tpl, $ilCtrl, $ilDB;
		/**
		 * @var $tpl    ilTemplate
		 * @var $ilCtrl ilCtrl
		 * @var $ilDB ilDB
		 */
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->db = $ilDB;
	}

	public function &executeCommand()
    {
		global $ilTabs, $lng, $ilAccess, $ilNavigationHistory, $ilCtrl;
		$lng->loadLanguageModule("orgu");
		$own_ex = array("illearningprogressgui", "illplistofprogressgui", "ilexportgui");
		$cmdClass = $this->ctrl->getCmdClass();
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->ref_id = $_GET["ref_id"];



        if($cmd != "cut")
        {
            $this->showTreeObject();
        }

        //TODO which comands are not used?
        switch($next_class)
        {
            case "ilobjusergui":
                include_once('./Services/User/classes/class.ilObjUserGUI.php');

                $this->tabs_gui->setTabActive('administrate_users');
                if(!$_GET['obj_id'])
                {
                    $this->gui_obj = new ilObjUserGUI("",$_GET['ref_id'],true, false);
                    $this->gui_obj->setCreationMode($this->creation_mode);
                    $ret =& $this->ctrl->forwardCommand($this->gui_obj);
                }
                else
                {
                    $this->gui_obj = new ilObjUserGUI("", $_GET['obj_id'],false, false);
                    $this->gui_obj->setCreationMode($this->creation_mode);
                    $ret =& $this->ctrl->forwardCommand($this->gui_obj);
                }

                $ilTabs->clearTargets();
                $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this,'listUsers'));
                break;

            case "ilobjuserfoldergui":
                include_once('./Services/User/classes/class.ilObjUserFolderGUI.php');

                $this->tabs_gui->setTabActive('administrate_users');
				if($this->ctrl->getCmd() == "view"){
					//This fix is because of the back button on the single user gui of the local user administration.
					$this->ctrl->redirect($this, "listUsers");
					return;
				}
				$this->gui_obj = new ilObjUserFolderGUI("",(int) $_GET['ref_id'],true, false);
				$this->gui_obj->setUserOwnerId((int) $_GET['ref_id']);
				$this->gui_obj->setCreationMode($this->creation_mode);
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				break;

			case "ilcolumngui":
				$this->checkPermission("read");
				$this->prepareOutput();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
				$this->renderObject();
				break;

			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->infoScreen();
				break;

			case 'ilusertablegui':
				include_once './Services/User/classes/class.ilUserTableGUI.php';
				$u_table = new ilUserTableGUI($this, "listUsers");
				$u_table->initFilter();
				$this->ctrl->setReturn($this,'listUsers');
				$this->ctrl->forwardCommand($u_table);
				break;

			case "ilcommonactiondispatchergui":
				$this->prepareOutput();
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

	        case 'illearningprogressgui':
	        case 'illplistofprogressgui':
		        if($this->ctrl->getCmd() == "view"){
			        //This fix is because of the back button on the single user gui of the local user administration.
			        $this->ctrl->redirect($this, "showStaff");
			        return;
		        }

				if(!$this->checkPermForLP()){
					ilUtil::sendFailure($lng->txt("permission_denied"), true);
					$this->ctrl->redirect($this, "showStaff");
				}

				$this->prepareOutput();
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				if($user_id = $_GET["obj_id"]){
					$this->ctrl->saveParameterByClass("illearningprogressgui", "obj_id");
					$this->ctrl->saveParameterByClass("illearningprogressgui", "recursive");
					include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
					$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_USER_FOLDER,USER_FOLDER_ID,$_GET["obj_id"]);
					$this->ctrl->forwardCommand($new_gui);
				}

	            $ilTabs->clearTargets();
	            $ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this,'showStaff'));
				break;

			case 'ilexportgui':
				$this->prepareOutput();

				if($this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId())
				{
					//Simple XML and Simple XLS Export should only be available in the root orgunit folder as it always exports the whole tree
					$this->extendExportGUI();
				}

				$this->tabs_gui->setTabActive('export');
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;

			case 'ilrepositorysearchgui':
				if($cmd == 'addUserFromAutoComplete' && ! $_GET['local_roles']){
					$this->prepareOutput();
					$this->addStaffObject();
					break;
				}elseif($cmd == 'addUserFromAutoComplete'){
					$this->prepareOutput();
					$this->addOtherRolesObject();
					break;
				}
				$repo = new ilRepositorySearchGUI();
				$this->ctrl->forwardCommand($repo);
				break;

			default:
				$this->prepareOutput();

				if(!$cmd)
				{
					$cmd = "render";
				}

				switch($cmd)
				{
					case 'infoScreen':
						$ilTabs->setTabActive("info_short");
						$cmd .= "Object";
						$this->checkPermission("visible");
						$this->$cmd();
						break;
					case 'editExtId':
					case 'updateExtId':
						$ilTabs->setTabActive("settings");
						$cmd .= "Object";
						$this->checkPermission("read");
						$this->$cmd();
						break;
					case 'editTranslations':
					case 'saveTranslations':
					case 'addTranslation':
					case 'deleteTranslations':
						if (!$this->checkPermissionBool("write"))
						{
							$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
						}
						$ilTabs->setTabActive("settings");
						require_once './Modules/OrgUnit/classes/class.ilOrgUnitTranslationGUI.php';
						$ilOrgUnitTranslationGui = new ilOrgUnitTranslationGUI($this);
						$this->ctrl->forwardCommand($ilOrgUnitTranslationGui);
						break;
					case 'confirmRemoveFromRole':
					case 'confirmRemoveFromEmployees':
					case 'confirmRemoveFromSuperiors':
						$ilTabs->setTabActive("orgu_staff");
						$this->confirmRemoveUserObject($cmd);
						break;
					default:
						$cmd .= "Object";
						$this->checkPermission("read");
						$this->$cmd();
						break;
				}

				break;
		}


		/*$active_tab = $ilTabs->getActiveTab();


		$ilTabs->setTabActive($active_tab);
		*/
		//if($cmdClass != "ilexportgui")
		//{

		$this->setContentSubTabs($this->ctrl->getCmd());
		//}
	}

    /**
     * this one is called from the info button in the orgunit
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     */
    function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    /**
     * show information screen
     */
    function infoScreen()
    {
        global $ilAccess, $ilCtrl;

        if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
        {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        $ilCtrl->forwardCommand($info);
    }



    // METHODS for local user administration
    /**
     * Reset filter
     * (note: this function existed before data table filter has been introduced
     */
    protected function resetFilterObject()
    {
        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "listUsers",ilUserTableGUI::MODE_LOCAL_USER);
        $utab->resetOffset();
        $utab->resetFilter();

        // from "old" implementation
        $this->listUsersObject();
    }

    /**
     * Apply filter
     * @return
     */
    protected function applyFilterObject()
    {
        global $ilTabs;

        include_once("./Services/User/classes/class.ilUserTableGUI.php");
        $utab = new ilUserTableGUI($this, "listUsers", ilUserTableGUI::MODE_LOCAL_USER);
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->listUsersObject();
    }


	function listUsersObject($show_delete = false)
	{
		global $ilUser,$rbacreview, $ilToolbar, $rbacsystem;

		include_once './Services/User/classes/class.ilLocalUser.php';
		include_once './Services/User/classes/class.ilObjUserGUI.php';

		if(!$rbacsystem->checkAccess("cat_administrate_users",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_admin_users"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tabs_gui->setTabActive('administrate_users');

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.cat_admin_users.html',
			"Modules/Category");

		if(count($rbacreview->getGlobalAssignableRoles()) or in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			$ilToolbar->addButton(
				$this->lng->txt('add_user'),
				$this->ctrl->getLinkTargetByClass('ilobjusergui','create')
			);

			$ilToolbar->addButton(
				$this->lng->txt('import_users'),
				$this->ctrl->getLinkTargetByClass('ilobjuserfoldergui','importUserForm')
			);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
		}

		if($show_delete)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeleteUsers');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}

		$this->lng->loadLanguageModule('user');

		include_once("./Services/User/classes/class.ilUserTableGUI.php");
		$utab = new ilUserTableGUI($this, 'listUsers',ilUserTableGUI::MODE_LOCAL_USER);
		$this->tpl->setVariable('USERS_TABLE',$utab->getHTML());

		return true;
	}

	/**
	 * Show auto complete results
	 */
	protected function addUserAutoCompleteObject()
	{
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array('login','firstname','lastname','email'));
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList($_REQUEST['query']);
		exit();
	}


	function performDeleteUsersObject()
	{
		include_once './Services/User/classes/class.ilLocalUser.php';
		$this->checkPermission("cat_administrate_users");

		foreach($_POST['user_ids'] as $user_id)
		{
			if(!in_array($user_id,ilLocalUser::_getAllUserIds($this->object->getRefId())))
			{
				die('user id not valid');
			}
			if(!$tmp_obj =& ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			$tmp_obj->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('deleted_users'));
		$this->listUsersObject();

		return true;
	}

	function deleteUsersObject()
	{
		$this->checkPermission("cat_administrate_users");
		if(!count($_POST['id']))
		{
			ilUtil::sendFailure($this->lng->txt('no_users_selected'));
			$this->listUsersObject();

			return true;
		}

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('sure_delete_selected_users'));
		$confirm->setConfirm($this->lng->txt('delete'), 'performDeleteUsers');
		$confirm->setCancel($this->lng->txt('cancel'), 'listUsers');

		foreach($_POST['id'] as $user)
		{
			$name = ilObjUser::_lookupName($user);

			$confirm->addItem(
				'user_ids[]',
				$user,
				$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']'
			);
		}
		$this->tpl->setContent($confirm->getHTML());
	}

	function assignRolesObject()
	{
		global $rbacreview,$ilTabs;

		$this->checkPermission("cat_administrate_users");

		include_once './Services/User/classes/class.ilLocalUser.php';

		if(!isset($_GET['obj_id']))
		{
			ilUtil::sendFailure('no_user_selected');
			$this->listUsersObject();

			return true;
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTarget($this,'listUsers'));

		$roles = $this->__getAssignableRoles();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.cat_role_assignment.html',
			"Modules/Category");

		$ass_roles = $rbacreview->assignedRoles($_GET['obj_id']);

		$counter = 0;
		foreach($roles as $role)
		{
			$role_obj =& ilObjectFactory::getInstanceByObjId($role['obj_id']);

			$disabled = false;
			$f_result[$counter][] = ilUtil::formCheckbox(in_array($role['obj_id'],$ass_roles) ? 1 : 0,
				'role_ids[]',
				$role['obj_id'],
				$disabled);
			$f_result[$counter][] = $role_obj->getTitle();
			$f_result[$counter][] = $role_obj->getDescription();
			$f_result[$counter][] = $role['role_type'] == 'global' ?
				$this->lng->txt('global') :
				$this->lng->txt('local');

			unset($role_obj);
			++$counter;
		}
		$this->__showRolesTable($f_result,"assignRolesObject");
	}

	function assignSaveObject()
	{
		global $rbacreview,$rbacadmin;
		$this->checkPermission("cat_administrate_users");

		include_once './Services/User/classes/class.ilLocalUser.php';
		// check hack
		if(!isset($_GET['obj_id']) or !in_array($_REQUEST['obj_id'],ilLocalUser::_getAllUserIds()))
		{
			ilUtil::sendFailure('no_user_selected');
			$this->listUsersObject();

			return true;
		}
		$roles = $this->__getAssignableRoles();

		// check minimum one global role
		if(!$this->__checkGlobalRoles($_POST['role_ids']))
		{
			ilUtil::sendFailure($this->lng->txt('no_global_role_left'));
			$this->assignRolesObject();

			return false;
		}

		$new_role_ids = $_POST['role_ids'] ? $_POST['role_ids'] : array();
		$assigned_roles = $rbacreview->assignedRoles((int) $_REQUEST['obj_id']);
		foreach($roles as $role)
		{
			if(in_array($role['obj_id'],$new_role_ids) and !in_array($role['obj_id'],$assigned_roles))
			{
				$rbacadmin->assignUser($role['obj_id'],(int) $_REQUEST['obj_id']);
			}
			if(in_array($role['obj_id'],$assigned_roles) and !in_array($role['obj_id'],$new_role_ids))
			{
				$rbacadmin->deassignUser($role['obj_id'],(int) $_REQUEST['obj_id']);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('role_assignment_updated'));
		$this->assignRolesObject();

		return true;
	}

	// PRIVATE
	function __getAssignableRoles()
	{
		global $rbacreview,$ilUser;

		// check local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		// Admin => all roles
		if(in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			$global_roles = $rbacreview->getGlobalRolesArray();
		}
		elseif($tmp_obj->getTimeLimitOwner() == $this->object->getRefId())
		{
			$global_roles = $rbacreview->getGlobalAssignableRoles();
		}
		else
		{
			$global_roles = array();
		}
		return $roles = array_merge($global_roles,
			$rbacreview->getAssignableChildRoles($this->object->getRefId()));
	}

	function __checkGlobalRoles($new_assigned)
	{
		global $rbacreview,$ilUser;

		$this->checkPermission("cat_administrate_users");

		// return true if it's not a local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		if($tmp_obj->getTimeLimitOwner() != $this->object->getRefId() and
			!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			return true;
		}

		// new assignment by form
		$new_assigned = $new_assigned ? $new_assigned : array();
		$assigned = $rbacreview->assignedRoles((int) $_GET['obj_id']);

		// all assignable globals
		if(!in_array(SYSTEM_ROLE_ID,$rbacreview->assignedRoles($ilUser->getId())))
		{
			$ga = $rbacreview->getGlobalAssignableRoles();
		}
		else
		{
			$ga = $rbacreview->getGlobalRolesArray();
		}
		$global_assignable = array();
		foreach($ga as $role)
		{
			$global_assignable[] = $role['obj_id'];
		}

		$new_visible_assigned_roles = array_intersect($new_assigned,$global_assignable);
		$all_assigned_roles = array_intersect($assigned,$rbacreview->getGlobalRoles());
		$main_assigned_roles = array_diff($all_assigned_roles,$global_assignable);

		if(!count($new_visible_assigned_roles) and !count($main_assigned_roles))
		{
			return false;
		}
		return true;
	}


	function __showRolesTable($a_result_set,$a_from = "")
	{
		$this->checkPermission("cat_administrate_users");

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$this->ctrl->setParameter($this,'obj_id',$_GET['obj_id']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		// SET FOOTER BUTTONS
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.png"));

		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME","assignSave");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("change_assignment"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_GET['obj_id']);
		$title = $this->lng->txt('role_assignment').' ('.$tmp_obj->getFullname().')';

		$tbl->setTitle($title,"icon_role.png",$this->lng->txt("role_assignment"));
		$tbl->setHeaderNames(array('',
			$this->lng->txt("title"),
			$this->lng->txt('description'),
			$this->lng->txt("type")));
		$tbl->setHeaderVars(array("",
				"title",
				"description",
				"type"),
			array("ref_id" => $this->object->getRefId(),
				"cmd" => "assignRoles",
				"obj_id" => $_GET['obj_id'],
				"cmdClass" => "ilobjcategorygui",
				"cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","35%","45%","16%"));

		$this->set_unlimited = true;
		$this->__setTableGUIBasicData($tbl,$a_result_set,$a_from,true);
		$tbl->render();

		$this->tpl->setVariable("ROLES_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showUsersTable($a_result_set,$a_from = "",$a_footer = true)
	{
		$this->checkPermission("cat_administrate_users");

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$this->ctrl->setParameter($this,'sort_by',$_GET['sort_by']);
		$this->ctrl->setParameter($this,'sort_order',$_GET['sort_order']);
		$this->ctrl->setParameter($this,'offset',$_GET['offset']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		if($a_footer)
		{
			// SET FOOTER BUTTONS
			$tpl->setVariable("COLUMN_COUNTS",6);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.png"));

			$tpl->setCurrentBlock("tbl_action_button");
			$tpl->setVariable("BTN_NAME","deleteUser");
			$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("tbl_action_row");
			$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
			$tpl->parseCurrentBlock();

			$tbl->setFormName('cmd');
			$tbl->enable('select_all');
		}

		$tbl->setTitle($this->lng->txt("users"),"icon_usr.png",$this->lng->txt("users"));
		$tbl->setHeaderNames(array('',
			$this->lng->txt("username"),
			$this->lng->txt("firstname"),
			$this->lng->txt("lastname"),
			$this->lng->txt('context'),
			$this->lng->txt('role_assignment')));
		$tbl->setHeaderVars(array("",
				"login",
				"firstname",
				"lastname",
				"context",
				"role_assignment"),
			array("ref_id" => $this->object->getRefId(),
				"cmd" => "listUsers",
				"cmdClass" => "ilobjcategorygui",
				"cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("1px","20%","20%","20%","20%","20%"));
		$tbl->setSelectAllCheckbox('user_ids');

		$this->__setTableGUIBasicData($tbl,$a_result_set,$a_from,true);
		$tbl->render();

		$this->tpl->setVariable("USERS_TABLE",$tbl->tpl->get());

		return true;
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "",$a_footer = true)
	{
		global $ilUser;

		switch ($a_from)
		{
			case "listUsersObject":
				$tbl->setOrderColumn($_GET["sort_by"]);
				$tbl->setOrderDirection($_GET["sort_order"]);
				$tbl->setOffset($_GET["offset"]);
				$tbl->setMaxCount($this->all_users_count);
				$tbl->setLimit($ilUser->getPref('hits_per_page'));
				$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
				$tbl->setData($result_set);
				$tbl->disable('auto_sort');

				return true;


			case "assignRolesObject":
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
				if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
				{
					$_GET["sort_by"] = "login";
				}
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;

			case "clipboardObject":
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				$tbl->disable("footer");
				break;

			default:
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		if($this->set_unlimited)
		{
			$tbl->setLimit($_GET["limit"]*100);
		}
		else
		{
			$tbl->setLimit($_GET['limit']);
		}
		$tbl->setMaxCount(count($result_set));

		if($a_footer)
		{
			$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		}
		else
		{
			$tbl->disable('footer');
		}
		$tbl->setData($result_set);
	}

	public function editExtIdObject(){
		global $tpl;
		$form = $this->initEditExtIdForm();
		$tpl->setContent($form->getHTML());
	}

	public function updateExtIdObject(){
		global $tpl;
		$form = $this->initEditExtIdForm();
		$form->setValuesByPost();
		if($form->checkInput()){
			$this->object->setImportId($form->getItemByPostVar("ext_id")->getValue());
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("ext_id_updated"), true);
			$tpl->setContent($form->getHTML());
		}else{
			$tpl->setContent($form->getHTML());
		}
	}

	public function initEditExtIdForm(){
		$form = new ilPropertyFormGUI();
		$input = new ilTextInputGUI($this->lng->txt("ext_id"), "ext_id");
		$input->setValue($this->object->getImportId());
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("updateExtId", $this->lng->txt("save"));
		return $form;
	}


	private function extendExportGUI(){
		if($this->ctrl->getCmd() != "")
			return;
		global $ilToolbar, $lng;
		/** @var ilToolbarGUI $toolbar */
		$toolbar = $ilToolbar;
		$toolbar->addButton($lng->txt("simple_xml"), $this->ctrl->getLinkTarget($this, "simpleExport"));
		$toolbar->addButton($lng->txt("simple_xls"), $this->ctrl->getLinkTarget($this, "simpleExportExcel"));
	}

	public function simpleExportObject(){
		$exporter = new ilOrgUnitExporter();
		$exporter->sendAndCreateSimpleExportFile();
	}

	public function simpleExportExcelObject(){
		$exporter = new ilOrgUnitExporter();
		$exporter->simpleExportExcel(ilObjOrgUnit::getRootOrgRefId());
	}

	protected function checkPermForLP(){
		$recursive = $_GET["recursive"];
		global $ilAccess, $ilUser;
		if(!$ilAccess->checkAccess("view_learning_progress".($recursive?"_rec":""), "", $_GET["ref_id"]))
			return false;
		//obj id / user_id is the id of the user which lp we want to inspect.
		if(!($user_id = $_GET["obj_id"]))
			return false;

		//the user has to be an employee in this or a subsequent org-unit / Or the users own learning-Progress
		if(!in_array($user_id, ilObjOrgUnitTree::_getInstance()->getEmployees($_GET["ref_id"], $recursive)) AND $ilUser->getId() != $user_id )
			return false;

		return true;
	}

	public function renderObject(){
		global $ilTabs, $ilToolbar;
		/** @var ilToolbarGUI $ilToolbar */
		$ilToolbar = $ilToolbar;
		parent::renderObject();
		$ilTabs->setTabActive("view_content");
		$this->tabs_gui->removeSubTab("page_editor");
		if($this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId())
		{
			$ilToolbar->addButton($this->lng->txt("simple_import"), $this->ctrl->getLinkTarget($this, "importScreen"));
		}
	}

	public function viewObject() {
		$this->renderObject();
	}

	function showPossibleSubObjects(){
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($this->object->getRefId());
		$gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
		$gui->setCreationUrl("ilias.php?ref_id=".$_GET["ref_id"]."&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
		$gui->render();
	}

	public function showTreeObject(){
		require_once("./Services/Tree/classes/class.ilTree.php");
		require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");

		$tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
		$tree->setTypeWhiteList(array("orgu"));
		if(!$tree->handleCommand()){
			global $tpl;
			$tpl->setLeftNavContent($tree->getHTML());
		}
		$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
	}
	/**
	 * called by prepare output
	 */
	function setTitleAndDescription()
	{
		global $rbacreview;
		# all possible create permissions
		$possible_ops_ids = $rbacreview->getOperationsByTypeAndClass(
			'orgu',
			'create'
		);

		global $lng;
		parent::setTitleAndDescription();
		if($this->object->getTitle() == "__OrgUnitAdministration")
			$this->tpl->setTitle($lng->txt("objs_orgu"));
		$this->tpl->setDescription($lng->txt("objs_orgu"));
	}

	protected function addAdminLocatorItems(){
		global $ilLocator, $tree, $ilCtrl, $lng;
		/** @var ilLocatorGUI $ilLocator */
		$ilLocator = $ilLocator;

		$path = $tree->getPathFull($_GET["ref_id"], ilObjOrgUnit::getRootOrgRefId());

		// add item for each node on path
		foreach ((array) $path as $key => $row)
		{
			if ($row["title"] == "__OrgUnitAdministration")
			{
				$row["title"] = $lng->txt("objs_orgu");
			}

			$ilCtrl->setParameterByClass("ilobjorgunitgui", "ref_id", $row["child"]);
			$ilLocator->addItem($row["title"],
				$ilCtrl->getLinkTargetByClass("ilobjorgunitgui", "view"),
				ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
			$ilCtrl->setParameterByClass("ilobjorgunitgui", "ref_id", $_GET["ref_id"]);
		}
	}

	protected function redirectToRefId($a_ref_id, $a_cmd = "")
	{
		$obj_type = ilObject::_lookupType($a_ref_id,true);
		if($obj_type != "orgu")
			parent::redirectToRefId($a_ref_id, $a_cmd);
		else{
			$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $a_ref_id);
			$this->ctrl->redirectByClass("ilObjOrgUnitGUI", $a_cmd);
		}
	}

	public function getTabs(&$tabs_gui){
		global $ilTabs, $ilAccess, $rbacsystem, $lng;
		/** @var ilTabsGUI $ilTabs */
		$ilTabs = $ilTabs;

        if ($rbacsystem->checkAccess('read',$this->ref_id))
        {
            $force_active = ($_GET["cmd"] == "" || $_GET["cmd"] == "render")
                ? true
                : false;
            $tabs_gui->addTab("view_content", $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, ""));

            //BEGIN ChangeEvent add info tab to category object
            $force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
                || strtolower($_GET["cmdClass"]) == "ilnotegui")
                ? true
                : false;
            $this->addInfoTab($tabs_gui, $force_active);
            //END ChangeEvent add info tab to category object
        }

		if($rbacsystem->checkAccess('write',$this->ref_id) OR $ilAccess->checkAccess("view_learning_progress", "", $_GET["ref_id"]))
		{
			$ilTabs->addTab("orgu_staff", $this->lng->txt("orgu_staff"), $this->ctrl->getLinkTarget($this, "showStaff"), "", 25);
		}

		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			if($_GET["ref_id"] != ilObjOrgUnit::getRootOrgRefId())
			{
				$ilTabs->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, "editTranslations"));
			}
		}


        include_once './Services/User/classes/class.ilUserAccountSettings.php';
        if(
            ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled() and
            $rbacsystem->checkAccess('cat_administrate_users',$this->ref_id))
        {
            $tabs_gui->addTarget("administrate_users",
                $this->ctrl->getLinkTarget($this, "listUsers"), "listUsers", get_class($this));
        }

        if($ilAccess->checkAccess('write','',$this->object->getRefId()))
        {
            $tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilexportgui',''),
                'export',
                'ilexportgui'
            );
        }
        parent::getTabs($tabs_gui);
	}

	public function showAdministrationPanel(&$tpl){
		parent::showAdministrationPanel($tpl);

		//an ugly encapsulation violation in order to remove the "verknüpfen"/"link" button.
		/** @var $toolbar ilToolbarGUI*/
		if(!$toolbar = $tpl->admin_panel_commands_toolbar)
			return;

		if(is_array($toolbar->items))
			foreach($toolbar->items as $key => $item){
				if($item["cmd"] == "link" || $item["cmd"] == "copy")
					unset($toolbar->items[$key]);
			}
	}

	public function showStaffObject(){
		global $ilTabs, $ilAccess;

		if(!$ilAccess->checkAccess("write", "", $_GET["ref_id"]) AND !$ilAccess->checkAccess("view_learning_progress", "", $_GET["ref_id"]))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}

		$ilTabs->setTabActive("orgu_staff");

		if($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->addStaffToolbar();
		}

		$this->ctrl->setParameter($this, "recursive", false);
		$this->tpl->setContent($this->getStaffTableHTML(false, "showStaff"));
	}

    public function showOtherRolesObject(){
        global $ilTabs;

        $ilTabs->setTabActive("orgu_staff");

        //$this->ctrl->setParameter($this, "recursive", false);
        if(!$this->checkAccess("write"))
            return;
        $this->tpl->setContent($this->getOtherRolesTableHTML());
    }

	public function showStaffRecObject(){
		global $ilTabs, $ilAccess;
		$ilTabs->setTabActive("orgu_staff");

		if(!$ilAccess->checkAccess("write", "", $_GET["ref_id"]) AND !$ilAccess->checkAccess("view_learning_progress_rec", "", $_GET["ref_id"]))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}

		$this->ctrl->setParameter($this, "recursive", true);
		$this->tpl->setContent($this->getStaffTableHTML(true, "showStaffRec"));
	}

	protected function addStaffToolbar() {
		global $lng, $ilToolbar;

		$types = array(
			"employee" => $this->lng->txt("employee"), "superior" => $this->lng->txt("superior")
		);

		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'user_type'				=> $types,
				'submit_name'			=> $lng->txt('add')
			)
		);
	}

	protected function addOtherRolesToolbar() {
		if(!$this->checkAccess("write"))
			return;

		global $lng, $ilToolbar, $rbacreview;

		if(!$this->checkAccess("write"))
			return;

		$arrLocalRoles = $rbacreview->getLocalRoles($_GET["ref_id"]);
		$types = array();

		foreach($arrLocalRoles as $role_id)
		{
			$ilObjRole = new ilObjRole($role_id);
			if(!preg_match("/il_orgu_/", $ilObjRole->getUntranslatedTitle()))
			{
				$types[$role_id] = $ilObjRole->getPresentationTitle();
			}
		}

		$this->ctrl->setParameterByClass('ilRepositorySearchGUI', 'local_roles', 'true');

		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'user_type'				=> $types,
				'submit_name'			=> $lng->txt('add')
			)
		);
	}

	public function addStaffObject(){
		if(!$this->checkAccess("write"))
			return;

		$users = explode(',', $_POST['user_login']);
		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);
			if($user_id)
			{
				$user_ids[] = $user_id;
			}
		}

		$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 0;

		if($user_type == "employee")
			$this->object->assignUsersToEmployeeRole($user_ids);
		elseif($user_type == "superior")
			$this->object->assignUsersToSuperiorRole($user_ids);
		else
			throw new Exception("The post request didn't specify wether the user_ids should be assigned to the employee or the superior role.");
		ilUtil::sendSuccess($this->lng->txt("users_successfuly_added"), true);
		$this->showStaffObject();
	}

	public function addOtherRolesObject(){
		global $rbacreview, $lng, $rbacadmin;

		if(!$this->checkAccess("write"))
			return;

		$users = explode(',', $_POST['user_login']);
		$user_ids = array();
		foreach($users as $user)
		{
			$user_id = ilObjUser::_lookupId($user);
			if($user_id)
			{
				$user_ids[] = $user_id;
			}
		}

		$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 0;
		$arrLocalRoles = $rbacreview->getLocalRoles($_GET["ref_id"]);
		if(in_array($user_type, $arrLocalRoles)){
			foreach($user_ids as $user_id)
			$rbacadmin->assignUser($user_type, $user_id);
		}else{
			ilUtil::sendFailure($lng->txt("no_permission"));
		}

		ilUtil::sendSuccess($this->lng->txt("users_successfuly_added"), true);
		$this->showOtherRolesObject();
	}

	/**
	 * @param bool $recursive
	 * @param string $table_cmd
	 * @return string the tables html.
	 */
	public function getStaffTableHTML($recursive = false, $table_cmd = "showStaff"){
		global $lng, $rbacreview;
		$superior_table = new ilOrgUnitStaffTableGUI($this, $table_cmd, "superior");
		$superior_table->setRecursive($recursive);
		$superior_table->parseData();
		$superior_table->setTitle($lng->txt("il_orgu_superior"));

		$employee_table = new ilOrgUnitStaffTableGUI($this, $table_cmd, "employee");
		$employee_table->setRecursive($recursive);
		$employee_table->parseData();
		$employee_table->setTitle($lng->txt("il_orgu_employee"));

		return $superior_table->getHTML().$employee_table->getHTML();
	}


    public function getOtherRolesTableHTML(){
        global $lng, $rbacreview;

        $arrLocalRoles = $rbacreview->getLocalRoles($_GET["ref_id"]);

        $html = "";
        foreach($arrLocalRoles as $role_id)
        {
            $ilObjRole = new ilObjRole($role_id);
            if(!preg_match("/il_orgu_/", $ilObjRole->getUntranslatedTitle()))
            {
                $other_roles_table = new ilOrgUnitOtherRolesTableGUI($this, 'other_role_'.$role_id, $role_id);
				$other_roles_table->readData();
				$html .= $other_roles_table->getHTML()."<br/>";
			}

        }

        if(!$html)
        {
            $html = $lng->txt("no_roles");
        } else {
            $this->addOtherRolesToolbar();
        }

        return $html;
    }

	protected function checkAccess($perm){
		global $ilAccess, $lng;
		if(!$ilAccess->checkAccess($perm, "", $_GET["ref_id"])){
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
			return false;
		}
		return true;
	}

	public function _goto($ref_id){
		global $ilCtrl;
		$ilCtrl->initBaseClass("ilAdministrationGUI");
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $ref_id);
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "admin_mode", "settings");
		$ilCtrl->redirectByClass(array("ilAdministrationGUI", "ilObjOrgUnitGUI"), "view");
	}

	/**
	 * @param ilTabsGUI $tabs_gui
	 * @param bool $force_activate
	 */
	protected function addInfoTab(&$tabs_gui, $force_activate){
		$tabs_gui->addTab("info_short", "Info",
			$this->ctrl->getLinkTarget(
				$this, "infoScreen")
			);
	}

	public function fromSuperiorToEmployeeObject(){
		if(!$this->checkAccess("write"))
			return;
		$this->object->deassignUserFromSuperiorRole($_GET["obj_id"]);
		$this->object->assignUsersToEmployeeRole(array($_GET["obj_id"]));
		ilUtil::sendSuccess($this->lng->txt("user_changed_successful"), true);
		$this->ctrl->redirect($this, "showStaff");
	}

	public function fromEmployeeToSuperiorObject(){
		if(!$this->checkAccess("write"))
			return;
		$this->object->deassignUserFromEmployeeRole($_GET["obj_id"]);
		$this->object->assignUsersToSuperiorRole(array($_GET["obj_id"]));
		ilUtil::sendSuccess($this->lng->txt("user_changed_successful"), true);
		$this->ctrl->redirect($this, "showStaff");
	}




    function confirmRemoveUserObject($cmd)
    {
        if(!$this->checkAccess("write"))
            return;


        switch($cmd)
        {
            case "confirmRemoveFromRole":
                $nextcmd = "removeFromRole";
                $paramname = "obj_id-role_id";
                $param = $_GET["obj_id"].'-'.$_GET["role_id"];
                break;
            case "confirmRemoveFromSuperiors":
                $nextcmd = "removeFromSuperiors";
                $paramname = "obj_id";
                $param = $_GET["obj_id"];
                break;
            case "confirmRemoveFromEmployees":
                $nextcmd = "removeFromEmployees";
                $paramname = "obj_id";
                $param = $_GET["obj_id"];
                break;
        }

        include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this,$nextcmd));
        $confirm->setHeaderText($this->lng->txt('orgu_staff_deassign'));
        $confirm->setConfirm($this->lng->txt('confirm'),$nextcmd);
        $confirm->setCancel($this->lng->txt('cancel'),'showStaff');

        $arrUser = ilObjUser::_lookupName($_GET["obj_id"]);


        $confirm->addItem($paramname,
              $param,
              $arrUser['lastname'].', '.$arrUser['firstname'].' ['.$arrUser['login'].']',
              ilUtil::getImagePath('icon_usr.png'));

        $this->tpl->setContent($confirm->getHTML());
    }

	public function removeFromSuperiorsObject(){
		if(!$this->checkAccess("write"))
			return;


		$this->object->deassignUserFromSuperiorRole($_POST["obj_id"]);
		ilUtil::sendSuccess($this->lng->txt("deassign_user_successful"), true);
		$this->ctrl->redirect($this, "showStaff");
	}

    public function removeFromEmployeesObject(){
		if(!$this->checkAccess("write"))
			return;
		$this->object->deassignUserFromEmployeeRole($_POST["obj_id"]);
		ilUtil::sendSuccess($this->lng->txt("deassign_user_successful"), true);
		$this->ctrl->redirect($this, "showStaff");
	}

    public function removeFromRoleObject(){
        if(!$this->checkAccess("write"))
            return;

        global $rbacadmin;

        $arrObjIdRolId = explode("-", $_POST["obj_id-role_id"]);
        $rbacadmin->deassignUser($arrObjIdRolId[1],$arrObjIdRolId[0]);

        ilUtil::sendSuccess($this->lng->txt("deassign_user_successful"), true);
        $this->ctrl->redirect($this, "showOtherRoles");
    }


	public function importScreenObject(){
		global $tpl;
		$form = $this->initSimpleImportForm("startImport");
		$tpl->setContent($form->getHTML());
	}

	public function userImportScreenObject(){
		global $tpl;
		$form = $this->initSimpleImportForm("startUserImport");
		$tpl->setContent($form->getHTML());
	}

	protected  function initSimpleImportForm($submit_action){
		$form = new ilPropertyFormGUI();
		$input = new ilFileInputGUI($this->lng->txt("import_xml_file"), "import_file");
		$input->setRequired(true);
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton($submit_action, $this->lng->txt("import"));
		return $form;
	}

	public function startImportObject(){
		global $tpl, $lng;
		$form = $this->initSimpleImportForm("startImport");
		if(!$form->checkInput()){
			$tpl->setContent($form->getHTML());
		}else{
			$file = $form->getInput("import_file");
			$importer = new ilOrgUnitImporter();
			try{
				$importer->simpleImport($file["tmp_name"]);
			}catch(Exception $e){
				global $ilLog;
				$ilLog->wirte($e->getMessage()."\\n".$e->getTraceAsString());
				ilUtil::sendFailure($lng->txt("import_failed"), true);
				$this->ctrl->redirect($this, "render");
			}
		$this->displayImportResults($importer);
		}
	}

	public function startUserImportObject(){
		global $tpl, $lng;
		$form = $this->initSimpleImportForm("startUserImport");
		if(!$form->checkInput()){
			$tpl->setContent($form->getHTML());
		}else{
			$file = $form->getInput("import_file");
			$importer = new ilOrgUnitImporter();
			try{
				$importer->simpleUserImport($file["tmp_name"]);
			}catch(Exception $e){
				global $ilLog;
				$ilLog->wirte($e->getMessage()."\\n".$e->getTraceAsString());
				ilUtil::sendFailure($lng->txt("import_failed"), true);
				$this->ctrl->redirect($this, "render");
			}
			$this->displayImportResults($importer);
		}
	}

	/**
	 * @param $importer ilOrgUnitImporter
	 */
	public function displayImportResults($importer){
		if(!$importer->hasErrors() && !$importer->hasWarnings()){
			$stats = $importer->getStats();
			ilUtil::sendSuccess(sprintf($this->lng->txt("import_successful"), $stats["created"], $stats["updated"], $stats["deleted"]), true);
		}
		if($importer->hasWarnings()){
			$msg = $this->lng->txt("import_terminated_with_warnings").":<br>";
			foreach($importer->getWarnings() as $warning)
				$msg.= "-".$this->lng->txt($warning["lang_var"])." (import id: ".$warning["import_id"].")<br>";
			ilUtil::sendInfo($msg, true);
		}
		if($importer->hasErrors()){
			$msg = $this->lng->txt("import_terminated_with_errors").":<br>";
			foreach($importer->getErrors() as $warning)
				$msg.= "-".$this->lng->txt($warning["lang_var"])." (import id: ".$warning["import_id"].")<br>";
			ilUtil::sendFailure($msg, true);
		}
	}

	public function setContentSubTabs($cmd = ""){
		global $ilTabs, $lng, $ilAccess;
		/** @var ilTabsGUI $ilTabs */
		$ilTabs = $ilTabs;

        $cmdClass = $this->ctrl->getCmdClass();
         switch($cmdClass) {
            case 'ilobjorgunitgui':
                switch($cmd) {
                    case 'render':
                    case 'view':
                    case 'cut':
                    case '':
	                    $ilTabs->clearSubTabs();
                        parent::setContentSubTabs();
                        $ilTabs->removeSubTab("page_editor");
						$ilTabs->setTabActive("view");
						$ilTabs->setTabActive("view_content");
                        break;
                    case 'addStaff':
                    case 'showStaff':
                    case 'showOtherRoles':
                    case 'showStaffRec':
                        $ilTabs->addSubTab("show_staff",sprintf($lng->txt("local_staff"), $this->object->getTitle()), $this->ctrl->getLinkTarget($this, "showStaff"));
                        if($ilAccess->checkAccess("view_learning_progress_rec", "", $_GET["ref_id"]))
                            $ilTabs->addSubTab("show_staff_rec", sprintf($lng->txt("rec_staff"), $this->object->getTitle()), $this->ctrl->getLinkTarget($this, "showStaffRec"));

	                    if($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
	                        $ilTabs->addSubTab("show_other_roles",sprintf($lng->txt("local_other_roles"), $this->object->getTitle()), $this->ctrl->getLinkTarget($this, "showOtherRoles"));

	                    if($cmd == 'showStaff')
                        $ilTabs->activateSubTab("show_staff");
                        if($cmd == 'showOtherRoles')
                        $ilTabs->activateSubTab("show_other_roles");
                        if($cmd == 'showStaffRec')
                        $ilTabs->activateSubTab("show_staff_rec");
                    break;
                    case 'editExtId':
                    case 'updateExtId':
                    case 'editTranslations':
                    case 'saveTranslations':
                    case 'addTranslation':
                    case 'deleteTranslations':
                        $ilTabs->addSubTab("edit_translations", $this->lng->txt("edit_translations"), $this->ctrl->getLinkTarget($this, "editTranslations"));
                        $ilTabs->addSubTab("edit_ext_id", $this->lng->txt("edit_ext_id"), $this->ctrl->getLinkTarget($this, "editExtId"));
                        if($cmd == 'editExtId' || $cmd == "updateExtId")
                            $ilTabs->setSubTabActive("edit_ext_id");
                        if($cmd == 'editTranslations' || $cmd == "saveTranslations" || $cmd == "addTranslation" || $cmd == "deleteTranslations")
                            $ilTabs->activateSubTab("edit_translations");
                    break;
                    default:
                        break;
                }
            break;
        }


	}


    protected function initCreateForm($a_new_type)
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($a_new_type."_new"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "title");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form->addCommandButton("save", $this->lng->txt($a_new_type."_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }




    public function showMoveIntoObjectTreeObject()
    {
        require_once("./Services/Tree/classes/class.ilTree.php");
        require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");

        $this->ctrl->setCmd('performPaste');

        $tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
        $tree->setTypeWhiteList(array("orgu"));
        if(!$tree->handleCommand())
        {
            global $tpl;
            $tpl->setContent($tree->getHTML());
        }
    }


	public function getAdminTabs(&$tabs_gui){
		$this->getTabs($tabs_gui);
	}

    /*
     * performPasteObject
     *
     * Prepare $_POST for the generic method performPasteIntoMultipleObjectsObject
     *
     */
    public function performPasteObject()
    {
        global $rbacsystem, $rbacadmin, $rbacreview, $log, $tree, $ilObjDataCache, $ilUser;

        if(!in_array($_SESSION['clipboard']['cmd'], array('cut')))
        {
            $message = __METHOD__.": cmd was not 'cut' ; may be a hack attempt!";
            $this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
        }

        if($_SESSION['clipboard']['cmd'] == 'cut')
        {
            if(isset($_GET['target_node']) && (int)$_GET['target_node'])
            {
                $_POST['nodes'] = array($_GET['target_node']);
                $this->performPasteIntoMultipleObjectsObject();
            }

        }

        $this->ctrl->returnToParent($this);
    }

	function doUserAutoCompleteObject(){

	}
}