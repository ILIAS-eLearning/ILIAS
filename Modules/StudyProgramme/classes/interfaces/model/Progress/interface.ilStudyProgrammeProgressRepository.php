<?php declare(strict_types=1);

/**
 * Covers the persistence of settings belonging to a study programme (SP).
 */
interface ilStudyProgrammeProgressRepository
{
    /**
     * Create a record corresponding to a progress and return corresponding object.
     * Will throw if a record already exists.
     */
    public function createFor(
        ilStudyProgrammeSettings $prg,
        ilStudyProgrammeAssignment $ass
    ) : ilStudyProgrammeProgress;

    /**
     * Load progress belonging to a id.
     * Will throw if the record does not exist yet.
     */
    public function get(int $id) : ilStudyProgrammeProgress;

    /**
     * Load progress belonging to a prg id and assignment.
     * Will throw if the record does not exist yet.
     */
    public function getByIds(
        int $prg_id,
        int $assignment_id
    ) : ilStudyProgrammeProgress;

    /**
     * Load progress belonging to a prg id and assignment.
     * Will throw if the record does not exist yet.
     *
     * @return ilStudyProgrammeProgress|void
     */
    public function getByPrgIdAndAssignmentId(
        int $prg_id,
        int $assignment_id
    );

    /**
     * Load progress objects belonging to a prg id and a user id.
     */
    public function getByPrgIdAndUserId(int $prg_id, int $usr_id) : array;

    /**
     * Load progress objects belonging to a prg id.
     */
    public function getByPrgId(int $prg_id) : array;

    /**
     * Load the first progress objects belonging to a prg id.
     *
     * @return ilStudyProgrammeProgress|void
     */
    public function getFirstByPrgId(int $prg_id);

    /**
     * Load progress objects belonging to an assignment id.
     * Will throw if the record does not exist yet.
     */
    public function getByAssignmentId(int $assignment_id) : array;

    /**
     * Load all progress objects which are successfull and whose
     * validity is expired.
     */
    public function getExpiredSuccessfull() : array;

    public function getRiskyToFailInstances() : array;

    public function getPassedDeadline() : array;

    /**
     * Update record corresponding to progress.
     * Will throw if the record does not exist yet.
     */
    public function update(ilStudyProgrammeProgress $progress) : void;

    /**
     * Delete record corresponding to progress.
     * Will throw if the record does not exist yet.
     */
    public function delete(ilStudyProgrammeProgress $progress) : void;
}
