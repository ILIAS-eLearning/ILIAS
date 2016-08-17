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
 */

require_once 'Services/Object/classes/class.ilObjectGUI.php';




class ilObjManualAssessmentGUI extends ilObjectGUI {
	const TAB_SETTINGS = 'settings';
	const TAB_INFO = 'info_short';
	const TAB_PERMISSION = 'perm_settings';
	const TAB_MEMBERS = 'members';
	const TAB_LP = 'learning_progress';
	public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true) {

		global $DIC;
		$this->type = 'mass';
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];
		$this->usr = $DIC['ilUser'];
		$this->ilias = $DIC['ilias'];
		$this->lng = $DIC['lng'];
		$this->tpl->getStandardTemplate();
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	function executeCommand() {
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
		switch($next_class) {
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive(self::TAB_PERMISSION);
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$ilPermissionGUI = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($ilPermissionGUI);
				break;
			case "ilmanualassessmentsettingsgui":
				$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
				require_once("Modules/ManualAssessment/classes/class.ilManualAssessmentSettingsGUI.php");
				$gui = new ilManualAssessmentSettingsGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilmanualassessmentmembersgui":
				$this->tabs_gui->setTabActive(self::TAB_MEMBERS);
				require_once("Modules/ManualAssessment/classes/class.ilManualAssessmentMembersGUI.php");
				$gui = new ilManualAssessmentMembersGUI($this, $this->ref_id);
				$this->ctrl->forwardCommand($gui);
				break;
			case "ilinfoscreengui":
				$this->viewObject();
			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				$this->tabs_gui->setTabActive(self::TAB_LP);
				$learning_progress = new ilLearningProgressGUI(
											ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
											$this->object->getRefId(),
											$this->usr->getId());
					$this->ctrl->forwardCommand($learning_progress);
				break;
			default:
				if(!$cmd) {
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();
			}
		return true;
	}

	public function viewObject() {
		$this->tabs_gui->setTabActive(self::TAB_INFO);
		require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$info = new ilInfoScreenGUI($this);
		if($this->object) {
			$info->addSection($this->lng->txt('general'));
			$info->addProperty($this->lng->txt('content'),$this->object->getSettings()->content());
		}
		$this->ctrl->forwardCommand($info);
	}

	public function getTabs() {
		$access_handler = $this->object->accessHandler();
		if($access_handler->checkAccessToObj($this->object,'read')) {
			$this->tabs_gui->addTab( self::TAB_INFO
									, $this->lng->txt("info_short")
									, $this->getLinkTarget("info")
									);
		}
		if($access_handler->checkAccessToObj($this->object,'write')) {
			$this->tabs_gui->addTab( self::TAB_SETTINGS
									, $this->lng->txt("settings")
									, $this->getLinkTarget("settings")
									);
		}
		if($access_handler->checkAccessToObj($this->object,'edit_members')) {
			$this->tabs_gui->addTab( self::TAB_MEMBERS
									, $this->lng->txt("members")
									, $this->getLinkTarget("members")
									);
		}
		if($access_handler->checkAccessToObj($this->object,'read_learning_progress') || $this->userIsMemberAndFinalized()) {
			$this->tabs_gui->addTab(self::TAB_LP
									, $this->lng->txt("LP")
									, $this->ctrl->getLinkTargetByClass('illearningprogressgui')
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

	protected function userIsMemberAndFinalized() {
		$member_storage = $this->object->membersStorage();
		if(!$member_storage->loadMembers($this->object)->userAllreadyMember($this->usr)) {
			return false;
		}
		if(!$member_storage->loadMember($this->object,$this->usr)->finalized()) {
			return false;
		}
		return true;
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
}