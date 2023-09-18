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

class ilOrgUnitPositionDBRepository implements OrgUnitPositionRepository
{
    public const TABLE_NAME = 'il_orgu_positions';
    private const TABLE_NAME_UA = 'il_orgu_ua';
    protected ilDBInterface $db;
    protected ilOrgUnitAuthorityDBRepository $authorityRepo;
    protected ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public function __construct(ilDBInterface $db, ilOrgUnitAuthorityDBRepository $authorityRepo, ilOrgUnitUserAssignmentDBRepository $assignmentRepo)
    {
        $this->db = $db;
        $this->authorityRepo = $authorityRepo;
        $this->assignmentRepo = $assignmentRepo;
    }

    /**
     * Get one or more positions filtered by field/value
     *
     * @return ilOrgUnitPosition[]
     * @throws Exception
     */
    public function get(int|string $value, string $field): array
    {
        $fields = [
            'id' => 'integer',
            'core_identifier' => 'integer',
            'title' => 'string'
        ];
        if (!in_array($field, array_keys($fields))) {
            throw new Exception("Invalid field: " . $field);
        }

        $query = 'SELECT id, title, description, core_position, core_identifier FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.' . $field . ' = ' . $this->db->quote($value, $fields[$field]);
        $res = $this->db->query($query);
        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = (new ilOrgUnitPosition((int) $rec['id']))
                ->withTitle((string) $rec['title'])
                ->withDescription((string) $rec['description'])
                ->withCorePosition((bool) $rec['core_position'])
                ->withCoreIdentifier((int) $rec['core_identifier'])
                ->withAuthorities($this->authorityRepo->get((int) $rec['id'], ilOrgUnitAuthority::POSITION_ID));
        }

        return $ret;
    }

    /**
     * Get a single position from a filtered query (see get())
     */
    public function getSingle(int|string $value, string $field): ?ilOrgUnitPosition
    {
        $pos = $this->get($value, $field);
        if (count($pos) === 0) {
            return null;
        }

        return (array_shift($pos));
    }

    /**
     * Returns all position objects
     *
     * @return ilOrgUnitPosition[]
     */
    public function getAllPositions(): array
    {
        $query = 'SELECT id, title, description, core_position, core_identifier FROM' . PHP_EOL
            . self::TABLE_NAME . PHP_EOL
            . 'WHERE 1';
        $res = $this->db->query($query);
        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = (new ilOrgUnitPosition((int) $rec['id']))
                ->withTitle((string) $rec['title'])
                ->withDescription((string) $rec['description'])
                ->withCorePosition((bool) $rec['core_position'])
                ->withCoreIdentifier((int) $rec['core_identifier'])
                ->withAuthorities($this->authorityRepo->get((int) $rec['id'], ilOrgUnitAuthority::POSITION_ID));
        }

        return $ret;
    }

    /**
     * Returns position data as an array, e.g. for using ids in forms
     *
     * @throws Exception
     */
    public function getArray(?string $key = null, ?string $field = null): array
    {
        if (!in_array($key, ['id', null])) {
            throw new Exception("Invalid key: " . $field);
        }

        $fields = [
            'id' => 'int',
            'title' => 'string',
            'description' => 'string',
            'core_identifier' => 'int',
            'core_position' => 'int'
        ];
        if (!in_array($field, array_keys($fields)) && $field !== null) {
            throw new Exception("Invalid field: " . $field);
        }

        if ($field !== null && $this->db->tableColumnExists(self::TABLE_NAME, $field)) {
            $query = 'SELECT id, ' . $field . ' FROM' . PHP_EOL
                . self::TABLE_NAME
                . '	WHERE 1';

            $res = $this->db->query($query);
            $ret = [];
            while ($rec = $this->db->fetchAssoc($res)) {
                $value = $rec[$field];
                if ($fields[$field] == 'int') {
                    $value = (int) $value;
                } elseif ($fields[$field] == 'string') {
                    $value = (string) $value;
                }
                if ($key !== null) {
                    $ret[$rec[$key]] = $value;
                } else {
                    $ret[] = $value;
                }
            }
        } else {
            $query = 'SELECT id, title, description, core_identifier, core_position FROM' . PHP_EOL
                . self::TABLE_NAME
                . '	WHERE 1';
            $res = $this->db->query($query);
            $ret = [];
            while ($rec = $this->db->fetchAssoc($res)) {
                if ($key !== null) {
                    $ret[$key] = $rec;
                } else {
                    $ret[] = $rec;
                }
            }
        }

        return $ret;
    }

    /**
     * Returns all core positions plus all positions with user assignments for a certain org unit
     * (kept for compatibility, filtering by ua will be moved to orgu later)
     *
     * @return ilOrgUnitPosition[]
     */
    public function getPositionsForOrgUnit(int $orgu_id): array
    {
        $query = 'SELECT DISTINCT ' . self::TABLE_NAME . '.id, ' . self::TABLE_NAME . '.*' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'LEFT JOIN ' . self::TABLE_NAME_UA . PHP_EOL
            . 'ON ' . self::TABLE_NAME . '.id = ' . self::TABLE_NAME_UA . '.position_id' . PHP_EOL
            . 'AND ' . self::TABLE_NAME_UA . '.orgu_id = ' . $this->db->quote($orgu_id, 'integer') . PHP_EOL
            . 'WHERE ' . self::TABLE_NAME_UA . '.user_id IS NOT NULL' . PHP_EOL
            . 'OR ' . self::TABLE_NAME . '.core_position = 1';
        $res = $this->db->query($query);
        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = (new ilOrgUnitPosition((int) $rec['id']))
                ->withTitle((string) $rec['title'])
                ->withDescription((string) $rec['description'])
                ->withCorePosition((bool) $rec['core_position'])
                ->withCoreIdentifier((int) $rec['core_identifier'])
                ->withAuthorities($this->authorityRepo->get((int) $rec['id'], ilOrgUnitAuthority::POSITION_ID));
        }

        return $ret;
    }

    public function create(): ilOrgUnitPosition
    {
        return new ilOrgUnitPosition();
    }

    /**
     * Saves position and its authorities
     */
    public function store(ilOrgUnitPosition $position): ilOrgUnitPosition
    {
        if ($position->getId() === 0) {
            $position = $this->insert($position);
        } else {
            $this->update($position);
        }

        $authorities = $position->getAuthorities();
        $ids = [];
        $new_authorities = [];
        foreach ($authorities as $authority) {
            $auth = $this->authorityRepo->store($authority->withPositionId($position->getId()));
            $ids[] = $auth->getId();
            $new_authorities[] = $auth;
        }
        $position = $position->withAuthorities($new_authorities);

        if (count($ids) > 0) {
            $this->authorityRepo->deleteLeftoverAuthorities($ids, $position->getId());
        }
        if (count($ids) === 0) {
            $authorities = $this->authorityRepo->get($position->getId(), 'position_id');
            foreach ($authorities as $authority) {
                $this->authorityRepo->delete($authority->getId());
            }
        }

        return $position;
    }

    private function insert(ilOrgUnitPosition $position): ilOrgUnitPosition
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'id' => [ 'integer', $id],
            'title' => [ 'text', $position->getTitle() ],
            'description' => [ 'text', $position->getDescription() ],
            'core_position' => [ 'integer', ($position->isCorePosition()) ? 1 : 0 ],
            'core_identifier' => [ 'integer', $position->getCoreIdentifier() ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        $ret = (new ilOrgUnitPosition($id))
            ->withTitle($position->getTitle())
            ->withDescription($position->getDescription())
            ->withCorePosition($position->isCorePosition())
            ->withCoreIdentifier($position->getCoreIdentifier())
            ->withAuthorities($position->getAuthorities());

        return $ret;
    }

    private function update(ilOrgUnitPosition $position): void
    {
        $where = [ 'id' => [ 'integer', $position->getId() ] ];

        $values = [
            'title' => [ 'text', $position->getTitle() ],
            'description' => [ 'text', $position->getDescription() ],
            'core_position' => [ 'integer', (($position->isCorePosition()) ? 1 : 0) ],
            'core_identifier' => [ 'integer', $position->getCoreIdentifier() ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * Deletes position and authorities/user assignments attached to it
     */
    public function delete(int $position_id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE id = ' . $this->db->quote($position_id, 'integer');

        $this->db->manipulate($query);

        $authorities = $this->authorityRepo->get($position_id, 'position_id');
        foreach ($authorities as $authority) {
            $this->authorityRepo->delete($authority->getId());
        }

        $assignments = $this->assignmentRepo->getByPosition($position_id);
        foreach ($assignments as $assignment) {
            $this->assignmentRepo->delete($assignment);
        }
    }

    /**
     * Gets or creates an authority object, as the authority repo is encapsulated into this repo
     */
    public function getAuthority(?int $id): ilOrgUnitAuthority
    {
        if ($id === null) {
            return $this->authorityRepo->create();
        } else {
            $authority = $this->authorityRepo->get($id, 'id');
            return array_shift($authority);
        }
    }
}
