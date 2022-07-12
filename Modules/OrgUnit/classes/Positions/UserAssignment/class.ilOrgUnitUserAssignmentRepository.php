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

    public static function getInstance() : self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function findOrCreateAssignment(int $user_id, int $position_id, int $orgu_id) : ilOrgUnitUserAssignment
    {
        $user_assignment = ilOrgUnitUserAssignment::where(array(
            'user_id' => $user_id,
            'position_id' => $position_id,
            'orgu_id' => $orgu_id,
        ))->first();
        if (!$user_assignment) {
            $user_assignment = new ilOrgUnitUserAssignment();
            $user_assignment->setPositionId($position_id);
            $user_assignment->setUserId($user_id);
            $user_assignment->setOrguId($orgu_id);
            $user_assignment->create();
        }

        return $user_assignment;
    }

    /**
     * @param $arr_user_ids []
     * @return ilOrgUnitUserAssignment[][] [user_id][][$user_assignment]
     */
    public function findAllUserAssingmentsByUserIds(array $arr_user_ids) : array
    {
        $user_assignment_list = ilOrgUnitUserAssignment::where(['user_id' => $arr_user_ids], 'IN')->get();

        $user_assignment_list_by_user = [];
        foreach ($user_assignment_list as $user_assignment) {
            $user_assignment_list_by_user[$user_assignment->getUserId()][] = $user_assignment;
        }

        return $user_assignment_list_by_user;
    }

    /**
     * @param int[] $arr_empl_user_ids
     * @return int[][] [user_id as an employee][][ user_id as a superior]
     */
    public function getEmplSuperiorList(array $arr_empl_user_ids) : array
    {
        global $DIC;

        $sql = "SELECT 
				orgu_ua.orgu_id AS orgu_id,
				orgu_ua.user_id AS empl,
				orgu_ua2.user_id as sup
				FROM
				il_orgu_ua as orgu_ua,
				il_orgu_ua as orgu_ua2
				WHERE
				orgu_ua.orgu_id = orgu_ua2.orgu_id 
				and orgu_ua.user_id <> orgu_ua2.user_id 
				and orgu_ua.position_id = " . ilOrgUnitPosition::CORE_POSITION_EMPLOYEE . "
				and orgu_ua2.position_id = " . ilOrgUnitPosition::CORE_POSITION_SUPERIOR . " 
				AND " . $DIC->database()->in('orgu_ua.user_id', $arr_empl_user_ids, false, 'integer');

        $st = $DIC->database()->query($sql);

        $empl_id__sup_ids = [];
        while ($data = $DIC->database()->fetchAssoc($st)) {
            $empl_id__sup_ids[$data['empl']][] = $data['sup'];
        }
        $this->arr_empl_user_ids = $empl_id__sup_ids;

        return $empl_id__sup_ids;
    }

    /**
     * @param int $user_id
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsOfUserId(int $user_id) : array
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
    public function getAssignmentOrFail(int $user_id, int $position_id, int $orgu_id) : ilOrgUnitUserAssignment
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
    public function getAssignmentsOfUserId(int $user_id) : array
    {
        return ilOrgUnitUserAssignment::where(['user_id' => $user_id])->get();
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnit(int $orgunit_ref_id) : array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $orgunit_ref_id])->getArray(null, 'user_id');
    }

    /**
     * @param int[] $orgunit_ref_id
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnits(array $orgunit_ref_id) : array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $orgunit_ref_id])->getArray(null, 'user_id');
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserIdsOfOrgUnitsOfUsersPosition(int $position_id, int $user_id, bool $recursive = false) : array
    {
        return ilOrgUnitUserAssignment::where(['orgu_id' => $this->getOrgUnitIdsOfUsersPosition($position_id, $user_id,
            $recursive)
        ])
                                      ->getArray(null, 'user_id');
    }

    /**
     * @param int[] $orgu_ids
     * @return int[]
     */
     public function getUserIdsOfOrgUnitsInPosition(array $orgu_ids, int $position_id) : array
    {
        return ilOrgUnitUserAssignment::where([
            'orgu_id' => $orgu_ids,
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @return int[]
     */
    public function getUserIdsOfUsersOrgUnitsInPosition(
        int $user_id,
        array $users_position_id,
        int $position_id,
        bool $recursive = false
    ) : array {
        return ilOrgUnitUserAssignment::where([
            'orgu_id' => $this->getOrgUnitIdsOfUsersPosition($users_position_id, $user_id, $recursive),
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @return int[]
     */
    public function getOrgUnitIdsOfUsersPosition(
        int $position_id,
        int $user_id,
        bool $recursive = false
    ) : array {
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
            $recursive_orgu_ids += $tree->getAllChildren($orgu_id);
        }

        return $recursive_orgu_ids;
    }

    /**
     * @return int[]
     */
     public function getUserIdsOfPosition(int $position_id) : array
    {
        return ilOrgUnitUserAssignment::where([
            'position_id' => $position_id,
        ])->getArray(null, 'user_id');
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    public function getUserAssignmentsOfPosition(int $position_id) : array
    {
        return ilOrgUnitUserAssignment::where([
            'position_id' => $position_id,
        ])->get();
    }

    public function deleteAllAssignmentsOfUser(int $user_id) : void
    {
        global $DIC;
        $q = "DELETE FROM il_orgu_ua WHERE user_id = " . $DIC->database()->quote($user_id, "integer");
        $DIC->database()->manipulate($q);
    }
}
