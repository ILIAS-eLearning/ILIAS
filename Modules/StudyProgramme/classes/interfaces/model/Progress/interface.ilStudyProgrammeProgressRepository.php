<?php declare(strict_types = 1);

/**
 * Covers the persistence of settings belonging to a study programme (SP).
 */
interface ilStudyProgrammeProgressRepository
{
	/**
	 * Create a record corresponding to a progress and return corresponding object.
	 * Will throw if a record allready exists.
	 */
	public function createFor(
		ilStudyProgrammeSettings $prg,
		ilStudyProgrammeAssignment $ass
	) : ilStudyProgrammeProgress;

	/**
	 * Load progress belonging to a id.
	 * Will throw if the record does not exist yet.
	 */
	public function read(int $id) : ilStudyProgrammeProgress;

	/**
	 * Load progress belonging to a prg id and assignment.
	 * Will throw if the record does not exist yet.
	 */
	public function readByIds(
		int $prg_id,
		int $assignment_id,
		int $usr_id
	) : ilStudyProgrammeProgress;

	/**
	 * Load progress belonging to a prg id and assignment.
	 * Will throw if the record does not exist yet.
	 */
	public function readByPrgIdAndAssignmentId(
		int $prg_id,
		int $assignment_id
	);

	/**
	 * Load progress objects belonging to a prg id and a user id.
	 */
	public function readByPrgIdAndUserId(int $prg_id, int $usr_id) : array;

	/**
	 * Load progress objects belonging to a prg id.
	 */
	public function readByPrgId(int $prg_id) : array;

	/**
	 * Load the first progress objects belonging to a prg id.
	 */
	public function readFirstByPrgId(int $prg_id);

	/**
	 * Load progress objects belonging to an assignment id.
	 * Will throw if the record does not exist yet.
	 */
	public function readByAssignmentId(int $assignment_id) : array;

	/**
	 * Update record corresponding to progress.
	 * Will throw if the record does not exist yet.
	 */
	public function update(ilStudyProgrammeProgress $progress);

	/**
	 * Delete record corresponding to progress.
	 * Will throw if the record does not exist yet.
	 */
	public function delete(ilStudyProgrammeProgress $progress);
}