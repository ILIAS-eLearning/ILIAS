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

use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableTypes;
use ILIAS\Questions\AnswerForm\Capabilities\Feedback\TableTypes as FeedbackTableTypes;
use ILIAS\Questions\Question\Persistence\TableTypes as QuestionTableTypes;
use ILIAS\Questions\Persistence\TableNameBuilder;

class OverarchingQuestionTables implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function __construct(
        private readonly TableNameBuilder $basic_table_name_builder
    ) {
    }

    #[\Override]
    public function prepare(
        \ilDBInterface $db
    ): void {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            QuestionTableTypes::Questions
        );
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
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            AnswerFormGenericTableTypes::AnswerForms
        );
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
                    'notnull' => false
                ],
                'additional_text' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'additional_text_legacy' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
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
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            QuestionTableTypes::Responses
        );
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
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            QuestionTableTypes::Linking
        );
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
                ],
                'position' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 2,
                    'notnull' => false
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

    public function step_5(): void
    {
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            QuestionTableTypes::MigrationsTable
        );
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'new_question_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'old_question_id' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 4,
                    'notnull' => false
                ],
                'success' => [
                    'type' => \ilDBConstants::T_INTEGER,
                    'length' => 1,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields(
            $table_name,
            ['old_question_id']
        )) {
            $this->db->addPrimaryKey(
                $table_name,
                ['old_question_id']
            );
        }
    }

    public function step_6(): void
    {
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            FeedbackTableTypes::FeedbackGeneric
        );
        if (!$this->db->tableExists($table_name)) {
            $this->db->createTable($table_name, [
                'answer_form_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'feedback_best_response' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'feedback_best_response_legacy' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'feedback_other_response' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'feedback_other_response_legacy' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields(
            $table_name,
            ['answer_form_id']
        )) {
            $this->db->addPrimaryKey(
                $table_name,
                ['answer_form_id']
            );
        }
    }

    public function step_7(): void
    {
        $table_name = $this->basic_table_name_builder->getTableNameFor(
            FeedbackTableTypes::FeedbackSpecific
        );
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
                'parent_id' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => true
                ],
                'condition' => [
                    'type' => \ilDBConstants::T_TEXT,
                    'length' => 64,
                    'notnull' => false
                ],
                'feedback' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ],
                'feedback_legacy' => [
                    'type' => \ilDBConstants::T_CLOB,
                    'notnull' => true
                ]
            ]);
        }

        if (!$this->db->primaryExistsByFields(
            $table_name,
            ['id']
        )) {
            $this->db->addPrimaryKey(
                $table_name,
                ['id'],
            );
        }

        if (!$this->db->indexExistsByFields(
            $table_name,
            [
                'answer_form_id',
                'parent_id',
                'condition'
            ]
        )) {
            $this->db->manipulate(
                "CREATE UNIQUE INDEX apc_idx ON {$table_name} (`answer_form_id`, `parent_id`, `condition`)"
            );
        }
    }
}
