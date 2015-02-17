<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Modules/TrainingProgramme/classes/model/class.ilTrainingProgrammeProgress.php");

/**
 * Represents the progress of a user at one node of a training programme.
 *
 * A user could have multiple progress' on one node, since he could also have
 * multiple assignments to one node.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilTrainingProgrammeUserProgress {
	protected $progress; // ilTrainingProgrammeProgress
	
	/**
	 * Throws when id does not refer to a training programme progress.
	 *
	 * Expects an array [assignment_id, program_node_id, user_id] or an
	 * ilTrainingProgress as first parameter.
	 *
	 * @throws ilException
	 * @param int[] | ilTrainingProgrammeAssignment $a_ids_or_model 
	 */
	public function __construct($a_ids_or_model) {
		/*if ($a_id_or_model instanceof ilTrainingProgrammeProgress) {
			$this->progress = $a_id_or_model;
		}
		else {
			// TODO: this won't work
			$this->progress = ilTrainingProgrammeProgress::find($a_id_or_model);
		}
		if ($this->progress === null) {
			throw new ilException("ilTrainingProgrammeUserProgress::__construct: "
								 ."Unknown progress id '$a_id'.");
		}*/
	}
	
	/**
	 * Get an instance. Just wraps constructor.
	 *
	 * @throws ilException
	 * @param  int $a_assignment_id
	 * @param  int $a_program_id
	 * @param  int $a_user_id
	 * @return ilTrainingProgrammeUserAssignment
	 */
	static public function getInstance($a_assignment_id, $a_programme_id, $a_user_id) {
		return new ilTrainingProgrammeUserAssignment(array($a_assignment_id, $a_programme_id, $a_user_id));
	}
	
	/**
	 * Get the program node where this progress belongs to was made. 
	 *
	 * Throws when program this assignment is about has no ref id.
	 *
	 * @throws ilException
	 * @return ilObjTrainingProgramme
	 */
	public function getTrainingProgramme() {
		require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgramme.php");
		$refs = ilObject::_getAllReferences($this->assignment->getRootId());
		if (!count($refs)) {
			throw new ilException("ilTrainingProgrammeUserAssignment::getTrainingProgramme: "
								 ."could not find ref_id for program '"
								 .$this->assignment->getRootId()."'.");
		}
		return ilObjTrainingProgramme::getInstanceByRefId(array_shift($refs));
	}
	
	/**
	 * Get the assignment this progress belongs to.
	 *
	 * @return ilTrainingProgrammeUserAssignment
	 */
	public function getAssignment() {
		
	}
	
	/**
	 * Get the id of the user who is assigned.
	 *
	 * @return int
	 */
	public function getUserId() {
		$this->assignment->getUserId();
	}
	
	/**
	 * Get the status of the progress.
	 *
	 * @return ilTrainingProgrammeProgress::$STATUS
	 */
	public function getStatus() {
		return $this->progress->getStatus();
	}
	
	/**
	 * Get the amount of points needed to complete the node. This is the amount
	 * of points yielded for the completion of the node above as well.
	 *
	 * @return int
	 */
	public function getAmountOfPoints() {
		return $this->progress->getAmountOfPoints();
	}
	
	/**
	 * Get the amount of points the user currently achieved.
	 *
	 * @return int
	 */
	public function getCurrentAmountOfPoints() {
		return $this->progress->getCurrentAmountOfPoints();
	}
	
	/**
	 * Get the id of the user who did the last change on this progress.
	 *
	 * @return int
	 */
	public function getLastChangeBy() {
		return $this->progress->getLastChangeBy();
	}
	
	/**
	 * Get the id of the user or course that lead to completion of this node.
	 *
	 * @return int | null
	 */
	public function getCompletionBy() {
		return $this->progress->getCompletionBy();
	}
	
	/**
	 * Remove this assignment.
	 */
	public function remove() {
		return $this->getTrainingProgramme()->removeAssignment($this);
	}
	
	/**
	 * Delete the assignment from database.
	 */
	public function delete() {
		$this->assignment->delete();
	}
}

?>