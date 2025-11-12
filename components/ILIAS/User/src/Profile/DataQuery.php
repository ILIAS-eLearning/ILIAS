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

namespace ILIAS\User\Profile;

use ILIAS\User\Profile\Fields\Field;

class DataQuery
{
    private const string ARRAY_SEPARATOR = '##!:!##';
    private const string RECORDS_QUERY_INIT = 'SELECT ';

    private string $cnt_query_init;

    private array $multi_fields = [];
    private array $select_fields;
    private array $udf_fields = [];
    private array $join = [];
    private array $where = [];
    private string $order = '';
    private bool $multi_data_table_joined = false;
    private bool $additional_fields_processed = false;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly string $user_base_table_name,
        private readonly string $user_values_table_name,
        array $select_fields
    ) {
        $this->cnt_query_init = 'SELECT COUNT(DISTINCT ' . $this->user_base_table_name
            . '.usr_id) cnt FROM ' . $this->user_base_table_name;
        $this->select_fields = array_map(
            fn(string $v): string => $this->user_base_table_name . '.' . $v,
            $select_fields
        );
    }

    public function withAdditionalAdditionalTableSelectField(string $field_name): self
    {
        $clone = clone $this;
        $clone->select_fields[] = $field_name;
        return $clone;
    }

    public function withAdditionalDefaultTableSelectField(string $field_name): self
    {
        $clone = clone $this;
        $clone->select_fields[] = $this->user_base_table_name . '.' . $field_name;
        return $clone;
    }

    public function withAdditionalUdfField(Field $field): self
    {
        $clone = clone $this;
        $clone->udf_fields[] = $field;
        return $clone;
    }

    public function withAdditionalMultiField(string $field_name): self
    {
        $clone = clone $this;
        $clone->multi_fields[] = $field_name;
        return $clone;
    }

    public function withAdditionalJoin(string $join): self
    {
        $clone = clone $this;
        $clone->join[] = $join;
        return $clone;
    }

    public function withAdditionalWhere(string $where): self
    {
        $clone = clone $this;
        $clone->where[] = $where;
        return $clone;
    }

    public function withAdditionalMultiDataWhere(string $identifier, string|array $value): self
    {
        if (is_array($value)) {
            $value_query = $this->db->in($this->user_values_table_name . '.value', $value, false, \ilDBConstants::T_TEXT);
        } else {
            $value_query = $this->db->like($this->user_values_table_name . '.value', \ilDBConstants::T_TEXT, "%{$value}%");
        }
        $clone = clone $this;
        $clone->where[] = $this->user_values_table_name . ".field_id = '{$identifier}' AND {$value_query}";
        return $clone;
    }

    public function withAdditionalTableOrder(string $order): self
    {
        $clone = clone $this;
        $clone->order = $order;
        return $clone;
    }

    public function withMultiDataTableOrder(string $order_field, string $direction): self
    {
        $clone = clone $this;
        $clone->order = "ORDER BY `{$order_field}` {$direction}";
        return $clone;
    }

    public function withDefaultTableOrderFields(array $order_fields, string $direction): self
    {
        $clone = clone $this;
        $clone->order = 'ORDER BY ' . implode(', ', array_reduce(
            $order_fields,
            function (array $c, string $v) use ($direction): array {
                $c[] = $this->user_base_table_name . ".`{$v}` {$direction}";
                return $c;
            },
            []
        ));
        return $clone;
    }

    public function withLimitedUsers(array $users): self
    {
        if ($users === []) {
            return $this;
        }
        $clone = clone $this;
        $clone->where[] = $this->db->in('usr_data.usr_id', $users, false, \ilDBConstants::T_INTEGER);
        return $clone;
    }

    public function withJoinedMultiDataTable(): self
    {
        $clone = clone $this;
        $clone->join[] = $this->buildJoinForMultiDataTable();
        $clone->multi_data_table_joined = true;
        return $clone;
    }

    public function buildRecordsQueryString(): string
    {
        return self::RECORDS_QUERY_INIT . implode(', ', $this->select_fields) . PHP_EOL
            . 'FROM usr_data' . PHP_EOL
            . implode(PHP_EOL, $this->join) . PHP_EOL
            . 'WHERE usr_data.usr_id <> ' . $this->db->quote(ANONYMOUS_USER_ID, \ilDBConstants::T_INTEGER) . PHP_EOL
            . $this->buildWhere()
            . 'GROUP BY ' . $this->user_base_table_name . '.usr_id' . PHP_EOL
            . $this->order;
    }

    public function buildCntQueryString(): string
    {
        return $this->cnt_query_init . PHP_EOL
            . implode(PHP_EOL, $this->join) . PHP_EOL
            . 'WHERE usr_data.usr_id <> ' . $this->db->quote(ANONYMOUS_USER_ID, \ilDBConstants::T_INTEGER) . PHP_EOL
            . $this->buildWhere();
    }

    public function withAdditionalSelectAndJoinForUdfAndMultiValueFields(): self
    {
        if ($this->additional_fields_processed
            || $this->multi_fields === [] && $this->udf_fields === []) {
            return $this;
        }

        $clone = clone $this;
        if (!$this->multi_data_table_joined) {
            $clone->join[] = $this->buildJoinForMultiDataTable();
            $clone->multi_data_table_joined = true;
        }

        foreach ($this->multi_fields as $field) {
            $clone->select_fields[] = 'GROUP_CONCAT(DISTINCT IF(' . $this->user_values_table_name
                . ".field_id = {$this->db->quote($field, \ilDBConstants::T_TEXT)}, "
                . $this->user_values_table_name . '.value, NULL) '
                . "SEPARATOR '" . self::ARRAY_SEPARATOR . "') `{$field}`";
        }

        foreach ($this->udf_fields as $field) {
            $clone->select_fields[] = 'GROUP_CONCAT(DISTINCT IF(' . $this->user_values_table_name
                . ".field_id = {$this->db->quote($field->getIdentifier(), \ilDBConstants::T_TEXT)}, "
                . $this->user_values_table_name . '.value, NULL) '
                . "SEPARATOR '" . self::ARRAY_SEPARATOR . "') `udf_{$field->getIdentifier()}`";
        }

        $clone->additional_fields_processed = true;
        return $clone;
    }

    public function explodeArrayValues(array $row): array
    {
        return array_map(
            static function (mixed $v): mixed {
                if (!is_string($v) || mb_stristr($v, self::ARRAY_SEPARATOR) === false) {
                    return $v;
                }

                return explode(self::ARRAY_SEPARATOR, $v);
            },
            $row
        );
    }

    private function buildWhere(): string
    {
        if ($this->where === []) {
            return '';
        }
        return 'AND ' . implode(PHP_EOL . 'AND ', $this->where) . PHP_EOL;
    }

    private function buildJoinForMultiDataTable(): string
    {
        return 'LEFT JOIN ' . $this->user_values_table_name . ' ON '
            . $this->user_values_table_name . '.usr_id = ' . $this->user_base_table_name . '.usr_id';
    }
}
