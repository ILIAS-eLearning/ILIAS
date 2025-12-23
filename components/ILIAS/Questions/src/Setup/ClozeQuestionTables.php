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

use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Persistence\TableTypes;

class ClozeQuestionTables implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function __construct(
        private readonly TableNameBuilder $table_name_builder
    ) {
    }

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $table_name = $this->table_name_builder->getTableNameFor(TableTypes::TypeSpecificAnswerForms);
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'answer_form_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                 'scoring_identical_responses' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 32,
                    'notnull' => true
                ],
                'combinations_activated' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['answer_form_id'])) {
            $this->db->addPrimaryKey($table_name, ['answer_form_id']);
        }
    }

    public function step_2(): void
    {
        $table_name = $this->table_name_builder->getTableNameFor(TableTypes::AnswerInputs);
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'answer_form_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'position' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => true
                ],
                'gap_type' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 32,
                    'notnull' => true
                ],
                'max_chars' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => false
                ],
                'step_size' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ],
                'text_matching_method' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 32,
                    'notnull' => false
                ],
                'min_autocomplete' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => false
                ],
                'shuffle_answer_options' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => false
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['answer_form_id'])) {
            $this->db->addIndex($table_name, ['answer_form_id'], 'af');
        }
    }

    public function step_3(): void
    {
        $table_name = $this->table_name_builder->getTableNameFor(TableTypes::AnswerOptions);
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'answer_input_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'position' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => true
                ],
                'text_value' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => false
                ],
                'lower_limit' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ],
                'upper_limit' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ],
                'points' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['answer_input_id'])) {
            $this->db->addIndex($table_name, ['answer_input_id'], 'ai');
        }
    }

    public function step_4(): void
    {
        $table_name = $this->table_name_builder->getTableNameFor(TableTypes::Additional, 'combinations');
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'answer_form_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'answer_options' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => true
                ],
                'points' => [
                    'type' => \ilDBConstants::T_FLOAT,
                    'notnull' => false
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['answer_form_id'])) {
            $this->db->addIndex($table_name, ['answer_form_id'], 'ai');
        }
    }

    public function step_5(): void
    {
        $table_name = $this->table_name_builder->getTableNameFor(TableTypes::Responses);
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'answer_input_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'selected_answer_option' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => false
                ],
                'text' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 4000,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields($table_name, ['id'])) {
            $this->db->addPrimaryKey($table_name, ['id']);
        }

        if (!$this->db->indexExistsByFields($table_name, ['answer_input_id'])) {
            $this->db->addIndex($table_name, ['answer_input_id'], 'ai');
        }
    }
}
