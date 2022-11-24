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


interface PRGAssignmentRepository
{
    public function createFor(
        int $prg_obj_id,
        int $usr_id,
        int $assigning_usr_id
    ): ilPRGAssignment;

    public function get(int $id): ilPRGAssignment;
    public function store(ilPRGAssignment $assignment): void;
    public function delete(ilPRGAssignment $assignment): void;
    public function deleteAllAssignmentsForProgrammeId(int $prg_obj_id): void;

    /**
     * get all assignments for a user
     * @return ilPRGAssignment[]
     */
    public function getForUser(int $usr_id): array;

    /**
    * get all assignments for all (or given) users,
    * where the given node is part of the assignment
    * @return ilPRGAssignment[]
    */
    public function getAllForNodeIsContained(
        int $prg_obj_id,
        array $user_filter = null,
        ilPRGAssignmentFilter $custom_filters = null
    ): array;

    /**
    * get all assignments for all (or given) users,
    * where the given node is the root-node of the assignment
    * @return ilPRGAssignment[]
    */
    public function getAllForSpecificNode(int $prg_obj_id, array $user_filter = null): array;

    /**
     * @return ilPRGAssignment[]
     */
    public function getPassedDeadline(\DateTimeImmutable $deadline): array;

    /**
     * @return ilPRGAssignment[]
     */
    public function getDashboardInstancesforUser(int $usr_id): array;

    /**
     * @param array <int $prg_obj_id, DateTimeImmutable $due>
     * @return ilPRGAssignment[]
     */
    public function getAboutToExpire(
        array $programmes_and_due,
        bool $discard_formerly_notified = true
    ): array;

    public function getExpiredAndNotInvalidated(): array;
}
