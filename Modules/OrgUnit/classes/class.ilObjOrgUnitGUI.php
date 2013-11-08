<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
require_once("./Modules/OrgUnit/classes/Staff/class.ilOrgUnitStaffGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("./Modules/OrgUnit/classes/LocalUser/class.ilLocalUserGUI.php");
require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
require_once("./Modules/OrgUnit/classes/Translation/class.ilTranslationGUI.php");
require_once("./Services/User/classes/class.ilUserAccountSettings.php");
require_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");
require_once("./Services/User/classes/class.ilObjUserFolderGUI.php");
require_once("class.ilOrgUnitExportGUI.php");
require_once("class.ilObjOrgUnitAccess.php");
/**
 * Class ilObjOrgUnit GUI class
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 * Date: 4/07/13
 * Time: 1:09 PM
 *
 * @ilCtrl_IsCalledBy ilObjOrgUnitGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI, ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilInfoScreenGUI, ilObjStyleSheetGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI, ilDidacticTemplateGUI, illearningprogressgui
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilTranslationGUI, ilLocalUserGUI, ilOrgUnitExportGUI, ilOrgUnitStaffGUI
 */
class ilObjOrgUnitGUI extends ilContainerGUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	public $tpl;
	/**
	 * @var ilTabsGUI
	 */
	public $tabs_gui;
	/**
	 * @var ilAccessHandler
	 */
	protected $ilAccess;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;
	/**
	 * @var ilLocatorGUI
	 */
	protected $ilLocator;
	/**
	 * @var ilTree
	 */
	public $tree;
	/**
	 * @var ilOrgUnit
	 */
	public $object;


	function __construct() {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng;
		parent::ilContainerGUI(array(), $_GET["ref_id"], true, false);

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;

		$lng->loadLanguageModule("orgu");
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		parent::prepareOutput();
		$this->showTree();

		switch ($next_class) {
			case "illocalusergui":
				$this->tabs_gui->setTabActive('administrate_users');
				$ilLocalUserGUI = new ilLocalUserGUI($this);
				$this->ctrl->forwardCommand($ilLocalUserGUI);
				break;
			case "ilorgunitstaffgui":
			case "ilrepositorysearchgui":
				$this->tabs_gui->setTabActive('orgu_staff');
				$ilOrgUnitStaffGUI = new ilOrgUnitStaffGUI($this);
				$this->ctrl->forwardCommand($ilOrgUnitStaffGUI);
				break;
			case "ilobjusergui":
				switch ($cmd) {
					case "create":
						$ilObjUserGUI = new ilObjUserGUI("", (int)$_GET['ref_id'], true, false);
						$ilObjUserGUI->setCreationMode(true);
						$ret = $this->ctrl->forwardCommand($ilObjUserGUI);
						break;
					case "view":
					case "update":
						$ilObjUserGUI = new ilObjUserGUI("", (int)$_GET['obj_id'], false, false);
						$ilObjUserGUI->setCreationMode(false);
						$this->ctrl->forwardCommand($ilObjUserGUI);
						break;
					case "cancel":
						$this->ctrl->redirectByClass("illocalusergui","index");
						break;
				}
				break;
			case "ilobjuserfoldergui":
				$this->tabs_gui->setTabActive('administrate_users');
				$ilObjUserFolderGUI = new ilObjUserFolderGUI("", (int)$_GET['ref_id'], true, false);
				$ilObjUserFolderGUI->setUserOwnerId((int)$_GET['ref_id']);
				$ilObjUserFolderGUI->setCreationMode(true);
				$this->ctrl->forwardCommand($ilObjUserFolderGUI);
				break;
			case "ilinfoscreengui":
				$this->tabs_gui->setTabActive("info_short");
				if (!$this->ilAccess->checkAccess("visible", "", $this->ref_id)) {
					$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
				}
				$info = new ilInfoScreenGUI($this);
				$this->ctrl->forwardCommand($info);
				break;
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				$ilPermissionGUI = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($ilPermissionGUI);
				break;
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case 'illearningprogressgui':
			case 'illplistofprogressgui':
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt('backto_lua'), $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", 'showStaff'));
				if (!ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->object->getRefid(),$_GET['obj_id'])) {
					ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
					$this->ctrl->redirectByClass("ilOrgUnitStaffGUI", "showStaff");
				}
				$this->ctrl->saveParameterByClass("illearningprogressgui", "obj_id");
				$this->ctrl->saveParameterByClass("illearningprogressgui", "recursive");
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_USER_FOLDER, USER_FOLDER_ID, $_GET["obj_id"]);
				$this->ctrl->forwardCommand($new_gui);
				break;
			case 'ilorgunitexportgui':
				$this->tabs_gui->setTabActive('export');;
				$ilOrgUnitExportGUI = new ilOrgUnitExportGUI($this);
				$ilOrgUnitExportGUI->addFormat('xml');
				$this->ctrl->forwardCommand($ilOrgUnitExportGUI);
				break;
			case 'iltranslationgui':
				$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("edit_translations"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));
				$this->tabs_gui->addSubTab("edit_ext_id", $this->lng->txt("edit_ext_id"), $this->ctrl->getLinkTarget($this, "editExtId"));
				$this->tabs_gui->setTabActive("settings");
				$this->tabs_gui->setSubTabActive("edit_translations");

				$ilTranslationGui = new ilTranslationGUI($this);
				$this->ctrl->forwardCommand($ilTranslationGui);
				break;
			default:
				switch ($cmd) {
					case '':
					case 'view':
					case 'render':
						$this->view();
					break;
					case 'importScreen':
					case 'userImportScreen':
						$this->tabs_gui->setTabActive("view_content");
						$this->$cmd();
					break;
					case 'create':
						parent::createObject();
						break;
					case 'save':
						parent::saveObject();
						break;
					case 'delete':
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					case 'cut':
						parent::cutObject();
						break;
					case 'clear':
						parent::clearObject();
						break;
					case 'editExtId':
					case 'updateExtId':
						$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("edit_translations"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));
						$this->tabs_gui->addSubTab("edit_ext_id", $this->lng->txt("edit_ext_id"), $this->ctrl->getLinkTarget($this, "editExtId"));
						$this->tabs_gui->setTabActive("settings");
						$this->tabs_gui->setSubTabActive("edit_ext_id");
						$this->$cmd();
						break;
					case 'enableAdministrationPanel':
						parent::enableAdministrationPanelObject();
						break;
					case 'disableAdministrationPanel':
						parent::disableAdministrationPanelObject();
						break;
					default:
						//$cmd .= "Object";
						$this->checkPermission("read");
						$this->$cmd();
						break;
				}
				break;
		}

	}

	public function editExtId() {
		$this->checkPermission("read");
		$form = $this->initEditExtIdForm();
		$this->tpl->setContent($form->getHTML());
	}


	public function updateExtId() {
		global $tpl;
		$form = $this->initEditExtIdForm();
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$this->object->setImportId($form->getItemByPostVar("ext_id")->getValue());
			$this->object->update();
			ilUtil::sendSuccess($this->lng->txt("ext_id_updated"), true);
			$this->ctrl->redirect($this,"editExtId");
		} else {
			$tpl->setContent($form->getHTML());
		}
	}


	public function initEditExtIdForm() {
		$form = new ilPropertyFormGUI();
		$input = new ilTextInputGUI($this->lng->txt("ext_id"), "ext_id");
		$input->setValue($this->object->getImportId());
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("updateExtId", $this->lng->txt("save"));

		return $form;
	}


	public function view() {
		parent::renderObject();
		$this->tabs_gui->setTabActive("view_content");
		$this->tabs_gui->removeSubTab("page_editor");
		if ($this->ilAccess->checkAccess("write", "", $_GET["ref_id"]) AND $this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
			$this->toolbar->addButton($this->lng->txt("simple_import"), $this->ctrl->getLinkTarget($this, "importScreen"));
			$this->toolbar->addButton($this->lng->txt("simple_user_import"), $this->ctrl->getLinkTarget($this, "userImportScreen"));
		}
	}


	function showPossibleSubObjects() {
		include_once "Services/Object/classes/class.ilObjectAddNewItemGUI.php";
		$gui = new ilObjectAddNewItemGUI($this->object->getRefId());
		$gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
		$gui->setCreationUrl("ilias.php?ref_id=" . $_GET["ref_id"]
			. "&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
		$gui->render();
	}


	public function showTree() {
		require_once("./Services/Tree/classes/class.ilTree.php");
		require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");
		$tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
		$tree->setTypeWhiteList(array( "orgu" ));
		if (! $tree->handleCommand()) {
			global $tpl;
			$tpl->setLeftNavContent($tree->getHTML());
		}
		$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
	}


	/**
	 * called by prepare output
	 */
	function setTitleAndDescription() {
		global $rbacreview;
		# all possible create permissions
		$possible_ops_ids = $rbacreview->getOperationsByTypeAndClass('orgu', 'create');
		global $lng;
		parent::setTitleAndDescription();
		if ($this->object->getTitle() == "__OrgUnitAdministration") {
			$this->tpl->setTitle($lng->txt("objs_orgu"));
		}
		$this->tpl->setDescription($lng->txt("objs_orgu"));
	}


	protected function addAdminLocatorItems() {
		$path = $this->tree->getPathFull($_GET["ref_id"], ilObjOrgUnit::getRootOrgRefId());
		// add item for each node on path
		foreach ((array)$path as $key => $row) {
			if ($row["title"] == "__OrgUnitAdministration") {
				$row["title"] = $this->lng->txt("objs_orgu");
			}
			$this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $row["child"]);
			$this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", "view"), ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
			$this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $_GET["ref_id"]);
		}
	}


	protected function redirectToRefId($a_ref_id, $a_cmd = "") {
		$obj_type = ilObject::_lookupType($a_ref_id, true);
		if ($obj_type != "orgu") {
			parent::redirectToRefId($a_ref_id, $a_cmd);
		} else {
			$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $a_ref_id);
			$this->ctrl->redirectByClass("ilObjOrgUnitGUI", $a_cmd);
		}
	}


	public function getTabs(&$tabs_gui){
		global $ilTabs, $ilAccess, $rbacsystem, $lng;

		if ($rbacsystem->checkAccess('read', $this->ref_id)) {
			$this->tabs_gui->addTab("view_content", $lng->txt("content"), $this->ctrl->getLinkTarget($this, ""));
			$this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}

		//Tabs for OrgUnits exclusive root!
		if($this->object->getRefId() != ilObjOrgUnit::getRootOrgRefId())
		{
			if (ilObjOrgUnitAccess::_checkAccessStaff($this->object->getRefId())) {
				$this->tabs_gui->addTab("orgu_staff", $this->lng->txt("orgu_staff"), $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "showStaff"));
			}
			if ($ilAccess->checkAccess('write', '',$this->object->getRefId())) {
				$this->tabs_gui->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTargetByClass("ilTranslationGUI", "editTranslations"));
			}
			if (ilObjOrgUnitAccess::_checkAccessAdministrateUsers($this->object->getRefId())) {
				$this->tabs_gui->addTab("administrate_users", $this->lng->txt("administrate_users"), $this->ctrl->getLinkTargetByClass("ilLocalUserGUI", "index"));
			}
		}

		if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('export', $this->ctrl->getLinkTargetByClass('ilorgunitexportgui', ''), 'export', 'ilorgunitexportgui');
		}

		parent::getTabs($tabs_gui);
	}


	public function showAdministrationPanel($tpl) {
		parent::showAdministrationPanel($tpl);
		//an ugly encapsulation violation in order to remove the "verknüpfen"/"link" and copy button.
		/** @var $toolbar ilToolbarGUI */
		if (! $toolbar = $tpl->admin_panel_commands_toolbar) {
			return;
		}
		if (is_array($toolbar->items)) {
			foreach ($toolbar->items as $key => $item) {
				if ($item["cmd"] == "link" || $item["cmd"] == "copy") {
					unset($toolbar->items[$key]);
				}
			}
		}
	}


	protected function checkAccess($perm) {
		global $ilAccess, $lng;
		if (! $ilAccess->checkAccess($perm, "", $_GET["ref_id"])) {
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this, "");

			return false;
		}

		return true;
	}


	public function _goto($ref_id) {
		global $ilCtrl;
		$ilCtrl->initBaseClass("ilAdministrationGUI");
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $ref_id);
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "admin_mode", "settings");
		$ilCtrl->redirectByClass(array( "ilAdministrationGUI", "ilObjOrgUnitGUI" ), "view");
	}


	public function importScreen() {
		$form = $this->initSimpleImportForm("startImport");
		$this->tpl->setContent($form->getHTML());
	}


	public function userImportScreen() {
		$form = $this->initSimpleImportForm("startUserImport");
		$this->tpl->setContent($form->getHTML());
	}


	protected function initSimpleImportForm($submit_action) {
		$form = new ilPropertyFormGUI();
		$input = new ilFileInputGUI($this->lng->txt("import_xml_file"), "import_file");
		$input->setRequired(true);
		$form->addItem($input);
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton($submit_action, $this->lng->txt("import"));

		return $form;
	}


	public function startImport() {
		global $tpl, $lng;
		$form = $this->initSimpleImportForm("startImport");
		if (! $form->checkInput()) {
			$tpl->setContent($form->getHTML());
		} else {
			$file = $form->getInput("import_file");
			$importer = new ilOrgUnitImporter();
			try {
				$importer->simpleImport($file["tmp_name"]);
			} catch (Exception $e) {
				global $ilLog;
				$ilLog->wirte($e->getMessage() . "\\n" . $e->getTraceAsString());
				ilUtil::sendFailure($lng->txt("import_failed"), true);
				$this->ctrl->redirect($this, "render");
			}
			$this->displayImportResults($importer);
		}
	}


	public function startUserImport() {
		global $tpl, $lng;
		$form = $this->initSimpleImportForm("startUserImport");
		if (! $form->checkInput()) {
			$tpl->setContent($form->getHTML());
		} else {
			$file = $form->getInput("import_file");
			$importer = new ilOrgUnitImporter();
			try {
				$importer->simpleUserImport($file["tmp_name"]);
			} catch (Exception $e) {
				global $ilLog;
				$ilLog->wirte($e->getMessage() . "\\n" . $e->getTraceAsString());
				ilUtil::sendFailure($lng->txt("import_failed"), true);
				$this->ctrl->redirect($this, "render");
			}
			$this->displayImportResults($importer);
		}
	}


	/**
	 * @param $importer ilOrgUnitImporter
	 */
	public function displayImportResults($importer) {
		if (! $importer->hasErrors() && ! $importer->hasWarnings()) {
			$stats = $importer->getStats();
			ilUtil::sendSuccess(sprintf($this->lng->txt("import_successful"), $stats["created"], $stats["updated"], $stats["deleted"]), true);
		}
		if ($importer->hasWarnings()) {
			$msg = $this->lng->txt("import_terminated_with_warnings") . ":<br>";
			foreach ($importer->getWarnings() as $warning) {
				$msg .= "-" . $this->lng->txt($warning["lang_var"]) . " (import id: " . $warning["import_id"] . ")<br>";
			}
			ilUtil::sendInfo($msg, true);
		}
		if ($importer->hasErrors()) {
			$msg = $this->lng->txt("import_terminated_with_errors") . ":<br>";
			foreach ($importer->getErrors() as $warning) {
				$msg .= "-" . $this->lng->txt($warning["lang_var"]) . " (import id: " . $warning["import_id"] . ")<br>";
			}
			ilUtil::sendFailure($msg, true);
		}
	}


	public function showMoveIntoObjectTreeObject() {
		require_once("./Services/Tree/classes/class.ilTree.php");
		require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");
		$this->ctrl->setCmd('performPaste');
		$ilOrgUnitExplorerGUI = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
		$ilOrgUnitExplorerGUI->setTypeWhiteList(array( "orgu" ));
		if (!$ilOrgUnitExplorerGUI->handleCommand()) {
			$this->tpl->setContent($ilOrgUnitExplorerGUI->getHTML());
		}
	}

	public function getAdminTabs(&$tabs_gui) {
		$this->getTabs($tabs_gui);
	}

	/*
	 * performPasteObject
	 *
	 * Prepare $_POST for the generic method performPasteIntoMultipleObjectsObject
	 *
	 */
	public function performPaste() {
		global $rbacsystem, $rbacadmin, $rbacreview, $log, $tree, $ilObjDataCache, $ilUser;
		if (! in_array($_SESSION['clipboard']['cmd'], array( 'cut' ))) {
			$message = __METHOD__ . ": cmd was not 'cut' ; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}
		if ($_SESSION['clipboard']['cmd'] == 'cut') {
			if (isset($_GET['target_node']) && (int)$_GET['target_node']) {
				$_POST['nodes'] = array( $_GET['target_node'] );
				$this->performPasteIntoMultipleObjectsObject();
			}
		}
		$this->ctrl->returnToParent($this);
	}


	function doUserAutoCompleteObject() {
	}



	/*
	 * METHODS for local user administration
	 */
	//Make protected function avaiable for ilLocalUserGUI...
	public function __initTableGUI() {
		return parent::__initTableGUI();
	}
	//Make protected function avaiable for ilLocalUserGUI
	public function __setTableGUIBasicData($tbl, $a_result_set, $a_from, $a_form) {
		return parent::__setTableGUIBasicData($tbl, $a_result_set, $a_from, $a_form);
	}
}