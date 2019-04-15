<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Events for the StudyProgramme.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilStudyProgrammeEvents {

	public function __construct(
		\ilAppEventHandler $app_event_handler,
		\ilStudyProgrammeAssignmentRepository $assignemnt_repo
	) {
		$this->app_event_handler = $app_event_handler;
		$this->assignemnt_repo = $assignemnt_repo;
	}

	const COMPONENT = "Modules/StudyProgramme";
	public $app_event_handler;

	protected function raise($a_event, $a_parameter) {
		$this->app_event_handler->raise(self::COMPONENT, $a_event, $a_parameter);
	}
	
	public function userAssigned(ilStudyProgrammeUserAssignment $a_assignment) {
		$this->raise("userAssigned", array
			( "root_prg_id"		=> $a_assignment->getStudyProgramme()->getId()
			, "usr_id"			=> $a_assignment->getUserId()
			, "ass_id"			=> $a_assignment->getId()
			));
	}
	
	public function userDeassigned(ilStudyProgrammeUserAssignment $a_assignment) {
		$this->raise("userDeassigned", array
			( "root_prg_id"		=> $a_assignment->getStudyProgramme()->getId()
			, "usr_id"			=> $a_assignment->getUserId()
			, "ass_id"			=> $a_assignment->getId()
			));
	}
	
	public function userSuccessful(ilStudyProgrammeUserProgress $a_progress) {
		$ass = $this->assignemnt_repo->read($a_progress->getAssignmentId());
		$this->raise("userSuccessful", array
			( "root_prg_id"		=> $ass->getRootId()
			, "prg_id"			=> $a_progress->getStudyProgramme()->getId()
			, "usr_id"			=> $ass->getUserId()
			, "ass_id"			=> $ass->getId()
			));
	}
}

?>