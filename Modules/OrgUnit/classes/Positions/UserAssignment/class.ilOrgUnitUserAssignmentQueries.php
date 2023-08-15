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
declare(strict_types=1);

/**
 * Class ilOrgUnitUserAssignmentQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated Please use OrgUnitUserAssignmentRepository
 */
class ilOrgUnitUserAssignmentQueries extends ilOrgUnitUserAssignmentDBRepository
{
    protected static ilOrgUnitUserAssignmentQueries $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            global $DIC;
            self::$instance = new self($DIC["ilDB"]);
        }

        return self::$instance;
    }

    /**
     * @deprecated Please use getPositionsByUser() from OrgUnitUserAssignmentRepository
     */
    public function getPositionsOfUserId(int $user_id): array
    {
        return $this->getPositionsByUser($user_id);
    }

    /**
     * @deprecated Please use find() from OrgUnitUserAssignmentRepository
     * @throws ilException
     */
    public function getAssignmentOrFail(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        $assignment = $this->find($user_id, $position_id, $orgu_id);
        if (!$assignment) {
            throw new ilException('UserAssignment not found');
        }
        return $assignment;
    }

    /**
     * @deprecated Please use getByUsers() from OrgUnitUserAssignmentRepository
     */
    public function getAssignmentsOfUserId(int $user_id): array
    {
        return $this->getByUsers([$user_id]);
    }

    /**
     * @deprecated Please use getByUsers() from OrgUnitUserAssignmentRepository
     */
    public function getAssignmentsOfUserIds(array $user_ids): array
    {
        return $this->getByUsers($user_ids);
    }

    /**
     * @deprecated Please use getByUserAndPosition() from OrgUnitUserAssignmentRepository
     */
    public function getAssignmentsOfUserIdAndPosition(int $user_id, int $position_id): array
    {
        return $this->getByUserAndPosition($user_id, $position_id);
    }

    /**
     * @deprecated Please use getUsersByOrgUnits() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfOrgUnit(int $orgu_id): array
    {
        return $this->getUsersByOrgUnits([$orgu_id]);
    }

    /**
     * @deprecated Please use getUsersByOrgUnits() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfOrgUnits(array $orgu_ids): array
    {
        return $this->getUsersByOrgUnits($orgu_ids);
    }

    /**
     * @deprecated Please use getUsersByUserAndPosition() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfOrgUnitsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false): array
    {
        return $this->getUsersByUserAndPosition($user_id, $position_id, $recursive);
    }

    /**
     * @deprecated Please use getUsersByOrgUnitsAndPosition() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, int $position_id): array
    {
        return $this->getUsersByOrgUnitsAndPosition($orgu_ids, $position_id);
    }

    /**
     * @deprecated Please use getFilteredUsersByUserAndPosition() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfUsersOrgUnitsInPosition(int $user_id, int $users_position_id, int $position_id, bool $recursive = false): array
    {
        return $this->getFilteredUsersByUserAndPosition($user_id, $position_id, $users_position_id, $recursive);
    }

    /**
     * @deprecated Please use getOrgUnitsByUser() from OrgUnitUserAssignmentRepository
     */
    public function getOrgUnitIdsofUser(int $user_id): array
    {
        return $this->getOrgUnitsByUser($user_id);
    }

    /**
     * @deprecated Please use getOrgUnitsByUserAndPosition() from OrgUnitUserAssignmentRepository
     */
    public function getOrgUnitIdsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false): array
    {
        return $this->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);
    }

    /**
     * @deprecated Please use getUsersByPosition() from OrgUnitUserAssignmentRepository
     */
    public function getUserIdsOfPosition(int $position_id): array
    {
        return $this->getUsersByPosition($position_id);
    }

    /**
     * @deprecated Please use getByPosition() from OrgUnitUserAssignmentRepository
     */
    public function getUserAssignmentsOfPosition(int $position_id): array
    {
        return $this->getByPosition($position_id);
    }

    /**
     * @deprecated Please use deleteByUser() from OrgUnitUserAssignmentRepository
     */
    public function deleteAllAssignmentsOfUser(int $user_id): void
    {
        $this->deleteByUser($user_id);
    }
}
