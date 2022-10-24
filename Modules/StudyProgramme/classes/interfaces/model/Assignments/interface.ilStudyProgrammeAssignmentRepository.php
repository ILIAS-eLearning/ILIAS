<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

interface ilStudyProgrammeAssignmentRepository
{
    /**
     * Create a record corresponding to a SP-Object and return represending settings.
     * Will throw if a record allready exists.
     */
    public function createFor(int $prg_id, int $usr_id, int $assigning_usr_id): ilStudyProgrammeAssignment;

    /**
     * Load settings belonging to a SP-Object.
     * Will throw if the record does not exist yet.
     */
    public function get(int $id): ?ilStudyProgrammeAssignment;

    /**
     * Get all assignments of a user.
     * @return ilStudyProgrammeAssignment[]
     */
    public function getByUsrId(int $usr_id): array;

    /**
     * Get all assignments to a prg.
     * @return ilStudyProgrammeAssignment[]
     */
    public function getByPrgId(int $prg_id): array;

    /**
     * Get all assignments due to restart and not restrted yet.
     *
     * @return ilStudyProgrammeAssignment[]
     */
    public function getDueToRestart(): array;

    /**
     * Get all assignments due to restart and not restrted yet.
     * @return ilStudyProgrammeAssignment[]
     */
    public function getDueToManuelRestart(int $days_before_end): array;

    /**
     * Update settings belonging to a SP-Object.
     * Will throw if the record does not exist yet.
     */
    public function update(ilStudyProgrammeAssignment $assignment): void;

    /**
     * Delete record corresponding to settings.
     * Will throw if the record does not exist yet.
     */
    public function delete(ilStudyProgrammeAssignment $assignment): void;
}
