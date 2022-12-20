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

class ilOrgUnitUserAssignmentDBRepository implements OrgUnitUserAssignmentRepository
{
    public const TABLE_NAME = 'il_orgu_ua';
    protected ilDBInterface $db;
    protected ilAppEventHandler $ilAppEventHandler;
    protected ilOrgUnitPositionDBRepository $positionRepo;

    public function __construct(ilDBInterface $db, ilAppEventHandler $handler = null)
    {
        $this->db = $db;

        if ($handler) {
            $this->ilAppEventHandler = $handler;
        } else {
            global $DIC;
            $this->ilAppEventHandler = $DIC->event();
        }
    }

    private function getPositionRepo(): \ilOrgUnitPositionDBRepository
    {
        if (!isset($this->positionRepo)) {
            $dic = \ilOrgUnitLocalDIC::dic();
            $this->positionRepo = $dic["repo.Positions"];
        }

        return $this->positionRepo;
    }

    public function get(int $user_id, int $position_id, int $orgu_id): ilOrgUnitUserAssignment
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.orgu_id = ' . $this->db->quote($orgu_id, 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return (new ilOrgUnitUserAssignment())
                ->withUserId($user_id)
                ->withPositionId($position_id)
                ->withOrguId($orgu_id);
        }

        $rec = $this->db->fetchAssoc($res);
        return (new ilOrgUnitUserAssignment((int) $rec['id']))
            ->withUserId((int) $rec['user_id'])
            ->withPositionId((int) $rec['position_id'])
            ->withOrguId((int) $rec['orgu_id']);
    }

    public function find(int $user_id, int $position_id, int $orgu_id): ?ilOrgUnitUserAssignment
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.orgu_id = ' . $this->db->quote($orgu_id, 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        return (new ilOrgUnitUserAssignment((int) $rec['id']))
            ->withUserId((int) $rec['user_id'])
            ->withPositionId((int) $rec['position_id'])
            ->withOrguId((int) $rec['orgu_id']);
    }

    /**
     * @return ilOrgUnitUserAssignment[]
     */
    private function getAll(int $id, string $field): array
    {
        if (!in_array($field, ['user_id', 'position_id', 'orgu_id'])) {
            throw new Exception("Invalid field: " . $field);
        }
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.' . $field . ' = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = (new ilOrgUnitUserAssignment((int) $rec['id']))
                ->withUserId((int) $rec['user_id'])
                ->withPositionId((int) $rec['position_id'])
                ->withOrguId((int) $rec['orgu_id']);
        }
        return $ret;
    }

    private function getById(int $id): ?ilOrgUnitUserAssignment
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.id = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        return (new ilOrgUnitUserAssignment((int) $rec['id']))
                ->withUserId((int) $rec['user_id'])
                ->withPositionId((int) $rec['position_id'])
                ->withOrguId((int) $rec['orgu_id']);
    }


    public function store(ilOrgUnitUserAssignment $assignment): ilOrgUnitUserAssignment
    {
        if ($assignment->getId() === 0) {
            $assignment = $this->insert($assignment);
        } else {
            $this->update($assignment);
        }

        $this->raiseEvent('assignUserToPosition', $assignment);

        return $assignment;
    }

    private function insert(ilOrgUnitUserAssignment $assignment): ilOrgUnitUserAssignment
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'id' => [ 'integer', $id ],
            'user_id' => [ 'integer', $assignment->getUserId() ],
            'position_id' => [ 'integer', $assignment->getPositionId() ],
            'orgu_id' => [ 'integer', $assignment->getOrguId() ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        $ret = (new ilOrgUnitUserAssignment($id))
            ->withUserId($assignment->getUserId())
            ->withPositionId($assignment->getPositionId())
            ->withOrguId($assignment->getOrguId());

        return $ret;
    }

    private function update(ilOrgUnitUserAssignment $assignment): void
    {
        $where = [ 'id' => [ 'integer', $assignment->getId() ] ];

        $values = [
            'user_id' => [ 'integer', $assignment->getUserId() ],
            'position_id' => [ 'integer', $assignment->getPositionId(),
            'orgu_id' => [ 'integer', $assignment->getOrguId() ]]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $assigment_id): void
    {
        if ($assigment_id === 0) {
            return;
        }

        $assignment = $this->getById($assigment_id);
        if ($assignment) {
            $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
                . ' WHERE id = ' . $this->db->quote($assigment_id, 'integer');
            $this->db->manipulate($query);

            $this->raiseEvent('deassignUserFromPosition', $assignment);
        }
    }

    public function deleteByUser(int $user_id): void
    {
        $assignments = $this->getAll($user_id, 'user_id');
        foreach ($assignments as $assignment) {
            $this->delete($assignment->getId());
        }
    }

    public function getAssignmentsByUsers(array $user_ids): array
    {
        $assigments = [];
        foreach ($user_ids as $user_id) {
            $assigments += $this->getAll($user_id, 'user_id');
        }
        return $assigments;
    }

    public function getAssignmentsByPosition(int $position_id): array
    {
        return $this->getAll($position_id, 'position_id');
    }

    public function getAssignmentsByOrgUnit(int $orgu_id): array
    {
        return $this->getAll($orgu_id, 'orgu_id');
    }


    public function getAssignmentsByUserAndPosition(int $user_id, int $position_id): array
    {
        $assignments = $this->getAll($user_id, 'user_id');
        $ret = [];
        foreach ($assignments as $assignment) {
            if ($position_id == $assignment->getPositionId()) {
                $ret[] = $assignment;
            }
        }
        return $ret;
    }

    public function getUsersByOrgUnits(array $orgu_ids): array
    {
        $users = [];
        foreach ($orgu_ids as $orgu_id) { // TODO ?
            $assignments = $this->getAll($orgu_id, 'orgu_id');
            foreach ($assignments as $assignment) {
                $users[] = $assignment->getUserId();
            }
        }
        return $users;
    }

    public function getUsersByPosition(int $position_id): array
    {
        $assignments = $this->getAll($position_id, 'position_id');
        $users = [];
        foreach ($assignments as $assignment) {
            $users[] = $assignment->getUserId();
        }
        return $users;
    }

    public function getUsersByOrgUnitsAndPosition(array $orgu_ids, int $position_id): array
    {
        $users = [];
        foreach ($orgu_ids as $orgu_id) { // TODO ?
            $assignments = $this->getAll($orgu_id, 'orgu_id');
            foreach ($assignments as $assignment) {
                if ($position_id == $assignment->getPositionId()) {
                    $users[] = $assignment->getUserId();
                }
            }
        }
        return $users;
    }

    public function getUsersByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array
    {
        $orgu_ids = $this->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);

        $users = [];
        foreach ($orgu_ids as $orgu_id) { // TODO ?
            $assignments = $this->getAll($orgu_id, 'orgu_id');
            foreach ($assignments as $assignment) {
                $users[] = $assignment->getUserId();
            }
        }
        return $users;
    }

    public function getFilteredUsersByUserAndPosition(int $user_id, int $position_id, int $position_filter_id, bool $recursive = false): array
    {
        $orgu_ids = $this->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);

        $users = [];
        foreach ($orgu_ids as $orgu_id) { // TODO ?
            $assignments = $this->getAll($orgu_id, 'orgu_id');
            foreach ($assignments as $assignment) {
                if ($position_filter_id == $assignment->getPositionId()) {
                    $users[] = $assignment->getUserId();
                }
            }
        }
        return $users;
    }

    public function getOrgUnitsByUser(int $user_id): array
    {
        $orgu_ids = [];
        $orgus = $this->getAssignmentsByUsers([$user_id]);
        foreach ($orgus as $orgu) {
            $orgu_ids[] = $orgu->getOrguId();
        }
        return $orgu_ids;
    }

    public function getOrgUnitsByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array
    {
        $assignments = $this->getAll($user_id, 'user_id');
        $orgu_ids = [];
        foreach ($assignments as $assignment) {
            if ($position_id == $assignment->getPositionId()) {
                $orgu_ids[] = $assignment->getOrguId();
            }
        }

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

    public function getPositionsByUser(int $user_id): array
    {
        $positions = [];
        foreach ($this->getAssignmentsByUsers([$user_id]) as $assignment) {
            $positions[] = $this->getPositionRepo()->getSingle($assignment->getPositionId(), 'id');
        }
        return $positions;
    }

    public function getSuperiorsByUsers(array $user_ids): array
    {
        $query = "SELECT 
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
				AND " . $this->db->in('orgu_ua.user_id', $user_ids, false, 'integer');

        $st = $this->db->query($query);

        $empl_id__sup_ids = [];
        while ($data = $this->db->fetchAssoc($st)) {
            $empl_id__sup_ids[$data['empl']][] = $data['sup'];
        }
        return $empl_id__sup_ids;
    }

    protected function raiseEvent(string $event, ilOrgUnitUserAssignment $assignment): void
    {
        $this->ilAppEventHandler->raise('Modules/OrgUnit', $event, array(
            'obj_id' => $assignment->getOrguId(),
            'usr_id' => $assignment->getUserId(),
            'position_id' => $assignment->getPositionId()
        ));
    }
}
