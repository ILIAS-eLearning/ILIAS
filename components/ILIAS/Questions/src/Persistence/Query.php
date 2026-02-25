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

use ILIAS\Data\Range;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class Query
{
    private array $select = [];
    private array $where = [];
    private array $joins = [];
    private array $order = [];
    private ?Range $range = null;

    private array $binding_types = [];
    private array $binding_values = [];

    private ?array $current_record = null;

    /**
     * @param Table $base_table The base table will be used as the table in the
     * "From" statement
     */
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private string $component_name_space,
        private Table $base_table
    ) {
    }

    public function getTableNameBuilder(
        ?TableSubNameSpace $table_sub_name_space
    ): TableNameBuilder {
        return new TableNameBuilder(
            $this->component_name_space,
            $table_sub_name_space
        );
    }

    public function getRefinery(): Refinery
    {
        return $this->refinery;
    }

    public function withAdditionalSelect(
        Select $select
    ): self {
        $clone = clone $this;
        $clone->select[] = $select;
        return $clone;
    }

    public function withAdditionalJoin(
        Join $join
    ): self {
        $clone = clone $this;
        $clone->joins[] = $join;
        return $clone;
    }

    public function withAdditionalWhere(
        Where $where
    ): self {
        $clone = clone $this;
        $clone->where[] = $where;
        return $clone;
    }

    public function withAdditionalOrder(
        Order $order
    ): self {
        $clone = clone $this;
        $clone->order[] = $order;
        return $clone;
    }

    public function withRange(
        Range $range
    ): self {
        $clone = clone $this;
        $clone->range = $range;
        return $clone;
    }

    public function loadNextRecord(
        ?Column $group_by
    ): \Generator {
        $result = $this->toSql();

        $this->current_record = [$this->db->fetchAssoc($result)];
        if ($this->current_record[0] === null) {
            return null;
        }

        if ($group_by === null) {
            yield from $this->loadNextRecordUngrouped($result);
            return;
        }

        yield from $this->loadNextRecordGrouped(
            $result,
            $group_by->getColumnAlias()
        );
    }

    public function retrieveCurrentRecord(
        Table $table,
        Transformation $transformation
    ): mixed {
        $table_name = $table->getName();
        $filtered_record = [];
        foreach ($this->current_record as $data_set) {
            $filtered_dataset = $this->filterDataSetByTable($table_name, $data_set);
            if (array_filter($filtered_dataset) !== []) {
                $filtered_record[] = $filtered_dataset;
            }
        }

        return $transformation->transform($filtered_record);
    }

    private function toSql(): \ilDBStatement
    {
        return $this->db->queryF(
            'SELECT ' . implode(
                ', ',
                array_reduce(
                    $this->select,
                    static fn(array $c, Select $v): array => [...$c, ...$v->toColumnsArray()],
                    []
                )
            ) . " FROM {$this->base_table->getName()}"
            . array_reduce(
                $this->joins,
                static fn(string $c, Join $v): string => $c . PHP_EOL . $v->toSql(),
                ''
            ) . PHP_EOL
            . $this->buildWhereString()
            . 'ORDER BY ' . implode(
                ', ',
                array_reduce(
                    $this->order,
                    static function (array $c, Order $v): array {
                        $c[] = $v->toSql();
                        return $c;
                    },
                    []
                )
            ) . PHP_EOL
            . ($this->range !== null ? "LIMIT {$this->range->getStart()}, {$this->range->getLength()}" : ''),
            $this->binding_types,
            $this->binding_values
        );
    }

    private function buildWhereString(): string
    {
        return array_reduce(
            $this->where,
            function (?string $c, Where $v): string {
                $this->addValueToBinding($v->getRight());
                if ($c === null) {
                    return "WHERE {$v->toSql()}" . PHP_EOL;
                }

                return "{$c}{$v->getLogicalOperator()} {$v->toSql()}" . PHP_EOL;
            }
        ) ?? '';
    }

    private function addValueToBinding(
        Value $value
    ): void {
        if (!is_array($value->getValue())) {
            $this->binding_types[] = $value->getType();
            $this->binding_values[] = $value->getValue();
            return;
        }

        foreach ($value->getValue() as $v) {
            $this->binding_types[] = $value->getType();
            $this->binding_values[] = $v;
        }
    }

    private function loadNextRecordGrouped(
        \ilDBStatement $result,
        string $group_by
    ): \Generator {
        while (($db_record = $this->db->fetchAssoc($result)) !== null) {
            if ($db_record[$group_by] === $this->current_record[0][$group_by]) {
                $this->current_record[] = $db_record;
                continue;
            }
            yield $this;
            $this->current_record = [$db_record];
        }
        yield $this;
    }

    private function loadNextRecordUngrouped(
        \ilDBStatement $result
    ): \Generator {
        while (($db_record = $this->db->fetchAssoc($result)) !== null) {
            $this->current_record = $db_record;
            yield $this;
        }
    }

    private function filterDataSetByTable(
        string $table_name,
        array $data_set
    ): array {
        return array_reduce(
            array_keys($data_set),
            function (array $c, string $v) use ($table_name, $data_set): array {
                if (str_starts_with($v, $table_name)) {
                    $c[mb_substr($v, mb_strlen($table_name) + 1)] = $data_set[$v];
                }
                return $c;
            },
            []
        );
    }
}
