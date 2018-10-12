<?php
/* @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de> */
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
class ilIndividualAssessmentMembersTableGUI {

	public function __construct(
		ilLanguage $lng,
		ilIndividualAssessmentAccessHandler $iass_access,
		$current_user_id
	) {
		$this->iass_access = $iass_access;
		$this->lng = $lng;
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
				//$content = $this->getContent($record);

				$row = $row
					->withHeadline($headline)
					->withSubheadline($subheadline)
					->withImportantFields($important_infos)
					->withContent($ui_factory->listing()->descriptive(array()))
					->withFurtherFields($further_fields);

				return $row;
			}
		);

		$data = array_slice($this->getData(), $offset, $limit);
		return $renderer->render($ptable->withData($data));
	}

	protected function getHeadline(ilIndividualAssessmentMember $record)
	{
		return $record->lastname().", ".$record->firstname();
	}

	protected function getSubheadline(ilIndividualAssessmentMember $record)
	{
		if(!$this->userMayViewGrades() && !$this->userMayEditGrades()) {
			return "";
		}

		return $this->lng->txt("grading").": ".$this->getStatus($record->finalized(), $record->LPStatus(), $record->examinerId());
	}

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

	protected function getContent(ilIndividualAssessmentMember $record)
	{
		
	}

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

	protected function getGradedInformations(ilDateTime $event_time)
	{
		return array(
			$this->lng->txt("iass_event_time").": " => $event_time->get(IL_CAL_DATE)
		);
	}

	protected function getExaminierLogin($examinerId)
	{
		return array(
			$this->lng->txt("iass_graded_by").": " => ilObjUser::_lookupLogin($examinerId)
		);
	}

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

	protected function viewLocation($finalized, $examinerId, $usr_id)
	{
		if(($this->userIsSystemAdmin() && !$finalized)
			|| (!$finalized && $this->userMayEditGradesOf($a_set["usr_id"]) && $this->wasEditedByViewer($examinerId))
		) {
			return true;
		}

		if(($this->userIsSystemAdmin() && $finalized)
			|| ($finalized && $this->userMayAmendGrades())
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
