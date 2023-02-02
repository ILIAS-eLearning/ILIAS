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

    protected function getPositionRepo(): \ilOrgUnitPositionDBRepository
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
        if ($res->numRows() > 0) {
            $rec = $this->db->fetchAssoc($res);
            return (new ilOrgUnitUserAssignment((int) $rec['id']))
                ->withUserId((int) $rec['user_id'])
                ->withPositionId((int) $rec['position_id'])
                ->withOrguId((int) $rec['orgu_id']);
        }

        $assignment = (new ilOrgUnitUserAssignment())
            ->withUserId($user_id)
            ->withPositionId($position_id)
            ->withOrguId($orgu_id);
        $assignment = $this->store($assignment);
        return $assignment;
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

    protected function insert(ilOrgUnitUserAssignment $assignment): ilOrgUnitUserAssignment
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

    protected function update(ilOrgUnitUserAssignment $assignment): void
    {
        $where = [ 'id' => [ 'integer', $assignment->getId() ] ];

        $values = [
            'user_id' => [ 'integer', $assignment->getUserId() ],
            'position_id' => [ 'integer', $assignment->getPositionId(),
            'orgu_id' => [ 'integer', $assignment->getOrguId() ]]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(ilOrgUnitUserAssignment $assignment): bool
    {
        if ($assignment->getId() === 0) {
            return false;
        }

        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE id = ' . $this->db->quote($assignment->getId(), 'integer');
        $rows = $this->db->manipulate($query);

        if ($rows > 0) {
            $this->raiseEvent('deassignUserFromPosition', $assignment);
            return true;
        }

        return false;
    }

    public function deleteByUser(int $user_id): bool
    {
        if ($user_id <= 0) {
            return false;
        }

        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE user_id = ' . $this->db->quote($user_id, 'integer');
        $rows = $this->db->manipulate($query);

        if ($rows > 0) {
            return true;
        }

        return false;
    }

    public function getByUsers(array $user_ids): array
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . $this->db->in('user_id', $user_ids, false, 'integer');
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

    public function getByPosition(int $position_id): array
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer');
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

    public function getByOrgUnit(int $orgu_id): array
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.orgu_id = ' . $this->db->quote($orgu_id, 'integer');
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


    public function getByUserAndPosition(int $user_id, int $position_id): array
    {
        $query = 'SELECT id, user_id, position_id, orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
            . '	AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer');
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

    public function getUsersByOrgUnits(array $orgu_ids): array
    {
        $query = 'SELECT user_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . $this->db->in(self::TABLE_NAME . '.orgu_id', $orgu_ids, false, 'integer');
        $res = $this->db->query($query);
        $users = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $users[] = (int) $rec['user_id'];
        }
        return $users;
    }

    public function getUsersByPosition(int $position_id): array
    {
        $query = 'SELECT user_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer');
        $res = $this->db->query($query);
        $users = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $users[] = (int) $rec['user_id'];
        }
        return $users;
    }

    public function getUsersByOrgUnitsAndPosition(array $orgu_ids, int $position_id): array
    {
        $query = 'SELECT user_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . $this->db->in(self::TABLE_NAME . '.orgu_id', $orgu_ids, false, 'integer') . PHP_EOL
            . '	AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer');
        $res = $this->db->query($query);
        $users = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $users[] = (int) $rec['user_id'];
        }
        return $users;
    }

    public function getUsersByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array
    {
        $orgu_ids = $this->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);

        $query = 'SELECT user_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . $this->db->in(self::TABLE_NAME . '.orgu_id', $orgu_ids, false, 'integer');
        $res = $this->db->query($query);
        $users = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $users[] = (int) $rec['user_id'];
        }
        return $users;
    }

    public function getFilteredUsersByUserAndPosition(int $user_id, int $position_id, int $position_filter_id, bool $recursive = false): array
    {
        $orgu_ids = $this->getOrgUnitsByUserAndPosition($user_id, $position_id, $recursive);

        $query = 'SELECT user_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . $this->db->in(self::TABLE_NAME . '.orgu_id', $orgu_ids, false, 'integer') . PHP_EOL
            . '	AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_filter_id, 'integer');
        $res = $this->db->query($query);
        $users = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $users[] = (int) $rec['user_id'];
        }
        return $users;
    }

    public function getOrgUnitsByUser(int $user_id): array
    {
        $query = 'SELECT orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer');
        $res = $this->db->query($query);
        $orgu_ids = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $orgu_ids[] = (int) $rec['orgu_id'];
        }
        return $orgu_ids;
    }

    public function getOrgUnitsByUserAndPosition(int $user_id, int $position_id, bool $recursive = false): array
    {
        $query = 'SELECT orgu_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer') . PHP_EOL
            . '	AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer');
        $res = $this->db->query($query);
        $orgu_ids = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $orgu_ids[] = (int) $rec['orgu_id'];
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
        $query = 'SELECT DISTINCT position_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.user_id = ' . $this->db->quote($user_id, 'integer');
        $res = $this->db->query($query);

        $positions = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $positions[] = $this->getPositionRepo()->getSingle((int) $rec['position_id'], 'id');
        }
        return $positions;
    }

    public function getSuperiorsByUsers(array $user_ids): array
    {
        $query = 'SELECT ' . PHP_EOL
            . ' ua.orgu_id AS orgu_id,' . PHP_EOL
            . ' ua.user_id AS empl,' . PHP_EOL
            . ' ua2.user_id as sup' . PHP_EOL
            . ' FROM' . PHP_EOL
            . self::TABLE_NAME . ' as ua,' . PHP_EOL
            . self::TABLE_NAME . ' as ua2' . PHP_EOL
            . ' WHERE ua.orgu_id = ua2.orgu_id' . PHP_EOL
            . ' AND ua.user_id <> ua2.user_id' . PHP_EOL
            . ' AND ua.position_id = ' . $this->db->quote(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE, 'integer') . PHP_EOL
            . ' AND ua2.position_id = ' . $this->db->quote(ilOrgUnitPosition::CORE_POSITION_SUPERIOR, 'integer') . PHP_EOL
            . ' AND ' . $this->db->in('ua.user_id', $user_ids, false, 'integer');
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return [];
        }

        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[$rec['empl']][] = $rec['sup'];
        }
        return $ret;
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
