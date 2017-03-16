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
require_once(dirname(__FILE__) . '/Types/class.ilOrgUnitTypeGUI.php');
require_once(dirname(__FILE__) . '/Settings/class.ilObjOrgUnitSettingsFormGUI.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
require_once('./Services/Container/classes/class.ilContainerByTypeContentGUI.php');
require_once("./Modules/OrgUnit/classes/Extension/class.ilOrgUnitExtension.php");

/**
 * Class ilObjOrgUnit GUI class
 *
 * @author            : Oskar Truffer <ot@studer-raimann.ch>
 * @author            : Martin Studer <ms@studer-raimann.ch>
 * @author            : Stefan Wanzenried <sw@studer-raimann.ch>
 * Date: 15/04/14
 * Time: 10:13 AM
 *
 * @ilCtrl_IsCalledBy ilObjOrgUnitGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilPermissionGUI, ilPageObjectGUI, ilContainerLinkListGUI, ilObjUserGUI, ilObjUserFolderGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilInfoScreenGUI, ilObjStyleSheetGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilColumnGUI, ilObjectCopyGUI, ilUserTableGUI, ilDidacticTemplateGUI, illearningprogressgui
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilTranslationGUI, ilLocalUserGUI, ilOrgUnitExportGUI, ilOrgUnitStaffGUI, ilExtIdGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitSimpleImportGUI, ilOrgUnitSimpleUserImportGUI
 * @ilCtrl_Calls      ilObjOrgUnitGUI: ilOrgUnitTypeGUI
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
	 * @var ilObjOrgUnit
	 */
	public $object;
	/**
	 * @var ilLog
	 */
	protected $ilLog;
	/**
	 * @var Ilias
	 */
	public $ilias;


	public function __construct() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilAccess = $DIC['ilAccess'];
		$ilToolbar = $DIC['ilToolbar'];
		$ilLocator = $DIC['ilLocator'];
		$tree = $DIC['tree'];
		$lng = $DIC['lng'];
		$ilLog = $DIC['ilLog'];
		$ilias = $DIC['ilias'];
		parent::__construct(array(), $_GET["ref_id"], true, false);

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->type = 'orgu';

		$lng->loadLanguageModule("orgu");
		$this->tpl->addCss('./Modules/OrgUnit/templates/default/orgu.css');
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		parent::prepareOutput();

		//Otherwise move-Objects would not work
		if ($cmd != "cut") {
			$this->showTree();
		}

		switch ($next_class) {
			case "illocalusergui":
				if (!ilObjOrgUnitAccess::_checkAccessAdministrateUsers((int)$_GET['ref_id'])) {
					ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
					$this->ctrl->redirect($this);
				}
				$this->tabs_gui->setTabActive('administrate_users');
				$ilLocalUserGUI = new ilLocalUserGUI($this);
				$this->ctrl->forwardCommand($ilLocalUserGUI);
				break;
			case "ilorgunitsimpleimportgui":
				$this->tabs_gui->setTabActive("view");
				$this->setContentSubTabs();
				$this->tabs_gui->setSubTabActive('import');
				$ilOrgUnitSimpleImportGUI = new ilOrgUnitSimpleImportGUI($this);
				$this->ctrl->forwardCommand($ilOrgUnitSimpleImportGUI);
				break;
			case "ilorgunitsimpleuserimportgui":
				$ilOrgUnitSimpleUserImportGUI = new ilOrgUnitSimpleUserImportGUI($this);
				$this->ctrl->forwardCommand($ilOrgUnitSimpleUserImportGUI);
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
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "save":
						$ilObjUserGUI = new ilObjUserGUI("", $_GET['ref_id'], true, false);
						$ilObjUserGUI->setCreationMode(true);
						$this->ctrl->forwardCommand($ilObjUserGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "view":
					case "update":
						$ilObjUserGUI = new ilObjUserGUI("", (int)$_GET['obj_id'], false, false);
						$ilObjUserGUI->setCreationMode(false);
						$this->ctrl->forwardCommand($ilObjUserGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
					case "cancel":
						$this->ctrl->redirectByClass("illocalusergui", "index");
						break;
				}
				break;
			case "ilobjuserfoldergui":
				switch ($cmd) {
					case "view":
						$this->ctrl->redirectByClass("illocalusergui", "index");
						break;
					default:
						$ilObjUserFolderGUI = new ilObjUserFolderGUI("", (int)$_GET['ref_id'], true, false);
						$ilObjUserFolderGUI->setUserOwnerId((int)$_GET['ref_id']);
						$ilObjUserFolderGUI->setCreationMode(true);
						$this->ctrl->forwardCommand($ilObjUserFolderGUI);
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTargetByClass("illocalusergui", 'index'));
						break;
				}
				break;
			case "ilinfoscreengui":
				$this->tabs_gui->setTabActive("info_short");
				if (!$this->ilAccess->checkAccess("read", "", $this->ref_id) AND !$this->ilAccess->checkAccess("visible", "", $this->ref_id)) {
					$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->MESSAGE);
				}
				$info = new ilInfoScreenGUI($this);
				$amd_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
				$amd_gui->setInfoObject($info);
				$amd_gui->parse();
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
				if (!ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->object->getRefid(), $_GET['obj_id'])) {
					ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
					$this->ctrl->redirectByClass("ilOrgUnitStaffGUI", "showStaff");
				}
				$this->ctrl->saveParameterByClass("illearningprogressgui", "obj_id");
				$this->ctrl->saveParameterByClass("illearningprogressgui", "recursive");
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_ORG_UNIT, $_GET["ref_id"], $_GET['obj_id']);
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
				$this->setSubTabsSettings('edit_translations');

				$ilTranslationGui = new ilTranslationGUI($this);
				$this->ctrl->forwardCommand($ilTranslationGui);
				break;
			case 'ilorgunittypegui':
				$this->tabs_gui->setTabActive('orgu_types');
				$types_gui = new ilOrgUnitTypeGUI($this);
				$this->ctrl->forwardCommand($types_gui);
				break;
			default:
				switch ($cmd) {
					case '':
					case 'view':
					case 'render':
					case 'cancel':
					case 'cancelDelete':
						$this->view();
						break;
					case 'performPaste':
						$this->performPaste();
						break;
					case 'paste':
						$this->performPaste();
						break;
					case 'performPasteIntoMultipleObjects':
						$this->performPasteIntoMultipleObjectsObject();
						break;
					case 'create':
						parent::createObject();
						break;
					case 'save':
						parent::saveObject();
						break;
					case 'delete':
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					case 'cut':
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
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
					case 'editSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_settings');
						$this->editSettings();
						break;
					case 'updateSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_settings');
						$this->updateSettings();
						break;
					case 'editAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_advanced_settings');
						$this->editAdvancedSettings();
						break;
					case 'updateAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_advanced_settings');
						$this->updateAdvancedSettings();
						break;
					case 'importFile':
						$this->importFileObject();
						break;
				}
				break;
		}
	}


	public function view() {
		if (!$this->ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
			if ($this->ilAccess->checkAccess("visible", "", $_GET["ref_id"])) {
				ilUtil::sendFailure($this->lng->txt("msg_no_perm_read"));
				$this->ctrl->redirectByClass('ilinfoscreengui', '');
			}

			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
		}

		parent::renderObject();
		$this->tabs_gui->setTabActive("view_content");
		$this->tabs_gui->removeSubTab("page_editor");
		$this->tabs_gui->removeSubTab("ordering"); // Mantis 0014728

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
	protected function initCreationForms($a_new_type) {
		$forms = array(
			self::CFORM_NEW => $this->initCreateForm($a_new_type),
			self::CFORM_IMPORT => $this->initImportForm($a_new_type),
		);

		return $forms;
	}


	public function showPossibleSubObjects() {
		$gui = new ilObjectAddNewItemGUI($this->object->getRefId());
		$gui->setMode(ilObjectDefinition::MODE_ADMINISTRATION);
		$gui->setCreationUrl("ilias.php?ref_id=" . $_GET["ref_id"] . "&admin_mode=settings&cmd=create&baseClass=ilAdministrationGUI");
		$gui->render();
	}


	public function showTree() {
		$tree = new ilOrgUnitExplorerGUI("orgu_explorer", "ilObjOrgUnitGUI", "showTree", new ilTree(1));
		$tree->setTypeWhiteList(
			$this->getTreeWhiteList()
		);
		if (!$tree->handleCommand()) {
			$this->tpl->setLeftNavContent($tree->getHTML());
		}
		$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
	}

	protected function getTreeWhiteList() {
		$whiteList = array("orgu");
		$pls = ilOrgUnitExtension::getActivePluginIdsForTree();
		return array_merge($whiteList, $pls);
	}


	/**
	 * called by prepare output
	 */
	public function setTitleAndDescription() {
		# all possible create permissions
		parent::setTitleAndDescription();
		if ($this->object->getTitle() == "__OrgUnitAdministration") {
			$this->tpl->setTitle($this->lng->txt("objs_orgu"));
			$this->tpl->setDescription($this->lng->txt("objs_orgu"));
		}

		// Check for custom icon of type
		if ($this->ilias->getSetting('custom_icons')) {
			$icons_cache = ilObjOrgUnit::getIconsCache();
			$icon_file = (isset($icons_cache[$this->object->getId()])) ? $icons_cache[$this->object->getId()] : '';
			if ($icon_file) {
				$this->tpl->setTitleIcon($icon_file, $this->lng->txt("obj_" . $this->object->getType()));
			}
		}
	}


	/**
	 * @param bool $a_do_not_add_object
	 */
	protected function addAdminLocatorItems($a_do_not_add_object = false) {
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


	/**
	 * @param int    $a_ref_id
	 * @param string $a_cmd
	 */
	protected function redirectToRefId($a_ref_id, $a_cmd = "") {
		$obj_type = ilObject::_lookupType($a_ref_id, true);
		if ($obj_type != "orgu") {
			parent::redirectToRefId($a_ref_id, $a_cmd);
		} else {
			$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $a_ref_id);
			$this->ctrl->redirectByClass("ilObjOrgUnitGUI", $a_cmd);
		}
	}


	/**
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getTabs() {
		if ($this->ilAccess->checkAccess('read', '', $this->object->getRefId())) {
			$this->tabs_gui->addTab("view_content", $this->lng->txt("content"), $this->ctrl->getLinkTarget($this, ""));
			$this->tabs_gui->addTab("info_short", "Info", $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}

		// Tabs for OrgUnits exclusive root!
		if ($this->object->getRefId() != ilObjOrgUnit::getRootOrgRefId()) {
			if (ilObjOrgUnitAccess::_checkAccessStaff($this->object->getRefId())) {
				$this->tabs_gui->addTab("orgu_staff", $this->lng->txt("orgu_staff"), $this->ctrl->getLinkTargetByClass("ilOrgUnitStaffGUI", "showStaff"));
			}
			if ($this->ilAccess->checkAccess('write', '', $this->object->getRefId())) {
				$this->tabs_gui->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, 'editSettings'));
			}
			if (ilObjOrgUnitAccess::_checkAccessAdministrateUsers($this->object->getRefId())) {
				$this->tabs_gui->addTab("administrate_users", $this->lng->txt("administrate_users"), $this->ctrl->getLinkTargetByClass("ilLocalUserGUI", "index"));
			}
		}

		if ($this->ilAccess->checkAccess('write', '', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('export', $this->ctrl->getLinkTargetByClass('ilorgunitexportgui', ''), 'export', 'ilorgunitexportgui');

			// Add OrgUnit types tab
			if ($this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
				$this->tabs_gui->addTab('orgu_types', $this->lng->txt('orgu_types'), $this->ctrl->getLinkTargetByClass('ilOrgUnitTypeGUI'));
			}
		}
		parent::getTabs();
	}


	/**
	 * @param $active_tab_id
	 */
	protected function setSubTabsSettings($active_tab_id) {
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->tabs_gui->addSubTab('edit_settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, 'editSettings'));
		$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("obj_multilinguality"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));

		$ilOrgUnitType = $this->object->getOrgUnitType();
		if ($ilOrgUnitType instanceof ilOrgUnitType) {
			if (count($ilOrgUnitType->getAssignedAdvancedMDRecords(true))) {
				$this->tabs_gui->addSubTab('edit_advanced_settings', $this->lng->txt('orgu_adv_settings'), $this->ctrl->getLinkTarget($this, 'editAdvancedSettings'));
			}
		}

		$this->tabs_gui->setSubTabActive($active_tab_id);
		switch ($next_class) {
			case 'iltranslationgui':
				$this->tabs_gui->setSubTabActive("edit_translations");
				break;
			case '':
				switch ($cmd) {
					case 'editSettings':
						$this->tabs_gui->setSubTabActive('edit_settings');
						break;
					case 'editAdvancedSettings':
					case 'updateAdvancedSettings':
						$this->tabs_gui->setSubTabActive('edit_advanced_settings');
						break;
				}
				break;
		}

		return;
	}

	/**
	 * Set content sub tabs
	 */
	function setContentSubTabs()
	{
		$this->addStandardContainerSubTabs();
		//only display the import tab at the first level
		if ($this->ilAccess->checkAccess("write", "", $_GET["ref_id"]) AND $this->object->getRefId() == ilObjOrgUnit::getRootOrgRefId()) {
			$this->tabs_gui->addSubTab("import", $this->lng->txt("import"), $this->ctrl->getLinkTargetByClass("ilOrgUnitSimpleImportGUI", "chooseImport"));
		}
	}


	/**
	 * Initialize the form for editing advanced meta data
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function initAdvancedSettingsForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton('updateAdvancedSettings', $this->lng->txt('save'));
		$form->addCommandButton('editSettings', $this->lng->txt('cancel'));

		return $form;
	}


	/**
	 * Edit Advanced Metadata
	 */
	protected function editAdvancedSettings() {
		if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		$form = $this->initAdvancedSettingsForm();
		$gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
		$gui->setPropertyForm($form);
		$gui->parse();
		$this->tpl->setContent($form->getHTML());
	}


	/**
	 * Update Advanced Metadata
	 */
	protected function updateAdvancedSettings() {
		if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		$form = $this->initAdvancedSettingsForm();
		$gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
		$gui->setPropertyForm($form);
		$form->checkInput();
		$gui->parse();
		if ($gui->importEditFormPostValues()) {
			$gui->writeEditForm();
			ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
			$this->ctrl->redirect($this, 'editAdvancedSettings');
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}

	public function editSettings() {
		if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		$form = new ilObjOrgUnitSettingsFormGUI($this, $this->object);
		$this->tpl->setContent($form->getHTML());
	}


	public function updateSettings() {
		if (!$this->ilAccess->checkAccess("write", "", $this->ref_id)) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		$form = new ilObjOrgUnitSettingsFormGUI($this, $this->object);
		if ($form->saveObject()) {
			ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
			$this->ctrl->redirect($this, 'editSettings');
		} else {
			$this->tpl->setContent($form->getHTML());
		}
	}


	/**
	 * @param $tpl
	 */
	public function showAdministrationPanel(&$tpl) {
		parent::showAdministrationPanel($tpl);
		//an ugly encapsulation violation in order to remove the "verknüpfen"/"link" and copy button.
		/** @var $toolbar ilToolbarGUI */
		if (!$toolbar = $tpl->admin_panel_commands_toolbar) {
			return;
		}
		if (is_array($toolbar->items)) {
			foreach ($toolbar->items as $key => $item) {
				if ($item["cmd"] == "link" || $item["cmd"] == "copy" || $item["cmd"] == "download") {
					unset($toolbar->items[$key]);
				}
			}
		}
	}


	public static function _goto($ref_id) {
		global $DIC;
		$ilCtrl = $DIC['ilCtrl'];
		$ilCtrl->initBaseClass("ilAdministrationGUI");
		$ilCtrl->setTargetScript("ilias.php");
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $ref_id);
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "admin_mode", "settings");
		$ilCtrl->setParameterByClass("IlObjPluginDispatchGUI", "admin_mode", "settings");
		$ilCtrl->redirectByClass(array( "ilAdministrationGUI", "ilObjOrgUnitGUI" ), "view");
	}


	protected function getTreeSelectorGUI($cmd) {
		global $DIC;
		$tree = $DIC['tree'];
		$explorer = new ilOrgUnitExplorerGUI("rep_exp_sel", $this, "showPasteTree", $tree);
		$explorer->setAjax(false);
		$explorer->setSelectMode('nodes[]', false);

		return $explorer;
	}


	/**
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getAdminTabs() {
		$this->getTabs();
	}


	/**
	 * @description Prepare $_POST for the generic method performPasteIntoMultipleObjectsObject
	 */
	public function performPaste() {

		if (!in_array($_SESSION['clipboard']['cmd'], array( 'cut' ))) {
			$message = __METHOD__ . ": cmd was not 'cut' ; may be a hack attempt!";
			$this->ilias->raiseError($message, $this->ilias->error_obj->WARNING);
		}
		if ($_SESSION['clipboard']['cmd'] == 'cut') {
			if (isset($_GET['ref_id']) && (int)$_GET['ref_id']) {
				$_POST['nodes'] = array( $_GET['ref_id'] );
				$this->performPasteIntoMultipleObjectsObject();
			}
		}
		$this->ctrl->returnToParent($this);
	}


	/**
	 * ??
	 */
	function doUserAutoCompleteObject() {
	}

	//
	// METHODS for local user administration.
	//
	/**
	 * @return ilTableGUI
	 * @description Make protected function avaiable for ilLocalUserGUI...
	 */
	public function &__initTableGUI() {
		return parent::__initTableGUI();
	}


	/**
	 * @return ilTableGUI
	 * @description Make protected function avaiable for ilLocalUserGUI...
	 */
	public function __setTableGUIBasicData(&$tbl, &$result_set, $a_from = "") {
		return parent::__setTableGUIBasicData($tbl, $result_set, $a_from);
	}
}

?>