<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Events for the StudyProgramme.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilStudyProgrammeEvents {
	static protected $component = "Modules/StudyProgramme";
	static public $app_event_handler = null;
	
	static protected function initAppEventHandler() {
		if (self::$app_event_handler) {
			global $ilAppEventHandler;
			self::$app_event_handler = $ilAppEventHandler;
		}
	}
	
	static protected function raise($a_event, $a_parameter) {
		self::$app_event_handler->raise(self::$component, $a_event, $a_parameter);
	}
	
	static public function userAssigned(ilStudyProgrammeUserAssignment $a_assignment) {
		self::raise("userAssigned", array(
			
		));
	}
	
	static public function userDeassigned(ilStudyProgrammeUserAssignment $a_assignment) {
		self::raise("userDeassigned", array(
			
		));
	}
	
	static public function userSuccessful(ilStudyProgrammeUserProgress $a_progress) {
		self::raise("userSuccessful", array(
			
		));
	}
}

?>