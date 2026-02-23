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

namespace ILIAS\StaticURL\Shortlinks\Shortlink;

use ILIAS\StaticURL\Shortlinks\Shortlink\Target\Type;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\TypeData;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\TypeDataResolver;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RepositoryDB implements Repository
{
    private const string TABLE_NAME = 'il_shortlinks';

    public function __construct(
        private readonly \ilDBInterface $db,
        private TypeDataResolver $type_data_resolver
    ) {
    }

    public function hasId(string $id): bool
    {
        $q = $this->db->queryF(
            'SELECT 1 FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            [\ilDBConstants::T_TEXT],
            [$id]
        );

        return $q->numRows() > 0;
    }
    public function has(string $shortlink): bool
    {
        $q = $this->db->queryF(
            'SELECT 1 FROM ' . self::TABLE_NAME . ' WHERE alias = %s',
            [\ilDBConstants::T_TEXT],
            [$shortlink]
        );

        return $q->numRows() > 0;
    }

    private function rowToShortlink(array $row): Shortlink
    {
        json_decode((string) $row['target_type_data'], true);
        $target_type_data = new TypeData([]);
        $target_type_data->unserialize($row['target_type_data']);

        return new ShortlinkDTO(
            $row['alias'],
            Type::from($row['target_type']),
            $target_type_data,
            (int) $row['position'],
            (bool) $row['active'],
            (int) $row['used'],
            $row['id'] ?? null
        );
    }

    public function getByAlias(string $shortlink): ?Shortlink
    {
        $q = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE alias = %s',
            [\ilDBConstants::T_TEXT],
            [$shortlink]
        );
        $row = $this->db->fetchAssoc($q);
        return $this->rowToShortlink($row);
    }

    public function getById(string $string): ?Shortlink
    {
        $q = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            [\ilDBConstants::T_TEXT],
            [$string]
        );
        $row = $this->db->fetchAssoc($q);
        return $this->rowToShortlink($row);
    }

    public function blank(Type $type = Type::REPO): Shortlink
    {
        return new ShortlinkDTO(
            '',
            $type,
            new TypeData(),
            0,
            true,
            0
        );
    }

    public function store(Shortlink $shortlink): Shortlink
    {
        if (empty($shortlink->getId())) {
            // shift position of all other items
            $this->db->manipulateF(
                'UPDATE ' . self::TABLE_NAME . ' SET position = position + 1 WHERE position >= %s',
                [\ilDBConstants::T_INTEGER],
                [$shortlink->getPosition()]
            );

            $shortlink = $shortlink->withId(
                uniqid('', true)
            );
        }

        $this->db->replace(
            self::TABLE_NAME,
            ['id' => [\ilDBConstants::T_TEXT, $shortlink->getId()],],
            [

                'alias' => [\ilDBConstants::T_TEXT, $shortlink->getAlias()],
                'target_type' => [\ilDBConstants::T_TEXT, $shortlink->getTargetType()->value],
                'target_type_data' => [\ilDBConstants::T_TEXT, $shortlink->getTargetData()->serialize()],
                'position' => [\ilDBConstants::T_INTEGER, $shortlink->getPosition()],
                'active' => [\ilDBConstants::T_INTEGER, (int) $shortlink->isActive()],
                'used' => [\ilDBConstants::T_INTEGER, $shortlink->getUsed()],
            ]
        );

        return $shortlink;
    }

    public function delete(Shortlink $shortlink): bool
    {
        return $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            [\ilDBConstants::T_TEXT],
            [$shortlink->getId()]
        ) > 0;
    }

    public function getRange(int $start, int $limit): \Generator
    {
        throw new \RuntimeException('Not implemented yet');
    }

    public function getAll(): \Generator
    {
        $q = $this->db->query(
            'SELECT * FROM ' . self::TABLE_NAME . ' ORDER BY position ASC'
        );
        while ($row = $this->db->fetchAssoc($q)) {
            yield $this->rowToShortlink($row);
        }
    }

    public function count(): int
    {
        $q = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM ' . self::TABLE_NAME
        );
        $row = $this->db->fetchAssoc($q);
        return (int) $row['cnt'];
    }

    public function increaseUsage(Shortlink $shortlink): Shortlink
    {
        return $this->store(
            $shortlink->increaseUsage()
        );
    }

    public function typeDataRevolver(): TypeDataResolver
    {
        return $this->type_data_resolver;
    }

}
