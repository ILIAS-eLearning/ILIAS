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

namespace ILIAS\Questions\Setup;

class OverarchingQuestionTables implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $table_name = 'qsts_questions';
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'page_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'notnull' => true
                ],
                'title' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 512,
                    'notnull' => true
                ],
                'author' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 512,
                    'notnull' => false
                ],
                'lifecycle' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 16,
                    'notnull' => true
                ],
                'remarks' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => false
                ],
                'original_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => false
                ],
                'last_update' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
                'created' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 8,
                    'notnull' => true
                ],
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }
    }

    public function step_2(): void
    {
        $table_name = 'qsts_answer_forms';
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => true
                ],
                'question_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'available_points' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ],
                'image_size' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => false
                ],
                'shuffle_answer_options' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => true
                ],
                'additional_text' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'additional_text_legacy' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => false
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['question_id'])) {
            $this->db->addIndex($table_name, ['question_id'], 'q');
        }
    }

    public function step_3(): void
    {
        $table_name = 'qsts_responses';
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'question_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'reached_points' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['question_id'])) {
            $this->db->addIndex($table_name, ['question_id'], 'q');
        }
    }

    public function step_4(): void
    {
        $table_name = 'qsts_linking';
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'question_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'obj_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['question_id'])) {
            $this->db->addPrimaryKey($table_name, ['question_id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['obj_id'])) {
            $this->db->addIndex($table_name, ['obj_id'], 'o');
        }
    }
}
