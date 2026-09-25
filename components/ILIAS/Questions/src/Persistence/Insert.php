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
 * ******************************************************************* */

declare(strict_types=1);

namespace ILIAS\Questions\Persistence;

class Insert
{
    protected array $value_sets = [];

    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     */
    public function __construct(
        protected readonly array $columns,
        array $values
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

        $this->value_sets[] = $values;
    }

    /**
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     */
    public function withAdditionalValues(
        array $values
    ): self {
        if (count($values) !== count($this->columns)) {
            throw new \InvalidArgumentException(
                "There MUST be the same amount of Values as there are Columns."
            );
        }

        $clone = clone $this;
        $clone->value_sets[] = $values;
        return $clone;
    }

    public function getTableToLock(): string
    {
        return $this->columns[0]->getTableName();
    }

    public function toManipulateString(
        \ilDBInterface $db
    ): string {
        return "INSERT INTO {$this->columns[0]->getTableName()}" . PHP_EOL
            . $this->buildColumnsString() . PHP_EOL
            . $this->buildValuesString($db);
    }

    protected function buildColumnsString(): string
    {
        return '('
            . implode(
                ', ',
                array_map(
                    fn(Column $v): string => $v->getColumnString(),
                    $this->columns
                )
            ) . ')';
    }

    protected function buildValuesString(
        \ilDBInterface $db
    ): string {
        $return = [];
        foreach ($this->value_sets as $values) {
            $return[] = '(' . implode(
                ', ',
                array_map(
                    fn(Value $v): string => $v->getQuotedValue($db),
                    $values
                )
            ) . ')';
        }

        return 'VALUES ' . implode(
            ',' . PHP_EOL,
            $return
        );
    }
}
