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
    public const MARKING_LOG_TABLE = 'tst_mark_log';
    public const ERROR_LOG_TABLE = 'tst_error_log';


    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function storeTestAdministrationInteraction(TestAdministrationInteraction $interaction): void
    {
        $this->db->insert(self::TEST_ADMINISTRATION_LOG_TABLE, $interaction->toStorage());
    }

    public function storeQuestionAdministrationInteraction(TestQuestionAdministrationInteraction $interaction): void
    {
        $this->db->insert(self::QUESTION_ADMINISTRATION_LOG_TABLE, $interaction->toStorage());
    }

    public function storeParticipantInteraction(TestParticipantInteraction $interaction): void
    {
        $this->db->insert(self::PARTICIPANT_LOG_TABLE, $interaction->toStorage());
    }

    public function storeMarkingInteraction(TestMarkingInteraction $interaction): void
    {
        $this->db->insert(self::MARKING_LOG_TABLE, $interaction->toStorage());
    }

    public function storeError(TestError $interaction): void
    {
        $this->db->insert(self::ERROR_LOG_TABLE, $interaction->toStorage());
    }

    /**
     * @return array<ILIAS\Test\Logging\TestUserInteraction>
     */
    public function getLogsForRefId(int $ref_id = null): array
    {

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
}
