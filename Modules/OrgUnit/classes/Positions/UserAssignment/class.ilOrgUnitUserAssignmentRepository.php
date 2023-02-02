<?php
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
 ********************************************************************
 */

namespace OrgUnit\Positions\UserAssignment;

use ilException;
use ilObjOrgUnitTree;
use ilOrgUnitUserAssignment;
use ilOrgUnitPosition;

class ilOrgUnitUserAssignmentRepository
{
    protected static self $instance;
    protected \ilOrgUnitPositionDBRepository $positionRepo;
    protected \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function getPositionRepo(): \ilOrgUnitPositionDBRepository
    {
        if (!isset($this->positionRepo)) {
            $dic = \ilOrgUnitLocalDIC::dic();
            $this->positionRepo = $dic["repo.Positions"];
        }

        return $this->positionRepo;
    }

    protected function getAssignmentRepo(): \ilOrgUnitUserAssignmentDBRepository
    {
        if (!isset($this->assignmentRepo)) {
            $dic = \ilOrgUnitLocalDIC::dic();
            $this->assignmentRepo = $dic["repo.UserAssignments"];
        }

        return $this->assignmentRepo;
    }

    /**
     * @deprecated Please use get() from ilOrgUnitUserAssignmentDBRepository
     */
    public function findOrCreateAssignment(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        return $this->getAssignmentRepo()->get($user_id, $position_id, $orgu_id);
    }

    /**
     * @deprecated Please use getByUsers() from ilOrgUnitUserAssignmentDBRepository
     */
    public function findAllUserAssingmentsByUserIds(array $arr_user_ids): array
    {
        $assignments = $this->getAssignmentRepo()->getByUsers($arr_user_ids);

        $user_assignment_list_by_user = [];
        foreach ($assignments as $user_assignment) {
            $user_assignment_list_by_user[$user_assignment->getUserId()][] = $user_assignment;
        }

        return $user_assignment_list_by_user;
    }

    /**
     * @deprecated Please use getSuperiorsByUsers() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getEmplSuperiorList(array $arr_empl_user_ids): array
    {
        return $this->getAssignmentRepo()->getSuperiorsByUsers($arr_empl_user_ids);
    }

    /**
     * @deprecated Please use getPositionsByUser() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getPositionsOfUserId(int $user_id): array
    {
        return $this->getAssignmentRepo()->getPositionsByUser($user_id);
    }

    /**
     * @deprecated Please use get() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getAssignmentOrFail(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        $assignment = $this->getAssignmentRepo()->get($user_id, $position_id, $orgu_id);
        if (!$assignment) {
            throw new  ilException('UserAssignment not found');
        }
        return $assignment;
    }

    /**
     * @deprecated Please use getByUsers() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getAssignmentsOfUserId(int $user_id): array
    {
        return $this->getAssignmentRepo()->getByUsers([$user_id]);
    }

    /**
     * @deprecated Please use getUsersByOrgUnits() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfOrgUnit(int $orgunit_ref_id): array
    {
        return $this->getAssignmentRepo()->getUsersByOrgUnits([$orgunit_ref_id]);
    }

    /**
     * @deprecated Please use getUsersByOrgUnits() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfOrgUnits(array $orgunit_ref_id): array
    {
        return $this->getAssignmentRepo()->getUsersByOrgUnits($orgunit_ref_id);
    }

    /**
     * @deprecated Please use getUsersByUserAndPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfOrgUnitsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false): array
    {
        return $this->getAssignmentRepo()->getUsersByUserAndPosition($user_id, $position_id, $recursive);
    }

    /**
     * @deprecated Please use getUsersByOrgUnitsAndPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, int $position_id): array
    {
        return $this->getAssignmentRepo()->getUsersByOrgUnitsAndPosition($orgu_ids, $position_id);
    }

    /**
     * @deprecated Please use getFilteredUsersByUserAndPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfUsersOrgUnitsInPosition(
        int $user_id,
        array $users_position_id,
        int $position_id,
        bool $recursive = false
    ): array {
        return $this->getAssignmentRepo()->getFilteredUsersByUserAndPosition($user_id, array_shift($users_position_id), $position_id, $recursive);
    }

    /**
     * @deprecated Please use getOrgUnitsByUserAndPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getOrgUnitIdsOfUsersPosition(
        int $position_id,
        int $user_id,
        bool $recursive = false
    ): array {
        return $this->getAssignmentRepo()->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);
    }

    /**
     * @deprecated Please use getUsersByPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserIdsOfPosition(int $position_id): array
    {
        return $this->getAssignmentRepo()->getUsersByPosition($position_id);
    }

    /**
     * @deprecated Please use getByPosition() from ilOrgUnitUserAssignmentDBRepository
     */
    public function getUserAssignmentsOfPosition(int $position_id): array
    {
        return $this->getAssignmentRepo()->getByPosition($position_id);
    }

    /**
     * @deprecated Please use deleteByUser() from ilOrgUnitUserAssignmentDBRepository
     */
    public function deleteAllAssignmentsOfUser(int $user_id): void
    {
        $this->getAssignmentRepo()->deleteByUser($user_id);
    }
}
