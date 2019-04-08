<?php
require_once 'Modules/StudyProgramme/classes/class.ilStudyProgrammeDIC.php';
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
	/**
	 * Throws when id does not refer to a study programme assignment.
	 *
	 * @throws ilException
	 * @param int | ilStudyProgrammeAssignment $a_id_or_model
	 */
	public function __construct(
		$a_id_or_model,
		\ilStudyProgrammeUserProgressDB $sp_user_progress_db,
		\ilStudyProgrammeAssignmentRepository $assignment_repository
	) {
		if ($a_id_or_model instanceof ilStudyProgrammeAssignment) {
			$this->assignment = $a_id_or_model;
		}
		else {
			$this->assignment = $assignment_repository->read($a_id_or_model);
		}
		if ($this->assignment === null) {
			throw new ilException("ilStudyProgrammeUserAssignment::__construct: "
								 ."Unknown assignmemt id '$a_id_or_model'.");
		}
		$this->sp_user_progress_db = $sp_user_progress_db;
		$this->assignment_repository = $assignment_repository;
	}

	/**
	 * Get an instance. Just wraps constructor.
	 *
	 * @throws ilException
	 * @param  int $a_id
	 * @return ilStudyProgrammeUserAssignment
	 */
	static public function getInstance($a_id) {
		return new ilStudyProgrammeUserAssignment(
			$a_id,
			ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'],
			ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository']
		);
	}

	/**
	 * Get all instances for a given user.
	 *
	 * @param int $a_user_id
	 * @return ilStudyProgrammeUserAssignment[]
	 */
	static public function getInstancesOfUser($a_user_id) {
		global $DIC;
		$tree = $DIC['tree'];
		$assignment_repository = ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository'];
		$assignments = $assignment_repository->readByUsrId($a_user_id);

		//if parent object is deleted or in trash
		//the assignment for the user should not be returned
		$ret = array();
		foreach($assignments as $ass) {
			$ass_obj = new ilStudyProgrammeUserAssignment(
				$ass,
				ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'],
				$assignment_repository
			);
			foreach (ilObject::_getAllReferences($ass_obj->assignment->getRootId()) as $value) {
				if($tree->isInTree($value)) {
					$ret[] = $ass_obj;
					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * Get all assignments that were made to the given program.
	 *
	 * @param int $a_program_id
	 * @return ilStudyProgrammeUserAssignment[]
	 */
	static public function getInstancesForProgram($a_program_id) {
		$assignment_repository = ilStudyProgrammeDIC::dic()['model.Assignment.ilStudyProgrammeAssignmentRepository'];
		$assignments = $assignment_repository->readByPrgId($a_program_id);
		return array_map(function($ass) {
			return new ilStudyProgrammeUserAssignment(
				$ass,
				ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'],
				$assignment_repository
			);

		}, array_values($assignments)); // use array values since we want keys 0...
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
		$progress_repository = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository'];

		// Make $this->assignment protected again afterwards.
		$prg->applyToSubTreeNodes(function($node) use ($id, $progress_repository) {
			try {
				$node->getProgressForAssignment($id);
			}
			catch(ilStudyProgrammeNoProgressForAssignmentException $e) {
				global $DIC;
				$ilLog = $DIC['ilLog'];
				$ilLog->write("Adding progress for: ".$this->getId()." ".$node->getId());
				$progress_repository->update(
					$progress_repository->createFor($node->getRawSettings(), $this->assignment)
						->setStatus(ilStudyProgrammeProgress::STATUS_NOT_RELEVANT)
				);
			}
		});

		return $this;
	}
}

?>