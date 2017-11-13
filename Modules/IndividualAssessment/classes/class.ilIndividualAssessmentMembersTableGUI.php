<?php
/* @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
require_once 'Services/Table/classes/class.ilTable2GUI.php';
require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';

/**
 * List of members fo iass
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentMembersTableGUI extends ilTable2GUI {
	public function __construct($a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->lng = $DIC['lng'];
		$this->viewer_id = (int)$DIC['ilUser']->getId();

		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(false);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/IndividualAssessment");
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));

		$this->iass_access = $this->parent_obj->object->accessHandler();

		foreach ($this->visibleColumns() as $lng_var => $params) {
			$this->addColumn($this->lng->txt($lng_var), $params[0]);
		}
		$this->setData(iterator_to_array($a_parent_obj->object->loadVisibleMembers()));
	}

	/**
	 * Get column user should be shown
	 *
	 * @return string()
	 */
	protected function visibleColumns() {
		$columns = array( 'name' 				=> array('name')
						, 'login' 				=> array('login'));
		if($this->userMayViewGrades() || $this->userMayEditGrades()) {
			$columns['grading'] = array('lp_status');
			$columns['iass_graded_by'] = array('iass_graded_by');
			$columns['iass_changed_by'] = array('iass_changed_by');
		}
		$columns['actions'] = array(null);
		return $columns;
	}

	/**
	 * @inheritdoc
	 */
	protected function fillRow($a_set) {
		$this->tpl->setVariable("FULLNAME", $a_set[ilIndividualAssessmentMembers::FIELD_LASTNAME].', '.$a_set[ilIndividualAssessmentMembers::FIELD_FIRSTNAME]);
		$this->tpl->setVariable("LOGIN", $a_set[ilIndividualAssessmentMembers::FIELD_LOGIN]);

		if(!ilObjUser::_lookupActive($a_set[ilIndividualAssessmentMembers::FIELD_USR_ID])) {
			$this->tpl->setCurrentBlock('access_warning');
			$this->tpl->setVariable('PARENT_ACCESS', $this->lng->txt('usr_account_inactive'));
			$this->tpl->parseCurrentBlock();
		}

		if($this->userMayViewGrades() || $this->userMayEditGrades()) {
			$status = $a_set[ilIndividualAssessmentMembers::FIELD_LEARNING_PROGRESS];
			if($status == 0)
			{
				$status = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
			}
			if($a_set['finalized'] === '0' && $a_set['examiner_id'] !== null)
			{
				$status = ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED;
			}
			$this->tpl->setVariable("LP_STATUS", $this->getEntryForStatus($status));

			$graded_by = "";
			if($a_set[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID] && $a_set[ilIndividualAssessmentMembers::FIELD_FINALIZED]) {
				$graded_by = $a_set[ilIndividualAssessmentMembers::FIELD_EXAMINER_LASTNAME].", ".$a_set[ilIndividualAssessmentMembers::FIELD_EXAMINER_FIRSTNAME];
			}
			$this->tpl->setVariable("GRADED_BY", $graded_by);

			$changed_by = "";
			if($a_set[ilIndividualAssessmentMembers::FIELD_CHANGER_ID]) {
				$changed_by =
					$a_set[ilIndividualAssessmentMembers::FIELD_CHANGER_LASTNAME].", ".
					$a_set[ilIndividualAssessmentMembers::FIELD_CHANGER_FIRSTNAME]." ".
					(new ilDateTime($a_set[ilIndividualAssessmentMembers::FIELD_CHANGE_TIME], IL_CAL_DATETIME))->get(IL_CAL_FKT_DATE, "d.m.Y H:i")
					;
			}
			$this->tpl->setVariable("CHANGED_BY", $changed_by);
		}

		$this->tpl->setVariable("ACTIONS",$this->buildActionDropDown($a_set));
	}

	/**
	 * Get image path for lp images
	 *
	 * @param int 	$a_status
	 *
	 * @return string
	 */
	protected function getImagetPathForStatus($a_status) {
		switch($a_status) {
			case ilIndividualAssessmentMembers::LP_IN_PROGRESS :
				$status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
				break;
			case ilIndividualAssessmentMembers::LP_COMPLETED :
				$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
				break;
			case ilIndividualAssessmentMembers::LP_FAILED :
				$status = ilLPStatus::LP_STATUS_FAILED_NUM;
				break;
			default :
				$status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED ;
				break;
		}
		return ilLearningProgressBaseGUI::_getImagePathForStatus($status);
	}

	/**
	 * Get text for lp status
	 *
	 * @param int 	$a_status
	 *
	 * @return string
	 */
	protected function getEntryForStatus($a_status) {
		switch($a_status) {
			case ilIndividualAssessmentMembers::LP_IN_PROGRESS :
				return $this->lng->txt('iass_status_pending');
				break;
			case ilIndividualAssessmentMembers::LP_COMPLETED :
				return $this->lng->txt('iass_status_completed');
				break;
			case ilIndividualAssessmentMembers::LP_FAILED :
				return $this->lng->txt('iass_status_failed');
				break;
			case ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED :
				return $this->lng->txt('iass_assessment_not_completed');
				break;
		}
	}

	/**
	 * Get the action drop down
	 *
	 * @param string[]
	 *
	 * @return ilAdvancedSelectionListGUI
	 */
	protected function buildActionDropDown($a_set) {
		$l = new ilAdvancedSelectionListGUI();
		$l->setListTitle($this->lng->txt("actions"));

		$this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $a_set['usr_id']);
		$edited_by_viewer = $this->setWasEditedByViewer((int)$a_set[ilIndividualAssessmentMembers::FIELD_EXAMINER_ID]);
		$finalized = (bool)$a_set[ilIndividualAssessmentMembers::FIELD_FINALIZED];

		if ($finalized && (($this->userMayEditGradesOf($a_set["usr_id"]) && $edited_by_viewer) || $this->userMayViewGrades())) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI','view');
			$l->addItem($this->lng->txt('iass_usr_view'), 'view', $target);
		}

		if(!$finalized && $this->userMayEditGradesOf($a_set["usr_id"]) && $edited_by_viewer) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI','edit');
			$l->addItem($this->lng->txt('iass_usr_edit'), 'edit', $target);
		}

		if(!$finalized && $this->userMayEditMembers()) {
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', $a_set['usr_id']);
			$target = $this->ctrl->getLinkTarget($this->parent_obj,'removeUserConfirmation');
			$this->ctrl->setParameter($this->parent_obj, 'usr_id', null);
			$l->addItem($this->lng->txt('iass_usr_remove'), 'removeUser', $target);
		}

		if($finalized && $this->userMayAmendGrades()) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'amend');
			$l->addItem($this->lng->txt('iass_usr_amend'), 'amend', $target);
		}

		if($this->userMayDownloadAttachment($a_set['usr_id']) && (string)$a_set['file_name'] !== '') {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'downloadAttachment');
			$l->addItem($this->lng->txt('iass_usr_download_attachment'), 'downloadAttachment', $target);
		}
		$this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);
		return $l->getHTML();
	}

	/**
	 * Check the set was edited by viewing user
	 *
	 * @param int 	$examiner_id
	 *
	 * @return bool
	 */
	protected function setWasEditedByViewer($examiner_id) {
		return $examiner_id === $this->viewer_id || 0 === $examiner_id;
	}

	/**
	 * User may edit grades
	 *
	 * @return bool
	 */
	protected function userMayEditGrades() {
		return $this->iass_access->mayGradeUser();
	}

	/**
	 * User may edit grades of a specific user.
	 *
	 * @param  int	$a_user_id
	 * @return bool
	 */
	protected function userMayEditGradesOf($a_user_id) {
		return $this->iass_access->mayGradeUserById($a_user_id);
	}

	/**
	 * User may view grades
	 *
	 * @return bool
	 */
	protected function userMayViewGrades() {
		return $this->iass_access->mayViewUser();
	}

	/**
	 * User may amend members records.
	 *
	 * @return bool
	 */
	protected function userMayEditMembers() {
		return $this->iass_access->mayEditMembers();
	}

	/**
	 * User may amend grades
	 *
	 * @return bool
	 */
	protected function userMayAmendGrades() {
		return $this->iass_access->mayAmendGradeUser();
	}

	/**
	 * User may download attachment
	 *
	 * @return bool
	 */
	protected function userMayDownloadAttachment($usr_id) {
		return $this->userMayViewGrades() || $this->userMayEditGrades() || $this->userMayEditGradesOf($usr_id);
	}
}
