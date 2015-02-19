<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Event listener for training programs. Has the following tasks:
 *
 *  * Remove all assignments of a user on all training programms when the
 *    user is removed.
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilTrainingProgrammeAppEventListener {
	
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		switch ($a_component) {
			case 'Services/User':
				switch ($a_event){
					case 'deleteUser': 
						self::onServiceUserDeleteUser($a_parameter);
						break;
				}
				break;
			default:
				throw new ilException("ilTrainingProgrammeAppEventListener::handleEvent: "
									 ."Won't handle events of '$a_component'.");
		}
	}

	private function onServiceUserDeleteUser($a_parameter) {
		require_once("./Modules/TrainingProgramme/classes/class.ilTrainingProgrammeUserAssignment.php");
		$assignments = ilTrainingProgrammeUserAssignment::getInstancesOfUser($a_parameter["usr_id"]);
		foreach ($assignments as $ass) {
			$ass->remove();
		}
	}
	

}