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

class Query
{
    private array $select = [];
    private array $where = [];
    private array $joins = [];
    private array $order = [];
    private ?int $limit = null;

    private array $binding_types = [];
    private array $binding_values = [];

    public function __construct(
        private readonly \ilDBInterface $db
    ) {

        $questions_table_definition = CoreTables::Questions;
        $answer_form_table_definition = CoreTables::AnswerForms;
        $questions_id_column = $questions_table_definition->getIdColumn();

        $this->select[] = new Select(
            $questions_table_definition->getTable(),
            $questions_table_definition->getColumnsForSelect()
        );

        $this->select[] = new Select(
            $answer_form_table_definition->getTable(),
            $answer_form_table_definition->getColumnsForSelect()
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

    public function withAdditionalSelect(Select $select): self
    {
        $clone = clone $this;
        $clone->select[] = $select;
        return $clone;
    }

    public function withAdditionalJoin(Join $join): self
    {
        $clone = clone $this;
        $clone->joins[] = $join;
        return $clone;
    }

    public function withAdditionalWhere(Where $where): self
    {
        $clone = clone $this;
        $clone->where[] = $where;
        return $clone;
    }

    public function withAdditionalOrder(Order $order): self
    {
        $clone = clone $this;
        $clone->order[] = $order;
        return $clone;
    }

    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function toSql(): \ilDBStatement
    {
        return $this->db->queryF(
            'SELECT ' . implode(
                ', ',
                array_reduce(
                    $this->select,
                    static fn(array $c, Select $v): array => [...$c, ...$v->toColumnsArray()],
                    []
                )
            ) . ' FROM ' . CoreTables::Questions->value
            . array_reduce(
                $this->joins,
                static fn(string $c, Join $v): string => $c . PHP_EOL . $v->toSql(),
                ''
            ) . PHP_EOL
            . $this->buildWhereString()
            . ($this->limit !== null ? "LIMIT = {$this->limit}" : ''),
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

    private function addValueToBinding(Value $value): void
    {
        if (!is_array($value)) {
            $this->binding_types[] = $value->getType();
            $this->binding_values[] = $value->getValue();
            return;
        }

        foreach ($value->getValue() as $v) {
            $this->binding_types[] = $value->getType();
            $this->binding_values[] = $v;
        }
    }
}
