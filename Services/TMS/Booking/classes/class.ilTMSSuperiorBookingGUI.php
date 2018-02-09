<?php
/**
 * cat-tms-patch start
 */

use ILIAS\TMS\Booking;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/TMS/Booking/classes/ilTMSBookingGUI.php");

/**
 * Displays the TMS superior booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTMSSuperiorBookingGUI extends \ilTMSBookingGUI {
	/**
	 * @inheritdocs
	 */
	protected function getComponentClass() {
		return Booking\SuperiorBookingStep::class;
	}

	/**
	 * @inheritdocs
	 */
	protected function setParameter($crs_ref_id, $usr_id) {
		assert('is_int($crs_ref_id) || is_null($crs_ref_id)');
		assert('is_int($usr_id) || is_null($usr_id)');

		$this->g_ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "crs_ref_id", $crs_ref_id);
		$this->g_ctrl->setParameterByClass("ilTMSSuperiorBookingGUI", "usr_id", $usr_id);
	}

	/**
	 * @inheritdocs
	 */
	protected function getPlayerTitle() {
		assert('is_numeric($_GET["usr_id"])');
		$usr_id = (int)$_GET["usr_id"];

		require_once("Services/User/classes/class.ilObjUser.php");
		return sprintf($this->g_lng->txt("booking_for"), ilObjUser::_lookupFullname($usr_id));
	}

	/**
	 * @inheritdocs
	 */
	protected function getDuplicatedCourseMessage($usr_id) {
		return array(sprintf($this->g_lng->txt("superior_duplicate_course_booked"), ilObjUser::_lookupFullname($usr_id)));
	}

	/**
	 * @inheritdoc
	 */
	protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id){
		$event = Booking\Actions::EVENT_SUPERIOR_BOOKED_COURSE;
		$this->fireBookingEvent($event, $target_usr_id, $crs_ref_id);
	}
}

/**
 * cat-tms-patch end
 */
