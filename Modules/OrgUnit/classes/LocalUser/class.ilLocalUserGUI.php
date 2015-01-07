<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/User/classes/class.ilUserTableGUI.php");
require_once("./Services/User/classes/class.ilLocalUser.php");
require_once("./Services/User/classes/class.ilObjUserGUI.php");
require_once("./Services/User/classes/class.ilObjUserFolderGUI.php");
/**
 * Class ilLocalUserGUI
 *
 * @author            Oskar Truffer <ot@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 */
class ilLocalUserGUI {

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs_gui;
	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var ilObjOrgUnit|ilObjCategory
	 */
	public $object;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;


	/**
	 * @param $parent_gui
	 */
	//TODO MST 14.11.2013 - we should split this class into ilLocalUserTableGUI and ilLocalUserRoleGUI
	function __construct($parent_gui) {
		global $tpl, $ilCtrl, $ilTabs, $ilToolbar, $lng, $rbacsystem, $ilAccess;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $parent_gui;
		$this->object = $parent_gui->object;
		$this->tabs_gui = $this->parent_gui->tabs_gui;
		$this->toolbar = $ilToolbar;
		$this->lng = $lng;
		$this->ilAccess = $ilAccess;
		$this->lng->loadLanguageModule('user');
		if (! $rbacsystem->checkAccess("cat_administrate_users", $this->parent_gui->object->getRefId())) {
			ilUtil::sendFailure($this->lng->txt("msg_no_perm_admin_users"), true);
		}
	}


	/**
	 * @return bool
	 */
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case "assignRoles":
			case "assignSave":
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
				$this->$cmd();
				break;
			default:
				$this->$cmd();
				break;
		}

		return true;
	}


	/**
	 * Reset filter
	 * (note: this function existed before data table filter has been introduced
	 */
	protected function resetFilter() {
		$table = new ilUserTableGUI($this, "index", ilUserTableGUI::MODE_LOCAL_USER);
		$table->resetOffset();
		$table->resetFilter();
		$this->index();
	}


	/**
	 * Apply filter
	 *
	 * @return
	 */
	protected function applyFilter() {
		$table = new ilUserTableGUI($this, "index", ilUserTableGUI::MODE_LOCAL_USER);
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->index();
	}


	function index($show_delete = false) {
		global $ilUser, $rbacreview, $rbacsystem;
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.cat_admin_users.html',
			"Modules/Category");
		if (count($rbacreview->getGlobalAssignableRoles())
			or in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))
		) {
			$this->toolbar->addButton(
				$this->lng->txt('add_user'),
				$this->ctrl->getLinkTargetByClass('ilobjusergui', 'create')
			);
			$this->toolbar->addButton(
				$this->lng->txt('import_users'),
				$this->ctrl->getLinkTargetByClass('ilobjuserfoldergui', 'importUserForm')
			);
		} else {
			ilUtil::sendInfo($this->lng->txt('no_roles_user_can_be_assigned_to'));
		}
		if ($show_delete) {
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD", 'performDeleteUsers');
			$this->tpl->setVariable("TXT_CONFIRM", $this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}
		$table = new ilUserTableGUI($this, 'index', ilUserTableGUI::MODE_LOCAL_USER);
		$this->tpl->setVariable('USERS_TABLE', $table->getHTML());

		return true;
	}


	/**
	 * Show auto complete results
	 */
	protected function addUserAutoCompleteObject() {
		include_once './Services/User/classes/class.ilUserAutoComplete.php';
		$auto = new ilUserAutoComplete();
		$auto->setSearchFields(array( 'login', 'firstname', 'lastname', 'email' ));
		$auto->enableFieldSearchableCheck(true);
		echo $auto->getList($_REQUEST['query']);
		exit();
	}


	/**
	 * Delete User
	 */
	function performDeleteUsersObject() {
		include_once './Services/User/classes/class.ilLocalUser.php';
		$this->checkPermission("cat_administrate_users");
		foreach ($_POST['user_ids'] as $user_id) {
			if (! in_array($user_id, ilLocalUser::_getAllUserIds($this->obj->getRefId()))) {
				die('user id not valid');
			}
			if (! $tmp_obj =& ilObjectFactory::getInstanceByObjId($user_id, false)) {
				continue;
			}
			$tmp_obj->delete();
		}
		ilUtil::sendSuccess($this->lng->txt('deleted_users'));
		$this->listUser();

		return true;
	}


	function deleteUsersObject() {
		$this->checkPermission("cat_administrate_users");
		if (! count($_POST['id'])) {
			ilUtil::sendFailure($this->lng->txt('no_users_selected'));
			$this->index();

			return true;
		}
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($this->lng->txt('sure_delete_selected_users'));
		$confirm->setConfirm($this->lng->txt('delete'), 'performDeleteUsers');
		$confirm->setCancel($this->lng->txt('cancel'), 'index');
		foreach ($_POST['id'] as $user) {
			$name = ilObjUser::_lookupName($user);
			$confirm->addItem(
				'user_ids[]',
				$user,
				$name['lastname'] . ', ' . $name['firstname'] . ' [' . $name['login'] . ']'
			);
		}
		$this->tpl->setContent($confirm->getHTML());
	}


	function assignRoles() {
		global $rbacreview;
		if (! $this->ilAccess->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}
		$offset = $_GET["offset"];
		// init sort_by (unfortunatly sort_by is preset with 'title'
		if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"])) {
			$_GET["sort_by"] = "login";
		}
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];
		include_once './Services/User/classes/class.ilLocalUser.php';
		if (! isset($_GET['obj_id'])) {
			ilUtil::sendFailure('no_user_selected');
			$this->index();

			return true;
		}
		$roles = $this->__getAssignableRoles();
		$this->tpl->addBlockfile('ADM_CONTENT', 'adm_content', 'tpl.cat_role_assignment.html',
			"Modules/Category");
		$ass_roles = $rbacreview->assignedRoles($_GET['obj_id']);
		$counter = 0;
		foreach ($roles as $role) {
			$role_obj =& ilObjectFactory::getInstanceByObjId($role['obj_id']);
			$disabled = false;
			$f_result[$counter][] = ilUtil::formCheckbox(in_array($role['obj_id'], $ass_roles) ? 1 : 0,
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
		$this->__showRolesTable($f_result, "assignRolesObject");
	}


	function assignSave() {
		global $rbacreview, $rbacadmin;
		if (! $this->ilAccess->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}
		include_once './Services/User/classes/class.ilLocalUser.php';
		// check hack
		if (! isset($_GET['obj_id']) or ! in_array($_REQUEST['obj_id'], ilLocalUser::_getAllUserIds())) {
			ilUtil::sendFailure('no_user_selected');
			$this->index();

			return true;
		}
		$roles = $this->__getAssignableRoles();
		// check minimum one global role
		if (! $this->__checkGlobalRoles($_POST['role_ids'])) {
			ilUtil::sendFailure($this->lng->txt('no_global_role_left'));
			$this->assignRolesObject();

			return false;
		}
		$new_role_ids = $_POST['role_ids'] ? $_POST['role_ids'] : array();
		$assigned_roles = $rbacreview->assignedRoles((int)$_REQUEST['obj_id']);
		foreach ($roles as $role) {
			if (in_array($role['obj_id'], $new_role_ids) and ! in_array($role['obj_id'], $assigned_roles)) {
				$rbacadmin->assignUser($role['obj_id'], (int)$_REQUEST['obj_id']);
			}
			if (in_array($role['obj_id'], $assigned_roles) and ! in_array($role['obj_id'], $new_role_ids)) {
				$rbacadmin->deassignUser($role['obj_id'], (int)$_REQUEST['obj_id']);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('role_assignment_updated'));
		$this->assignRoles();

		return true;
	}


	function __checkGlobalRoles($new_assigned) {
		global $rbacreview, $ilUser;
		if (! $this->ilAccess->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}
		// return true if it's not a local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		if ($tmp_obj->getTimeLimitOwner() != $this->object->getRefId() and
			! in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))
		) {
			return true;
		}
		// new assignment by form
		$new_assigned = $new_assigned ? $new_assigned : array();
		$assigned = $rbacreview->assignedRoles((int)$_GET['obj_id']);
		// all assignable globals
		if (! in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
			$ga = $rbacreview->getGlobalAssignableRoles();
		} else {
			$ga = $rbacreview->getGlobalRolesArray();
		}
		$global_assignable = array();
		foreach ($ga as $role) {
			$global_assignable[] = $role['obj_id'];
		}
		$new_visible_assigned_roles = array_intersect($new_assigned, $global_assignable);
		$all_assigned_roles = array_intersect($assigned, $rbacreview->getGlobalRoles());
		$main_assigned_roles = array_diff($all_assigned_roles, $global_assignable);
		if (! count($new_visible_assigned_roles) and ! count($main_assigned_roles)) {
			return false;
		}

		return true;
	}


	function __getAssignableRoles() {
		global $rbacreview, $ilUser;
		// check local user
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_REQUEST['obj_id']);
		// Admin => all roles
		if (in_array(SYSTEM_ROLE_ID, $rbacreview->assignedRoles($ilUser->getId()))) {
			$global_roles = $rbacreview->getGlobalRolesArray();
		} elseif ($tmp_obj->getTimeLimitOwner() == $this->object->getRefId()) {
			$global_roles = $rbacreview->getGlobalAssignableRoles();
		} else {
			$global_roles = array();
		}

		return $roles = array_merge($global_roles, $rbacreview->getAssignableChildRoles($this->object->getRefId()));
	}


	function __showRolesTable($a_result_set, $a_from = "") {
		if (! $this->ilAccess->checkAccess("cat_administrate_users", "", $_GET["ref_id"])) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");
		}
		$tbl =& $this->parent_gui->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$this->ctrl->setParameter($this, 'obj_id', $_GET['obj_id']);
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		// SET FOOTER BUTTONS
		$tpl->setVariable("COLUMN_COUNTS", 4);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
		$tpl->setCurrentBlock("tbl_action_button");
		$tpl->setVariable("BTN_NAME", "assignSave");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("change_assignment"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH", $this->tpl->tplPath);
		$tpl->parseCurrentBlock();
		$tmp_obj =& ilObjectFactory::getInstanceByObjId($_GET['obj_id']);
		$title = $this->lng->txt('role_assignment') . ' (' . $tmp_obj->getFullname() . ')';
		$tbl->setTitle($title, "icon_role.svg", $this->lng->txt("role_assignment"));
		$tbl->setHeaderNames(array(
			'',
			$this->lng->txt("title"),
			$this->lng->txt('description'),
			$this->lng->txt("type")
		));
		$tbl->setHeaderVars(array(
			"",
			"title",
			"description",
			"type"
		), array(
			"ref_id" => $this->object->getRefId(),
			"cmd" => "assignRoles",
			"obj_id" => $_GET['obj_id'],
			"cmdClass" => "ilobjcategorygui",
			"cmdNode" => $_GET["cmdNode"]
		));
		$tbl->setColumnWidth(array( "4%", "35%", "45%", "16%" ));
		$this->set_unlimited = true;
		$this->parent_gui->__setTableGUIBasicData($tbl, $a_result_set, $a_from, true);
		$tbl->render();
		$this->tpl->setVariable("ROLES_TABLE", $tbl->tpl->get());

		return true;
	}
}

