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

/**
 * Class ilOrgUnitUserAssignmentQueries
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitUserAssignmentQueries
{
    protected static ilOrgUnitUserAssignmentQueries $instance;

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsOfUserId(int $user_id): array
    {
        $positions = [];
        foreach ($this->getAssignmentsOfUserId($user_id) as $assignment) {
            $positions[] = ilOrgUnitPosition::find($assignment->getPositionId());
        }

        return $positions;
    }

    /**
     * @throws ilException
     */
    public function getAssignmentOrFail(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        $ua = ilOrgUnitUserAssignment::where([
            'user_id' => $user_id,
            'position_id' => $position_id,
            'orgu_id' => $orgu_id,
        ])->first();
        if (!$ua) {
            throw new  ilException('UserAssignement not found');
        }

        assert($ua instanceof ilOrgUnitUserAssignment);
        return $ua;
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsOfUserId(int $user_id): array
    {
        return ilOrgUnitUserAssignment::where(['user_id' => $user_id])->get();
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getAssignmentsOfUserIdAndPosition(int $user_id, int $position_id): array
    {
        return ilOrgUnitUserAssignment::where(
            [
                'user_id' => $user_id,
                'position_id' => $position_id
            ]
        )->get();
    }

    /**
     * @param $orgunit_ref_id
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnit(int $orgunit_ref_id): array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $orgunit_ref_id])
                                      ->getArray(null, 'user_id');
    }

    /**
     * @param int[] $orgunit_ref_id
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnits(array $orgunit_ref_id): array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $orgunit_ref_id])
                                      ->getArray(null, 'user_id');
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnitsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false): array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $this->getOrgUnitIdsOfUsersPosition(
            $position_id,
            $user_id,
            $recursive
        )
        ])
                                      ->getArray(null, 'user_id');
    }

    /**
     * @param int[] $orgu_ids
     * @return int[]
     */
    public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, int $position_id): array
    {
        return ilOrgUnitUserAssignment::where([
            'orgu_id' => $orgu_ids,
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @param int[] $users_position_id
     * @return int[]
     */
    public function getUserIdsOfUsersOrgUnitsInPosition(
        int $user_id,
        array $users_position_id,
        int $position_id,
        bool $recursive = false
    ): array {
        return ilOrgUnitUserAssignment::where([
            'orgu_id' => $this->getOrgUnitIdsOfUsersPosition($users_position_id, $user_id, $recursive),
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @return int[]
     */
    public function getOrgUnitIdsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false): array
    {
        $orgu_ids = ilOrgUnitUserAssignment::where([
            'position_id' => $position_id,
            'user_id' => $user_id,
        ])->getArray(null, 'orgu_id');

        if (!$recursive) {
            return $orgu_ids;
        }

        $recursive_orgu_ids = [];
        $tree = ilObjOrgUnitTree::_getInstance();
        foreach ($orgu_ids as $orgu_id) {
            $recursive_orgu_ids = $recursive_orgu_ids + $tree->getAllChildren($orgu_id);
        }

        return $recursive_orgu_ids;
    }

    /**
     * @return int[]
     */
    public function getUserIdsOfPosition(int $position_id): array
    {
        return ilOrgUnitUserAssignment::where([
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserAssignmentsOfPosition(int $position_id): array
    {
        return ilOrgUnitUserAssignment::where([
            'position_id' => $position_id,
        ])->get();
    }

    /**
     * @param int $user_id
     * @return void
     */
    public function deleteAllAssignmentsOfUser(int $user_id): void
    {
        global $DIC;
        $q = "DELETE FROM il_orgu_ua WHERE user_id = " . $DIC->database()->quote($user_id, "integer");
        $DIC->database()->manipulate($q);
    }
}
