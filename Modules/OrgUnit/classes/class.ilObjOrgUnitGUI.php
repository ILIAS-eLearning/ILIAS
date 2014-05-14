<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
require_once("./Services/User/classes/class.ilUserAccountSettings.php");
require_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");
require_once("./Services/User/classes/class.ilObjUserFolderGUI.php");
require_once("./Services/Tree/classes/class.ilTree.php");
require_once("./Modules/OrgUnit/classes/Staff/class.ilOrgUnitStaffGUI.php");
require_once("./Modules/OrgUnit/classes/LocalUser/class.ilLocalUserGUI.php");
require_once("./Modules/OrgUnit/classes/Translation/class.ilTranslationGUI.php");
require_once("./Modules/OrgUnit/classes/ExtId/class.ilExtIdGUI.php");
require_once("./Modules/OrgUnit/classes/SimpleImport/class.ilOrgUnitSimpleImportGUI.php");
require_once("./Modules/OrgUnit/classes/SimpleUserImport/class.ilOrgUnitSimpleUserImportGUI.php");
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitImporter.php");
require_once("./Services/Object/classes/class.ilObjectAddNewItemGUI.php");
require_once("class.ilOrgUnitExplorerGUI.php");
require_once("class.ilOrgUnitExportGUI.php");
require_once("class.ilObjOrgUnitAccess.php");
require_once("class.ilObjOrgUnitTree.php");
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
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilTranslationGUI, ilLocalUserGUI, ilOrgUnitExportGUI, ilOrgUnitStaffGUI, ilExtIdGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitSimpleImportGUI, ilOrgUnitSimpleUserImportGUI
 */
class ilObjOrgUnitGUI extends ilContainerGUI {

	/**
	 * @var ilCtrl
	 */
	public $ctrl;
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
	/**
	 * @var ilLog
	 */
	protected $ilLog;


	public function __construct() {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog;
		parent::ilContainerGUI(array(), $_GET["ref_id"], true, false);

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;

		$lng->loadLanguageModule("orgu");
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		parent::prepareOutput();

        //Otherwise move-Objects would not work
        if($cmd != "cut")
        {
            $this->showTree();
        }


		switch ($next_class) {
			case "illocalusergui":
				$this->tabs_gui->setTabActive('administrate_users');
				$ilLocalUserGUI = new ilLocalUserGUI($this);
				$this->ctrl->forwardCommand($ilLocalUserGUI);
				break;
			case "ilextidgui":
				$this->tabs_gui->setTabActive("settings");
				$this->setSubTabsSettings();
				$ilExtIdGUI = new ilExtIdGUI($this);
				$this->ctrl->forwardCommand($ilExtIdGUI);
				break;
			case "ilorgunitsimpleimportgui":
				$this->tabs_gui->setTabActive("view_content");
				$ilOrgUnitSimpleImportGUI = new ilOrgUnitSimpleImportGUI($this);
				$this->ctrl->forwardCommand($ilOrgUnitSimpleImportGUI);
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTarget($this));
				break;
			case "ilorgunitsimpleuserimportgui":
				$this->tabs_gui->setTabActive("view_content");
				$ilOrgUnitSimpleUserImportGUI = new ilOrgUnitSimpleUserImportGUI($this);
				$this->ctrl->forwardCommand($ilOrgUnitSimpleUserImportGUI);
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTarget($this));
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
						$this->ctrl->forwardCommand($ilObjUserGUI);
						$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "save":
						$ilObjUserGUI = new ilObjUserGUI("",$_GET['ref_id'],true, false);
						$ilObjUserGUI->setCreationMode(true);
						$this->ctrl->forwardCommand($ilObjUserGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "view":
					case "update":
						$ilObjUserGUI = new ilObjUserGUI("", (int)$_GET['obj_id'], false, false);
						$ilObjUserGUI->setCreationMode(false);
						$this->ctrl->forwardCommand($ilObjUserGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "cancel":
						$this->ctrl->redirectByClass("illocalusergui","index");
						break;
				}
				break;
			case "ilobjuserfoldergui":
				switch ($cmd) {
					case "view":
						$this->ctrl->redirectByClass("illocalusergui","index");
						break;
					default:
						$ilObjUserFolderGUI = new ilObjUserFolderGUI("", (int)$_GET['ref_id'], true, false);
						$ilObjUserFolderGUI->setUserOwnerId((int)$_GET['ref_id']);
						$ilObjUserFolderGUI->setCreationMode(true);
						$this->ctrl->forwardCommand($ilObjUserFolderGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
				}
				break;
			case "ilinfoscreengui":
				$this->tabs_gui->setTabActive("info_short");
				if (!$this->ilAccess->checkAccess("read", "", $this->ref_id) AND !$this->ilAccess->checkAccess("visible", "", $this->ref_id)) {
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
				$this->tabs_gui->setBackTarget($this->lng->txt('backto_staff'), $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", 'showStaff'));
				if (!ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->object->getRefid(),$_GET['obj_id'])) {
					ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
					$this->ctrl->redirectByClass("ilOrgUnitStaffGUI", "showStaff");
				}
				$this->ctrl->saveParameterByClass("illearningprogressgui", "obj_id");
				$this->ctrl->saveParameterByClass("illearningprogressgui", "recursive");
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_ORG_UNIT, $_GET["ref_id"], $_GET['obj_id']);
				$this->ctrl->forwardCommand($new_gui);
				break;
			case 'ilorgunitexportgui':
				$this->tabs_gui->setTabActive('export');;
				$ilOrgUnitExportGUI = new ilOrgUnitExportGUI($this);
				$ilOrgUnitExportGUI->addFormat('xml');
				$this->ctrl->forwardCommand($ilOrgUnitExportGUI);
				break;
			case 'iltranslationgui':
				$this->tabs_gui->setTabActive("settings");
				$this->setSubTabsSettings();

				$ilTranslationGui = new ilTranslationGUI($this);
				$this->ctrl->forwardCommand($ilTranslationGui);
				break;
			default:
				switch ($cmd) {
					case '':
					case 'view':
					case 'render':
					case 'cancel':
						$this->view();
					break;
                    case 'performPaste':
                        $this->performPaste();
                        break;
					case 'create':
						parent::createObject();
						break;
					case 'save':
						parent::saveObject();
						break;
					case 'delete':
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTarget($this));
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					case 'cut':
                        $this->tabs_gui->clearTargets();
                        $this->tabs_gui->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTarget($this));
                        parent::cutObject();
						break;
					case 'clear':
						parent::clearObject();
						break;
					case 'enableAdministrationPanel':
						parent::enableAdministrationPanelObject();
						break;
					case 'disableAdministrationPanel':
						parent::disableAdministrationPanelObject();
						break;
                    case 'getAsynchItemList':
                        parent::getAsynchItemListObject();
                        break;
				}
				break;
		}

	}


	public function view() {

		if (!$this->ilAccess->checkAccess("read", "",  $_GET["ref_id"])) {
			if($this->ilAccess->checkAccess("visible", "",  $_GET["ref_id"])) {
				ilUtil::sendFailure($this->lng->txt("msg_no_perm_read"));
				$this->ctrl->redirectByClass('ilinfoscreengui', '');
			}

			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->WARNING);
		}

		parent::renderObject();
		$this->tabs_gui->setTabActive("view_content");
		$this->tabs_gui->removeSubTab("page_editor");
		if ($this->ilAccess->checkAccess("write", "", $_GET["ref_id"]) AND $this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
			$this->toolbar->addButton($this->lng->txt("simple_import"), $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleImportGUI", "importScreen"));
			$this->toolbar->addButton($this->lng->txt("simple_user_import"), $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleUserImportGUI", "userImportScreen"));
		}
	}


	/**
	 * initCreationForms
	 *
	 * We override the method of class.ilObjectGUI because we have no copy functionality
	 * at the moment
	 *
	 * @param string $a_new_type
	 *
	 * @return array
	 */
	protected function initCreationForms($a_new_type)
	{
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type),
		);

		return $forms;
	}


	public function showPossibleSubObjects() {
		$gui = new ilObjectAddNewItemGUI($this->object->getRefId());
		$gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
		$gui->setCreationUrl("ilias.php?ref_id=" . $_GET["ref_id"]
			. "&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
		$gui->render();
	}


	public function showTree() {
		$tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
		$tree->setTypeWhiteList(array( "orgu" ));
		if (! $tree->handleCommand()) {
			$this->tpl->setLeftNavContent($tree->getHTML());
		}
		$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
	}


	/**
	 * called by prepare output
	 */
	public function setTitleAndDescription() {
		# all possible create permissions
		//$possible_ops_ids = $rbacreview->getOperationsByTypeAndClass('orgu', 'create');
		parent::setTitleAndDescription();
		if ($this->object->getTitle() == "__OrgUnitAdministration") {
			$this->tpl->setTitle($this->lng->txt("objs_orgu"));
		}
		$this->tpl->setDescription($this->lng->txt("objs_orgu"));
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
		if ($this->ilAccess->checkAccess('read', '',$this->object->getRefId())) {
			$this->tabs_gui->addTab("view_content", $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, ""));
			$this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}

		//Tabs for OrgUnits exclusive root!
		if($this->object->getRefId() != ilObjOrgUnit::getRootOrgRefId())
		{
			if (ilObjOrgUnitAccess::_checkAccessStaff($this->object->getRefId())) {
				$this->tabs_gui->addTab("orgu_staff", $this->lng->txt("orgu_staff"), $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "showStaff"));
			}
			if ($this->ilAccess->checkAccess('write', '',$this->object->getRefId())) {
				$this->tabs_gui->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTargetByClass("ilTranslationGUI", "editTranslations"));
			}
			if (ilObjOrgUnitAccess::_checkAccessAdministrateUsers($this->object->getRefId())) {
				$this->tabs_gui->addTab("administrate_users", $this->lng->txt("administrate_users"), $this->ctrl->getLinkTargetByClass("ilLocalUserGUI", "index"));
			}
		}

		if ($this->ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('export', $this->ctrl->getLinkTargetByClass('ilorgunitexportgui', ''), 'export', 'ilorgunitexportgui');
		}

		parent::getTabs($tabs_gui);
	}

	private function setSubTabsSettings()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("edit_translations"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));
		$this->tabs_gui->addSubTab("edit_ext_id", $this->lng->txt("edit_ext_id"), $this->ctrl->getLinkTargetByClass("ilextidgui", "edit"));

		switch ($next_class) {
			case 'iltranslationgui':
				$this->tabs_gui->setSubTabActive("edit_translations");
				break;
			case 'ilextidgui':
				$this->tabs_gui->setSubTabActive("edit_ext_id");
				break;
		}
		return;
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

	public function _goto($ref_id) {
        global $ilCtrl;
        $ilCtrl->initBaseClass("ilAdministrationGUI");
        $ilCtrl->setTargetScript("ilias.php");
        $ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $ref_id);
        $ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "admin_mode", "settings");
        $ilCtrl->redirectByClass(array( "ilAdministrationGUI", "ilObjOrgUnitGUI" ), "view");
	}

	public function showPasteTreeObject() {

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

	/**
	 * @description Prepare $_POST for the generic method performPasteIntoMultipleObjectsObject
	 */
	public function performPaste() {
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

	//
	// METHODS for local user administration.
	//
	/**
	 * @return ilTableGUI
	 * @description Make protected function avaiable for ilLocalUserGUI...
	 */
	public function __initTableGUI() {
		return parent::__initTableGUI();
	}

	/**
	 * @return ilTableGUI
	 * @description Make protected function avaiable for ilLocalUserGUI...
	 */
	public function __setTableGUIBasicData($tbl, $a_result_set, $a_from, $a_form) {
		return parent::__setTableGUIBasicData($tbl, $a_result_set, $a_from, $a_form);
	}
}
?>