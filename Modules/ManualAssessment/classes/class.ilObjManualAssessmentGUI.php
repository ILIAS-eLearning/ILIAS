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
 */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once("./Services/AccessControl/classes/class.ilPermissionGUI.php");


class ilObjManualAssessmentGUI extends ilObjectGUI {
	const TAB_SETTINGS = 'settings';
	const TAB_INFO = 'info_short';
	const TAB_PERMISSION = 'perm_settings';
	const TAB_MEMBERS = 'members';
	public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true) {
		global $DIC;
		$this->type = 'mass';
		$this->tpl = $DIC['tpl'];
		$this->ctrl = $DIC['ilCtrl'];
		$this->tpl->getStandardTemplate();
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}

	function executeCommand() {
		global $ilUser,$ilCtrl, $ilTabs, $lng;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
		
		switch($next_class) {
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive(self::TAB_PERMISSION);
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
				$this->tabs_gui->setTabActive(self::TAB_INFO);
				$info = new ilInfoScreenGUI($this);
				$this->fillInfoScreen($info);
				$this->ctrl->forwardCommand($info);
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
		parent::getTabs();
	//	$this->tabs_gui->clearTargets();
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
		return $this->ctrl->getLinkTarget($this, $a_cmd);
	}

	public function getBaseEditForm() {
		return $this->initEditForm();
	}
}