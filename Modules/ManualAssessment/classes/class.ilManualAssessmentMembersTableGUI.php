<?php
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Modules/ManualAssessment/classes/Members/class.ilManualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';
class ilManualAssessmentMembersTableGUI extends ilTable2GUI {
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->lng = $DIC['lng'];
		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/ManualAssessment");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));
		$this->parent_obj = $a_parent_obj;
		$this->may_edit = $this->userMayEditGrades();
		$this->may_view = $this->userMayViewGrades();
		$this->columns = $this->visibleColumns();
		foreach ($this->columns as $lng_var => $params) {
			$this->addColumn($this->lng->txt($lng_var), $params[0]);
		}
		$this->setData(iterator_to_array($a_parent_obj->object->loadMembers()));
	}

	protected function visibleColumns() {
		$columns = array( 'name' 				=> array('name')
						, 'login' 				=> array('login'));
		if($this->may_view) {
			$columns['lp_status'] = array('lp_status');
			$columns['graded_by'] = array('graded_by');
		}
		$columns['actions'] = array(null);
		return $columns;
	}

	protected function fillRow($a_set) {
		$this->tpl->setCurrentBlock('user_data');
		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->parseCurrentBlock();
		if($this->may_view) {
			$this->tpl->setCurrentBlock('lp_info');
			$this->tpl->setVariable("LP_STATUS",
				$this->getImagetPathForStatus($a_set[ilManualAssessmentMembers::FIELD_LEARNING_PROGRESS]));
			$this->tpl->setVariable("GRADED_BY", $a_set[ilManualAssessmentMembers::FIELD_EXAMINER_ID] 
				?	$a_set[ilManualAssessmentMembers::FIELD_EXAMINER_LASTNAME].", "
						.$a_set[ilManualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME]
				: 	'');
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock('actions');
		$this->tpl->setVariable("ACTIONS",$this->buildActionDropDown($a_set));
		$this->tpl->parseCurrentBlock();
	}

	protected function getImagetPathForStatus($a_status) {
		switch($a_status) {
			case ilManualAssessmentMembers::LP_IN_PROGRESS :
				$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
				break;
			case ilManualAssessmentMembers::LP_COMPLETED :
				$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
				break;
			case ilManualAssessmentMembers::LP_FAILED :
				$status = ilLPStatus::LP_STATUS_FAILED_NUM;
				break;
			default :
				$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED ;
				break;
		}
		return ilLearningProgressBaseGUI::_getImagePathForStatus($status);
	}

	protected function buildActionDropDown($a_set) {
		$l = new ilAdvancedSelectionListGUI();
		if(!$a_set['finalized']) {	
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', $a_set['usr_id']);
			$target = $this->ctrl->getLinkTarget($this->parent_obj,'removeUser');
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', null);
			$l->addItem($this->lng->txt('mass_usr_remove'), 'removeUser', $target);
		}
		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', $a_set['usr_id']);
		if(!$a_set['finalized'] && $this->may_edit) {
			$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI','edit');
			$l->addItem($this->lng->txt('mass_usr_edit'), 'edit', $target);
		} 
		if(($a_set['finalized'] && $this->may_edit) || $this->may_view) {
			$target = $this->ctrl->getLinkTargetByClass('ilManualAssessmentMemberGUI','view');
			$l->addItem($this->lng->txt('mass_usr_view'), 'view', $target);
		}
		$this->ctrl->setParameterByClass('ilManualAssessmentMemberGUI', 'usr_id', null);
		return $l->getHTML();
	}

	protected function userMayEditGrades() {
		return $this->parent_obj->object->accessHandler()
			->checkAccessToObj($this->parent_obj->object,'edit_learning_progress');
	}

	protected function userMayViewGrades() {
		return $this->parent_obj->object->accessHandler()
			->checkAccessToObj($this->parent_obj->object,'read_learning_progress');
	}
}