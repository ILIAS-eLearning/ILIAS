<?php declare(strict_types = 1);

class ilStudyProgrammeUserAssignmentDB
{
	public function __construct(
		\ilStudyProgrammeUserProgressDB $sp_user_progress_db,
		\ilStudyProgrammeAssignmentRepository $assignment_repository,
		\ilStudyProgrammeProgressRepository $progress_repository,
		\ilTree $tree,
		\ilLogger $log
	)
	{
		$this->sp_user_progress_db = $sp_user_progress_db;
		$this->assignment_repository = $assignment_repository;
		$this->progress_repository = $progress_repository;
		$this->tree = $tree;
		$this->log = $log;

	}

	public function getInstanceById(int $id)
	{
		$assignment = $this->assignment_repository->read($id);
		if ($assignment === null) {
			throw new ilException("ilStudyProgrammeUserAssignment::__construct: "
								 ."Unknown assignmemt id '$id'.");
		}
		return new ilStudyProgrammeUserAssignment(
			$assignment,
			$this->sp_user_progress_db,
			$this->assignment_repository,
			$this->progress_repository,
			$this->log
		);
	}

	public function getInstanceByModel(\ilStudyProgrammeAssignment $assignment)
	{
		return new ilStudyProgrammeUserAssignment(
			$assignment,
			$this->sp_user_progress_db,
			$this->assignment_repository,
			$this->progress_repository,
			$this->log
		);
	}

	public function getInstancesOfUser(int $user_id)
	{
		$assignments = $this->assignment_repository->readByUsrId($user_id);

		//if parent object is deleted or in trash
		//the assignment for the user should not be returned
		$ret = [];
		foreach($assignments as $ass) {
			foreach (ilObject::_getAllReferences($ass->getRootId()) as $value) {
				if($this->tree->isInTree($value)) {
					$ret[] = new ilStudyProgrammeUserAssignment(
						$ass,
						$this->sp_user_progress_db,
						$this->assignment_repository,
						$this->progress_repository,
						$this->log
					);
					continue 2;
				}
			}

		}
		return $ret;
	}

	public function getInstancesForProgram(int $program_id)
	{
		$assignments = $this->assignment_repository->readByPrgId($program_id);
		return array_map(function($ass) {
			return new ilStudyProgrammeUserAssignment(
				$ass,
				$this->sp_user_progress_db,
				$this->assignment_repository,
				$this->progress_repository,
				$this->log
			);

		}, array_values($assignments)); // use array values since we want keys 0...
	}
}