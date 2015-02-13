<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeAssignment.php");

/**
 * Represents one assignment of a user to a training programme.
 *
 * A user could have multiple assignments per programme.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTrainingProgrammeUserAssignment {
	protected $assignment; // ilTrainingProgrammeAssignment
	
	/**
	 * Throws when id does not refer to a training programme assignment.
	 *
	 * @throws ilException
	 * @param int $a_id
	 */
	public function __construct($a_id) {
		$this->assigment = ilTrainingProgrammeAssignment::find($a_id);
		if ($this->assignment === null) {
			throw new ilException("ilTrainingProgrammeUserAssignment::__construct: "
								 ."Unknown assignemt id '$a_id'.");
		}
	}
	
	/**
	 * Get an instance. Just wraps constructor.
	 *
	 * @throws ilException
	 * @param  int $a_id
	 * @return ilTrainingProgrammeUserAssignment
	 */
	static public function getInstance($a_id) {
		return new ilTrainingProgrammeUserAssignment($a_id);
	}
	
	/**
	 * Get the id of the assignment.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->assignment->getId();
	}
	
	/**
	 * Get the training programme node where this assignment was made. 
	 *
	 * Throws when training programme this assignment is about has no ref id.
	 *
	 * @throws ilException
	 * @return ilObjTrainingProgramme
	 */
	public function getTrainingProgramme() {
		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");
		$refs = ilObject::_getAllReferences($this->assignment->getRootId());
		if (!count($refs)) {
			throw new ilException("ilTrainingProgrammeUserAssignment::getTrainingProgramme: "
								 ."could not find ref_id for training program '"
								 .$this->assignment->getRootId()."'.");
		}
		return ilObjTrainingProgramme::getInstance($refs[0]);
	}
	
	/**
	 * Remove this assignment.
	 */
	public function remove() {
		return $this->getTrainingProgramme()->removeAssignment($this);
	}
}

?>