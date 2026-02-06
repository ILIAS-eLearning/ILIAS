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

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Persistence;
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

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Refinery $refinery
    ) {
        $questions_linking_table_definition = CoreTables::Linking;
        $questions_table_definition = CoreTables::Questions;
        $answer_form_table_definition = CoreTables::AnswerForms;
        $questions_id_column = $questions_table_definition->getIdColumn();

        $this->select[] = new Select(
            $questions_linking_table_definition->getColumns()
        );

        $this->select[] = new Select(
            $questions_table_definition->getColumns()
        );

        $this->select[] = new Select(
            $answer_form_table_definition->getColumns()
        );

        $this->joins[] = new Join(
            $questions_linking_table_definition->getIdColumn(),
            $questions_table_definition->getIdColumn(),
            JoinType::Inner
        );

        $this->joins[] = new Join(
            $questions_id_column,
            $answer_form_table_definition->getForeignKeyColumn(),
            JoinType::Left
        );

        $this->order[] = new Order(
            $questions_id_column
        );

        $this->order[] = new Order(
            $answer_form_table_definition->getIdColumn()
        );
    }

    public function getPersistenceForDefinitionClass(
        string $definition_class
    ): Persistence {
        return $this->answer_form_factory
            ->getDefinitionForClass($definition_class)
            ->getPersistence();
    }

    public function getTableNameBuilder(
        string $definition_class
    ): TableNameBuilder {
        return new TableNameBuilder(
            $this->answer_form_factory
                ->getDefinitionForClass($definition_class)
                ->getPersistence()
                ->getTableNameSpace()
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

    public function loadNextRecord(): \Generator
    {
        $alias = CoreTables::Questions->getIdColumn()->getColumnAlias();

        $result = $this->toSql();

        $this->current_record = [$this->db->fetchAssoc($result)];
        if ($this->current_record[0] === null) {
            return null;
        }

        while (($db_record = $this->db->fetchAssoc($result)) !== null) {
            if ($db_record[$alias] === $this->current_record[0][$alias]) {
                $this->current_record[] = $db_record;
                continue;
            }
            yield $this;
            $this->current_record = [$db_record];
        }
        yield $this;
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
            ) . ' FROM ' . CoreTables::Linking->value
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

    public function filterDataSetByTable(
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
