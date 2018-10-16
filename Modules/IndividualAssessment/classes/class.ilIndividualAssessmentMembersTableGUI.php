<?php
/* Copyright (c) 2017 Denis Klöpfer <denis.kloepfer@concepts-and-training.de>  Extended GPL, see ./LICENSE */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see ./LICENSE */

require_once 'Modules/IndividualAssessment/classes/Members/class.ilIndividualAssessmentMembersStorageDB.php';
require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
require_once 'Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'Services/Tracking/classes/class.ilLPStatus.php';

/**
 * List of members fo iass
 */
class ilIndividualAssessmentMembersTableGUI {

	public function __construct(
		ilIndividualAssessmentMembersGUI $parent,
		ilLanguage $lng,
		ilCtrl $ctrl,
		ilIndividualAssessmentAccessHandler $iass_access,
		$current_user_id
	) {
		$this->parent = $parent;
		$this->lng = $lng;
		$this->ctrl = $ctrl;
		$this->iass_access = $iass_access;
		$this->current_user_id = $current_user_id;
	}

	/**
	 * Set data to show in table
	 *
	 * @param mixed[] 	$data
	 *
	 * @return void
	 */
	public function setData(array $data) {
		$this->data = $data;
	}

	/**
	 * Get data should me shown in table
	 *
	 * @return mixed[]
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Renders the presentation table
	 *
	 * @param 	ILIAS\UI\Component\Component[] 	$view_constrols
	 * @param	int			$offset
	 * @param	int|null	$limit
	 * @return 	string
	 */
	public function render($view_constrols, $offset = 0, $limit = null) {
		global $DIC;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		//build table
		$ptable = $f->table()->presentation(
			"", //title
			$view_constrols,
			function ($row, ilIndividualAssessmentMember $record, $ui_factory, $environment) { //mapping-closure
				$headline = $this->getHeadline($record);
				$subheadline = $this->getSubheadline($record);
				$important_infos = $this->importantInfos($record);
				$further_fields = $this->getFurtherFields($record);
				$content = $this->getContent($record);
				$action = $this->getAction($record, $ui_factory);

				$row = $row
					->withHeadline($headline)
					->withSubheadline($subheadline)
					->withImportantFields($important_infos)
					->withContent($ui_factory->listing()->descriptive($content))
					->withFurtherFieldsHeadline($this->lng->txt("iass_further_field_headline"))
					->withFurtherFields($further_fields)
					->withAction($action);

				return $row;
			}
		);

		$data = array_slice($this->getData(), $offset, $limit);
		return $renderer->render($ptable->withData($data));
	}

	/**
	 * Returns the headline for each row
	 */
	protected function getHeadline(ilIndividualAssessmentMember $record)
	{
		return $record->lastname().", ".$record->firstname();
	}

	/**
	 * Returns the subheadline for each row
	 */
	protected function getSubheadline(ilIndividualAssessmentMember $record)
	{
		if(!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
			return "";
		}

		return $this->lng->txt("grading").": ".$this->getStatus($record->finalized(), $record->LPStatus(), $record->examinerId());
	}

	/**
	 * Returns all informations needed for important row
	 */
	protected function importantInfos(ilIndividualAssessmentMember $record)
	{
		$finalized = $record->finalized();
		$ret = [];

		if(!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
			return $ret;
		}

		if(!$finalized) {
			return $ret;
		}

		$ret = array_merge($ret, $this->getGradedInformations($record->eventTime()));
		$ret = array_merge($ret, $this->getExaminierLogin($record->examinerId()));

		return $ret;
	}

	/**
	 * Return all content elements for each row
	 */
	protected function getContent(ilIndividualAssessmentMember $record)
	{
		$ret = [];
		if(!$this->checkEditable($record->finalized(), $record->examinerId(),$record->id())
			&& !$this->checkAmendable($record->finalized())
		) {
			return $ret;
		}

		$ret = array_merge($ret, $this->getRecordNote($record->record()));
		$ret = array_merge($ret, $this->getInternalRecordNote($record->internalNote()));

		return $ret;
	}

	/**
	 * Returns all informations needed for further informations for each row
	 */
	protected function getFurtherFields(ilIndividualAssessmentMember $record)
	{
		$ret = [];

		if(!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
			return $ret;
		}

		$ret = array_merge($ret, $this->importantInfos($record));
		$ret = array_merge(
			$ret,
			$this->getLocationInfos(
				$record->place(),
				$record->finalized(),
				$record->examinerId(),
				$record->id()
			)
		);

		return $ret;
	}

	/**
	 * Return the ui control with executable actions
	 */
	protected function getAction(ilIndividualAssessmentMember $record, $ui_factory) {
		$items = [];

		$examiner_id = $record->examinerId();
		$usr_id = $record->id();
		$finalized = $record->finalized();
		$file_name = $record->fileName();

		$this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', $usr_id);

		if($this->checkViewable($finalized, $examiner_id, $usr_id)) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI','view');
			$items[] = $ui_factory->button()->shy($this->lng->txt('iass_usr_view'), $target);
		}

		if($this->checkEditable($finalized, $examiner_id, $usr_id)) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI','edit');
			$items[] = $ui_factory->button()->shy($this->lng->txt('iass_usr_edit'), $target);
		}

		if($this->checkUserRemoveable($finalized)) {
			$this->ctrl->setParameterByClass('ilIndividualAssessmentMembersGUI', 'usr_id', $usr_id);
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMembersGUI', 'removeUserConfirmation');
			$items[] = $ui_factory->button()->shy($this->lng->txt('iass_usr_remove'), $target);
			$this->ctrl->setParameterByClass('ilIndividualAssessmentMembersGUI', 'usr_id', null);
		}

		if($this->checkAmendable($finalized)) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'amend');
			$items[] = $ui_factory->button()->shy($this->lng->txt('iass_usr_amend'), $target);
		}

		if($this->checkDownloadFile($usr_id, $file_name)) {
			$target = $this->ctrl->getLinkTargetByClass('ilIndividualAssessmentMemberGUI', 'downloadAttachment');
			$items[] = $ui_factory->button()->shy($this->lng->txt('iass_usr_download_attachment'), $target);
		}
		$this->ctrl->setParameterByClass('ilIndividualAssessmentMemberGUI', 'usr_id', null);

		$action = $ui_factory->dropdown()->standard($items)->withLabel($this->lng->txt("actions"));
		return $action;
	}

	/**
	 * Returns readable status
	 */
	protected function getStatus($finalized, $status, $examinerId)
	{
		if($status == 0)
		{
			$status = ilIndividualAssessmentMembers::LP_IN_PROGRESS;
		}
		if(!$finalized && !is_null($examinerId))
		{
			$status = ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED;
		}

		return $this->getEntryForStatus($status);
	}

	/**
	 * Returns informations about the grading
	 */
	protected function getGradedInformations(ilDateTime $event_time)
	{
		return array(
			$this->lng->txt("iass_event_time").": " => $event_time->get(IL_CAL_DATE)
		);
	}

	/**
	 * Returns login of examinier
	 */
	protected function getExaminierLogin($examinerId)
	{
		return array(
			$this->lng->txt("iass_graded_by").": " => ilObjUser::_lookupLogin($examinerId)
		);
	}

	/**
	 * Returns the location of assessment
	 */
	protected function getLocationInfos($location, $finalized, $examinerId, $usr_id)
	{
		if(!$this->viewLocation($finalized, $examinerId, $usr_id)) {
			return array();
		}

		return array(
			$this->lng->txt("iass_location").": " => $location
		);
	}

	/**
	 * Returns inforamtions out of record note
	 */
	protected function getRecordNote($record_note)
	{
		return array(
			$this->lng->txt("iass_record").": " => $record_note
		);
	}

	/**
	 * Returns inforamtions out of internal record note
	 */
	protected function getInternalRecordNote($internal_note)
	{
		if(is_null($internal_note)) {
			$internal_note = "";
		}

		return array(
			$this->lng->txt("iass_internal_note").": " => $internal_note
		);
	}

	/**
	 * Get text for lp status
	 *
	 * @param int	$a_status
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
	 * Check user may view the location
	 */
	protected function viewLocation($finalized, $examinerId, $usr_id)
	{
		return $this->checkEditable($finalized, $examinerId, $usr_id)
			|| $this->checkAmendable($finalized);
	}

	/**
	 * Check the current user has visible permission on record
	 */
	protected function checkViewable($finalized, $examinerId, $usr_id)
	{
		if (($this->userIsSystemAdmin() && $finalized)
			|| ($finalized && (
				($this->userMayEditGradesOf($usr_id)
					&& $this->wasEditedByViewer($examinerId))
				|| $this->userMayViewGrades())
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check the current user has edit permission on record
	 */
	protected function checkEditable($finalized, $examinerId, $usr_id)
	{
		if(($this->userIsSystemAdmin() && !$finalized)
			|| (!$finalized && $this->userMayEditGradesOf($$usr_id)
				&& $this->wasEditedByViewer($examinerId)
			)
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check the current user has amend permission on record
	 */
	protected function checkAmendable($finalized)
	{
		if(($this->userIsSystemAdmin() && $finalized)
			|| ($finalized && $this->userMayAmendGrades())
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check the current user is allowed to remove the user
	 */
	protected function checkUserRemoveable($finalized)
	{
		if(($this->userIsSystemAdmin() && !$finalized)
			|| (!$finalized && $this->userMayEditMembers())
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check the current user is allowed to download the record file
	 */
	protected function checkDownloadFile($usr_id, $file_name)
	{
		if((!is_null($file_name) && $file_name !== '')
			&& ($this->userIsSystemAdmin() || $this->userMayDownloadAttachment($usr_id))
		) {
			return true;
		}

		return false;
	}

	/**
	 * User may view grades
	 *
	 * @return bool
	 */
	protected function userMayViewGrades()
	{
		return $this->iass_access->mayViewUser();
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
	 * User may amend grades
	 *
	 * @return bool
	 */
	protected function userMayAmendGrades() {
		return $this->iass_access->mayAmendGradeUser();
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
	 * Check whether usr is admin.
	 *
	 * @return bool
	 */
	protected function userIsSystemAdmin()
	{
		return $this->iass_access->isSystemAdmin();
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
	 * Check the set was edited by viewing user
	 *
	 * @param int	$examiner_id
	 *
	 * @return bool
	 */
	protected function wasEditedByViewer($examiner_id) {
		return $examiner_id === $this->current_user_id || 0 === $examiner_id;
	}
}
