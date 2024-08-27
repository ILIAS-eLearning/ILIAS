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

require_once(__DIR__ . "/../../UI/tests/Base.php");
require_once(__DIR__ . "/../../UI/tests/Component/Input/Field/CommonFieldRendering.php");

use ILIAS\Test\Settings\ScoreReporting\ScoreSettings;
use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultDetails;
use ILIAS\Test\Settings\ScoreReporting\SettingsGamification;

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Symbol as S;
use ILIAS\Data;

class ScoreSettingsTest extends ilTestBaseTestCase
{
    use BaseUITestTrait;
    use CommonFieldRendering;

    public function testScoreSettingsBuild(): void
    {
        $id = -666;
        $s = new ScoreSettings(
            $id,
            new SettingsScoring($id),
            new SettingsResultSummary($id),
            new SettingsResultDetails($id),
            new SettingsGamification($id)
        );
        $this->assertInstanceOf(ScoreSettings::class, $s);
        $this->assertEquals($id, $s->getTestId());
        $this->assertInstanceOf(SettingsScoring::class, $s->getScoringSettings());
        $this->assertInstanceOf(SettingsResultSummary::class, $s->getResultSummarySettings());
        $this->assertInstanceOf(SettingsResultDetails::class, $s->getResultDetailsSettings());
        $this->assertInstanceOf(SettingsGamification::class, $s->getGamificationSettings());
    }

    public function testScoreSettingsScoring(): void
    {
        $s = new SettingsScoring(-666);
        $this->assertEquals(-667, $s->withTestId(-667)->getTestId());
        $this->assertEquals(2, $s->withCountSystem(2)->getCountSystem());
        $this->assertEquals(4, $s->withScoreCutting(4)->getScoreCutting());
        $this->assertEquals(5, $s->withPassScoring(5)->getPassScoring());
    }

    public function testScoreSettingsSummary(): void
    {
        $dat = new \DateTimeImmutable();
        $s = new SettingsResultSummary(-666);
        $this->assertEquals(5, $s->withScoreReporting(5)->getScoreReporting());
        $this->assertTrue($s->withScoreReporting(1)->getScoreReportingEnabled());
        $this->assertFalse($s->withScoreReporting(0)->getScoreReportingEnabled());
        $this->assertEquals($dat, $s->withReportingDate($dat)->getReportingDate());
        $this->assertTrue($s->withShowGradingStatusEnabled(true)->getShowGradingStatusEnabled());
        $this->assertFalse($s->withShowGradingStatusEnabled(false)->getShowGradingStatusEnabled());
        $this->assertTrue($s->withShowGradingMarkEnabled(true)->getShowGradingMarkEnabled());
        $this->assertFalse($s->withShowGradingMarkEnabled(false)->getShowGradingMarkEnabled());
        $this->assertTrue($s->withPassDeletionAllowed(true)->getPassDeletionAllowed());
        $this->assertFalse($s->withPassDeletionAllowed(false)->getPassDeletionAllowed());
        $this->assertTrue($s->withShowPassDetails(true)->getShowPassDetails());
        $this->assertFalse($s->withShowPassDetails(false)->getShowPassDetails());
    }

    public function testScoreSettingsDetails(): void
    {
        $s = new SettingsResultDetails(-666);
        $this->assertEquals(192, $s->withResultsPresentation(192)->getResultsPresentation(192));
        $this->assertTrue($s->withShowExamIdInTestResults(true)->getShowExamIdInTestResults());
        $this->assertTrue($s->withShowPassDetails(true)->getShowPassDetails());
        $this->assertFalse($s->withShowPassDetails(false)->getShowPassDetails());
        $this->assertTrue($s->withShowSolutionPrintview(true)->getShowSolutionPrintview());
        $this->assertFalse($s->withShowSolutionPrintview(false)->getShowSolutionPrintview());
        $this->assertTrue($s->withShowSolutionFeedback(true)->getShowSolutionFeedback());
        $this->assertFalse($s->withShowSolutionFeedback(false)->getShowSolutionFeedback());
        $this->assertTrue($s->withShowSolutionAnswersOnly(true)->getShowSolutionAnswersOnly());
        $this->assertFalse($s->withShowSolutionAnswersOnly(false)->getShowSolutionAnswersOnly());
        $this->assertTrue($s->withShowSolutionSignature(true)->getShowSolutionSignature());
        $this->assertFalse($s->withShowSolutionSignature(false)->getShowSolutionSignature());
        $this->assertTrue($s->withShowSolutionSuggested(true)->getShowSolutionSuggested());
        $this->assertFalse($s->withShowSolutionSuggested(false)->getShowSolutionSuggested());
        $this->assertTrue($s->withShowSolutionListComparison(true)->getShowSolutionListComparison());
        $this->assertFalse($s->withShowSolutionListComparison(false)->getShowSolutionListComparison());
        $this->assertTrue($s->withShowPassDetails(true)->getShowPassDetails());
    }

    public function testScoreSettingsGamification(): void
    {
        $s = new SettingsGamification(-666);
        $this->assertTrue($s->withHighscoreEnabled(true)->getHighscoreEnabled());
        $this->assertFalse($s->withHighscoreEnabled(false)->getHighscoreEnabled());
        $this->assertTrue($s->withHighscoreAnon(true)->getHighscoreAnon());
        $this->assertFalse($s->withHighscoreAnon(false)->getHighscoreAnon());
        $this->assertTrue($s->withHighscoreAchievedTS(true)->getHighscoreAchievedTS());
        $this->assertFalse($s->withHighscoreAchievedTS(false)->getHighscoreAchievedTS());
        $this->assertTrue($s->withHighscoreScore(true)->getHighscoreScore());
        $this->assertFalse($s->withHighscoreScore(false)->getHighscoreScore());
        $this->assertTrue($s->withHighscorePercentage(true)->getHighscorePercentage());
        $this->assertFalse($s->withHighscorePercentage(false)->getHighscorePercentage());
        $this->assertTrue($s->withHighscoreHints(true)->getHighscoreHints());
        $this->assertFalse($s->withHighscoreHints(false)->getHighscoreHints());
        $this->assertTrue($s->withHighscoreWTime(true)->getHighscoreWTime());
        $this->assertFalse($s->withHighscoreWTime(false)->getHighscoreWTime());
        $this->assertTrue($s->withHighscoreOwnTable(true)->getHighscoreOwnTable());
        $this->assertFalse($s->withHighscoreOwnTable(false)->getHighscoreOwnTable());
        $this->assertTrue($s->withHighscoreTopTable(true)->getHighscoreTopTable());
        $this->assertFalse($s->withHighscoreTopTable(false)->getHighscoreTopTable());
        $this->assertEquals(15, $s->withHighscoreTopNum(15)->getHighscoreTopNum());
    }

    protected function getUIPack()
    {
        return [
            $this->getLanguage(),
            $this->getFieldFactory(),
            $this->getRefinery()
        ];
    }

    public function testScoreSettingsSectionScoring(): void
    {
        $s = new SettingsScoring(666);
        $actual = $this->render(
            $s->toForm(...$this->getUIPack())
        );

        $i1 = $this->getFormWrappedHtml(
            'RadioFieldInput',
            'tst_text_count_system',
            '
            <fieldset id="id_1" class="il-input-radio">
                <input type="radio" id="id_1_0_opt" value="0" checked="checked" />
                <label for="id_1_0_opt">tst_count_partial_solutions</label>
                <div class="help-block">tst_count_partial_solutions_desc</div>
            
                <input type="radio" id="id_1_1_opt" value="1" />
                <label for="id_1_1_opt">tst_count_correct_solutions</label>
                <div class="help-block">tst_count_correct_solutions_desc</div>
            </fieldset>
            ',
            null,
            null,
            ''
        );
        $i2 = $this->getFormWrappedHtml(
            'RadioFieldInput',
            'tst_score_cutting',
            '
            <fieldset id="id_2" class="il-input-radio">
                <input type="radio" id="id_2_0_opt" value="0" checked="checked" />
                <label for="id_2_0_opt">tst_score_cut_question</label>
                <div class="help-block">tst_score_cut_question_desc</div>

                <input type="radio" id="id_2_1_opt" value="1" />
                <label for="id_2_1_opt">tst_score_cut_test</label>
                <div class="help-block">tst_score_cut_test_desc</div>
            </fieldset>
            ',
            null,
            null,
            ''
        );
        $i3 = $this->getFormWrappedHtml(
            'RadioFieldInput',
            'tst_pass_scoring',
            '
            <fieldset id="id_3" class="il-input-radio">
                <input type="radio" id="id_3_0_opt" value="0" checked="checked" />
                <label for="id_3_0_opt">tst_pass_last_pass</label>
                <div class="help-block">tst_pass_last_pass_desc</div>
            
                <input type="radio" id="id_3_1_opt" value="1" />
                <label for="id_3_1_opt">tst_pass_best_pass</label>
                <div class="help-block">tst_pass_best_pass_desc</div>
            </fieldset>
            ',
            null,
            null,
            ''
        );

        $expected = $this->getFormWrappedHtml(
            'SectionFieldInput',
            'test_scoring',
            $i1 . $i2 . $i3,
            null,
            'id_4',
            ''
        );
        $this->assertHTMLEquals($expected, $this->brutallyTrimSignals($actual));
    }


    public function getUIFactory(): NoUIFactory
    {
        return new class () extends NoUIFactory {
            public function symbol(): C\Symbol\Factory
            {
                return new S\Factory(
                    new S\Icon\Factory(),
                    new S\Glyph\Factory(),
                    new S\Avatar\Factory()
                );
            }
        };
    }

    public function testScoreSettingsSectionSummary(): void
    {
        $data_factory = new \ILIAS\Data\Factory();
        $language = $this->getLanguage();
        $refinery = new \ILIAS\Refinery\Factory($data_factory, $language);

        $field_factory = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new \ILIAS\UI\Implementation\Component\SignalGenerator(),
            $data_factory,
            $refinery,
            $language
        );
        $ui = [$language, $field_factory, $refinery];

        $s = new SettingsResultSummary(666);
        $actual = $this->render(
            $s->toForm(...array_merge($ui, [[
                'user_time_zone' => 'Europe/Berlin',
                'user_date_format' => $data_factory->dateFormat()->withTime24(
                    $data_factory->dateFormat()->standard()
                )
            ]]))
        );

        $i1_1_1 = $this->getFormWrappedHtml(
            'GroupFieldInput',
            '<input type="radio" id="id_2_2_opt" value="2" /><label for="id_2_2_opt">tst_results_access_always</label>',
            '',
            'tst_results_access_always_desc',
            null,
            null,
            ''
        );
        $i1_1_2 = $this->getFormWrappedHtml(
            'GroupFieldInput',
            '<input type="radio" id="id_2_1_opt" value="1" /><label for="id_2_1_opt">tst_results_access_finished</label>',
            '',
            'tst_results_access_finished_desc',
            null,
            null,
            ''
        );
        $i1_1_3 = $this->getFormWrappedHtml(
            'GroupFieldInput',
            '<input type="radio" id="id_2_4_opt" value="4" /><label for="id_2_4_opt">tst_results_access_passed</label>',
            '',
            'tst_results_access_passed_desc',
            null,
            null,
            ''
        );

        $i1_1_4_1 = $this->getFormWrappedHtml(
            'DateTimeFieldInput',
            'tst_reporting_date<span class="asterisk">*</span>',
            '<div class="input-group date il-input-datetime">
                <input id="id_3" type="datetime-local" class="form-control form-control-sm" />
            </div>',
            null,
            'id_3',
            ''
        );

        $i1_1_4 = $this->getFormWrappedHtml(
            'GroupFieldInput',
            '<input type="radio" id="id_2_3_opt" value="3" /><label for="id_2_3_opt">tst_results_access_date</label><span class="asterisk">*</span>',
            $i1_1_4_1,
            'tst_results_access_date_desc',
            null,
            null,
            ''
        );

        $i1_1 = $this->getFormWrappedHtml(
            'SwitchableGroupFieldInput',
            'tst_results_access_setting<span class="asterisk">*</span>',
            $i1_1_1 . $i1_1_2 . $i1_1_3 . $i1_1_4,
            null,
            'id_2',
            ''
        );

        $i1_2 = $this->getFormWrappedHtml(
            'CheckboxFieldInput',
            'tst_results_grading_opt_show_status',
            '<input type="checkbox" id="id_4" value="checked" class="form-control form-control-sm" />',
            'tst_results_grading_opt_show_status_desc',
            'id_4',
            ''
        );
        $i1_3 = $this->getFormWrappedHtml(
            'CheckboxFieldInput',
            'tst_results_grading_opt_show_mark',
            '<input type="checkbox" id="id_5" value="checked" class="form-control form-control-sm" />',
            'tst_results_grading_opt_show_mark_desc',
            'id_5',
            ''
        );
        $i1_4 = $this->getFormWrappedHtml(
            'CheckboxFieldInput',
            'tst_results_grading_opt_show_details',
            '<input type="checkbox" id="id_6" value="checked" class="form-control form-control-sm" />',
            'tst_results_grading_opt_show_details_desc',
            'id_6',
            ''
        );
        $i1_5 = $this->getFormWrappedHtml(
            'CheckboxFieldInput',
            'tst_pass_deletion',
            '<input type="checkbox" id="id_7" value="checked" class="form-control form-control-sm" />',
            'tst_pass_deletion_allowed',
            'id_7',
            ''
        );

        $i1 = $this->getFormWrappedHtml(
            'OptionalGroupFieldInput',
            '<input type="checkbox" id="id_1" value="checked" /><label for="id_1">tst_results_access_enabled</label>',
            $i1_1 . $i1_2 . $i1_3 . $i1_4 . $i1_5,
            'tst_results_access_enabled_desc',
            'id_1',
            ''
        );

        $expected = $this->getFormWrappedHtml(
            'SectionFieldInput',
            'test_results',
            $i1,
            null,
            'id_8',
            ''
        );
        $this->assertEquals($expected, $this->brutallyTrimSignals($actual));
    }


    public function testScoreSettingsSectionDetails(): void
    {
        $s = new SettingsResultDetails(666);
        $tax_ids = [1,2];
        $actual = $this->render(
            $s->toForm(
                ...array_merge(
                    $this->getUIPack(),
                    [['taxonomy_options' => $tax_ids]]
                )
            )
        );
        $opts = [
            ['tst_results_print_best_solution', 'tst_results_print_best_solution_info'],
            ['tst_show_solution_feedback', 'tst_show_solution_feedback_desc'],
            ['tst_show_solution_suggested', 'tst_show_solution_suggested_desc'],
            ['tst_show_solution_printview', 'tst_show_solution_printview_desc'],
            ['tst_hide_pagecontents', 'tst_hide_pagecontents_desc'],
            ['tst_show_solution_signature', 'tst_show_solution_signature_desc'],
            ['examid_in_test_res', 'examid_in_test_res_desc'],
        ];
        $options = '';
        foreach($opts as $index => $entry) {
            list($label, $byline) = $entry;
            $nr = (string) ($index + 1);
            $checked = $index === 6 ? ' checked="checked"' : '';
            $field_html = '<input type="checkbox" id="id_' . $nr . '" value="checked"' . $checked . ' class="form-control form-control-sm" />';
            $options .= $this->getFormWrappedHtml(
                'CheckboxFieldInput',
                $label,
                $field_html,
                $byline,
                'id_' . $nr,
                ''
            );
        }

        $expected = $this->getFormWrappedHtml(
            'SectionFieldInput',
            'tst_results_details_options',
            $options,
            null,
            'id_8',
            ''
        );
        $this->assertEquals($expected, $this->brutallyTrimSignals($actual));
    }


    public function testScoreSettingsSectionGamification(): void
    {
        $s = new SettingsGamification(666);
        $actual = $this->render(
            $s->toForm(...$this->getUIPack())
        );

        $fields = $this->getFormWrappedHtml(
            'RadioFieldInput',
            'tst_highscore_mode<span class="asterisk">*</span>',
            '<fieldset id="id_2" class="il-input-radio">
                <input type="radio" id="id_2_1_opt" value="1" /><label for="id_2_1_opt">tst_highscore_own_table</label><div class="help-block">tst_highscore_own_table_description</div>
                <input type="radio" id="id_2_2_opt" value="2" /><label for="id_2_2_opt">tst_highscore_top_table</label><div class="help-block">tst_highscore_top_table_description</div>
                <input type="radio" id="id_2_3_opt" value="3" checked="checked" /><label for="id_2_3_opt">tst_highscore_all_tables</label><div class="help-block">tst_highscore_all_tables_description</div>
            </fieldset>',
            null,
            null,
            ''
        );
        $fields .= $this->getFormWrappedHtml(
            'NumericFieldInput',
            'tst_highscore_top_num<span class="asterisk">*</span>',
            '<input id="id_3" type="number" value="10" class="form-control form-control-sm" />',
            'tst_highscore_top_num_description',
            'id_3',
            ''
        );


        $opts = [
            ['tst_highscore_anon', 'tst_highscore_anon_description'],
            ['tst_highscore_achieved_ts', 'tst_highscore_achieved_ts_description'],
            ['tst_highscore_score', 'tst_highscore_score_description'],
            ['tst_highscore_percentage', 'tst_highscore_percentage_description'],
            ['tst_highscore_hints', 'tst_highscore_hints_description'],
            ['tst_highscore_wtime', 'tst_highscore_wtime_description']
        ];
        foreach($opts as $index => $entry) {
            list($label, $byline) = $entry;
            $nr = (string) ($index + 4);
            $field_html = '<input type="checkbox" id="id_' . $nr . '" value="checked" checked="checked" class="form-control form-control-sm" />';
            $fields .= $this->getFormWrappedHtml(
                'CheckboxFieldInput',
                $label,
                $field_html,
                $byline,
                'id_' . $nr,
                ''
            );
        }

        $group = $this->getFormWrappedHtml(
            'OptionalGroupFieldInput',
            '<input type="checkbox" id="id_1" value="checked" /><label for="id_1">tst_highscore_enabled</label>',
            $fields,
            'tst_highscore_description',
            'id_1',
            ''
        );

        $expected = $this->getFormWrappedHtml(
            'SectionFieldInput',
            'tst_results_gamification',
            $group,
            null,
            'id_10',
            ''
        );
        $this->assertEquals($expected, $this->brutallyTrimSignals($actual));
    }

    public function testScoreSettingsDirectlyAccessedByTestObj(): void
    {
        $id = -666;
        $s = new ScoreSettings(
            $id,
            new SettingsScoring($id),
            new SettingsResultSummary($id),
            new SettingsResultDetails($id),
            new SettingsGamification($id)
        );

        $t = new class ($s) extends ilObjTest {
            public function __construct($s)
            {
                $this->score_settings = $s;
            }
        };

        $this->assertIsInt($t->getCountSystem());
        $this->assertIsInt($t->getScoreCutting());
        $this->assertIsInt($t->getPassScoring());
        $this->assertNull($t->getReportingDate());
        $this->assertIsBool($t->getShowPassDetails());
        $this->assertIsBool($t->getShowSolutionAnswersOnly());
        $this->assertIsBool($t->getShowSolutionSignature());
        $this->assertIsBool($t->getShowSolutionSuggested());
        $this->assertIsBool($t->getShowSolutionListComparison());
        $this->assertIsBool($t->isPassDeletionAllowed());
        $this->assertIsInt($t->getExportSettings());
        $this->assertIsBool($t->getHighscoreEnabled());
        $this->assertIsBool($t->getHighscoreAnon());
        $this->assertIsBool($t->getHighscoreAchievedTS());
        $this->assertIsBool($t->getHighscoreScore());
        $this->assertIsBool($t->getHighscorePercentage());
        $this->assertIsBool($t->getHighscoreHints());
        $this->assertIsBool($t->getHighscoreWTime());
        $this->assertIsBool($t->getHighscoreOwnTable());
        $this->assertIsBool($t->getHighscoreTopTable());
        $this->assertIsInt($t->getHighscoreTopNum());
        $this->assertIsInt($t->getHighscoreMode());
    }

    public function testScoreSettingsRelayingTestId(): void
    {
        $id = -666;
        $s = new ScoreSettings(
            $id,
            new SettingsScoring($id),
            new SettingsResultSummary($id),
            new SettingsResultDetails($id),
            new SettingsGamification($id)
        );

        $nu_id = 1234;
        $s = $s->withTestId($nu_id);
        $this->assertEquals($nu_id, $s->getTestId());
        $this->assertEquals($nu_id, $s->getScoringSettings()->getTestId());
        $this->assertEquals($nu_id, $s->getResultSummarySettings()->getTestId());
        $this->assertEquals($nu_id, $s->getResultDetailsSettings()->getTestId());
        $this->assertEquals($nu_id, $s->getGamificationSettings()->getTestId());
    }
}
