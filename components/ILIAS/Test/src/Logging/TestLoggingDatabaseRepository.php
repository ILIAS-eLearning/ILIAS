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

namespace ILIAS\Test\Logging;

use ILIAS\Data\Range;
use ILIAS\Data\Order;

class TestLoggingDatabaseRepository implements TestLoggingRepository
{
    private const LEGACY_LOG_TABLE = 'ass_log';
    public const TEST_ADMINISTRATION_LOG_TABLE = 'tst_tst_admin_log';
    public const QUESTION_ADMINISTRATION_LOG_TABLE = 'tst_qst_admin_log';
    public const PARTICIPANT_LOG_TABLE = 'tst_pax_log';
    public const SCORING_LOG_TABLE = 'tst_mark_log';
    public const ERROR_LOG_TABLE = 'tst_error_log';

    public function __construct(
        private readonly Factory $factory,
        private readonly \ilDBInterface $db
    ) {
    }

    public function storeTestAdministrationInteraction(TestAdministrationInteraction $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::TEST_ADMINISTRATION_LOG_TABLE)];
        $this->db->insert(self::TEST_ADMINISTRATION_LOG_TABLE, $storage_array);
    }

    public function storeQuestionAdministrationInteraction(TestQuestionAdministrationInteraction $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::QUESTION_ADMINISTRATION_LOG_TABLE)];
        $this->db->insert(self::QUESTION_ADMINISTRATION_LOG_TABLE, $storage_array);
    }

    public function storeParticipantInteraction(TestParticipantInteraction $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::PARTICIPANT_LOG_TABLE)];
        $this->db->insert(self::PARTICIPANT_LOG_TABLE, $storage_array);
    }

    public function storeScoringInteraction(TestScoringInteraction $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::SCORING_LOG_TABLE)];
        $this->db->insert(self::SCOPRING_LOG_TABLE, $storage_array);
    }

    public function storeError(TestError $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::ERROR_LOG_TABLE)];
        $this->db->insert(self::ERROR_LOG_TABLE, $storage_array);
    }

    /**
     * @return \Generator<\ILIAS\Test\Logging\TestUserInteraction>
     */
    public function getLogs(
        array $valid_types,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?array $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): \Generator {
        if ($this->isFilterValid(
            $valid_types,
            $log_entry_type_filter,
            $interaction_type_filter
        )
        ) {
            yield from $this->retrieveInteractionsFromDatabase(
                $valid_types,
                $range,
                $order,
                $from_filter,
                $to_filter,
                $test_filter,
                $admin_filter,
                $pax_filter,
                $question_filter,
                $ip_filter,
                $log_entry_type_filter,
                $interaction_type_filter
            );
        }
    }

    public function getLog(
        string $unique_id
    ): TestUserInteraction {
        return $this->buildUserInteractionForUniqueIdentifier($unique_id);
    }

    /**
     * @param array<string> $unique_ids
     */
    public function deleteLogs(
        array $unique_ids
    ): void {
        $this->db->manipulate(
            $this->buildDeleteQueryForUniqueIds($unique_ids)
        );
    }

    public function getLegacyLogsForObjId(
        int $obj_id = null,
        bool $without_student_interactions = false
    ): array {
        $log = [];
        if ($without_student_interactions === true) {
            $result = $this->db->queryF(
                'SELECT * FROM ' . self::LEGACY_LOG_TABLE . ' WHERE obj_fi = %s AND test_only = %s ORDER BY tstamp',
                ['integer', 'text'],
                [
                    $obj_id,
                    1
                ]
            );
        } else {
            $result = $this->db->queryF(
                'SELECT * FROM ' . self::LEGACY_LOG_TABLE . ' WHERE obj_fi = %s ORDER BY tstamp',
                ['integer'],
                [
                    $obj_id
                ]
            );
        }
        while ($row = $this->db->fetchAssoc($result)) {
            if (!array_key_exists($row["tstamp"], $log)) {
                $log[$row["tstamp"]] = [];
            }
            $log[$row["tstamp"]][] = $row;
        }
        krsort($log);
        // flatten array
        $log_array = [];
        foreach ($log as $value) {
            foreach ($value as $row) {
                $log_array[] = $row;
            }
        }
        return $log_array;
    }

    private function buildUserInteractionForUniqueIdentifier(string $unique_id): ?TestUserInteraction
    {
        $unique_id_array = explode('_', $unique_id);
        if (count($unique_id_array) !== 2
            || !is_numeric($unique_id_array[1])) {
            return null;
        }

        switch ($unique_id_array[0]) {
            case TestAdministrationInteraction::IDENTIFIER:
                return $this->buildTestAdministrationInteractionFromId($unique_id_array[1]);

            case TestQuestionAdministrationInteraction::IDENTIFIER:
                return $this->buildQuestionAdministrationInteractionFromId($unique_id_array[1]);

            case TestParticipantInteraction::IDENTIFIER:
                return $this->buildParticipantInteractionFromId($unique_id_array[1]);

            case TestScoringInteraction::IDENTIFIER:
                return $this->buildScoringInteractionFromId($unique_id_array[1]);

            case TestError::IDENTIFIER:
                return $this->buildErrorFromId($unique_id_array[1]);
        }
    }

    private function buildTestAdministrationInteractionFromId(int $id): ?TestAdministrationInteraction
    {
        $query = $this->buildSelectStatementById($id, self::TEST_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildTestAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildQuestionAdministrationInteractionFromId(int $id): ?TestQuestionAdministrationInteraction
    {
        $query = $this->buildSelectStatementById($id, self::QUESTION_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildQuestionAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildParticipantInteractionFromId(int $id): ?TestParticipantInteraction
    {
        $query = $this->buildSelectStatementById($id, self::PARTICIPANT_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildParticipantInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildScoringInteractionFromId(int $id): ?TestScoringInteraction
    {
        $query = $this->buildSelectStatementById($id, self::SCORING_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildSelectStatementById($this->db->fetchObject($query));
    }

    private function buildErrorFromId(int $id): ?TestError
    {
        $query = $this->buildSelectStatementById($id, self::ERROR_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildErrorFromDBValues($this->db->fetchObject($query));
    }

    private function retrieveInteractionsFromDatabase(
        array $valid_types,
        Range $range,
        Order $order,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?array $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): \Generator {
        $result = $this->db->query(
            $this->buildInteractionsQuery(
                $valid_types,
                $range,
                $order,
                $from_filter,
                $to_filter,
                $test_filter,
                $admin_filter,
                $pax_filter,
                $question_filter,
                $ip_filter,
                $log_entry_type_filter,
                $interaction_type_filter
            )
        );

        while ($interaction = $result->fetchObject()) {
            switch ($interaction->type) {
                case TestAdministrationInteraction::IDENTIFIER:
                    yield $this->factory->buildTestAdministrationInteractionFromDBValues($interaction);

                    // no break
                case TestQuestionAdministrationInteraction::IDENTIFIER:
                    yield $this->factory->buildQuestionAdministrationInteractionFromDBValues($interaction);

                    // no break
                case TestParticipantInteraction::IDENTIFIER:
                    yield $this->factory->buildParticipantInteractionFromDBValues($interaction);

                    // no break
                case TestScoringInteraction::IDENTIFIER:
                    yield $this->factory->buildScoringInteractionFromDBValues($interaction);

                    // no break
                case TestError::IDENTIFIER:
                    yield $this->factory->buildErrorFromDBValues($interaction);
            }
        }
    }

    private function buildSelectStatementById(int $id, string $table_name): \ilDBStatement
    {
        return $this->db->queryF(
            'SELECT * FROM ' . $table_name . ' WHERE id=%s',
            [\ilDBConstants::T_INTEGER],
            [$id]
        );
    }

    private function buildDeleteQueryForUniqueIds(array $unique_ids): \ilDBStatement
    {
        $query = '';
        foreach ($this->parseUniqueIdsToTableNameArray($unique_ids) as $table_name => $values) {
            $query .= "DELETE FROM {$table_name} WHERE "
                . $this->db->in('id', $values) . ';' . PHP_EOL;
        }
        return $query;
    }

    private function buildInteractionsQuery(
        array $valid_types,
        Range $range,
        Order $order,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?array $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): string {
        $log_entry_types_to_fetch = array_keys($valid_types);

        $query = [];
        foreach ($log_entry_types_to_fetch as $type) {
            if ($log_entry_type_filter !== null
                    && !in_array($type, $log_entry_type_filter)
                || $interaction_type_filter !== null
                    && array_intersect(
                        $valid_types[$type],
                        $interaction_type_filter
                    ) === []
            ) {
                continue;
            }
            $query[] = $this->buildTableQueryFromFilterValues(
                $type,
                $from_filter,
                $to_filter,
                $test_filter,
                $admin_filter,
                $pax_filter,
                $question_filter,
                $ip_filter,
                $interaction_type_filter
            );
        }

        $query_string = implode(PHP_EOL . 'UNION' . PHP_EOL, $query);

        $init = PHP_EOL . ' ORDER BY ';
        $order_by_string = $order->join($init, fn($ret, $key, $value) => "{$ret} {$key} {$value}, ");
        if ($order_by_string !== $init) {
            $query_string .= mb_substr($order_by_string, 0, -2);
        }
        return PHP_EOL . ' LIMIT ' . $range->getStart() . ', ' . $range->getLength();
    }

    private function buildTableQueryFromFilterValues(
        string $type,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?array $ip_filter,
        ?array $interaction_type_filter
    ): string {
        $table_name = $this->getTableNameForTypeIdentifier($type);
        if ($pax_filter !== null
                && ($table_name === self::TEST_ADMINISTRATION_LOG_TABLE
                    || $table_name === self::QUESTION_ADMINISTRATION_LOG_TABLE)
            || $admin_filter !== null
                && $table_name === self::PARTICIPANT_LOG_TABLE
            || $ip_filter !== null
                && $table_name !== self::PARTICIPANT_LOG_TABLE
            || $question_filter !== null
                && $table_name === self::TEST_ADMINISTRATION_LOG_TABLE
        ) {
            return '';
        }

        $query = $this->buildSelectForTable($table_name, $type);

        $where = $this->buildWhereFromFilterValues(
            $from_filter,
            $to_filter,
            $test_filter,
            $admin_filter,
            $pax_filter,
            $question_filter,
            $ip_filter,
            $interaction_type_filter
        );

        return $query . $where;
    }

    private function buildSelectForTable(string $table_name, string $type): string
    {
        switch ($table_name) {
            case self::TEST_ADMINISTRATION_LOG_TABLE:
                return "SELECT {$type} AS type, id, ref_id, NULL AS qst_id, admin_id, "
                    . 'NULL AS pax_id, NULL AS source_ip, interaction_type, modified_ts, '
                    . "additional_data FROM {$table_name}";
            case self::QUESTION_ADMINISTRATION_LOG_TABLE:
                return "SELECT {$type} AS type, id, ref_id, qst_id, admin_id, "
                    . 'NULL AS pax_id, NULL AS source_ip, interaction_type, modified_ts, '
                    . "additional_data FROM {$table_name}";
            case self::PARTICIPANT_LOG_TABLE:
                return "SELECT {$type} AS type, id, ref_id, qst_id, NULL AS admin_id, "
                    . 'pax_id, source_ip, interaction_type, modified_ts, '
                    . "additional_data FROM {$table_name}";
            case self::SCORING_LOG_TABLE:
                return "SELECT {$type} AS type, id, ref_id, qst_id, admin_id, "
                    . 'pax_id, NULL AS source_ip, interaction_type, modified_ts, '
                    . "additional_data FROM {$table_name}";
            case self::ERROR_LOG_TABLE:
                return "SELECT {$type} AS type, id, ref_id, qst_id, admin_id, "
                    . 'pax_id, NULL AS source_ip, interaction_type, modified_ts, '
                    . "error_message AS additional_data FROM {$table_name}";
        }
    }

    private function buildWhereFromFilterValues(
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?array $ip_filter,
        ?array $interaction_type_filter
    ): string {
        $where = [];
        if ($from_filter !== null) {
            $where[] = 'modification_ts > ' . $from_filter;
        }
        if ($to_filter !== null) {
            $where[] = 'modification_ts < ' . $to_filter;
        }
        if ($test_filter !== null) {
            $where[] = $this->db->in('ref_id', $test_filter);
        }
        if ($admin_filter !== null) {
            $where[] = $this->db->in('admin_id', $admin_filter);
        }
        if ($pax_filter !== null) {
            $where[] = $this->db->in('pax_id', $pax_filter);
        }
        if ($question_filter !== null) {
            $where[] = $this->db->in('qst_id', $question_filter);
        }
        if ($ip_filter !== null) {
            $where[] = $this->db->like('source_ip', $ip_filter);
        }
        if ($interaction_type_filter !== null) {
            $where[] = $this->db->like('interaction_type', $interaction_type_filter);
        }

        if ($where === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $where);
    }

    private function isFilterValid(
        array $valid_types,
        ?array $log_entry_types,
        ?array $interaction_types
    ): bool {
        if ($log_entry_types !== null
                && !$this->areLogEntryTypesValid(
                    $valid_types,
                    $log_entry_types
                )
            || $interaction_types !== null
                && !$this->areInteractionTypesValid(
                    $valid_types,
                    $log_entry_types,
                    $interaction_types
                )
        ) {
            return false;
        }
        return true;
    }

    private function areLogEntryTypesValid(
        array $valid_types,
        array $filter_log_types
    ): bool {
        return array_intersect(
            $filter_log_types,
            array_keys($valid_types)
        ) === $filter_log_types;
    }

    private function areInteractionTypesValid(
        array $valid_types,
        array $filter_log_types,
        array $filter_interaction_types
    ): bool {
        if ($filter_log_types !== []) {
            $valid_types = array_filter(
                $valid_types,
                fn(string $key): bool => in_array($key, $filter_log_types),
                ARRAY_FILTER_USE_KEY
            );
        }
        $valid_interaction_types = array_reduce(
            $valid_types,
            fn(array $et, array $it): array => $et + $it,
            []
        );

        if (array_intersect($filter_interaction_types, $valid_interaction_types)
            === $filter_interaction_types) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int> $unique_ids
     * @return array<string, array<int>>
     */
    private function parseUniqueIdsToTableNameArray(array $unique_ids): string
    {
        return array_reduce(
            $unique_ids,
            function (array $type_array, string $unique_id): array {
                $unique_id_array = explode('_', $unique_id);
                if (count($unique_id_array) !== 2
                    || !is_numeric($unique_id_array[1])) {
                    return $type_array;
                }

                $table_name = $this->getTableNameForTypeIdentifier($unique_id_array[0]);
                if (!array_key_exists($table_name, $unique_id_array[1])) {
                    $type_array[$table_name] = [];
                }

                $type_array[$table_name][] = [\ilDBConstants::T_INTEGER, $unique_id_array[1]];
                return $type_array;
            },
            []
        );
    }

    private function getTableNameForTypeIdentifier(string $identifier): string
    {
        switch ($identifier) {
            case TestAdministrationInteraction::IDENTIFIER:
                return self::TEST_ADMINISTRATION_LOG_TABLE;

            case TestQuestionAdministrationInteraction::IDENTIFIER:
                return self::QUESTION_ADMINISTRATION_LOG_TABLE;

            case TestParticipantInteraction::IDENTIFIER:
                return self::PARTICIPANT_LOG_TABLE;

            case TestScoringInteraction::IDENTIFIER:
                return self::SCORING_LOG_TABLE;

            case TestError::IDENTIFIER:
                return self::ERROR_LOG_TABLE;
        }
    }
}
