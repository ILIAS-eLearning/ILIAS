<?php

declare(strict_types=1);

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


use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\UI\Implementation\Component\Symbol as S;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

class ScoreSettingsTest extends ILIAS_UI_TestBase
{
    public function testScoreSettingsBuild(): void
    {
        $id = -666;
        $s = new ilObjTestScoreSettings(
            $id,
            new ilObjTestSettingsScoring($id),
            new ilObjTestSettingsResultSummary($id),
            new ilObjTestSettingsResultDetails($id),
            new ilObjTestSettingsGamification($id)
        );
        $this->assertInstanceOf(ilObjTestScoreSettings::class, $s);
        $this->assertEquals($id, $s->getTestId());
        $this->assertInstanceOf(ilObjTestSettingsScoring::class, $s->getScoringSettings());
        $this->assertInstanceOf(ilObjTestSettingsResultSummary::class, $s->getResultSummarySettings());
        $this->assertInstanceOf(ilObjTestSettingsResultDetails::class, $s->getResultDetailsSettings());
        $this->assertInstanceOf(ilObjTestSettingsGamification::class, $s->getGamificationSettings());
    }

    public function testScoreSettingsScoring(): void
    {
        $s = new ilObjTestSettingsScoring(-666);
        $this->assertEquals(-667, $s->withTestId(-667)->getTestId());
        $this->assertEquals(2, $s->withCountSystem(2)->getCountSystem());
        $this->assertEquals(4, $s->withScoreCutting(4)->getScoreCutting());
        $this->assertEquals(5, $s->withPassScoring(5)->getPassScoring());
    }

    public function testScoreSettingsSummary(): void
    {
        $dat = new \DateTimeImmutable();
        $s = new ilObjTestSettingsResultSummary(-666);
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
        $s = new ilObjTestSettingsResultDetails(-666);
        $this->assertTrue($s->withPrintBestSolutionWithResult(true)->getPrintBestSolutionWithResult());
        $this->assertEquals(192, $s->withResultsPresentation(192)->getResultsPresentation(192));
        $this->assertTrue($s->withShowExamIdInTestResults(true)->getShowExamIdInTestResults());
        $this->assertTrue($s->withShowPassDetails(true)->getShowPassDetails());
        $this->assertFalse($s->withShowPassDetails(false)->getShowPassDetails());
        $this->assertTrue($s->withShowSolutionDetails(true)->getShowSolutionDetails());
        $this->assertFalse($s->withShowSolutionDetails(false)->getShowSolutionDetails());
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
        $this->assertTrue($s->withExportSettingsSingleChoiceShort(true)->getExportSettingsSingleChoiceShort());
        $this->assertFalse($s->withExportSettingsSingleChoiceShort(false)->getExportSettingsSingleChoiceShort());
        $this->assertTrue($s->withShowPassDetails(true)->getShowPassDetails());
        $tax_ids = [1,3,5,17];
        $this->assertEquals($tax_ids, $s->withTaxonomyFilterIds($tax_ids)->getTaxonomyFilterIds());
    }

    public function testScoreSettingsGamification(): void
    {
        $s = new ilObjTestSettingsGamification(-666);
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



    protected function getFieldFactory()
    {
        $factory = new I\Input\Field\Factory(
            $this->createMock(I\Input\UploadLimitResolver::class),
            new IncrementalSignalGenerator(),
            new Data\Factory(),
            $this->getRefinery(),
            $this->getLanguage()
        );
        return $factory;
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
        $s = new ilObjTestSettingsScoring(666);
        $actual = $this->getDefaultRenderer()->render(
            $s->toForm(...$this->getUIPack())
        );

        $expected = <<<EOT
<div class="il-section-input">
    <div class="il-section-input-header"><h2>test_scoring</h2></div>
    
    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_text_count_system</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <div id="id_1" class="il-input-radio">

                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_1_0_opt" name="" value="0" checked="checked" />
                    <label for="id_1_0_opt">tst_count_partial_solutions</label>
                    <div class="help-block">tst_count_partial_solutions_desc</div>
                </div>
                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_1_1_opt" name="" value="1" />
                    <label for="id_1_1_opt">tst_count_correct_solutions</label>
                    <div class="help-block">tst_count_correct_solutions_desc</div>
                </div>

            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_score_cutting</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <div id="id_2" class="il-input-radio">
            
                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_2_0_opt" name="" value="0" checked="checked" />
                    <label for="id_2_0_opt">tst_score_cut_question</label>
                    <div class="help-block">tst_score_cut_question_desc</div>
                </div>
                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_2_1_opt" name="" value="1" />
                    <label for="id_2_1_opt">tst_score_cut_test</label>
                    <div class="help-block">tst_score_cut_test_desc</div>
                </div>

            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_pass_scoring</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <div id="id_3" class="il-input-radio">
                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_3_0_opt" name="" value="0" checked="checked" />
                    <label for="id_3_0_opt">tst_pass_last_pass</label>
                    <div class="help-block">tst_pass_last_pass_desc</div>
                </div>
                <div class="form-control form-control-sm il-input-radiooption">
                    <input type="radio" id="id_3_1_opt" name="" value="1" />
                    <label for="id_3_1_opt">tst_pass_best_pass</label>
                    <div class="help-block">tst_pass_best_pass_desc</div>
                </div>
            </div>
        </div>
    </div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
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

        $s = new ilObjTestSettingsResultSummary(666);
        $actual = $this->getDefaultRenderer()->render(
            $s->toForm(...$ui)
        );

        $expected = <<<EOT
<div class="il-section-input">
    <div class="il-section-input-header"><h2>test_results</h2></div>
        <div class="form-group row">
            <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_access_enabled</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <input type="checkbox" id="id_1" value="checked" name="" class="form-control form-control-sm" />
                <div class="help-block">tst_results_access_enabled_desc</div>
                <div class="form-group row">
                    <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_access_setting<span class="asterisk">*</span></label>
                    <div class="col-sm-8 col-md-9 col-lg-10">
                        <div id="id_2" class="il-input-radio">
                            <div class="form-control form-control-sm il-input-radiooption">
                                <input type="radio" id="id_2_2_opt" name="" value="2" />
                                <label for="id_2_2_opt">tst_results_access_always</label>
                                <div class="help-block">tst_results_access_always_desc</div>
                            </div>
                            <div class="form-control form-control-sm il-input-radiooption">
                                <input type="radio" id="id_2_1_opt" name="" value="1" />
                                <label for="id_2_1_opt">tst_results_access_finished</label>
                                <div class="help-block">tst_results_access_finished_desc</div>
                            </div>
                            <div class="form-control form-control-sm il-input-radiooption">
                                <input type="radio" id="id_2_4_opt" name="" value="4" />
                                <label for="id_2_4_opt">tst_results_access_passed</label>
                                <div class="help-block">tst_results_access_passed_desc</div>
                            </div>

                            <div class="form-control form-control-sm il-input-radiooption">
                                <input type="radio" id="id_2_3_opt" name="" value="3" />
                                <label for="id_2_3_opt">tst_results_access_date</label>
                                <div class="form-group row">
                                    <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">tst_reporting_date<span class="asterisk">*</span></label>
                                    <div class="col-sm-8 col-md-9 col-lg-10">
                                        <div class="input-group date il-input-datetime" id="id_3">
                                            <input type="text" name="" placeholder="YYYY-MM-DD HH:mm" class="form-control form-control-sm" />
                                            <span class="input-group-addon"><a class="glyph" href="#" aria-label="calendar"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></a></span>
                                        </div>
                                    </div>
                                </div>
                            <div class="help-block">tst_results_access_date_desc</div>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="form-group row">
                <label for="id_4" class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_grading_opt_show_status</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_4" value="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_results_grading_opt_show_status_desc</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_5" class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_grading_opt_show_mark</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_5" value="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_results_grading_opt_show_mark_desc</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_6" class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_grading_opt_show_details</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_6" value="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_results_grading_opt_show_details_desc</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_7" class="control-label col-sm-4 col-md-3 col-lg-2">tst_pass_deletion</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_7" value="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_pass_deletion_allowed</div>
                </div>
            </div>
        </div>
    </div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }




    public function testScoreSettingsSectionDetails(): void
    {
        $s = new ilObjTestSettingsResultDetails(666);
        $tax_ids = [1,2];
        $actual = $this->getDefaultRenderer()->render(
            $s->toForm(
                ...array_merge(
                    $this->getUIPack(),
                    [['taxonomy_options' => $tax_ids]]
                )
            )
        );

        $expected = <<<EOT
<div class="il-section-input">
    <div class="il-section-input-header"><h2>tst_results_details_options</h2></div>
    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_details</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_1" value="checked" name="" class="form-control form-control-sm" />
            <div class="help-block">tst_show_solution_details_desc</div>
            <div class="form-group row">
                <label for="id_2" class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_print_best_solution</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_2" value="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_results_print_best_solution_info</div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_details_singlepage</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_3" value="checked" name="" class="form-control form-control-sm" />
            <div class="help-block">tst_show_solution_details_singlepage_desc</div>
            <div class="form-group row">
                <label for="id_4" class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_print_best_solution_singlepage</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_4" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_results_print_best_solution_singlepage_info</div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_5" class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_feedback</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_5" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_show_solution_feedback_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_6" class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_suggested</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_6" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_show_solution_suggested_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_7" class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_printview</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_7" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_show_solution_printview_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_8" class="control-label col-sm-4 col-md-3 col-lg-2">tst_hide_pagecontents</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_8" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_hide_pagecontents_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_9" class="control-label col-sm-4 col-md-3 col-lg-2">tst_show_solution_signature</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_9" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_show_solution_signature_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_10" class="control-label col-sm-4 col-md-3 col-lg-2">examid_in_test_res</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_10" value="checked" checked="checked" name="" class="form-control form-control-sm" /><div class="help-block">examid_in_test_res_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label for="id_11" class="control-label col-sm-4 col-md-3 col-lg-2">tst_exp_sc_short</label><div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_11" value="checked" name="" class="form-control form-control-sm" /><div class="help-block">tst_exp_sc_short_desc</div>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_results_tax_filters</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <ul class="il-input-multiselect" id="id_12">
                <li>
                    <input type="checkbox" name="[]" value="0" /><span>1</span>
                </li>
                <li>
                    <input type="checkbox" name="[]" value="1" /><span>2</span>
                </li>
            </ul>
        </div>
    </div>

</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }


    public function testScoreSettingsSectionGamification(): void
    {
        $s = new ilObjTestSettingsGamification(666);
        $actual = $this->getDefaultRenderer()->render(
            $s->toForm(...$this->getUIPack())
        );

        $expected = <<<EOT
<div class="il-section-input">

    <div class="il-section-input-header"><h2>tst_results_gamification</h2></div>
    
    <div class="form-group row">
        <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_enabled</label>
        <div class="col-sm-8 col-md-9 col-lg-10">
            <input type="checkbox" id="id_1" value="checked" name="" class="form-control form-control-sm" />
            <div class="help-block">tst_highscore_description</div>
    
            <div class="form-group row">
                <label class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_mode<span class="asterisk">*</span></label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <div id="id_2" class="il-input-radio">
                        <div class="form-control form-control-sm il-input-radiooption">
                            <input type="radio" id="id_2_1_opt" name="" value="1" />
                            <label for="id_2_1_opt">tst_highscore_own_table</label>
                            <div class="help-block">tst_highscore_own_table_description</div>
                        </div>

                        <div class="form-control form-control-sm il-input-radiooption">
                            <input type="radio" id="id_2_2_opt" name="" value="2" />
                            <label for="id_2_2_opt">tst_highscore_top_table</label>
                            <div class="help-block">tst_highscore_top_table_description</div>
                        </div>

                        <div class="form-control form-control-sm il-input-radiooption">
                            <input type="radio" id="id_2_3_opt" name="" value="3" checked="checked" />
                            <label for="id_2_3_opt">tst_highscore_all_tables</label>
                            <div class="help-block">tst_highscore_all_tables_description</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="id_3" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_top_num<span class="asterisk">*</span></label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input id="id_3" type="number" value="10" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_top_num_description</div>
                </div>
            </div>
            
            <div class="form-group row">
                <label for="id_4" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_anon</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_4" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_anon_description</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_5" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_achieved_ts</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_5" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_achieved_ts_description</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_6" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_score</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_6" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_score_description</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_7" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_percentage</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_7" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_percentage_description</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_8" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_hints</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_8" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_hints_description</div>
                </div>
            </div>
            <div class="form-group row">
                <label for="id_9" class="control-label col-sm-4 col-md-3 col-lg-2">tst_highscore_wtime</label>
                <div class="col-sm-8 col-md-9 col-lg-10">
                    <input type="checkbox" id="id_9" value="checked" checked="checked" name="" class="form-control form-control-sm" />
                    <div class="help-block">tst_highscore_wtime_description</div>
                </div>
            </div>
        
        </div>

    </div>
</div>
EOT;

        $this->assertHTMLEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($this->brutallyTrimSignals($actual))
        );
    }

    public function testScoreSettingsDirectlyAccessedByTestObj(): void
    {
        $id = -666;
        $s = new ilObjTestScoreSettings(
            $id,
            new ilObjTestSettingsScoring($id),
            new ilObjTestSettingsResultSummary($id),
            new ilObjTestSettingsResultDetails($id),
            new ilObjTestSettingsGamification($id)
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
        $this->assertIsBool($t->getShowSolutionDetails());
        $this->assertIsBool($t->getShowSolutionAnswersOnly());
        $this->assertIsBool($t->getShowSolutionSignature());
        $this->assertIsBool($t->getShowSolutionSuggested());
        $this->assertIsBool($t->getShowSolutionListComparison());
        $this->assertIsBool($t->getShowSolutionListOwnAnswers());
        $this->assertIsBool($t->isPassDeletionAllowed());
        $this->assertIsInt($t->getExportSettings());
        $this->assertIsBool($t->getExportSettingsSingleChoiceShort());
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
        $s = new ilObjTestScoreSettings(
            $id,
            new ilObjTestSettingsScoring($id),
            new ilObjTestSettingsResultSummary($id),
            new ilObjTestSettingsResultDetails($id),
            new ilObjTestSettingsGamification($id)
        );

        $nu_id =  1234;
        $s = $s->withTestId($nu_id);
        $this->assertEquals($nu_id, $s->getTestId());
        $this->assertEquals($nu_id, $s->getScoringSettings()->getTestId());
        $this->assertEquals($nu_id, $s->getResultSummarySettings()->getTestId());
        $this->assertEquals($nu_id, $s->getResultDetailsSettings()->getTestId());
        $this->assertEquals($nu_id, $s->getGamificationSettings()->getTestId());
    }
}
