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

namespace ILIAS\Questions\Persistence;

class Update
{
    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     * @param array<\ILIAS\Questions\Persistence\Where> $where
     */
    public function __construct(
        private readonly array $columns,
        private readonly array $values,
        private readonly array $where
    ) {
        if ($columns === [] || count($columns) !== count($values)) {
            throw new \InvalidArgumentException(
                "There MUST be at least one Column and the same amount of Values as there are Columns."
            );
        }

        $table_name = $columns[0]->getTableName();
        foreach ($columns as $column) {
            if ($column->getTableName() !== $table_name) {
                throw new \InvalidArgumentException(
                    "All Columns MUST belong to the same Table."
                );
            }
        }
    }

    public function getTableToLock(): string
    {
        return $this->columns[0]->getTableName();
    }

    public function toManipulateString(
        \ilDBInterface $db
    ): string {
        return "UPDATE {$this->columns[0]->getTableName()}" . PHP_EOL
            . $this->buildSetterString($db) . PHP_EOL
            . $this->buildWhereString($db);
    }

    private function buildSetterString(
        \ilDBInterface $db
    ): string {
        return trim(
            array_reduce(
                array_keys($this->columns),
                fn(string $c, int $v): string => $c
                    . "{$this->columns[$v]->getColumnString()} = {$this->values[$v]->getQuotedValue($db)},",
                'SET '
            ),
            ','
        );
    }

    private function buildWhereString(
        \ilDBInterface $db
    ): string {
        $values = [];
        return sprintf(
            array_reduce(
                $this->where,
                function (?string $c, Where $v) use ($db, &$values): string {
                    $quoted_value = $v->getRight()->getQuotedValue($db);
                    if (is_array($quoted_value)) {
                        $values = [
                            ...$values,
                            ...array_values($quoted_value)
                        ];
                    } else {
                        $values[] = $quoted_value;
                    }

                    if ($c === null) {
                        return "WHERE {$v->toSql()}" . PHP_EOL;
                    }

                    return "{$c}{$v->getLogicalOperator()->value} {$v->toSql()}" . PHP_EOL;
                }
            ) ?? '',
            ...$values
        );
    }
}
