<?php
require_once("./Modules/StudyProgramme/classes/model/Progress/class.ilStudyProgrammeProgress.php");
require_once("./Modules/StudyProgramme/classes/interfaces/model/Progress/interface.ilStudyProgrammeProgressRepository.php");
require_once("./Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");

/**
 * Storage implementation for ilStudyProgrammeUserProgress.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 * @author : Nils Haagen <Nils Haagen@concepts-and-training.de>
 */
class ilStudyProgrammeUserProgressDB {

	public function __construct(ilStudyProgrammeProgressRepository $progress_repository)
	{
		$this->progress_repository = $progress_repository;
	}

	/**
	 * Get an instance. Just wraps constructor.
	 *
	 * @throws ilException
	 * @param  int $a_assignment_id
	 * @param  int $a_program_id
	 * @param  int $a_user_id
	 * @return ilStudyProgrammeUserProgress
	 */
	public function getInstance($a_assignment_id, $a_program_id, $a_user_id) {
		$prgrs = $this->progress_repository->readByIds($a_program_id,$a_assignment_id,$a_user_id);
		return new ilStudyProgrammeUserProgress($prgrs,$this->progress_repository);
	}

	/**
	 * Get an instance by progress id.
	 *
	 * @param  int $a_prgrs_id
	 * @return ilStudyProgrammeUserProgress
	 */
	public function getInstanceById($a_prgrs_id) {
		$prgrs = $this->progress_repository->read($a_prgrs_id);
		if ($prgrs === null) {
			throw new ilException("Unknown progress id $a_prgrs_id.");
		}
		return new ilStudyProgrammeUserProgress($prgrs,$this->progress_repository);
	}

	/**
	 * Get the instances that user has on program.
	 *
	 * @param  int $a_program_id
	 * @param  int $a_user_id
	 * @return ilStudyProgrammeUserProgress[]
	 */
	public function getInstancesForUser($a_program_id, $a_user_id) {
		$progresses = $this->progress_repository->readByPrgIdAndUserId($a_program_id,$a_user_id);
		return array_values(array_map(function($dat) {
			return new ilStudyProgrammeUserProgress($dat,$this->progress_repository);
		}, $progresses));
	}

	/**
	 * Get the instance for the assignment on the program.
	 *
	 * Throws when the node does not belong to the assignment.
	 *
	 * @throws ilException
	 * @param  int $a_program_id
	 * @param  int $a_user_id
	 * @return ilStudyProgrammeUserProgress
	 */
	public function getInstanceForAssignment($a_program_id, $a_assignment_id) {
		$progress = $this->progress_repository->readByPrgIdAndAssignmentId($a_program_id,$a_assignment_id);
		if (!$progress) {
			require_once("Modules/StudyProgramme/classes/exceptions/class.ilStudyProgrammeNoProgressForAssignmentException.php");
			throw new ilStudyProgrammeNoProgressForAssignmentException
								("ilStudyProgrammeUserProgress::getInstanceForAssignment: "
								."Assignment '$a_assignment_id' does not belong to program "
								."'$a_program_id'");
		}
		return new ilStudyProgrammeUserProgress($progress,$this->progress_repository);
	}

	/**
	 * Get the instance for an assignment.
	 *
	 * Throws when the node does not belong to the assignment.
	 *
	 * @throws ilException
	 * @param  int $a_program_id
	 * @param  int $a_user_id
	 * @return ilStudyProgrammeUserProgress
	 */
	public function getInstancesForAssignment($a_assignment_id) {
		$progresses = $this->progress_repository->readByAssignmentId($a_assignment_id);
		if (count($progresses) == 0) {
			require_once("Modules/StudyProgramme/classes/exceptions/class.ilStudyProgrammeNoProgressForAssignmentException.php");
			throw new ilStudyProgrammeNoProgressForAssignmentException
								("ilStudyProgrammeUserProgress::getInstancesForAssignment: "
								."Can't find progresses for assignment '$a_assignment_id'.");
		}
		return array_map(function($dat) {
			return new ilStudyProgrammeUserProgress($dat,$this->progress_repository);
		}, $progresses);
	}

	/**
	 * Get the instances for a program node.
	 *
	 * @param int $a_program_id
	 * @return ilStudyProgrammeUserProgress[]
	 */
	public function getInstancesForProgram($a_program_id) {
		$progresses = $this->progress_repository->readByPrgId($a_program_id);
		return array_values(array_map(function($dat) {
			return new ilStudyProgrammeUserProgress($dat,$this->progress_repository);
		}, $progresses));
	}

	/**
	 * Get a user readable representation of a status.
	 */
	public function statusToRepr($a_status) {
		global $DIC;
		$lng = $DIC['lng'];
		$lng->loadLanguageModule("prg");

		if ($a_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
			return $lng->txt("prg_status_in_progress");
		}
		if ($a_status == ilStudyProgrammeProgress::STATUS_COMPLETED) {
			return $lng->txt("prg_status_completed");
		}
		if ($a_status == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
			return $lng->txt("prg_status_accredited");
		}
		if ($a_status == ilStudyProgrammeProgress::STATUS_NOT_RELEVANT) {
			return $lng->txt("prg_status_not_relevant");
		}
		if ($a_status == ilStudyProgrammeProgress::STATUS_FAILED) {
			return $lng->txt("prg_status_failed");
		}
		throw new ilException("Unknown status: '$a_status'");
	}

}
