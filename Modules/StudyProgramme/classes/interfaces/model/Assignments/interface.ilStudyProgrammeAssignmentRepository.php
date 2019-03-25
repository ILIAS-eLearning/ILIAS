<?php declare(strict_types = 1);

interface ilStudyProgrammeAssignmentRepository
{
	/**
	 * Create a record corresponding to a SP-Object and return represending settings.
	 * Will throw if a record allready exists.
	 */
	public function createFor(int $prg_id, int $usr_id, int $assigning_usr_id) : ilStudyProgrammeAssignment;

	/**
	 * Load settings belonging to a SP-Object.
	 * Will throw if the record does not exist yet.
	 */
	public function read(int $id) : ilStudyProgrammeAssignment;

	public function readByUsrId(int $usr_id) : array;
	public function readByPrgId(int $prg_id) : array;

	/**
	 * Update settings belonging to a SP-Object.
	 * Will throw if the record does not exist yet.
	 */
	public function update(ilStudyProgrammeAssignment $assignment);

	/**
	 * Delete record corresponding to settings.
	 * Will throw if the record does not exist yet.
	 */
	public function delete(ilStudyProgrammeAssignment $assignment);
}