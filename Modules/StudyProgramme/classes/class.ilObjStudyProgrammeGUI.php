<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Container/classes/class.ilContainerGUI.php");
require_once("./Services/AccessControl/classes/class.ilObjRole.php");
require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");
require_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
require_once("./Services/Object/classes/class.ilObjectAddNewItemGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeTreeGUI.php");
require_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
require_once("./Modules/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php");
require_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php");
require_once("./Services/Object/classes/class.ilObjectCopyGUI.php");
require_once("./Services/Repository/classes/class.ilRepUtil.php");

/**
 * Class ilObjStudyProgrammeGUI class
 *
 * @author				Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilColumnGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeSettingsGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeTreeGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjStudyProgrammeMembersGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilObjectTranslationGUI
 * @ilCtrl_Calls ilObjStudyProgrammeGUI: ilCertificateGUI
 */

class ilObjStudyProgrammeGUI extends ilContainerGUI {
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

	/**
	 * @var ilHelp
	 */
	protected $help;

	/**
	 * @var ilObjStudyProgrammeSettingsGUI
	 */
	protected $settings_gui;

	/**
	 * @var ilObjStudyProgrammeMembersGUI
	 */
	protected $members_gui;

	/**
	 * @var ilObjStudyProgrammeTreeGUI
	 */
	protected $tree_gui;

	/**
	 * @var ilStudyProgrammeTypeGUI
	 */
	protected $type_gui;

	/**
	 * @var ilStudyProgrammeTypeRepository
	 */
	protected $type_repository;


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
		$ilHelp = $DIC['ilHelp'];
		$ilUser = $DIC['ilUser'];

		parent::__construct(array(), (int) $_GET['ref_id'], true, false);

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->ilAccess = $ilAccess;
		$this->ilLocator = $ilLocator;
		$this->tree = $tree;
		$this->toolbar = $ilToolbar;
		$this->ilLog = $ilLog;
		$this->ilias = $ilias;
		$this->type = "prg";
		$this->help = $ilHelp;
		$this->user = $ilUser;

		$lng->loadLanguageModule("prg");

		$this->settings_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeSettingsGUI'];
		$this->members_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeMembersGUI'];
		$this->tree_gui = ilStudyProgrammeDIC::dic()['ilObjStudyProgrammeTreeGUI'];
		$this->type_gui = ilStudyProgrammeDIC::dic()['ilStudyProgrammeTypeGUI'];

		$this->type_repository = ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository'];
	}


	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		if ($cmd == "") {
			$cmd = "view";
		}

		$this->addToNavigationHistory();

		parent::prepareOutput();

		// show repository tree
		$this->showRepTree();
		$this->addHeaderAction();
		switch ($next_class) {
			case "ilinfoscreengui":
				$this->tabs_gui->setTabActive(self::TAB_INFO);
				$this->denyAccessIfNotAnyOf(array("read", "visible"));
				$info = new ilInfoScreenGUI($this);
				$this->fillInfoScreen($info);
				$this->ctrl->forwardCommand($info);

				// I guess this is how it was supposed to work, but it doesn't... it won't respect our sub-id and sub-type when creating the objects!
				// So we reimplemented the stuff in the method parseInfoScreen()
				//                $info = new ilInfoScreenGUI($this);
				//                $amd_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'orgu', $this->object->getId(), 'orgu_type', $this->object->getOrgUnitTypeId());
				//                $amd_gui->setInfoObject($info);
				//                $amd_gui->setSelectedOnly(true);
				//                $amd_gui->parse();
				//                $this->ctrl->forwardCommand($info);
				break;
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				$ilPermissionGUI = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($ilPermissionGUI);
				break;
			case "ilcommonactiondispatchergui":
				require_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilobjstudyprogrammesettingsgui":
				$this->denyAccessIfNot("write");

				$this->getSubTabs('settings');
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				$this->tabs_gui->setSubTabActive('settings');

				$this->settings_gui->setParentGUI($this);
				$this->settings_gui->setRefId($this->ref_id);
				$this->ctrl->forwardCommand($this->settings_gui);
				break;
			/*case 'iltranslationgui':
				$this->denyAccessIfNot("write");

				$this->getSubTabs('settings');
				$this->tabs_gui->setTabActive("settings");
				$this->tabs_gui->setSubTabActive('edit_translations');

				$ilTranslationGui = new ilTranslationGUI($this);
				$this->ctrl->forwardCommand($ilTranslationGui);
				break;*/
			case "ilobjstudyprogrammemembersgui":
				$this->denyAccessIfNot("manage_members");
				$this->tabs_gui->setTabActive(self::TAB_MEMBERS);
				$this->members_gui->setParentGUI($this);
				$this->members_gui->setRefId($this->ref_id);
				$this->ctrl->forwardCommand($this->members_gui);
				break;
			case "ilobjstudyprogrammetreegui":
				$this->denyAccessIfNot("write");

				$this->getSubTabs($cmd);
				$this->setContentSubTabs();
				$this->tabs_gui->setTabActive(self::TAB_VIEW_CONTENT);
				$this->tabs_gui->setSubTabActive(self::SUBTAB_VIEW_TREE);

				// disable admin panel
				$_SESSION["il_cont_admin_panel"] = false;

				$this->tree_gui->setRefId($this->id);
				$this->ctrl->forwardCommand($this->tree_gui);
				break;
			case 'ilstudyprogrammetypegui':
				$this->tabs_gui->setTabActive('subtypes');

				$this->type_gui->setParentGUI($this);
				$this->ctrl->forwardCommand($this->type_gui);
				break;
			case 'ilobjectcopygui':
				$gui = new ilobjectcopygui($this);
				$this->ctrl->forwardCommand($gui);
				break;
			case 'ilobjecttranslationgui':
				$this->denyAccessIfNot("write");
				$this->getSubTabs('settings');
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				$this->tabs_gui->setSubTabActive('settings_trans');
				$transgui = new ilObjectTranslationGUI($this);
				$this->ctrl->forwardCommand($transgui);
				break;
			case "ilcertificategui":
				$this->getSubTabs('settings');
				$this->denyAccessIfNot("write");
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				$this->tabs_gui->setSubTabActive('certificate');
				$guiFactory = new ilCertificateGUIFactory();
				$output_gui = $guiFactory->create($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;
			case false:
				$this->getSubTabs($cmd);
				switch ($cmd) {
					case "cancelDelete":
						$cmd = "view";
					case "create":
					case "save":
					case "view":
					case "cancel":
					case "edit":
						$this->$cmd();
						break;
					case "delete":
						$this->tabs_gui->clearTargets();
						$this->tabs_gui->setBackTarget($this->lng->txt("back"), $this->ctrl->getLinkTarget($this));
						parent::deleteObject();
						break;
					case 'confirmedDelete':
						parent::confirmedDeleteObject();
						break;
					case 'editAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->tabs_gui->setSubTabActive('edit_advanced_settings');
						//$this->setSubTabsSettings('edit_advanced_settings');
						$this->editAdvancedSettings();
						break;
					case 'updateAdvancedSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->tabs_gui->setSubTabActive('edit_advanced_settings');
						//$this->setSubTabsSettings('edit_advanced_settings');
						$this->updateAdvancedSettings();
						break;
					case "infoScreen":
						$this->ctrl->redirectByClass("ilInfoScreenGUI", "showSummary");
						break;
					case 'getAsynchItemList':
						parent::getAsynchItemListObject();
						break;
					case 'trash':
					case 'undelete':
					case 'confirmRemoveFromSystem':
					case 'removeFromSystem':
					case 'deliverCertificate':
						$cmd .= "Object";
						$this->$cmd();
						break;
					/*case 'editSettings':
						$this->tabs_gui->setTabActive("settings");
						$this->setSubTabsSettings('edit_settings');
						$this->editSettings();
						break;
					case '':
					case 'view':
					case 'render':
					case 'cancel':
					case 'cancelDelete':
						$this->view();
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
						break;*/
					default:
						throw new ilException("ilObjStudyProgrammeGUI: Command not supported: $cmd");
				}
				break;

			default:
				throw new ilException("ilObjStudyProgrammeGUI: Can't forward to next class $next_class");
		}
	}


	/**
	 * creates the object
	 */
	protected function create() {
		parent::createObject();
	}


	/**
	 * Saves the object
	 *
	 * If its a async call, the response is sent as a json string
	 */
	protected function save() {
		parent::saveObject();

		if($this->ctrl->isAsynch()) {
			$form = $this->getAsyncCreationForm();
			$form->setValuesByPost();
			echo ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>$this->ctrl->getCmd(), "success"=>false, "errors"=>$form->getErrors()));
			exit();
		}
	}


	/**
	 * Cancel the object generation
	 *
	 * If
	 */
	protected function cancel() {
		$async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>"cancel", "success"=>false));

		ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

		parent::cancelCreation();
	}


	/**
	 * After save hook
	 * Sets the sorting of the container correctly. If its a async call, a json string is returned.
	 *
	 * @param ilObject $a_new_object
	 */
	protected function afterSave(ilObject $a_new_object)
	{
		// set default sort to manual
		$settings = new ilContainerSortingSettings($a_new_object->getId());
		$settings->setSortMode(ilContainer::SORT_MANUAL);
		$settings->setSortDirection(ilContainer::SORT_DIRECTION_ASC);
		$settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
		$settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
		$settings->save();

		$async_response = ilAsyncOutputHandler::encodeAsyncResponse(array("cmd" =>"cancel", "success"=>true, "message"=>$this->lng->txt("object_added")));

		ilAsyncOutputHandler::handleAsyncOutput("", $async_response, false);

		ilUtil::sendSuccess($this->lng->txt("object_added"), true);

		$this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			$this->ctrl->getLinkTarget($this, "edit", "", false, false)));
	}


	/**
	 * Default view method
	 */
	protected function view() {
		$this->denyAccessIfNot("read");
		$this->tabs_gui->setTabActive(self::TAB_VIEW_CONTENT);

		parent::renderObject();
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
		$form->addCommandButton('editAdvancedSettings', $this->lng->txt('cancel'));

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
		$gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'prg', $this->object->getId(), 'prg_type', $this->object->getSubtypeId());
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
		$gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'prg', $this->object->getId(), 'prg_type', $this->object->getSubtypeId());
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


	/**
	 * Overwritten from ilObjectGUI since copy and import are not implemented.
	 *
	 * @param string $a_new_type
	 *
	 * @return array
	 */
	protected function initCreationForms($a_new_type) {
		return array( self::CFORM_NEW => $this->initCreateForm($a_new_type));
	}


	/**
	 * Method for implementing async windows-output
	 * Should be moved into core to enable async requests on creation-form
	 *
	 * @return ilAsyncPropertyFormGUI
	 */
	public function getAsyncCreationForm() {
		$asyncForm = new ilAsyncPropertyFormGUI();

		$tmp_forms = $this->initCreationForms('prg');
		$asyncForm->cloneForm($tmp_forms[self::CFORM_NEW]);
		$asyncForm->setAsync(true);

		return $asyncForm;
	}

	////////////////////////////////////
	// HELPERS
	////////////////////////////////////

	protected function checkAccess($a_which) {
		return $this->ilAccess->checkAccess($a_which, "", $this->ref_id);
	}

	protected function denyAccessIfNot($a_perm) {
		return $this->denyAccessIfNotAnyOf(array($a_perm));
	}

	protected function denyAccessIfNotAnyOf($a_perms) {
		foreach ($a_perms as $perm) {
			if ($this->checkAccess($perm)) {
				return;
			}
		}

		if ($this->checkAccess("visible")) {
			ilUtil::sendFailure($this->lng->txt("msg_no_perm_write"));
			$this->ctrl->redirectByClass('ilinfoscreengui', '');
		}

		$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"), $this->ilias->error_obj->WARNING);
	}

	const TAB_VIEW_CONTENT = "view_content";
	const SUBTAB_VIEW_TREE = "view_tree";
	const TAB_INFO = "info_short";
	const TAB_SETTINGS = "settings";
	const TAB_MEMBERS = "members";
	const TAB_SUBTYPES = "subtypes";

	/**
	 * Adds the default tabs to the gui
	 */
	public function getTabs() {
		$this->help->setScreenIdComponent("prg");
		if ($this->checkAccess("read")) {
			$this->tabs_gui->addTab( self::TAB_VIEW_CONTENT
								   , $this->lng->txt("content")
								   , $this->getLinkTarget("view"));
		}

		if ($this->checkAccess("read")) {
			$this->tabs_gui->addTab( self::TAB_INFO
								   , $this->lng->txt("info_short")
								   , $this->getLinkTarget("info_short")
								   );
		}

		if ($this->checkAccess("write")) {
			$this->tabs_gui->addTab( self::TAB_SETTINGS
								   , $this->lng->txt("settings")
								   , $this->getLinkTarget("settings")
								   );
		}

		if ($this->checkAccess("manage_members") ) {
			$this->tabs_gui->addTab( self::TAB_MEMBERS
					   , $this->lng->txt("members")
					   , $this->getLinkTarget("members")
					   );
		}
		parent::getTabs();
	}

	/**
	 * Adds subtabs based on the parent tab
	 *
	 * @param $a_parent_tab | string of the parent tab-id
	 */
	public function getSubTabs($a_parent_tab) {
		switch($a_parent_tab) {
			case self::TAB_VIEW_CONTENT:
			case self::SUBTAB_VIEW_TREE:
			case 'view':
				if($this->checkAccess("read")) {
					$this->tabs_gui->addSubTab(self::TAB_VIEW_CONTENT, $this->lng->txt("view"), $this->getLinkTarget("view"));
				}

				if($this->checkAccess("write")) {
					$this->tabs_gui->addSubTab(self::SUBTAB_VIEW_TREE, $this->lng->txt("cntr_manage"), $this->getLinkTarget(self::SUBTAB_VIEW_TREE));
				}
				break;
			case 'settings':
			case 'editAdvancedSettings':
				$this->tabs_gui->addSubTab('settings', $this->lng->txt('settings'), $this->getLinkTarget('settings'));
				$this->tabs_gui->addSubTab("settings_trans",$this->lng->txt("obj_multilinguality"),$this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", ""));
				//$this->tabs_gui->addSubTab("edit_translations", $this->lng->txt("obj_multilinguality"), $this->ctrl->getLinkTargetByClass("iltranslationgui", "editTranslations"));
				$sub_type_id = $this->object->getSubtypeId();
				if($sub_type_id) {
					$type = $this->type_repository->readType($sub_type_id);
				}
				if (
					!is_null($type) &&
					count(
						$this->type_repository->readAssignedAMDRecordIdsByType(
							$type->getId()
							,true
						)
					) > 0
				) {
					$this->tabs_gui->addSubTab('edit_advanced_settings', $this->lng->txt('prg_adv_settings'), $this->ctrl->getLinkTarget($this, 'editAdvancedSettings'));
				}
				if(ilCertificate::isActive())
				{
					$this->tabs_gui->addSubTabTarget(
						"certificate",
						$this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"),
						"", "ilcertificategui");
				}
				break;
		}

	}


	/**
	 * Disable default content subtabs
	 */
	public function setContentSubTabs() {
		return;
	}


	/**
	 * Generates a link based on a cmd
	 *
	 * @param $a_cmd
	 *
	 * @return string
	 */
	protected function getLinkTarget($a_cmd) {
		if ($a_cmd == "info_short") {
			return $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
		}
		if ($a_cmd == "settings") {
			return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammesettingsgui", "view");
		}
		if($a_cmd == self::SUBTAB_VIEW_TREE) {
			return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammetreegui", "view");
		}
		if ($a_cmd == "members") {
			return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammemembersgui", "view");
		}
		if($a_cmd == "subtypes") {
			return $this->ctrl->getLinkTargetByClass("ilstudyprogrammetypegui", "listTypes");
		}

		return $this->ctrl->getLinkTarget($this, $a_cmd);
	}


	/**
	 * Adding meta-data to the info-screen
	 *
	 * @param $a_info_screen
	 */
	protected function fillInfoScreen($a_info_screen)
	{
		if(!$this->object->getSubtypeId() ||
			!ilStudyProgrammeDIC::dic()['model.Type.ilStudyProgrammeTypeRepository']
				->readType($this->object->getSubtypeId())
		) {
			return;
		}
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'prg', $this->object->getId(), 'prg_type', $this->object->getSubtypeId());
		$record_gui->setInfoObject($a_info_screen);
		$record_gui->parse();
	}

	protected function edit(){
		$this->denyAccessIfNot("write");

		$this->getSubTabs('settings');
		$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
		$this->tabs_gui->setSubTabActive('settings');
		$gui = $this->settings_gui;
		$gui->setParentGUI($this);
		$gui->setRefId($this->ref_id);
		$this->ctrl->setCmd("view");
		$this->ctrl->forwardCommand($gui);
	}

	/**
	 * _goto
	 * Deep link
	 *
	 * @param string $a_target
	 */
	public static function _goto($a_target) {
		global $DIC;
		$ilAccess = $DIC['ilAccess'];
		$ilErr = $DIC['ilErr'];
		$ilCtrl = $DIC['ilCtrl'];
		$id = explode("_", $a_target);
		$ilCtrl->initBaseClass("ilRepositoryGUI");
		$ilCtrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $id[0]);

		$ilCtrl->redirectByClass(array( "ilRepositoryGUI", "ilobjstudyprogrammegui" ), "view");
	}

	public function addToNavigationHistory(){
		global $DIC;
		$ilNavigationHistory = $DIC['ilNavigationHistory'];

		if(!$this->getCreationMode() &&
			$this->ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$link = ilLink::_getLink($_GET["ref_id"], "iass");

			$ilNavigationHistory->addItem($_GET['ref_id'],
				$link, 'prg');
		}
	}

	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
	{

		$lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
		$validator = new ilCertificateDownloadValidator();
		if (true === $validator->isCertificateDownloadable($this->user->getId(), $this->object->getId())) {
			$cert_url = $this->ctrl->getLinkTarget($this, "deliverCertificate");
			$this->lng->loadLanguageModule("certificate");
			$lg->addCustomCommand($cert_url, "download_certificate");
			$lg->addHeaderIcon("cert_icon",
				ilUtil::getImagePath("icon_cert.svg"),
				$this->lng->txt("download_certificate"),
				null,
				null,
				$cert_url);
		}
		return $lg;
	}


	protected function deliverCertificateObject()
	{
		global $DIC;

		$user_id = (int)$this->user->getId();
		$obj_id = (int)$this->object->getId();

		$validator = new ilCertificateDownloadValidator();
		if (false === $validator->isCertificateDownloadable($user_id, $obj_id)) {
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		$repository = new ilUserCertificateRepository();
		$cert_logger = $DIC->logger()->cert();
		$pdf_action = new ilCertificatePdfAction(
			$cert_logger,
			new ilPdfGenerator($repository, $cert_logger),
			new ilCertificateUtilHelper(),
			$this->lng->txt('error_creating_certificate_pdf')
		);
		$pdf_action->downloadPdf($user_id, $obj_id);
	}
}

?>
