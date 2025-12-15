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

namespace ILIAS\Questions\Question\Persistence;

class Insert
{
    private array $values_to_insert = [];

    /**
     * @param array<\ILIAS\Questions\Question\Persistence\Column> $columns
     */
    public function __construct(
        private readonly Table $table,
        private readonly array $columns
    ) {
        foreach ($columns as $column) {
            if ($column->getTableName() !== $this->table->getName()) {
                throw new \InvalidArgumentException(
                    "You can only add Columns of the table {$this->table->getName()} to this Insert."
                );
            }
        }
    }

    public function getTableName(): string
    {
        return $this->table->getName();
    }

    /**
     *
     * @param array<\ILIAS\Questions\Question\Persistence\Value> $values
     * @return self
     */
    public function withAdditionalDataSet(
        array $values
    ): self {
        if (count($this->columns) !== count($values)) {
            throw new \InvalidArgumentException(
                "There MUST be the same amount of Values as there are Columns."
            );
        }

        $clone = clone $this;
        $clone->values_to_insert[] = $values;
        return $clone;
    }

    public function toManipulateString(\ilDBInterface $db): string
    {
        return "INSERT INTO {$this->table->getName()} "
            . $this->buildColumnsString() . PHP_EOL
            . $this->buildValuesString($db);
    }

    private function buildColumnsString(): string
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

    private function buildValuesString(\ilDBInterface $db): string
    {
        $return = [];
        foreach ($this->values_to_insert as $values) {
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
