<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Event listener for study programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all study programms when the
 *    user is removed.
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilStudyProgrammeAppEventListener {
	
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		switch ($a_component) {
			case "Services/User":
				switch ($a_event){
					case "deleteUser": 
						self::onServiceUserDeleteUser($a_parameter);
						break;
				}
				break;
			case "Services/Tracking":
				switch($a_event) {
					case "updateStatus":
						self::onServiceTrackingUpdateStatus($a_parameter);
				}
				break;
			default:
				throw new ilException("ilStudyProgrammeAppEventListener::handleEvent: "
									 ."Won't handle events of '$a_component'.");
		}
	}

	private function onServiceUserDeleteUser($a_parameter) {
		require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserAssignment.php");
		$assignments = ilStudyProgrammeUserAssignment::getInstancesOfUser($a_parameter["usr_id"]);
		foreach ($assignments as $ass) {
			$ass->remove();
		}
	}
	
	private function onServiceTrackingUpdateStatus($a_par) {
		require_once("./Services/Tracking/classes/class.ilLPStatus.php");
		if ($a_par["status"] != ilLPStatus::LP_STATUS_COMPLETED_NUM) {
			return;
		}
		
		require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
		ilObjStudyProgramme::setProgressesCompletedFor($a_par["obj_id"], $a_par["usr_id"]);
	}
}