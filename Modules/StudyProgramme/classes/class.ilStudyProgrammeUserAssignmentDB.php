<?php

declare(strict_types=1);

class ilStudyProgrammeUserAssignmentDB
{
	/**
	 * @var ilStudyProgrammeUserProgressDB
	 */
	protected $sp_user_progress_db;

	/**
	 * @var ilStudyProgrammeAssignmentRepository
	 */
	protected $assignment_repository;

	/**
	 * @var ilStudyProgrammeProgressRepository
	 */
	protected $progress_repository;

	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var ilLogger
	 */
	protected $log;

    /**
     * @var ilStudyProgrammeEvents
     */
	protected $sp_events;

	public function __construct(
		ilStudyProgrammeUserProgressDB $sp_user_progress_db,
        ilStudyProgrammeAssignmentRepository $assignment_repository,
		ilStudyProgrammeProgressRepository $progress_repository,
		ilTree $tree,
		ilLogger $log,
		ilStudyProgrammeEvents $sp_events
	) {
		$this->sp_user_progress_db = $sp_user_progress_db;
		$this->assignment_repository = $assignment_repository;
		$this->progress_repository = $progress_repository;
		$this->tree = $tree;
		$this->log = $log;
		$this->sp_events = $sp_events;
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
			$this->log,
			$this->sp_events
		);
	}

	public function getInstanceByModel(\ilStudyProgrammeAssignment $assignment)
	{
		return new ilStudyProgrammeUserAssignment(
			$assignment,
			$this->sp_user_progress_db,
			$this->assignment_repository,
			$this->progress_repository,
			$this->log,
			$this->sp_events
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
						$this->log,
						$this->sp_events
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
				$this->log,
				$this->sp_events
			);

		}, array_values($assignments)); // use array values since we want keys 0...
	}

	/**
	 * @return ilStudyProgrammeUserAssignment[]
	 */
	public function getDueToRestartInstances() : array
	{
		return array_map(
			function($ass) {
				return new ilStudyProgrammeUserAssignment(
					$ass,
					$this->sp_user_progress_db,
					$this->assignment_repository,
					$this->progress_repository,
					$this->log,
					$this->sp_events
				);
			},
			$this->assignment_repository->readDueToRestart()
		);
	}

    /**
     * @return ilStudyProgrammeUserAssignment[]
     */
    public function getDueToRestartAndMail() : array
    {
        return array_map(
            function($ass) {
                return new ilStudyProgrammeUserAssignment(
                    $ass,
                    $this->sp_user_progress_db,
                    $this->assignment_repository,
                    $this->progress_repository,
                    $this->log,
                    $this->sp_events
                );
            },
            $this->assignment_repository->readDueToRestartAndMail()
        );
    }

    public function reminderSendFor(int $assignment_id) : void
    {
        $this->assignment_repository->reminderSendFor($assignment_id);
    }
}