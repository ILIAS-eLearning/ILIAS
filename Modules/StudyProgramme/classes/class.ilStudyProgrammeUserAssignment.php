<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


/**
 * Represents one assignment of a user to a study programme.
 *
 * A user could have multiple assignments per programme.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilStudyProgrammeUserAssignment {
	public $assignment; // ilStudyProgrammeAssignment

	/**
	 * @var ilStudyProgrammeUserProgressDB
	 */
	private $sp_user_progress_db;
	protected $assignment_repository;
	protected $progress_repository;
	protected $log;
	/**
	 * Throws when id does not refer to a study programme assignment.
	 *
	 * @throws ilException
	 * @param int | ilStudyProgrammeAssignment $a_id_or_model
	 */
	public function __construct(
		ilStudyProgrammeAssignment $assignment,
		\ilStudyProgrammeUserProgressDB $sp_user_progress_db,
		\ilStudyProgrammeAssignmentRepository $assignment_repository,
		\ilStudyProgrammeProgressRepository $progress_repository,
		\ilLogger $log
	) {
		$this->assignment = $assignment;
		$this->sp_user_progress_db = $sp_user_progress_db;
		$this->assignment_repository = $assignment_repository;
		$this->progress_repository = $progress_repository;
		$this->log = $log;
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
	 * Get the program node where this assignment was made.
	 *
	 * Throws when program this assignment is about has no ref id.
	 *
	 * @throws ilException
	 * @return ilObjStudyProgramme
	 */
	public function getStudyProgramme() {
		$refs = ilObject::_getAllReferences($this->assignment->getRootId());
		if (!count($refs)) {
			throw new ilException("ilStudyProgrammeUserAssignment::getStudyProgramme: "
								 ."could not find ref_id for program '"
								 .$this->assignment->getRootId()."'.");
		}
		return ilObjStudyProgramme::getInstanceByRefId(array_shift($refs));
	}

	/**
	 * Get the progress on the root node of the programme.
	 *
	 * @throws ilException
	 * @return ilStudyProgrammeUserProgress
	 */
	public function getRootProgress() {
		return $this->getStudyProgramme()->getProgressForAssignment($this->getId());
	}

	/**
	 * Get the id of the user who is assigned.
	 *
	 * @return int
	 */
	public function getUserId() {
		return $this->assignment->getUserId();
	}

	/**
	 * Remove this assignment.
	 */
	public function deassign() {
		$this->getStudyProgramme()->removeAssignment($this);
	}

	/**
	 * Delete the assignment from database.
	 */
	public function delete() {
		$progresses = $this->sp_user_progress_db->getInstancesForAssignment($this->getId());
		foreach ($progresses as $progress) {
			$progress->delete();
		}
		$this->assignment_repository->delete(
			$this->assignment
		);
	}

	/**
	 * Update all unmodified nodes in this assignment to the current state
	 * of the program.
	 *
	 * @return $this
	 */
	public function updateFromProgram() {
		$prg = $this->getStudyProgramme();
		$id = $this->getId();

		$prg->applyToSubTreeNodes(function($node) use ($id) {
			/**
			 * @var ilObjTrainingProgramme $node
			 * @var ilTrainingProgrammeUserProgress $progress
			 */
			$progress = $node->getProgressForAssignment($id);
			return $progress->updateFromProgramNode();
		});

		return $this;
	}

	/**
	 * Add missing progresses for new nodes in the programm.
	 *
	 * The new progresses will be set to not relevant.
	 *
	 * @return $this
	 */
	public function addMissingProgresses() {
		$prg = $this->getStudyProgramme();
		$id = $this->getId();
		$log = $this->log;
		$progress_repository = $this->progress_repository;
		$assignment = $this->assignment;
		// Make $this->assignment protected again afterwards.
		$prg->applyToSubTreeNodes(
				function($node) use ($id,$log,$progress_repository,$assignment) {
					try {
						$node->getProgressForAssignment($id);
					}
					catch(ilStudyProgrammeNoProgressForAssignmentException $e) {
						$log->write("Adding progress for: ".$id." ".$node->getId());
						$progress_repository->update(
							$progress_repository->createFor(
								$node->getRawSettings(),
								$assignment
							)->setStatus(
								ilStudyProgrammeProgress::STATUS_NOT_RELEVANT
							)
						);
					}
				}
		);

		return $this;
	}
}

?>