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

interface OrgUnitUserAssignmentRepository
{
    /**
     * Get existing user assignment or create a new one
     */
    public function get(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment;

    /**
     * Find assignment for user, position and org-unit
     * Does not create new assigment, returns null if no assignment exists
     */
    public function find(int $user_id, int $position_id, int $orgu_id): ?ilOrgUnitUserAssignment;

    /**
     * Store assignment to db
     */
    public function store(ilOrgUnitUserAssignment $assignment): ilOrgUnitUserAssignment;

    /**
     * Delete a single assignment
     * Returns false if no assignment was found
     */
    public function delete(ilOrgUnitUserAssignment $assignment): bool;

    /**
     * Delete all assignments for a user_id
     * Returns false if no assignments were found
     */
    public function deleteByUser(int $user_id): bool;

    /**
     * Get assignments for one or more users
     *
     * @param int[] $user_ids
     * @return ilOrgUnitUserAssignment[]
     */
    public function getByUsers(array $user_ids): array;

    /**
     * Get all assignments for a position
     *
     * @return ilOrgUnitUserAssignment[]
     */
    public function getByPosition(int $position_id): array;

    /**
     * Get all assignments for an org-unit
     *
     * @return ilOrgUnitUserAssignment[]
     */
    public function getByOrgUnit(int $orgu_id): array;

    /**
     * Get assignments for a user in a dedicated position
     *
     * @return ilOrgUnitUserAssignment[]
     */
    public function getByUserAndPosition(int $user_id, int $position_id): array;

    /**
     * Get all users for a given set of org-units
     *
     * @param int[] $orgu_ids
     * @return int[]
     */
    public function getUsersByOrgUnits(array $orgu_ids): array;

    /**
     * Get all users with a certain position
     *
     * @return int[]
     */
    public function getUsersByPosition(int $position_id): array;

    /**
     * Get all users in a specific position for a given set of org-units
     *
     * @param int[] $orgu_ids
     * @return int[]
     */
    public function getUsersByOrgUnitsAndPosition(array $orgu_ids, int $position_id): array;

    /**
     * Get all users from org-units where the user has a certain position
     * i.e. all users from all org-units where the user is an employee
     *
     * @return int[]
     */
    public function getUsersByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array;

    /**
     * Get all users with position $position_filter_id from those org-units, where the user has position $position_id
     * i.e. all employees of all org-units where the user is a superior
     *
     * @return int[]
     */
    public function getFilteredUsersByUserAndPosition(int $user_id, int $position_id, int $position_filter_id, bool $recursive = false): array;

    /**
     * Get all org-units a user is assigned to
     *
     * @return int[]
     */
    public function getOrgUnitsByUser(int $user_id): array;

    /**
     * Get all org-units where a user has a dedicated position
     *
     * @return int[]
     */
    public function getOrgUnitsByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array;

    /**
     * Get all positions a user is assigned to
     *
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsByUser(int $user_id): array;

    /**
     * Get all superiors of one or more users
     * $user_id => [ $superior_ids ]
     *
     * @param int[] $user_ids
     * @return int[]
     */
    public function getSuperiorsByUsers(array $user_ids): array;
}
