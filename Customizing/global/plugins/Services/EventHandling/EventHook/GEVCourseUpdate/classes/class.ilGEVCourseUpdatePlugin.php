<?php

require_once("./Services/EventHandling/classes/class.ilEventHookPlugin.php");

class ilGEVCourseUpdatePlugin extends ilEventHookPlugin
{
	final function getPluginName() {
		return "GEVCourseUpdate";
	}
	
	final function handleEvent($a_component, $a_event, $a_parameter) {
		if ($a_component !== "Modules/Course" || $a_event !== "update") {
			return;
		}
		
		$this->updatedCourses($a_parameter["object"], $a_parameter["obj_id"]);
	}

	public function updatedCourses($a_crs, $a_crs_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		global $ilLog;

		try {
			$utils = gevCourseUtils::getInstance($a_crs_id);

			$a_crs->enableWaitingList($utils->getWaitingListActive());
			$a_crs->enableSubscriptionMembershipLimitation($utils->getWaitingListActive());
			$a_crs->setSubscriptionMaxMembers(intval($utils->getMaxParticipants()));

			$a_crs->update(false);
		}
		catch (Exception $e) {
			$ilLog->write("Error in GEVCourseUpdate::updatedCourses: ".print_r($e, true));
		}
	}
}

?>