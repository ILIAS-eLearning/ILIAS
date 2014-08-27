<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class ilGEVWaitingListPlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVWaitingList";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		switch ($a_component) {
			case "Services/CourseBooking":
				return $this->bookingEvent($a_event, $a_parameter);
			default:
				break;
		}
	}
	
	protected function bookingEvent($a_event, $a_parameter) {
		if ($a_event !== "setStatus") {
			return;
		}
		require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$os = $a_parameter["old_status"];
		$ns = $a_parameter["new_status"];
		$usr_id = intval($a_parameter["user_id"]);
		$crs_id = intval($a_parameter["crs_obj_id"]);
		$crs_utils = gevCourseUtils::getInstance($crs_id);

		if ($os == ilCourseBooking::STATUS_BOOKED && in_array($ns, array(ilCourseBooking::STATUS_CANCELLED_WITH_COSTS
															 , ilCourseBooking::STATUS_CANCELLED_WITHOUT_COSTS))) {
			$crs_utils->fillFreePlacesFromWaitingList();
		}
	}
}

?>