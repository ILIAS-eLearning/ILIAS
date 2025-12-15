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

namespace ILIAS\Questions\Question\Persistence;

class Update
{
    public function __construct(
        private readonly Table $table,
        private readonly array $columns,
        private readonly array $values,
        private readonly array $where
    ) {
        foreach ($columns as $column) {
            if ($column->getTableName() !== $this->table->getName()) {
                throw new \InvalidArgumentException(
                    "You can only add Columns of the table {$this->table->getName()} to this Insert."
                );
            }
        }

        if (count(columns) !== count($values)) {
            throw new \InvalidArgumentException(
                "There MUST be the same amount of Values as there are Columns."
            );
        }
    }

    public function getTableName(): string
    {
        return $this->table->getName();
    }

    public function toManipulateString(\ilDBInterface $db): string
    {
        return "UPDATE {$this->table->getName()}" . PHP_EOL
            . $this->buildSetterString($db) . PHP_EOL
            . $this->buildWhereString($db);
    }

    private function buildSetterString(\ilDBInterface $db): string
    {
        return trim(
            array_reduce(
                array_keys($this->columns),
                fn(string $c, int $v): string => $c
                    . "{$this->columns[$v]->getColumnString()} = {$this->values[$v]->getQuotedValue()},",
                'SET '
            ),
            ','
        );
    }

    private function buildWhereString(\ilDBInterface $db): string
    {
        $values = [];

        return sprintf(
            array_reduce(
                $this->where,
                function (?string $c, Where $v) use ($db, &$values): string {
                    $values[] = $v->getRight()->getQuotedValue($db);

                    if ($c === null) {
                        return "WHERE {$v->toSql()}" . PHP_EOL;
                    }

                    return "{$c}{$v->getLogicalOperator()} {$v->toSql()}" . PHP_EOL;
                }
            ),
            $values
        );
    }
}
