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
     * @return array<\ILIAS\Test\Logging\TestUserInteraction>
     */
    public function getLogs(
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        array $filter_data,
        ?int $ref_id
    ): \Generator {
        yield ;
    }

    public function getLog(
        string $unique_identifier
    ): TestUserInteraction {
        return $this->buildUserInteractionForUniqueIdentifier($unique_identifier);
    }

    /**
     * @param array<string> $unique_identifiers
     */
    public function deleteLogs(
        array $unique_identifiers
    ): void {

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

    private function buildUserInteractionForUniqueIdentifier(string $unique_identifier): ?TestUserInteraction
    {
        $unique_identifier_array = explode('_', $unique_identifier);
        if (count($unique_identifier_array) !== 2
            || !is_numeric($unique_identifier_array[1])) {
            return null;
        }

        switch ($unique_identifier_array[0]) {
            case TestAdministrationInteraction::IDENTIFIER:
                return $this->buildTestAdministrationInteractionFromId($unique_identifier_array[1]);

            case TestQuestionAdministrationInteraction::IDENTIFIER:
                return $this->buildQuestionTestAdministrationInteractionFromId($unique_identifier_array[1]);

            case TestParticipantInteraction::IDENTIFIER:
                return $this->buildParticipantInteractionFromId($unique_identifier_array[1]);

            case TestScoringInteraction::IDENTIFIER:
                return $this->buildScoringInteractionFromId($unique_identifier_array[1]);

            case TestError::IDENTIFIER:
                return $this->buildErrorFromId($unique_identifier_array[1]);
        }
    }

    private function buildTestAdministrationInteractionFromId(int $id): ?TestAdministrationInteraction
    {
        $query = $this->buildSelectByIdQuery($id, self::TEST_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildTestAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildQuestionAdministrationInteractionFromId(int $id): ?TestQuestionAdministrationInteraction
    {
        $query = $this->buildSelectByIdQuery($id, self::QUESTION_ADMINISTRATION_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildQuestionAdministrationInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildParticipantInteractionFromId(int $id): ?TestParticipantInteraction
    {
        $query = $this->buildSelectByIdQuery($id, self::PARTICIPANT_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildParticipantInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildScoringInteractionFromId(int $id): ?TestScoringInteraction
    {
        $query = $this->buildSelectByIdQuery($id, self::SCORING_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildScoringInteractionFromDBValues($this->db->fetchObject($query));
    }

    private function buildErrorFromId(int $id): ?TestError
    {
        $query = $this->buildSelectByIdQuery($id, self::ERROR_LOG_TABLE);
        if ($this->db->numRows($query) !== 0) {
            return null;
        }

        return $this->factory->buildErrorFromDBValues($this->db->fetchObject($query));
    }

    private function buildSelectByIdQuery(int $id, string $table_name): \ilDBStatement
    {
        return $this->db->queryF(
            'SELECT * FROM ' . $table_name . ' WHERE id=%s',
            [\ilDBConstants::T_INTEGER],
            [$id]
        );
    }
}
