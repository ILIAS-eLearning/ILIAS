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

namespace ILIAS\Test\Setup;

use ILIAS\Test\Logging\TestLoggingDatabaseRepository;
use ILIAS\Test\Certificate\TestPlaceholderValues;

class Test10DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableColumnExists('tst_tests', 'ip_range_from')) {
            $this->db->addTableColumn(
                'tst_tests',
                'ip_range_from',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }
        if (!$this->db->tableColumnExists('tst_tests', 'ip_range_to')) {
            $this->db->addTableColumn(
                'tst_tests',
                'ip_range_to',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }
    }

    public function step_2(): void
    {
        $this->db->update(
            'il_cert_cron_queue',
            ['adapter_class' => [\ilDBConstants::T_TEXT, TestPlaceholderValues::class]],
            ['adapter_class' => [\ilDBConstants::T_TEXT, 'ilTestPlaceholderValues']]
        );
    }

    public function step_3(): void
    {
        if (!$this->db->tableExists(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE, [
                'id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'admin_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'interaction_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => true
                ],
                'modification_ts' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'additional_data' => [
                    'type' => \ilDBConstants::T_CLOB
                ]
            ]);
            $this->db->createSequence(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE);
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE, ['id']);
            $this->db->addIndex(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE, ['ref_id'], 'rid');
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE, [
                'id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'qst_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8
                ],
                'admin_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'interaction_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => true
                ],
                'modification_ts' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'additional_data' => [
                    'type' => \ilDBConstants::T_CLOB
                ]
            ]);
            $this->db->createSequence(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE);
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE, ['id']);
            $this->db->addIndex(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE, ['ref_id'], 'rid');
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE, [
                'id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'qst_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => false
                ],
                'pax_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'source_ip' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 42,
                    'notnull' => true
                ],
                'interaction_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => true
                ],
                'modification_ts' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'additional_data' => [
                    'type' => \ilDBConstants::T_CLOB
                ]
            ]);
            $this->db->createSequence(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE);
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE, ['id']);
            $this->db->addIndex(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE, ['ref_id'], 'rid');
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::SCORING_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::SCORING_LOG_TABLE, [
                'id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'qst_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'admin_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'pax_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'interaction_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => true
                ],
                'modification_ts' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'additional_data' => [
                    'type' => \ilDBConstants::T_CLOB
                ]
            ]);
            $this->db->createSequence(TestLoggingDatabaseRepository::SCORING_LOG_TABLE);
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::SCORING_LOG_TABLE, ['id']);
            $this->db->addIndex(TestLoggingDatabaseRepository::SCORING_LOG_TABLE, ['ref_id'], 'rid');
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::ERROR_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::ERROR_LOG_TABLE, [
                'id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'ref_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'qst_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8
                ],
                'admin_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8
                ],
                'pax_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8
                ],
                'interaction_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => true
                ],
                'modification_ts' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'error_message' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => true,
                    'default' => ''
                ]
            ]);
            $this->db->createSequence(TestLoggingDatabaseRepository::ERROR_LOG_TABLE);
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::ERROR_LOG_TABLE, ['id']);
            $this->db->addIndex(TestLoggingDatabaseRepository::ERROR_LOG_TABLE, ['ref_id'], 'rid');
        }
    }

    public function step_4(): void
    {
        if (!$this->db->tableColumnExists('tst_invited_user', 'ip_range_from')) {
            $this->db->addTableColumn(
                'tst_invited_user',
                'ip_range_from',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }
        if (!$this->db->tableColumnExists('tst_invited_user', 'ip_range_to')) {
            $this->db->addTableColumn(
                'tst_invited_user',
                'ip_range_to',
                [
                    'type' => 'text',
                    'length' => 39
                ]
            );
        }


        if ($this->db->tableColumnExists('tst_invited_user', 'clientip')) {
            $this->db->manipulate('UPDATE tst_invited_user SET ip_range_from = clientip, ip_range_to = clientip WHERE ip_range_from IS NULL AND ip_range_to IS NULL');
            $this->db->dropTableColumn('tst_invited_user', 'clientip');
        }
    }

    public function step_5(): void
    {
        if (!$this->db->tableColumnExists('tst_addtime', 'user_fi')) {
            $this->db->addTableColumn(
                'tst_addtime',
                'user_fi',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ]
            );
        }
        if (!$this->db->tableColumnExists('tst_addtime', 'test_fi')) {
            $this->db->addTableColumn(
                'tst_addtime',
                'test_fi',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ]
            );
        }

        if ($this->db->tableColumnExists('tst_addtime', 'active_fi')) {
            $this->db->manipulate(
                '
                UPDATE tst_addtime INNER JOIN tst_active ON tst_active.active_id = tst_addtime.active_fi
                SET tst_addtime.test_fi = tst_active.test_fi, tst_addtime.user_fi = tst_active.user_fi'
            );

            $this->db->dropTableColumn('tst_addtime', 'active_fi');
        }

        if (!$this->db->primaryExistsByFields('tst_addtime', ['user_fi', 'test_fi'])) {
            $this->db->addPrimaryKey('tst_addtime', ['user_fi', 'test_fi']);
        }
    }

    public function step_6(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'broken')) {
            $this->db->dropTableColumn('tst_tests', 'broken');
        }
    }

    public function step_7(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'obligations_enabled')) {
            $this->db->dropTableColumn('tst_tests', 'obligations_enabled');
        }

        if ($this->db->tableColumnExists('tst_pass_result', 'obligations_answered')) {
            $this->db->dropTableColumn('tst_pass_result', 'obligations_answered');
        }

        if ($this->db->tableColumnExists('tst_test_question', 'obligatory')) {
            $this->db->dropTableColumn('tst_test_question', 'obligatory');
        }

        if ($this->db->tableColumnExists('tst_result_cache', 'obligations_answered')) {
            $this->db->dropTableColumn('tst_result_cache', 'obligations_answered');
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists('tst_pass_result', 'finalized_by')) {
            $this->db->addTableColumn(
                'tst_pass_result',
                'finalized_by',
                [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 256,
                    'notnull' => false
                ]
            );
        }
    }

    public function step_9(): void
    {
        $this->db->manipulate('DELETE FROM rbac_operations WHERE operation = "tst_statistics"');
    }

    public function step_10(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'author')) {
            $this->db->dropTableColumn('tst_tests', 'author');
        }
    }

    public function step_11(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'enable_processing_time')) {
            $this->db->manipulateF(
                'UPDATE tst_tests SET enable_processing_time = %s WHERE enable_processing_time IS NULL',
                [\ilDBConstants::T_INTEGER],
                [0]
            );
            $this->db->modifyTableColumn(
                'tst_tests',
                'enable_processing_time',
                [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ]
            );
        }
    }
}
