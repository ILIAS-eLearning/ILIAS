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

namespace ILIAS\Test\Settings;

use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;
use ILIAS\Test\Settings\MainSettings\MainSettings;
use ILIAS\Test\Settings\MainSettings\RedirectionModes;
use ILIAS\Test\Settings\MainSettings\SettingsAccess;
use ILIAS\Test\Settings\MainSettings\SettingsAdditional;
use ILIAS\Test\Settings\MainSettings\SettingsFinishing;
use ILIAS\Test\Settings\MainSettings\SettingsGeneral;
use ILIAS\Test\Settings\MainSettings\SettingsIntroduction;
use ILIAS\Test\Settings\MainSettings\SettingsParticipantFunctionality;
use ILIAS\Test\Settings\MainSettings\SettingsQuestionBehaviour;
use ILIAS\Test\Settings\MainSettings\SettingsTestBehaviour;
use ILIAS\Test\Settings\ScoreReporting\ScoreReportingTypes;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;
use ILIAS\Test\Settings\ScoreReporting\SettingsGamification;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultDetails;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\Test\Settings\Templates\PersonalSettingsTemplate;

class SettingsFactory
{
    public function createMainSettingsFromDBRow(array $row): MainSettings
    {
        return new MainSettings(
            $row['id'],
            new SettingsGeneral(
                $row['question_set_type'],
                (bool) $row['anonymity']
            ),
            new SettingsIntroduction(
                (bool) $row['intro_enabled'],
                $row['introduction'],
                $row['introduction_page_id'],
                (bool) $row['conditions_checkbox_enabled'],
            ),
            new SettingsAccess(
                (bool) $row['starting_time_enabled'],
                $row['starting_time'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['starting_time'])
                    : null,
                (bool) $row['ending_time_enabled'],
                $row['ending_time'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['ending_time'])
                    : null,
                (bool) $row['password_enabled'],
                $row['password'],
                $row['ip_range_from'],
                $row['ip_range_to'],
                (bool) $row['fixed_participants'],
            ),
            new SettingsTestBehaviour(
                $row['nr_of_tries'],
                (bool) $row['block_after_passed'],
                $row['pass_waiting'],
                (bool) $row['enable_processing_time'],
                $row['processing_time'],
                (bool) $row['reset_processing_time'],
                $row['kiosk'],
                (bool) $row['examid_in_test_pass']
            ),
            new SettingsQuestionBehaviour(
                (int) $row['title_output'],
                (bool) $row['autosave'],
                $row['autosave_ival'],
                (bool) $row['shuffle_questions'],
                (bool) $row['answer_feedback_points'],
                (bool) $row['answer_feedback'],
                (bool) $row['specific_feedback'],
                (bool) $row['instant_verification'],
                (bool) $row['force_inst_fb'],
                (bool) $row['inst_fb_answer_fixation'],
                (bool) $row['follow_qst_answer_fixation']
            ),
            new SettingsParticipantFunctionality(
                (bool) $row['use_previous_answers'],
                (bool) $row['suspend_test_allowed'],
                (bool) $row['sequence_settings'],
                $row['usr_pass_overview_mode'],
                (bool) $row['show_marker'],
                (bool) $row['show_questionlist']
            ),
            new SettingsFinishing(
                (bool) $row['enable_examview'],
                (bool) $row['showfinalstatement'],
                $row['finalstatement'],
                $row['concluding_remarks_page_id'],
                RedirectionModes::tryFrom($row['redirection_mode']) ?? RedirectionModes::NONE,
                $row['redirection_url']
            ),
            new SettingsAdditional(
                (bool) $row['skill_service'],
                (bool) $row['hide_info_tab']
            )
        );
    }

    public function createDefaultMainSettings(): MainSettings
    {
        return new MainSettings(
            0,
            new SettingsGeneral(),
            new SettingsIntroduction(),
            new SettingsAccess(),
            new SettingsTestBehaviour(),
            new SettingsQuestionBehaviour(
                0,
                false,
                0,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false
            ),
            new SettingsParticipantFunctionality(),
            new SettingsFinishing(),
            new SettingsAdditional()
        );
    }

    public function createScoreSettingsFromDBRow(array $row): ScoreSettings
    {
        return new ScoreSettings(
            $row['id'],
            (new SettingsScoring())
                ->withCountSystem((int) $row['count_system'])
                ->withScoreCutting((int) $row['score_cutting'])
                ->withPassScoring((int) $row['pass_scoring']),
            (new SettingsResultSummary())
                ->withScoreReporting(ScoreReportingTypes::from($row['score_reporting']))
                ->withReportingDate(!empty($row['reporting_date'])
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['reporting_date'])
                    : null)
                ->withShowGradingStatusEnabled((bool) $row['show_grading_status'])
                ->withShowGradingMarkEnabled((bool) $row['show_grading_mark'])
                ->withPassDeletionAllowed((bool) $row['pass_deletion_allowed']),
            //->withShowPassDetails derived from results_presentation with bit RESULTPRES_BIT_PASS_DETAILS
            (new SettingsResultDetails())
                ->withResultsPresentation((int) $row['results_presentation'])
                ->withShowExamIdInTestResults((bool) $row['examid_in_test_res'])
                ->withExportSettings((int) $row['exportsettings']),
            (new SettingsGamification())
                ->withHighscoreEnabled((bool) $row['highscore_enabled'])
                ->withHighscoreAnon((bool) $row['highscore_anon'])
                ->withHighscoreAchievedTS((bool) $row['highscore_achieved_ts'])
                ->withHighscoreScore((bool) $row['highscore_score'])
                ->withHighscorePercentage((bool) $row['highscore_percentage'])
                ->withHighscoreWTime((bool) $row['highscore_wtime'])
                ->withHighscoreOwnTable((bool) $row['highscore_own_table'])
                ->withHighscoreTopTable((bool) $row['highscore_top_table'])
                ->withHighscoreTopNum((int) $row['highscore_top_num'])
        );
    }

    public function createTemplateFromDBRow(array $row): PersonalSettingsTemplate
    {
        return new PersonalSettingsTemplate(
            $row['test_defaults_id'],
            $row['user_fi'],
            $row['name'],
            $row['description'] ?? '',
            $row['author'] ?? '',
            \DateTimeImmutable::createFromFormat('U', (string) $row['tstamp']),
            $row['settings_id'] ?? -1,
        );
    }
}
