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
		if (self::$app_event_handler === null) {
			global $DIC;
			$ilAppEventHandler = $DIC['ilAppEventHandler'];
			self::$app_event_handler = $ilAppEventHandler;
		}
	}
	
	static protected function raise($a_event, $a_parameter) {
		self::initAppEventHandler();
		self::$app_event_handler->raise(self::$component, $a_event, $a_parameter);
	}
	
	static public function userAssigned(ilStudyProgrammeUserAssignment $a_assignment) {
		self::raise("userAssigned", array
			( "root_prg_id"		=> $a_assignment->getStudyProgramme()->getId()
			, "usr_id"			=> $a_assignment->getUserId()
			, "ass_id"			=> $a_assignment->getId()
			));
	}
	
	static public function userDeassigned(ilStudyProgrammeUserAssignment $a_assignment) {
		self::raise("userDeassigned", array
			( "root_prg_id"		=> $a_assignment->getStudyProgramme()->getId()
			, "usr_id"			=> $a_assignment->getUserId()
			, "ass_id"			=> $a_assignment->getId()
			));
	}
	
	static public function userSuccessful(ilStudyProgrammeUserProgress $a_progress) {
		$ass = $a_progress->getAssignment();
		self::raise("userSuccessful", array
			( "root_prg_id"		=> $ass->getStudyProgramme()->getId()
			, "prg_id"			=> $a_progress->getStudyProgramme()->getId()
			, "usr_id"			=> $ass->getUserId()
			, "ass_id"			=> $ass->getId()
			));
	}
}

?>