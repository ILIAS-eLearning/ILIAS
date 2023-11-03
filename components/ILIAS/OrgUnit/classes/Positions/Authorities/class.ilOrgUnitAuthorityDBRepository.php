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
 *********************************************************************/

declare(strict_types=1);

class ilOrgUnitAuthorityDBRepository implements OrgUnitAuthorityRepository
{
    public const TABLE_NAME = 'il_orgu_authority';
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function get(int $id, string $field): array
    {
        if (!in_array($field, [ilOrgUnitAuthority::FIELD_OVER, ilOrgUnitAuthority::POSITION_ID, 'id'])) {
            throw new Exception("Invalid field: " . $field);
        }
        $query = 'SELECT id, ' . self::TABLE_NAME . '.over, scope, position_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.' . $field . ' = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $ret[] = (new ilOrgUnitAuthority((int) $rec['id']))
                ->withOver((int) $rec['over'])
                ->withScope((int) $rec['scope'])
                ->withPositionId((int) $rec['position_id']);
        }

        return $ret;
    }

    public function create(): ilOrgUnitAuthority
    {
        return new ilOrgUnitAuthority();
    }

    public function store(ilOrgUnitAuthority $authority): ilOrgUnitAuthority
    {
        if ($authority->getId() === 0) {
            $authority = $this->insert($authority);
        } else {
            $this->update($authority);
        }

        return $authority;
    }

    private function insert(ilOrgUnitAuthority $authority): ilOrgUnitAuthority
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'id' => [ 'integer', $id],
            'over' => [ 'integer', $authority->getOver() ],
            'scope' => [ 'integer', $authority->getScope() ],
            'position_id' => [ 'integer', $authority->getPositionId() ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        $ret = (new ilOrgUnitAuthority($id))
            ->withOver($authority->getOver())
            ->withScope($authority->getScope())
            ->withPositionId($authority->getPositionId());

        return $ret;
    }

    private function update(ilOrgUnitAuthority $authority): void
    {
        $where = [ 'id' => [ 'integer', $authority->getId() ] ];

        $values = [
            'over' => [ 'integer', $authority->getOver() ],
            'scope' => [ 'integer', $authority->getScope() ],
            'position_id' => [ 'integer', $authority->getPositionId() ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE id = ' . $this->db->quote($id, 'integer');

        $this->db->manipulate($query);
    }

    /**
     * Deletes all authorities for a position
     */
    public function deleteByPositionId(int $position_id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE position_id = ' . $this->db->quote($position_id, 'integer');

        $this->db->manipulate($query);
    }

    /**
     * Deletes orphaned authorities on position save
     */
    public function deleteLeftoverAuthorities(array $ids, int $position_id): void
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL
            . ' AND ' . $this->db->in('id', $ids, true, 'integer');

        $this->db->manipulate($query);
    }
}
