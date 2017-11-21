<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/class.ilTMSBookingPlayerStateDB.php");

/**
 * Displays the TMS booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSBookingGUI  extends Booking\Player {
	use \ILIAS\TMS\MyUsersHelper;

	/**
	 * @var ilTemplate
	 */
	protected $g_tpl;

	/**
	 * @var ilCtrl
	 */
	protected $g_ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $g_user;

	/**
	 * @var	ilLanguage
	 */
	protected $g_lng;

	/**
	 * @var	mixed
	 */
	protected $parent_gui;

	/**
	 * @var string
	 */
	protected $parent_cmd;

	public function __construct($parent_gui, $parent_cmd, $execute_show = true) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->g_db = $DIC->database();

		$this->g_lng->loadLanguageModule('tms');

		$this->parent_gui = $parent_gui;
		$this->parent_cmd = $parent_cmd;

		/**
		 * ToDo: Remove this flag.
		 * It's realy ugly, but we need it. If we get here by a plugin parent
		 * the plugin executes show by him self. So we don't need it here
		 */
		$this->execute_show = $execute_show;
	}

	public function executeCommand() {
		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];

		if((int)$this->g_user->getId() !== $usr_id && !$this->checkIsSuperiorEmployeeBelowCurrent($usr_id)) {
			$this->redirectToPreviousLocation(array($this->g_lng->txt("no_permissions_to_book")), false);
		}

		if($this->duplicateCourseBooked($crs_ref_id, $usr_id)) {
			$this->redirectToPreviousLocation(array($this->g_lng->txt("duplicate_course_booked")), false);
		}

		global $DIC;
		$process_db = new ilTMSBookingPlayerStateDB();

		$this->init($DIC, $crs_ref_id, $usr_id, $process_db);

		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $this->process($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	// STUFF FROM Booking\Player

	/**
	 * @inheritdocs
	 */
	protected function getForm() {
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this));
		$form->setShowTopButtons(true);
		return $form;
	}

	/**
	 * @inheritdocs
	 */
	protected function txt($id) {
		if ($id === "abort") {
			$id = "cancel";
		}
		else if ($id === "next") {
			$id = "btn_next";
		}
		else if ($id == "aborted") {
			$id = "booking_aborted";
		}
		else if ($id == "previous") {
			$id = "btn_previous";
		}
		return $this->g_lng->txt($id);
	}

	/**
	 * @inheritdocs
	 */
	protected function redirectToPreviousLocation($messages, $success) {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
		$this->g_ctrl->setParameter($this->parent_gui, "s_user", $usr_id);

		if (count($messages)) {
			$message = join("<br/>", $messages);
			if ($success) {
				ilUtil::sendSuccess($message, true);
			}
			else {
				ilUtil::sendInfo($message, true);
			}
		}
		$this->g_ctrl->redirect($this->parent_gui, $this->parent_cmd);
	}

	/**
	 * @inheritdocs
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		if($usr_id === (int)$this->g_user->getId()) {
			return $this->g_lng->txt("booking");
		}

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("booking_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * @inheritdocs
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("booking_overview_description");
	}

	/**
	 * @inheritdocs
	 */
	protected function getConfirmButtonLabel() {
		return $this->g_lng->txt("booking_confirm");
	}

	/**
	 * Checks if a user is hierarchically under the current user.
	 *
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function checkIsSuperiorEmployeeBelowCurrent($usr_id) {
		$members_below = $this->getUserWhereCurrentCanBookFor($this->g_user->getId());
		return array_key_exists($usr_id, $members_below);
	}

	/**
	 * Checks user has booked trainings from same template
	 *
	 * @param int 	$crs_ref_id
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function duplicateCourseBooked($crs_ref_id, $usr_id) {
		$course = \ilObjectFactory::getInstanceByRefId($crs_ref_id);
		$template_id = $this->getTemplateIdOf($obj_id);

		$booked_courses = $this->getUnfinishedDuplicateBookedCoursesOfUser($course, $usr_id);
		$waiting = $this->getUnfnishedWaitingListCoursesOfUser($course, $usr_id);
		$courses = array_merge($booked_courses, $waiting);

		return count($courses) > 0;
	}

	/**
	 * Checks the user a parelle course to this he wants to book
	 *
	 * @param \ilObjCourse 	$course
	 * @param int 	$usr_id
	 * @return \ilObjCourse[]
	 */
	protected function getUnfinishedDuplicateBookedCoursesOfUser(\ilObjCourse $course, $usr_id)
	{
		assert('is_int($usr_id)');
		$start_date = $course->getCourseStart();
		if ($start_date === null) {
			return array();
		}

		$booked = $this->getUserBookedCourses($usr_id);
		return $this->filterDuplicateCourses((int)$course->getId(), $booked, $usr_id);
	}

	/**
	 * Checks the user a parelle course to this he wants to book
	 *
	 * @param \ilObjCourse 	$course
	 * @param int 	$usr_id
	 * @return \ilObjCourse[]
	 */
	protected function getUnfnishedWaitingListCoursesOfUser(\ilObjCourse $course, $usr_id)
	{
		assert('is_int($usr_id)');
		$start_date = $course->getCourseStart();
		if ($start_date === null) {
			return array();
		}

		$waitinglist_courses = $this->getUserWaitinglistCourses($usr_id);
		return $this->filterDuplicateCourses((int)$course->getId(), $waitinglist_courses, $usr_id);
	}

	/**
	 * Get courses where user is booked
	 *
	 * @param int	$usr_id
	 *
	 * @return \ilObjCourse[]
	 */
	protected function getUserBookedCourses($usr_id)
	{
		$ret = array();
		require_once("Services/Membership/classes/class.ilParticipants.php");
		foreach (\ilParticipants::_getMembershipByType($usr_id, "crs", true) as $crs_id) {
			$ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
			$ret[] = \ilObjectFactory::getInstanceByRefId($ref_id);
		}

		return $ret;
	}

	/**
	 * Get courses where user is on waiting list
	 *
	 * @param int	$usr_id
	 *
	 * @return \ilObjCourse[]
	 */
	protected function getUserWaitinglistCourses($usr_id)
	{
		$ret = array();
		require_once("Services/Membership/classes/class.ilWaitingList.php");
		foreach (\ilWaitingList::getIdsWhereUserIsOnList($usr_id) as $crs_id) {
			$ref_id = array_shift(\ilObject::_getAllReferences($crs_id));
			$ret[] = \ilObjectFactory::getInstanceByRefId($ref_id);
		}

		return $ret;
	}

	/**
	 * Filter courses with same template and not finished
	 *
	 * @param int 	$crs_id
	 * @param \ilObjCourse[] 	$courses
	 * @param int 	$usr_id
	 *
	 * @return \ilObjCourse[]
	 */
	protected function filterDuplicateCourses($crs_id, array $courses, $usr_id) {
		$template_id = $this->getTemplateIdOf($crs_id);

		return array_filter($courses, function ($course) use ($template_id, $usr_id) {
			$end_date = $course->getCourseEnd();
			if ($end_date === null) {
				return false;
			}

			if($this->courseFinished($end_date)) {
				return false;
			}

			$booked_template_id = $this->getTemplateIdOf((int)$course->getId());
			if(is_null($template_id) || is_null($booked_template_id)) {
				return false;
			}

			if ($booked_template_id != $template_id) {
				return false;
			}

			return true;
		});
	}


	/**
	 * Check user has passed course
	 *
	 * @param \ilDateTime 	$end_date
	 *
	 * @return bool
	 */
	protected function courseFinished($end_date) {
		$today = date("Y-m-d");
		return $end_date->get(IL_CAL_DATE) < $today;
	}

	/**
	 * Get template id of course
	 *
	 * @param int 	$crs_id
	 *
	 * @return int 	$template_id
	 */
	protected function getTemplateIdOf($crs_id)
	{
		$query = "SELECT source_id FROM crs_copy_mappings WHERE obj_id = ".$this->g_db->quote($crs_id, "integer");
		$res = $this->g_db->query($query);
		$row = $this->g_db->fetchAssoc($res);

		return $row["source_id"];
	}

}

/**
 * cat-tms-patch end
 */
