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

class Test10DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->tableExists(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE, [
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
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::TEST_ADMINISTRATION_LOG_TABLE, ['ref_id']);
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE, [
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
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::QUESTION_ADMINISTRATION_LOG_TABLE, ['ref_id']);
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE, [
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
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::PARTICIPANT_LOG_TABLE, ['ref_id']);
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::MARKING_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::MARKING_LOG_TABLE, [
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
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::MARKING_LOG_TABLE, ['ref_id']);
        }

        if (!$this->db->tableExists(TestLoggingDatabaseRepository::ERROR_LOG_TABLE)) {
            $this->db->createTable(TestLoggingDatabaseRepository::ERROR_LOG_TABLE, [
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
            $this->db->addPrimaryKey(TestLoggingDatabaseRepository::ERROR_LOG_TABLE, ['ref_id']);
        }
    }
}
