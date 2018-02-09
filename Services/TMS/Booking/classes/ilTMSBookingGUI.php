<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Displays the TMS superior booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
abstract class ilTMSBookingGUI {
	use \ILIAS\TMS\MyUsersHelper;

	const SELF_BOOKING = "self_booking";
	const SUPERIOR_BOOKING = "booking_superior";

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

	/**
	 * @var ilAppEventHandler
	 */
	protected $g_event_handler;



	final public function __construct($parent_gui, $parent_cmd, $execute_show = true) {
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->g_lng = $DIC->language();
		$this->g_db = $DIC->database();
		$this->g_event_handler = $DIC['ilAppEventHandler'];
		$this->g_tree = $DIC->repositoryTree();
		$this->g_objDefinition = $DIC["objDefinition"];
		$this->g_access = $DIC->access();
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
		assert('is_numeric($_GET["usr_id"]) || !in_array("usr_id", $_GET)');

		$crs_ref_id = (int)$_GET["crs_ref_id"];
		if(array_key_exists("usr_id", $_GET)) {
			$usr_id = (int)$_GET["usr_id"];
		} else {
			global $DIC;
			$usr_id = (int)$DIC->user()->getId();
		}

		$this->setParameter($crs_ref_id, $usr_id);

		$ilias_bindings = new Booking\ILIASBindings
			( $this->g_lng
			, $this->g_ctrl
			, $this
			, $this->parent_gui
			, $this->parent_cmd
			, $this->getPlayerTitle()
			, $this->getConfirmButtonLabel()
			, $this->getOverViewDescription()
			);

		$booking_allowed = $this->bookingAllowed($crs_ref_id, $usr_id);
		if(!$booking_allowed
			|| ((int)$this->g_user->getId() !== $usr_id && !$this->checkIsSuperiorEmployeeBelowCurrent($usr_id))
		) {
			$this->setParameter(null, null);
			return $ilias_bindings->redirectToPreviousLocation(array($this->g_lng->txt("no_permissions_to_book")), false);
		}

		$skip_duplicate_check = $this->duplicateStepsMayBeSkipped($crs_ref_id);
		if(!$skip_duplicate_check && $this->duplicateCourseBooked($crs_ref_id, $usr_id)) {
			$this->setParameter(null, null);
			return $ilias_bindings->redirectToPreviousLocation($this->getDuplicatedCourseMessage($usr_id), false);
		}

		global $DIC;
		$state_db = new Wizard\SessionStateDB();
		$wizard = new Booking\Wizard
			( $DIC
			, $this->getComponentClass()
			, (int)$this->g_user->getId()
			, $crs_ref_id
			, $usr_id
			, $this->getOnFinishClosure()
			);
		$player = new Wizard\Player
			( $ilias_bindings
			, $wizard
			, $state_db
			);

		$this->g_ctrl->setParameter($this->parent_gui, "s_user", $usr_id);

		$cmd = $this->g_ctrl->getCmd("start");
		$content = $player->run($cmd, $_POST);
		assert('is_string($content)');
		$this->g_tpl->setContent($content);
		if($this->execute_show) {
			$this->g_tpl->show();
		}
	}

	/**
	 * Set parameter for player
	 *
	 * @param int 	$crs_ref_id
	 * @param int 	$usr_id
	 *
	 * @return void
	 */
	abstract protected function setParameter($crs_ref_id, $usr_id);

	/**
	 * Execute this when the player is finished.
	 *
	 * @param int 	$acting_usr_id
	 * @param int 	$target_usr_id
	 * @param int 	$crs_ref_id
	 * @return void
	 */
	abstract protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);

	/**
	 * Wrap callOnFinish to be called from the Wizard.
	 *
	 * @return callable
	 */
	protected function getOnFinishClosure() {
		return function($acting_usr_id, $target_usr_id, $crs_ref_id) {
			return $this->callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);
		};
	}

	/**
	 * Lookup the course's obj_id.
	 * @param int 	$crs_ref_id
	 * @return int
	 */
	protected function lookupObjId($crs_ref_id) {
		assert('is_int($crs_ref_id)');
		$crs_obj_id = (int)\ilObject::_lookupObjId($crs_ref_id);
		return $crs_obj_id;
	}

	/**
	 * Get the title of the player.
	 *
	 * @return string
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"]) || !in_array("usr_id", $_GET)');
		if(in_array("usr_id", $_GET)) {
			$usr_id = (int)$_GET["usr_id"];
		} else {
			global $DIC;
			$usr_id = $DIC->user()->getId();
		}

		if($usr_id === (int)$this->g_user->getId()) {
			return $this->g_lng->txt("booking");
		}

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("booking_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * Get a description for the overview step.
	 *
	 * @return string
	 */
	protected function getOverViewDescription() {
		return $this->g_lng->txt("booking_overview_description");
	}

	/**
	 * Get the label for the confirm button.
	 *
	 * @return string
	 */
	protected function getConfirmButtonLabel() {
		return $this->g_lng->txt("booking_confirm");
	}

	/**
	 * Get the component class this GUI processes as steps.
	 *
	 * @return	string
	 */
	abstract protected function getComponentClass();

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
		$waiting = $this->getUnfinishedWaitingListCoursesOfUser($course, $usr_id);
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
	protected function getUnfinishedWaitingListCoursesOfUser(\ilObjCourse $course, $usr_id)
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

			if ($booked_template_id == $template_id
				&& !$this->isTemplateCourse($template_id)
			) {
				return false;
			}

			return true;
		});
	}

	/**
	 * Check the template course id is course with copy settings below
	 *
	 * @param int 	$crs_id
	 *
	 * @return bool
	 */
	protected function isTemplateCourse($crs_id) {
		if(!\ilPluginAdmin::isPluginActive("xcps")) {
			return false;
		}

		$query = "SELECT COUNT('obj_id') AS cnt FROM xcps_tpl_crs WHERE crs_id = ".$this->g_db->quote($crs_id, "integer");
		$res = $this->g_db->query($query);
		$row = $this->g_db->fetchAssoc($res);

		return (int)$row["cnt"] > 0;
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

	/**
	 * Get the failure message for duplicate courses
	 *
	 * @param int 	$usr_id
	 *
	 * @return string[]
	 */
	protected function getDuplicatedCourseMessage($usr_id) {
		return array($this->g_lng->txt("duplicate_course_booked"));
	}


	/**
	 * Raises an event with course ids and user id as params.
	 * @param string 	$event
	 * @param int 	$usr_id
	 * @param int 	$crs_ref_id
	 * @return void
	 */
	protected function fireBookingEvent($event, $usr_id, $crs_ref_id) {
		assert('is_string($event)');
		assert('is_int($usr_id)');
		assert('is_int($crs_ref_id)');

		$crs_obj_id = $this->lookupObjId($crs_ref_id);
		$this->g_event_handler->raise(
			'Modules/Course',
			$event,
			array(
				 'crs_ref_id' => $crs_ref_id,
				 'obj_id' => $crs_obj_id,
				 'usr_id' => $usr_id
			 )
		 );
	}

	/**
	 * Checks the duplicate function should be skipped
	 *
	 * @param int 	$crs_ref_id
	 *
	 * @return bool
	 */
	protected function duplicateStepsMayBeSkipped($crs_ref_id) {
		$xbkms = $this->getAllChildrenOfByType($crs_ref_id, "xbkm");
		foreach ($xbkms as $xbkm) {
			if($xbkm->getBooking()->getSkipDuplicateCheck()) {
				return true;
			}
		}
		return true;
	}

	/**
	 * Checks the duplicate function should be skipped
	 *
	 * @param int 	$crs_ref_id
	 * @param int 	$usr_id
	 *
	 * @return bool
	 */
	protected function bookingAllowed($crs_ref_id, $usr_id) {
		$xbkms = $this->getAllChildrenOfByType($crs_ref_id, "xbkm");

		if($usr_id == $this->g_user->getId()) {
			return $this->checkBookingAllowed($xbkms, self::SELF_BOOKING);
		} else {
			return $this->checkBookingAllowed($xbkms, self::SUPERIOR_BOOKING);
		}

		return false;
	}

	/**
	 * Checks there is any bkm where use has permission to self book
	 *
	 * @param ilObjBookingModalities[] 	$xbkms
	 * @param string 	$modus
	 *
	 * @return bool
	 */
	protected function checkBookingAllowed($xbkms, $modus) {
		foreach ($xbkms as $xbkm) {
			if($xbkm->getBooking()->getModus() == $modus
				&& $this->g_access->checkAccess("book_by_this", "", $xbkm->getRefId())
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get all children by type recursive
	 *
	 * @param int 	$ref_id
	 * @param string 	$search_type
	 *
	 * @return Object 	of search type
	 */
	protected function getAllChildrenOfByType($ref_id, $search_type) {
		$childs = $this->g_tree->getChilds($ref_id);
		$ret = array();

		foreach ($childs as $child) {
			$type = $child["type"];
			if($type == $search_type) {
				$ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
			}

			if($this->g_objDefinition->isContainer($type)) {
				$rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
				if(! is_null($rec_ret)) {
					$ret = array_merge($ret, $rec_ret);
				}
			}
		}

		return $ret;
	}
}

/**
 * cat-tms-patch end
 */
