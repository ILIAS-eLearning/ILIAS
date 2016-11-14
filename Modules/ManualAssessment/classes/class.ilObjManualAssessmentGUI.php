<?php

/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilManualAssessmentSettingsGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilManualAssessmentMembersGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilExportGUI
 */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentLP.php';
require_once 'Services/Tracking/classes/class.ilObjUserTracking.php';


class ilObjManualAssessmentGUI extends ilObjectGUI {
	const TAB_SETTINGS = 'settings';
	const TAB_INFO = 'info_short';
	const TAB_PERMISSION = 'perm_settings';
	const TAB_MEMBERS = 'members';
	const TAB_LP = 'learning_progress';
	const TAB_EXPORT = 'export';
	public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true) {

		global $DIC;
		$this->ilNavigationHistory = $DIC['ilNavigationHistory'];
		$this->type = 'mass';
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];
		$this->usr = $DIC['ilUser'];
		$this->ilias = $DIC['ilias'];
		$this->lng = $DIC['lng'];
		$this->ilAccess = $DIC['ilAccess'];
		$this->lng->loadLanguageModule('mass');
		$this->tpl->getStandardTemplate();
		$this->locator = $DIC['ilLocator'];
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	public function addLocatorItems() {

		if (is_object($this->object)) {
			$this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "view"), "", $this->object->getRefId());
		}
	}

	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
		$this->addToNavigationHistory();
		switch($next_class) {
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive(self::TAB_PERMISSION);
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$ilPermissionGUI = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($ilPermissionGUI);
				break;
			case 'ilmanualassessmentsettingsgui':
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentSettingsGUI.php';
				$gui = new ilManualAssessmentSettingsGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;
			case 'ilmanualassessmentmembersgui':
				$this->membersObject();
				break;
			case 'ilinfoscreengui':
				$this->tabs_gui->setTabActive(self::TAB_INFO);
				require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
				$info = $this->buildInfoScreen();
				$this->ctrl->forwardCommand($info);
				break;
			case 'illearningprogressgui':
				if(!$this->object->access_handler->checkAccessToObj($this->object,'read')) {
					$this->handleAccessViolation();
				}
				require_once 'Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$this->tabs_gui->setTabActive(self::TAB_LP);
				$learning_progress = new ilLearningProgressGUI(
											ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
											$this->object->getRefId(),
											$this->usr->getId());
				$this->ctrl->forwardCommand($learning_progress);
				break;
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilexportgui":
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$this->tabs_gui->setTabActive(self::TAB_EXPORT);
				$exp_gui = new ilExportGUI($this); // $this is the ilObj...GUI class of the resource
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
				break;
			default:
				if(!$cmd) {
					$cmd = 'view';
					if($this->object->access_handler->checkAccessToObj($this->object, 'edit_members')) {
						$this->ctrl->setCmdClass('ilmanualassessmentmembersgui');
						$cmd = 'members';
					}
				}
				$cmd .= 'Object';
				$this->$cmd();
			}
		return true;
	}

	public function tabsGUI() {
		return $this->tabs_gui;
	}

	public function viewObject() {
		$this->tabs_gui->setTabActive(self::TAB_INFO);
		require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
		$this->ctrl->setCmd('showSummary');
		$this->ctrl->setCmdClass('ilinfoscreengui');
		$info = $this->buildInfoScreen();
		$this->ctrl->forwardCommand($info);
	}

	public function membersObject() {
		$this->tabs_gui->setTabActive(self::TAB_MEMBERS);
		require_once 'Modules/ManualAssessment/classes/class.ilManualAssessmentMembersGUI.php';
		$gui = new ilManualAssessmentMembersGUI($this, $this->ref_id);
		$this->ctrl->forwardCommand($gui);
	}

	protected function buildInfoScreen() {
		$info = new ilInfoScreenGUI($this);
		if($this->object) {
			$info = $this->addGeneralDataToInfo($info);
			if($this->object->loadMembers()->userAllreadyMember($this->usr)) {
				$info = $this->addMemberDataToInfo($info);
			}
			$info = $this->addContactDataToInfo($info);
		}
		return $info;
	}

	protected function addMemberDataToInfo(ilInfoScreenGUI $info) {
		$member = $this->object->membersStorage()->loadMember($this->object,$this->usr);
		$info->addSection($this->lng->txt('grading_info'));
		if( $member->finalized()) {
			$info->addProperty($this->lng->txt('grading'),$this->getEntryForStatus($member->LPStatus()));
		}
		if($member->notify() && $member->finalized()) {
			$info->addProperty($this->lng->txt('grading_record'), nl2br($member->record()));
		}
		return $info;
	}

	protected function addGeneralDataToInfo(ilInfoScreenGUI $info) {
		$content = $this->object->getSettings()->content();
		if($content !== null && $content !== '') {
			$info->addSection($this->lng->txt('general'));
			$info->addProperty($this->lng->txt('content'),$content);
		}
		return $info;
	}

	protected function addContactDataToInfo(ilInfoScreenGUI $info) {
		$info_settings = $this->object->getInfoSettings();
		if($this->shouldShowContactInfo($info_settings)) {
			$info->addSection($this->lng->txt('mass_contact_info'));
			$info->addProperty($this->lng->txt('mass_contact'),$info_settings->contact());
			$info->addProperty($this->lng->txt('mass_responsibility'),$info_settings->responsibility());
			$info->addProperty($this->lng->txt('mass_phone'),$info_settings->phone());
			$info->addProperty($this->lng->txt('mass_mails'),$info_settings->mails());
			$info->addProperty($this->lng->txt('mass_consultation_hours'),$info_settings->consultationHours());
		}
		return $info;
	}

protected function shouldShowContactInfo(ilManualAssessmentInfoSettings $info_settings) {
	$val = $info_settings->contact();
	if($val !== null && $val !== '') {
		return true;
	}
	$val = $info_settings->responsibility();
	if($val !== null && $val !== '') {
		return true;
	}
	$val = $info_settings->phone();
	if($val !== null && $val !== '') {
		return true;
	}
	$val = $info_settings->mails();
	if($val !== null && $val !== '') {
		return true;
	}
	$val = $info_settings->consultationHours();
	if($val !== null && $val !== '') {
		return true;
	}
	return false;
}

public function getTabs() {
		$access_handler = $this->object->accessHandler();
		if($access_handler->checkAccessToObj($this->object,'read')) {
			$this->tabs_gui->addTab( self::TAB_INFO
									, $this->lng->txt('info_short')
									, $this->getLinkTarget('info')
									);
		}
		if($access_handler->checkAccessToObj($this->object,'write')) {
			$this->tabs_gui->addTab( self::TAB_SETTINGS
									, $this->lng->txt('settings')
									, $this->getLinkTarget('settings')
									);
		}
		if($access_handler->checkAccessToObj($this->object,'edit_members')
			|| $access_handler->checkAccessToObj($this->object,'edit_learning_progress')
			|| $access_handler->checkAccessToObj($this->object,'read_learning_progress') ) {
			$this->tabs_gui->addTab( self::TAB_MEMBERS
									, $this->lng->txt('il_mass_members')
									, $this->getLinkTarget('members')
									);
		}
		if(($access_handler->checkAccessToObj($this->object,'read_learning_progress')
			|| $access_handler->checkAccessToObj($this->object,'edit_learning_progress')
			|| ($this->object->loadMembers()->userAllreadyMember($this->usr)
			&& $this->object->isActiveLP()))
			&& ilObjUserTracking::_enabledLearningProgress()) {
			$this->tabs_gui->addTab(self::TAB_LP
									, $this->lng->txt('learning_progress')
									, $this->ctrl->getLinkTargetByClass('illearningprogressgui')
									);
		}

		if($access_handler->checkAccessToObj($this->object,'write'))
		{
			$this->tabs_gui->addTarget(
				self::TAB_EXPORT,
				$this->ctrl->getLinkTargetByClass('ilexportgui',''),
				'export',
				'ilexportgui'
			);
		}

		if($access_handler->checkAccessToObj($this->object,'edit_permission')) {
			$this->tabs_gui->addTarget(self::TAB_PERMISSION
									, $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm')
									, array()
									, 'ilpermissiongui'
									);
		}
		parent::getTabs();
	}

	protected function getLinkTarget($a_cmd) {
		if ($a_cmd == 'settings') {
			return $this->ctrl->getLinkTargetByClass('ilmanualassessmentsettingsgui', 'edit');
		}
		if ($a_cmd == 'info') {
			return $this->ctrl->getLinkTarget($this,'view');
		}
		if ($a_cmd == 'members') {
			return $this->ctrl->getLinkTargetByClass('ilmanualassessmentmembersgui', 'view');
		}
		return $this->ctrl->getLinkTarget($this, $a_cmd);
	}

	public function getBaseEditForm() {
		return $this->initEditForm();
	}

	public function handleAccessViolation() {
		global $DIC;
		$DIC['ilias']->raiseError($DIC['lng']->txt("msg_no_perm_read"), $DIC['ilias']->error_obj->WARNING);
	}

	public static function _goto($a_target, $a_add = '') {
		global $DIC;
		if ($DIC['ilAccess']->checkAccess( 'read', '', $a_target)) {
			ilObjectGUI::_gotoRepositoryNode($a_target, 'view');
		}
	}

	protected function getEntryForStatus($a_status ) {
		switch($a_status) {
			case ilManualAssessmentMembers::LP_IN_PROGRESS :
				return $this->lng->txt('mass_status_pending');
				break;
			case ilManualAssessmentMembers::LP_COMPLETED :
				return $this->lng->txt('mass_status_completed');
				break;
			case ilManualAssessmentMembers::LP_FAILED :
				return $this->lng->txt('mass_status_failed');
				break;
		}
	}

	protected function afterSave(ilObject $a_new_object) {
		ilUtil::sendSuccess($this->lng->txt("mass_added"),true);
		$this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
		ilUtil::redirect($this->ctrl->getLinkTargetByClass('ilmanualassessmentsettingsgui', 'edit', '', false, false));
	}

	public function addToNavigationHistory() {
		if(!$this->getCreationMode()) {
			$access_handler = $this->object->accessHandler();
			if($access_handler->checkAccessToObj($this->object,'read')) {
				$link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", "frameset");
				$this->ilNavigationHistory->addItem($_GET['ref_id'], $link, 'mass');
			}
		}
	}
}