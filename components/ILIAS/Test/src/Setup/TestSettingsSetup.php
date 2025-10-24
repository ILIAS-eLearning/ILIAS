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

const T_BOOLEAN = ['type' => \ilDBConstants::T_INTEGER, 'length' => 1, 'default' => 0];
const T_TINYINT = ['type' => \ilDBConstants::T_INTEGER, 'length' => 4, 'default' => 0];
const T_BIGINT = ['type' => \ilDBConstants::T_INTEGER, 'default' => 0];

const LEGACY_STORAGE_DATE_FORMAT = 'YmdHis';

trait TestSettingsSetup
{
    private const array SETTINGS_COLUMNS = [
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
        'reporting_date' => [T_BIGINT, 'ReportingDate'],
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

    private function columnIsNullable(array $column_def): bool
    {
        return array_key_exists('default', $column_def) && $column_def['default'] === null;
    }

    private function convertLegacyDate(string|\DateTimeImmutable|null $date): int
    {
        if ($date instanceof \DateTimeImmutable) {
            return $date->getTimestamp();
        }

        if ($date === '' || $date === null) {
            return 0;
        }

        return \DateTimeImmutable::createFromFormat(
            LEGACY_STORAGE_DATE_FORMAT,
            $date,
            new \DateTimeZone('UTC')
        )->getTimestamp();
    }
}
