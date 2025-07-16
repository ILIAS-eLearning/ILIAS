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

use ILIAS\components\Database\PDO\FieldDefinition\ForeignKeyConstraints;

const T_BOOLEAN = ['type' => \ilDBConstants::T_INTEGER, 'length' => 1, 'default' => 0];
const T_TINYINT = ['type' => \ilDBConstants::T_INTEGER, 'length' => 4, 'default' => 0];
const T_BIGINT = ['type' => \ilDBConstants::T_INTEGER, 'default' => 0];
const SETTINGS_COLUMNS = [
    'introduction' => [['type' => \ilDBConstants::T_TEXT, 'length' => 4000, 'default' => null], null],
    'sequence_settings' => [T_BOOLEAN, 'SequenceSettings'],
    'score_reporting' => [T_TINYINT, 'ScoreReporting'],
    'instant_verification' => [T_BOOLEAN, 'InstantFeedbackSolution'],
    'answer_feedback' => [T_BOOLEAN, 'AnswerFeedback'],
    'answer_feedback_points' => [T_BOOLEAN, 'AnswerFeedbackPoints'],
    'fixed_participants' => [T_BOOLEAN, 'fixed_participants'],
    'suspend_test_allowed' => [T_BOOLEAN, 'ShowCancel'],
    'anonymity' => [T_BOOLEAN, 'Anonymity'],
    'nr_of_tries' => [['type' => \ilDBConstants::T_INTEGER, 'length' => 8, 'default' => 0], 'NrOfTries'],
    'use_previous_answers' => [T_BOOLEAN, 'use_previous_answers'],
    'title_output' => [T_TINYINT, 'TitleOutput'],
    'processing_time' => [['type' => \ilDBConstants::T_TEXT, 'length' => 8, 'default' => null], 'ProcessingTime'],
    'enable_processing_time' => [T_BOOLEAN, 'EnableProcessingTime'],
    'reset_processing_time' => [T_BOOLEAN, 'ResetProcessingTime'],
    'reporting_date' => [['type' => \ilDBConstants::T_TEXT, 'length' => 14, 'default' => null], 'ReportingDate'],
    'shuffle_questions' => [T_BOOLEAN, 'Shuffle'],
    'count_system' => [T_TINYINT, 'CountSystem'],
    'score_cutting' => [T_TINYINT, 'ScoreCutting'],
    'pass_scoring' => [T_TINYINT, 'PassScoring'],
    'password' => [['type' => \ilDBConstants::T_TEXT, 'length' => 20, 'default' => null], 'password'],
    'results_presentation' => [['type' => \ilDBConstants::T_INTEGER, 'default' => 3], 'ResultsPresentation'],
    'usr_pass_overview_mode' => [T_BIGINT, 'ListOfQuestionsSettings'],
    'show_marker' => [T_BOOLEAN, 'ShowMarker'],
    'kiosk' => [T_BIGINT, 'Kiosk'],
    'finalstatement' => [['type' => \ilDBConstants::T_TEXT, 'length' => 4000, 'default' => null], null],
    'showfinalstatement' => [T_BOOLEAN, 'ShowFinalStatement'],
    'exportsettings' => [T_BIGINT, null],
    'print_bs_with_res' => [['type' => \ilDBConstants::T_INTEGER, 'length' => 1, 'default' => 0], 'show_solution_list_comparison'],
    'highscore_enabled' => [T_BOOLEAN, 'highscore_enabled'],
    'highscore_anon' => [T_BOOLEAN, 'highscore_anon'],
    'highscore_achieved_ts' => [T_BOOLEAN, 'highscore_achieved_ts'],
    'highscore_score' => [T_BOOLEAN, 'highscore_score'],
    'highscore_percentage' => [T_BOOLEAN, 'highscore_percentage'],
    'highscore_wtime' => [T_BOOLEAN, 'highscore_wtime'],
    'highscore_own_table' => [T_BOOLEAN, 'highscore_own_table'],
    'highscore_top_table' => [T_BOOLEAN, 'highscore_top_table'],
    'highscore_top_num' => [T_BIGINT, 'highscore_top_num'],
    'specific_feedback' => [T_BOOLEAN, 'SpecificAnswerFeedback'],
    'autosave' => [T_BOOLEAN, 'autosave'],
    'autosave_ival' => [T_BIGINT, 'autosave_ival'],
    'pass_deletion_allowed' => [T_BOOLEAN, 'pass_deletion_allowed'],
    'redirection_mode' => [T_TINYINT, 'redirection_mode'],
    'redirection_url' => [['type' => \ilDBConstants::T_TEXT, 'length' => 4000, 'default' => null], 'redirection_url'],
    'examid_in_test_pass' => [T_BOOLEAN, 'examid_in_test_pass'],
    'examid_in_test_res' => [T_BOOLEAN, 'examid_in_test_res'],
    'enable_examview' => [T_BOOLEAN, 'enable_examview'],
    'question_set_type' => [['type' => \ilDBConstants::T_TEXT, 'length' => 32, 'default' => 'FIXED_QUEST_SET'], 'questionSetType'],
    'skill_service' => [T_BOOLEAN, 'skill_service'],
    'show_grading_status' => [T_BOOLEAN, 'show_grading_status'],
    'show_grading_mark' => [T_BOOLEAN, 'show_grading_mark'],
    'inst_fb_answer_fixation' => [T_BOOLEAN, 'inst_fb_answer_fixation'],
    'intro_enabled' => [T_BOOLEAN, 'IntroEnabled'],
    'starting_time_enabled' => [T_BOOLEAN, 'StartingTimeEnabled'],
    'ending_time_enabled' => [T_BOOLEAN, 'EndingTimeEnabled'],
    'password_enabled' => [T_BOOLEAN, 'password_enabled'],
    'force_inst_fb' => [T_BOOLEAN, 'force_inst_fb'],
    'starting_time' => [T_BIGINT, 'StartingTime'],
    'ending_time' => [T_BIGINT, 'EndingTime'],
    'pass_waiting' => [['type' => \ilDBConstants::T_TEXT, 'length' => 15, 'default' => null], 'pass_waiting'],
    'follow_qst_answer_fixation' => [T_BOOLEAN, 'follow_qst_answer_fixation'],
    'block_after_passed' => [T_BOOLEAN, 'BlockAfterPassed'],
    'introduction_page_id' => [['type' => \ilDBConstants::T_INTEGER, 'default' => null], null],
    'concluding_remarks_page_id' => [['type' => \ilDBConstants::T_INTEGER, 'default' => null], null],
    'show_questionlist' => [T_BOOLEAN, null],
    'hide_info_tab' => [T_BOOLEAN, 'HideInfoTab'],
    'conditions_checkbox_enabled' => [T_BOOLEAN, 'ExamConditionsCheckboxEnabled'],
    'ip_range_from' => [['type' => \ilDBConstants::T_TEXT, 'length' => 39, 'default' => null], null],
    'ip_range_to' => [['type' => \ilDBConstants::T_TEXT, 'length' => 39, 'default' => null], null]
];

class Test11DBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if ($this->db->tableColumnExists('tst_tests', 'mailnotification')) {
            $this->db->dropTableColumn('tst_tests', 'mailnotification');
        }
        if ($this->db->tableColumnExists('tst_tests', 'mailnottype')) {
            $this->db->dropTableColumn('tst_tests', 'mailnottype');
        }
    }

    public function step_2(): void
    {
        // 1. Create table schema
        if (!$this->db->tableExists('tst_test_settings')) {
            // Create table and sequence table
            $this->db->createTable(
                'tst_test_settings',
                ['id' => ['type' => \ilDBConstants::T_INTEGER]],
            );
            $this->db->createSequence('tst_test_settings');
            $this->db->addPrimaryKey('tst_test_settings', ['id']);

            // Create table columns
            foreach (SETTINGS_COLUMNS as $key => $value) {
                [$column_def] = $value;

                // No columns should be nullable, except those with NULL by default
                if (!isset($column_def['notnull'])) {
                    $column_def['notnull'] = !$this->columnIsNullable($column_def);
                }

                $this->db->addTableColumn('tst_test_settings', $key, $column_def);
            }
        }

        // 2. Create a foreign key column in tst_tests
        if (!$this->db->tableColumnExists('tst_tests', 'settings_id')) {
            $this->db->addTableColumn(
                'tst_tests',
                'settings_id',
                ['type' => \ilDBConstants::T_INTEGER, 'default' => null, 'notnull' => false],
            );
            $this->db->addForeignKey(
                'test_settings_fkey',
                ['settings_id'],
                'tst_tests',
                ['id'],
                'tst_test_settings',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT,
            );
        }

        // 3. Create a foreign key column in tst_test_defaults
        if (!$this->db->tableColumnExists('tst_test_defaults', 'settings_id')) {
            $this->db->addTableColumn(
                'tst_test_defaults',
                'settings_id',
                ['type' => \ilDBConstants::T_INTEGER, 'default' => null, 'notnull' => false],
            );
            $this->db->addForeignKey(
                'test_default_fkey',
                ['settings_id'],
                'tst_test_defaults',
                ['id'],
                'tst_test_settings',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT
            );
        }

        // 4. Create tst_defaults_marks table to store marks reference
        if (!$this->db->tableExists('tst_defaults_marks')) {
            $this->db->createTable(
                'tst_defaults_marks',
                [
                    'defaults_id' => ['type' => \ilDBConstants::T_INTEGER],
                    'mark_id' => ['type' => \ilDBConstants::T_INTEGER],
                ],
            );
            $this->db->addPrimaryKey('tst_defaults_marks', ['defaults_id', 'mark_id']);

            $this->db->addForeignKey(
                'test_default_fkey',
                ['defaults_id '],
                'tst_defaults_marks',
                ['test_defaults_id'],
                'tst_test_defaults',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT
            );

            $this->db->addForeignKey(
                'test_mark_fkey',
                ['mark_id '],
                'tst_defaults_marks',
                ['mark_id'],
                'tst_mark',
                ForeignKeyConstraints::NO_ACTION,
                ForeignKeyConstraints::RESTRICT
            );
        }
    }

    public function step_3(): void
    {
        // 5. Migrate settings from 'tst_tests' to 'tst_test_settings'
        $columns = implode(',', array_keys(SETTINGS_COLUMNS));
        $tests_rows = $this->db->query("SELECT test_id, $columns FROM tst_tests");
        while ($row = $this->db->fetchAssoc($tests_rows)) {
            $settings_id = $this->db->nextId('tst_test_settings');
            $setting_data = ['id' => [\ilDBConstants::T_INTEGER, $settings_id]];

            foreach ($row as $column_name => $value) {
                if (isset(SETTINGS_COLUMNS[$column_name])) {
                    [$column_def] = SETTINGS_COLUMNS[$column_name];

                    // Convert legacy null values to 0
                    if ($column_def['type'] === \ilDBConstants::T_INTEGER && !$this->columnIsNullable($column_def)) {
                        $value = (int) $value;
                    }

                    $setting_data[$column_name] = [$column_def['type'], $value];
                }
            }

            $this->db->insert('tst_test_settings', $setting_data);
            $this->db->update(
                'tst_tests',
                ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
                ['test_id' => [\ilDBConstants::T_INTEGER, $row['test_id']]]
            );
        }
    }

    public function step_4(): void
    {
        // 6. Migrate settings from 'tst_test_defaults' to 'tst_test_settings' and 'tst_mark'
        $defaults_rows = $this->db->query("SELECT * FROM tst_test_defaults");
        while ($row = $this->db->fetchAssoc($defaults_rows)) {
            $settings_id = $this->db->nextId('tst_test_settings');
            $setting_data = ['id' => [\ilDBConstants::T_INTEGER, $settings_id]];

            // Migrate the legacy serialized column to a row in 'tst_test_settings'
            $raw_settings = unserialize($row['defaults'], ['allowed_classes' => [\DateTimeImmutable::class]]);
            foreach (SETTINGS_COLUMNS as $column_name => $column) {
                [$column_def, $raw_name] = $column;
                if (isset($raw_settings[$raw_name])) {
                    $value = $raw_settings[$raw_name];
                    $setting_data[$column_name] = [$column_def['type'], $value];
                }
            }

            // Insert the new row
            $this->db->insert('tst_test_settings', $setting_data);
            $this->db->update(
                'tst_test_defaults',
                ['settings_id' => [\ilDBConstants::T_INTEGER, $settings_id]],
                ['test_defaults_id' => [\ilDBConstants::T_INTEGER, $row['test_defaults_id']]]
            );

            // Migrate the legacy json decoded to a row in 'tst_mark'
            $raw_marks = json_decode($row['marks'], true);
            foreach ($raw_marks as $mark_data) {
                $mark_id = $this->db->nextId('tst_mark');
                $this->db->insert(
                    'tst_mark',
                    [
                        'mark_id' => [\ilDBConstants::T_INTEGER, $mark_id],
                        'test_fi' => [\ilDBConstants::T_INTEGER, 0],
                        'short_name' => [\ilDBConstants::T_TEXT, $mark_data['short_name']],
                        'official_name' => [\ilDBConstants::T_TEXT, $mark_data['official_name']],
                        'minimum_level' => [\ilDBConstants::T_FLOAT, $mark_data['minimum_level']],
                        'passed' => [\ilDBConstants::T_FLOAT, (int) $mark_data['passed']],
                        'tstamp' => [\ilDBConstants::T_INTEGER, $row['tstamp']],
                    ]
                );

                $this->db->insert(
                    'tst_defaults_marks',
                    [
                        'defaults_id' => [\ilDBConstants::T_INTEGER, $row['test_defaults_id']],
                        'mark_id' => [\ilDBConstants::T_INTEGER, $mark_id],
                    ]
                );
            }
        }
    }

    private function columnIsNullable(array $column_def): bool
    {
        return array_key_exists('default', $column_def) && $column_def['default'] === null;
    }
}
