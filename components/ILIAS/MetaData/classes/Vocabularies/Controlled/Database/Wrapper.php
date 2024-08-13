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

namespace ILIAS\MetaData\Vocabularies\Controlled\Database;

class Wrapper implements WrapperInterface
{
    protected \ilDBInterface $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function nextID(string $table): int
    {
        return $this->db->nextId($table);
    }

    public function insert(string $table, array $values): void
    {
        $this->db->insert($table, $values);
    }

    public function update(string $table, array $values, array $where): void
    {
        $this->db->update($table, $values, $where);
    }

    public function query(string $query): \Generator
    {
        $result = $this->db->query($query);

        while ($row = $result->fetchAssoc()) {
            yield $row;
        }
    }

    public function manipulate(string $query): void
    {
        $this->db->manipulate($query);
    }

    public function quoteAsInteger(string $value): string
    {
        return $this->db->quote($value, \ilDBConstants::T_INTEGER);
    }

    public function quoteAsString(string $value): string
    {
        return $this->db->quote($value, \ilDBConstants::T_TEXT);
    }

    public function in(string $field, string ...$values): string
    {
        return $this->db->in($field, $values, false, \ilDBConstants::T_TEXT);
    }
}
