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
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilObjTaxonomyGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilManualAssessmentSettingsGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilManualAssessmentMembersGUI
 * @ilCtrl_Calls ilObjManualAssessmentGUI: ilLearningProgressGUI
 */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");


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
		$access_control = $this->object->accessHandler();
		switch($next_class) {
			case 'ilpermissiongui':
				if($access_control->checkAccessToObj($this->object,'edit_permission')) {
					$this->tabs_gui->setTabActive(self::TAB_PERMISSION);
					$ilPermissionGUI = new ilPermissionGUI($this);
					$this->ctrl->forwardCommand($ilPermissionGUI);
				} else {
					$this->handleAccessViolation();
				}
				break;
			case "ilmanualassessmentsettingsgui":
				if($access_control->checkAccessToObj($this->object,'write')) {
					$this->tabs_gui->setTabActive(self::TAB_SETTINGS);
					require_once("Modules/ManualAssessment/classes/class.ilManualAssessmentSettingsGUI.php");
					$gui = new ilManualAssessmentSettingsGUI($this, $this->ref_id);
					$this->ctrl->forwardCommand($gui);
				} else {
					$this->handleAccessViolation();
				}
				break;
			case "ilmanualassessmentmembersgui":
				if($access_control->checkAccessToObj($this->object,'edit_members')) {
					$this->tabs_gui->setTabActive(self::TAB_MEMBERS);
					require_once("Modules/ManualAssessment/classes/class.ilManualAssessmentMembersGUI.php");
					$gui = new ilManualAssessmentMembersGUI($this, $this->ref_id);
					$this->ctrl->forwardCommand($gui);
				} else {
					$this->handleAccessViolation();
				}
				break;
			case "ilinfoscreengui":
				if($access_control->checkAccessToObj($this->object,'view')) {
					$this->tabs_gui->setTabActive(self::TAB_INFO);
					$info = new ilInfoScreenGUI($this);
					$this->fillInfoScreen($info);
					$this->ctrl->forwardCommand($info);
				} else {
					$this->handleAccessViolation();
				}
				break;
			case "illearningprogressgui":
				if($access_control->checkAccessToObj($this->object,'read_grades') ||
					$access_control->checkAccessToObj($this->object,'edit_grades')||
					$this->object->membersStorage()->userAllreadyMember($this->usr)) {
					include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
					$this->tabs_gui->setTabActive(self::TAB_LP);
					$new_gui = new ilLearningProgressGUI(3,
														  $this->object->getRefId(),
														  $this->usr->getId());
					$this->ctrl->forwardCommand($new_gui);
				} else {
					$this->handleAccessViolation();	
				}
				break;
			default:						
				if(!$cmd) {
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}

	public function viewObject() {

	}

	public function getTabs() {
		$this->tabs_gui->addTab( self::TAB_SETTINGS
								, $this->lng->txt("settings")
								, $this->getLinkTarget("settings")
								);
		$this->tabs_gui->addTab( self::TAB_MEMBERS
								, $this->lng->txt("members")
								, $this->getLinkTarget("members")
								);
		$this->tabs_gui->addTab( self::TAB_INFO
								, $this->lng->txt("info_short")
								, $this->getLinkTarget("info")
								);
		$this->tabs_gui->addTarget(self::TAB_PERMISSION
								, $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm')
								, array()
								, 'ilpermissiongui'
								);
		$this->tabs_gui->addTarget(self::TAB_LP
								, $this->ctrl->getLinkTargetByClass(array('illearningprogressgui','illplistofprogressgui'),"show")
								, array('illplistofprogressgui')
								, $this->lng->txt("LP"));
		parent::getTabs();
	}


	protected function getLinkTarget($a_cmd) {
		if ($a_cmd == "settings") {
			return $this->ctrl->getLinkTargetByClass("ilmanualassessmentsettingsgui", "edit");
		}
		if ($a_cmd == "info") {
			return $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "view");
		}
		if ($a_cmd == "members") {
			return $this->ctrl->getLinkTargetByClass("ilmanualassessmentmembersgui", "view");
		}
		if ($a_cmd == "members") {
			return $this->ctrl->getLinkTargetByClass("illearningprocessgui", "view");
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