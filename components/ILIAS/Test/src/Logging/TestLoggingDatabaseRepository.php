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

use ILIAS\Test\Logging\LogTable;
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

    private const VIEW_TABLE_TO_DB_TABLES = [
        LogTable::COLUMN_DATE_TIME => 'modification_ts',
        LogTable::COLUMN_CORRESPONDING_TEST => 'ref_id',
        LogTable::COLUMN_ADMIN => 'admin_id',
        LogTable::COLUMN_PARTICIPANT => 'pax_id',
        LogTable::COLUMN_SOURCE_IP => 'source_ip',
        LogTable::COLUMN_QUESTION => 'qst_id',
        LogTable::COLUMN_LOG_ENTRY_TYPE => 'type',
        LogTable::COLUMN_INTERACTION_TYPE => 'interaction_type'
    ];

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
        $this->db->insert(self::SCORING_LOG_TABLE, $storage_array);
    }

    public function storeError(TestError $interaction): void
    {
        $storage_array = $interaction->toStorage();
        $storage_array['id'] = [\ilDBConstants::T_INTEGER, $this->db->nextId(self::ERROR_LOG_TABLE)];
        $this->db->insert(self::ERROR_LOG_TABLE, $storage_array);
    }

    public function getLogs(
        array $valid_types,
        ?array $test_filter,
        ?\ILIAS\Data\Range $range = null,
        ?\ILIAS\Data\Order $order = null,
        ?int $from_filter = null,
        ?int $to_filter = null,
        ?array $admin_filter = null,
        ?array $pax_filter = null,
        ?array $question_filter = null,
        ?string $ip_filter = null,
        ?array $log_entry_type_filter = null,
        ?array $interaction_type_filter = null
    ): \Generator {
        if ($this->isFilterValid(
            $valid_types,
            $log_entry_type_filter,
            $interaction_type_filter
        )
        ) {
            yield from $this->retrieveInteractions(
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

    public function getLogsCount(
        array $valid_types,
        ?array $test_filter = null,
        ?int $from_filter = null,
        ?int $to_filter = null,
        ?array $admin_filter = null,
        ?array $pax_filter = null,
        ?array $question_filter = null,
        ?string $ip_filter = null,
        ?array $log_entry_type_filter = null,
        ?array $interaction_type_filter = null
    ): int {
        $query = $this->buildCountQuery(
            $valid_types,
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
        if ($query === '') {
            return 0;
        }
        $result = $this->db->query(
            $query
        );
        return $this->db->fetchObject($result)->cnt;
    }

    public function getLogsByUniqueIdentifiers(
        array $unique_ids
    ): \Generator {
        foreach ($this->parseUniqueIdsToTypeArray($unique_ids) as $type => $values) {
            $query = "SELECT *, '{$type}' AS type FROM {$this->getTableNameForTypeIdentifier($type)} WHERE "
                . $this->db->in('id', $values, false, \ilDBConstants::T_INTEGER);
            $result = $this->db->query($query);
            yield from $this->fetchInteractionForResult($result);
        }
    }

    public function getLog(
        string $unique_id
    ): ?TestUserInteraction {
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

    public function testHasParticipantInteractions(int $ref_id): bool
    {
        $query = $this->db->queryF(
            'SELECT COUNT(id) AS cnt FROM ' . self::PARTICIPANT_LOG_TABLE . ' WHERE ref_id=%s',
            [\ilDBConstants::T_INTEGER],
            [$ref_id]
        );
        return $this->db->fetchObject($query)->cnt > 0;
    }

    public function deleteParticipantInteractionsForTest(int $ref_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . self::PARTICIPANT_LOG_TABLE . ' WHERE ref_id=%s',
            [\ilDBConstants::T_INTEGER],
            [$ref_id]
        );
    }

    public function getLegacyLogsForObjId(?int $obj_id): array
    {
        $log = [];

        $where = '';
        if ($obj_id !== null) {
            $where = ' WHERE obj_fi = ' . $obj_id;
        }

        $result = $this->db->query(
            'SELECT * FROM ' . self::LEGACY_LOG_TABLE . $where . ' ORDER BY tstamp'
        );

        while ($row = $this->db->fetchAssoc($result)) {
            if (!array_key_exists($row['tstamp'], $log)) {
                $log[$row['tstamp']] = [];
            }
            $log[$row['tstamp']][] = $row;
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
                return $this->buildTestAdministrationInteractionFromId((int) $unique_id_array[1]);

            case TestQuestionAdministrationInteraction::IDENTIFIER:
                return $this->buildQuestionAdministrationInteractionFromId((int) $unique_id_array[1]);

            case TestParticipantInteraction::IDENTIFIER:
                return $this->buildParticipantInteractionFromId((int) $unique_id_array[1]);

            case TestScoringInteraction::IDENTIFIER:
                return $this->buildScoringInteractionFromId((int) $unique_id_array[1]);

            case TestError::IDENTIFIER:
                return $this->buildErrorFromId((int) $unique_id_array[1]);
        }

        return null;
    }

    private function buildTestAdministrationInteractionFromId(int $id): ?TestAdministrationInteraction
    {
        $query = $this->buildSelectStatementForId($id, self::TEST_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) === 0) {
            return null;
        }

        return $this->factory->buildTestAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildQuestionAdministrationInteractionFromId(int $id): ?TestQuestionAdministrationInteraction
    {
        $query = $this->buildSelectStatementForId($id, self::QUESTION_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) === 0) {
            return null;
        }

        return $this->factory->buildQuestionAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildParticipantInteractionFromId(int $id): ?TestParticipantInteraction
    {
        $query = $this->buildSelectStatementForId($id, self::PARTICIPANT_LOG_TABLE);
        if ($this->db->numRows($query) === 0) {
            return null;
        }

        return $this->factory->buildParticipantInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildScoringInteractionFromId(int $id): ?TestScoringInteraction
    {
        $query = $this->buildSelectStatementForId($id, self::SCORING_LOG_TABLE);
        if ($this->db->numRows($query) === 0) {
            return null;
        }

        return $this->factory->buildParticipantInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildErrorFromId(int $id): ?TestError
    {
        $query = $this->buildSelectStatementForId($id, self::ERROR_LOG_TABLE);
        if ($this->db->numRows($query) === 0) {
            return null;
        }

        return $this->factory->buildErrorFromDBValues($this->db->fetchObject($query));
    }

    private function retrieveInteractions(
        array $valid_types,
        ?Range $range,
        ?Order $order,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): \Generator {
        $result = $this->buildInteractionsStatementWithLimitAndOrder(
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
        yield from $this->fetchInteractionForResult($result);
    }

    private function fetchInteractionForResult(\ilDBStatement $result): \Generator
    {
        while ($interaction = $this->db->fetchObject($result)) {
            switch ($interaction->type) {
                case TestAdministrationInteraction::IDENTIFIER:
                    yield $this->factory->buildTestAdministrationInteractionFromDBValues($interaction);
                    break;
                case TestQuestionAdministrationInteraction::IDENTIFIER:
                    yield $this->factory->buildQuestionAdministrationInteractionFromDBValues($interaction);
                    break;
                case TestParticipantInteraction::IDENTIFIER:
                    yield $this->factory->buildParticipantInteractionFromDBValues($interaction);
                    break;
                case TestScoringInteraction::IDENTIFIER:
                    yield $this->factory->buildScoringInteractionFromDBValues($interaction);
                    break;
                case TestError::IDENTIFIER:
                    yield $this->factory->buildErrorFromDBValues($interaction);
                    break;
            }
        }
    }

    private function buildSelectStatementForId(int $id, string $table_name): \ilDBStatement
    {
        return $this->db->queryF(
            "SELECT * FROM {$table_name} WHERE id=%s",
            [\ilDBConstants::T_INTEGER],
            [$id]
        );
    }

    private function buildDeleteQueryForUniqueIds(array $unique_ids): string
    {
        if ($unique_ids[0] === 'ALL_OBJECTS') {
            return 'TUNCATE TABLE ' . self::TEST_ADMINISTRATION_LOG_TABLE . ';'
                . 'TUNCATE TABLE ' . self::QUESTION_ADMINISTRATION_LOG_TABLE . ';'
                . 'TUNCATE TABLE ' . self::PARTICIPANT_LOG_TABLE . ';'
                . 'TUNCATE TABLE ' . self::SCORING_LOG_TABLE . ';'
                . 'TUNCATE TABLE ' . self::ERROR_LOG_TABLE . ';';
        }
        $query = '';
        foreach ($this->parseUniqueIdsToTypeArray($unique_ids) as $type => $values) {
            $query .= "DELETE FROM {$this->getTableNameForTypeIdentifier($type)} WHERE "
                . $this->db->in('id', $values, false, \ilDBConstants::T_INTEGER) . ';' . PHP_EOL;
        }
        return $query;
    }

    private function buildInteractionsStatementWithLimitAndOrder(
        array $valid_types,
        ?Range $range,
        ?Order $order,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): \ilDBStatement {
        $query = $this->buildInteractionsQuery(
            $valid_types,
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

        $init = PHP_EOL . 'ORDER BY ';
        $order_by_string = $order?->join(
            $init,
            static fn(string $ret, string $key, string $value): string => "{$ret} "
                . self::VIEW_TABLE_TO_DB_TABLES[$key] . " {$value}, ",
        );
        if ($order_by_string !== null
            && $order_by_string !== $init) {
            $query .= mb_substr($order_by_string, 0, -2);
        }

        if ($range !== null) {
            $query .= PHP_EOL . 'LIMIT ' . $range->getStart() . ', ' . $range->getLength();
        }

        return $this->db->query($query);
    }

    private function buildCountQuery(
        array $valid_types,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
        ?array $log_entry_type_filter,
        ?array $interaction_type_filter
    ): ?string {
        $tables_query = $this->buildInteractionsQuery(
            $valid_types,
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
        if ($tables_query === '') {
            return '';
        }
        return 'SELECT COUNT(*) AS cnt FROM (' . PHP_EOL . $tables_query . PHP_EOL . ') x';
    }

    private function buildInteractionsQuery(
        array $valid_types,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
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

        return implode(
            PHP_EOL . 'UNION' . PHP_EOL,
            array_filter(
                $query,
                static fn(?string $select): bool => $select !== null
            )
        );
    }

    private function buildTableQueryFromFilterValues(
        string $type,
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
        ?array $interaction_type_filter
    ): ?string {
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
            return null;
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
                return "SELECT '{$type}' AS type, id, ref_id, NULL AS qst_id, admin_id, "
                    . 'NULL AS pax_id, NULL AS source_ip, interaction_type, modification_ts, '
                    . "additional_data FROM {$table_name}";
            case self::QUESTION_ADMINISTRATION_LOG_TABLE:
                return "SELECT '{$type}' AS type, id, ref_id, qst_id, admin_id, "
                    . 'NULL AS pax_id, NULL AS source_ip, interaction_type, modification_ts, '
                    . "additional_data FROM {$table_name}";
            case self::PARTICIPANT_LOG_TABLE:
                return "SELECT '{$type}' AS type, id, ref_id, qst_id, NULL AS admin_id, "
                    . 'pax_id, source_ip, interaction_type, modification_ts, '
                    . "additional_data FROM {$table_name}";
            case self::SCORING_LOG_TABLE:
                return "SELECT '{$type}' AS type, id, ref_id, qst_id, admin_id, "
                    . 'pax_id, NULL AS source_ip, interaction_type, modification_ts, '
                    . "additional_data FROM {$table_name}";
            case self::ERROR_LOG_TABLE:
                return "SELECT '{$type}' AS type, id, ref_id, qst_id, admin_id, "
                    . 'pax_id, NULL AS source_ip, interaction_type, modification_ts, '
                    . "error_message AS additional_data FROM {$table_name}";
            default:
                throw new \ErrorException('Unknown Database Table');
        }
    }

    private function buildWhereFromFilterValues(
        ?int $from_filter,
        ?int $to_filter,
        ?array $test_filter,
        ?array $admin_filter,
        ?array $pax_filter,
        ?array $question_filter,
        ?string $ip_filter,
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
            $where[] = $this->db->in('ref_id', $test_filter, false, \ilDBConstants::T_INTEGER);
        }
        if ($admin_filter !== null) {
            $where[] = $this->db->in('admin_id', $admin_filter, false, \ilDBConstants::T_INTEGER);
        }
        if ($pax_filter !== null) {
            $where[] = $this->db->in('pax_id', $pax_filter, false, \ilDBConstants::T_INTEGER);
        }
        if ($question_filter !== null) {
            $where[] = $this->db->in('qst_id', $question_filter, false, \ilDBConstants::T_INTEGER);
        }
        if ($ip_filter !== null) {
            $where[] = $this->db->like('source_ip', \ilDBConstants::T_TEXT, $ip_filter);
        }
        if ($interaction_type_filter !== null) {
            $where[] = $this->db->in('interaction_type', $interaction_type_filter, false, \ilDBConstants::T_TEXT);
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
        ?array $filter_log_types,
        array $filter_interaction_types
    ): bool {
        if ($filter_log_types !== null
            && $filter_log_types !== []) {
            $valid_types = array_filter(
                $valid_types,
                fn(string $key): bool => in_array($key, $filter_log_types),
                ARRAY_FILTER_USE_KEY
            );
        }
        $valid_interaction_types = array_reduce(
            $valid_types,
            fn(array $et, array $it): array => [...$et, ...$it],
            []
        );

        if (
            array_intersect($filter_interaction_types, $valid_interaction_types)
                === $filter_interaction_types
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int> $unique_ids
     * @return array<string, array<int>>
     */
    private function parseUniqueIdsToTypeArray(array $unique_ids): array
    {
        return array_reduce(
            $unique_ids,
            function (array $type_array, string $unique_id): array {
                $unique_id_array = explode('_', $unique_id);
                if (count($unique_id_array) !== 2
                    || !is_numeric($unique_id_array[1])) {
                    return $type_array;
                }

                if (!array_key_exists($unique_id_array[0], $type_array)) {
                    $type_array[$unique_id_array[0]] = [];
                }

                $type_array[$unique_id_array[0]][] = $unique_id_array[1];
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
            default:
                throw new \ErrorException('Unknown Identifier Type');
        }
    }
}
