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
		// TODO: Check if current user may book course for other user here.
		assert('$this->g_user->getId() === $_GET["usr_id"]');

		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];
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

	/**
	 * Redirects the user to the previous location with a message if he already has
	 * booked a course in the period of the course in $_GET["crs_ref_id"].
	 *
	 * @return void
	 */
	public function redirectOnParallelCourses() {
		assert('$this->g_user->getId() === $_GET["usr_id"]');

		assert('is_numeric($_GET["crs_ref_id"])');
		assert('is_numeric($_GET["usr_id"])');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		$usr_id = (int)$_GET["usr_id"];

		$parallel_courses = $this->getParallelCoursesOfUser($crs_ref_id, $usr_id);
		if (count($parallel_courses) > 0) {
			$message = $this->getParallelCourseMessage($parallel_courses);
			$this->redirectToPreviousLocation(array($message), false);
		}
	}

	/**
	 * Checks the user a parelle course to this he wants to book
	 *
	 * @param	int		$crs_ref_id
	 * @param	int		$usr_id
	 * @return	\ilObjCourse[]
	 */
	protected function getParallelCoursesOfUser($crs_ref_id, $usr_id) {
		assert('is_int($crs_ref_id)');
		assert('is_int($usr_id)');
		$booked_courses = $this->getUserBookedCourses($usr_id);
		$try_to_book_course = \ilObjectFactory::getInstanceByRefId($crs_ref_id);
		$parallel_courses = $this->getParallelCourses($try_to_book_course, $booked_courses);

		return $parallel_courses;
	}

	/**
	 * Get courses where user is booked
	 *
	 * @return	int	$usr_id
	 * @return \ilObjCourse[]
	 */
	protected function getUserBookedCourses($usr_id) {
		$ret = array();
		require_once("Services/Membership/classes/class.ilParticipants.php");
		foreach(\ilParticipants::_getMembershipByType($usr_id, "crs", true) as $crs_id) {
			$ret[] = \ilObjectFactory::getInstanceByObjId($crs_id);
		}

		return $ret;
	}

	/**
	 * Get courses running parallel
	 *
	 * @param \ilObjCourse 	$try_to_book_course
	 * @param \ilObjCourse[] 	$booked_courses
	 *
	 * @return \ilObjCourse[]
	 */
	protected function getParallelCourses(\ilObjCourse $try_to_book_course, array $booked_courses) {
		if($try_to_book_course->getCourseStart() === null) {
			return array();
		}

		$try_start = $try_to_book_course->getCourseStart()->get(IL_CAL_DATE);
		$try_end = $try_to_book_course->getCourseEnd()->get(IL_CAL_DATE);

		return array_filter($booked_courses, function($course) use ($try_start, $try_end) {
			if($course->getCourseStart() === null) {
				return false;
			}

			$course_start = $course->getCourseStart()->get(IL_CAL_DATE);
			$course_end = $course->getCourseEnd()->get(IL_CAL_DATE);

			return
				   ($try_start <= $course_start && $try_end >= $course_start)
				|| ($try_start >= $course_start && $try_start <= $course_end);
		});
	}

	/**
	 * Get message to display
	 *
	 * @param \ilObjCourse[] 	$parallel_courses
	 *
	 * @return string
	 */
	protected function getParallelCourseMessage(array $parallel_courses) {
		$tpl = new \ilTemplate("tpl.parallel_courses.html", true, true, "Services/TMS");

		$tpl->setVariable("BOOKING_NOT_POSSIBLE", $this->g_lng->txt("booking_not_possible"));
		$tpl->setVariable("CUTTING_TRAININGS", $this->g_lng->txt("cutting_trainings"));
		$tpl->setVariable("INFO_CAN_STORNO", $this->g_lng->txt("info_can_storno"));

		foreach ($parallel_courses as $key => $parallel_course) {
			$course_start = $this->formatDate($parallel_course->getCourseStart());
			$course_end = $this->formatDate($parallel_course->getCourseEnd());

			$tpl->setCurrentBlock("crs");
			$tpl->setVariable("CRS_TITLE", $parallel_course->getTitle());
			$tpl->setVariable("CRD_PERIOD", $course_start." - ".$course_end);
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	 * Form date.
	 *
	 * @param ilDateTime 	$dat
	 * @param bool 	$use_time
	 *
	 * @return string
	 */
	protected function formatDate(\ilDateTime $date) {
		global $DIC;
		$g_user = $DIC->user();
		require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
		$out_format = ilCalendarUtil::getUserDateFormat($use_time, true);
		$ret = $date->get(IL_CAL_FKT_DATE, $out_format, $g_user->getTimeZone());
		if(substr($ret, -5) === ':0000') {
			$ret = substr($ret, 0, -5);
		}

		return $ret;
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
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "crs_ref_id", null);
		$this->g_ctrl->setParameterByClass("ilTMSBookingGUI", "usr_id", null);
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
		return $this->g_lng->txt("booking");
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

}

/**
 * cat-tms-patch end
 */
