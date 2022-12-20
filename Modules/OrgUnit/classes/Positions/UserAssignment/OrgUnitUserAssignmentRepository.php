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
     * Gets existing user assignment or creates a new one
     */
    public function get(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment;

    public function find(int $user_id, int $position_id, int $orgu_id): ?ilOrgUnitUserAssignment;

    public function store(ilOrgUnitUserAssignment $assignment): ilOrgUnitUserAssignment;

    public function delete(int $assigment_id): void;

    public function deleteByUser(int $user_id): void;

    /**
     * @param int[] $user_ids
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsByUsers(array $user_ids): array;

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsByPosition(int $position_id): array;

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsByOrgUnit(int $orgu_id): array;

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsByUserAndPosition(int $user_id, int $position_id): array;

    /**
     * Get all users for a given set of org-units
     *
     * @param int[] $orgu_ids
     * @return int[]
     */
    public function getUsersByOrgUnits(array $orgu_ids): array;

    /**
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
     * @return int[]
     */
    public function getOrgUnitsByUser(int $user_id): array;

    /**
     * @return int[]
     */
    public function getOrgUnitsByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array;

    /**
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsByUser(int $user_id): array;

    /**
     * @param int[] $user_ids
     */
    public function getSuperiorsByUsers(array $user_ids): array;
}
