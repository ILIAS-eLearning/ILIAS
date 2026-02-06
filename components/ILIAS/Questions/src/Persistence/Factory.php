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

class Factory
{
    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     */
    public function insert(
        array $columns,
        array $values
    ): Insert {
        return new Insert($columns, $values);
    }

    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     */
    public function replace(
        array $columns,
        array $values
    ): Replace {
        return new Replace($columns, $values);
    }

    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     * @param array<\ILIAS\Questions\Persistence\Value> $values
     * @param array<\ILIAS\Questions\Persistence\Where> $where
     */
    public function update(
        array $columns,
        array $values,
        array $where
    ): Update {
        return new Update($columns, $values, $where);
    }

    /**
     * @param array<\ILIAS\Questions\Persistence\Where> $where
     */
    public function delete(
        Table $table,
        array $where
    ): Delete {
        return new Delete($table, $where);
    }

    public function table(
        CoreTables|TableTypes $table_definition,
        ?TableNameBuilder $table_name_builder = null,
        string $table_identifier = ''
    ): Table {
        return new Table($table_definition, $table_name_builder, $table_identifier);
    }

    public function column(
        Table $table,
        string $identifier
    ): Column {
        return new Column($table, $identifier);
    }

    /**
     * @param array<\ILIAS\Questions\Persistence\Column> $columns
     */
    public function select(
        array $columns
    ): Select {
        return new Select($columns);
    }

    public function join(
        Column $left,
        Column $right,
        JoinType $type = JoinType::Inner
    ): Join {
        return new Join($left, $right, $type);
    }

    public function where(
        Column $left,
        Value $right,
        Operator $comparison = Operator::Equal,
        Junctor $junctor = Junctor::Conjunction,
        bool $negate = false
    ): Where {
        return new Where($left, $right, $comparison, $junctor, $negate);
    }

    public function order(
        Column $column,
        OrderDirection $direction = OrderDirection::Asc
    ): Order {
        return new Order($column, $direction);
    }

    public function value(
        string $type,
        null|string|int|float|array $value
    ): Value {
        return new Value($type, $value);
    }
}
