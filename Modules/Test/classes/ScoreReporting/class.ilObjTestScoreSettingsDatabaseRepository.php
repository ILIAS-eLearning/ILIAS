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

class ilObjTestScoreSettingsDatabaseRepository implements ScoreSettingsRepository
{
    public const TABLE_NAME = 'tst_tests';
    public const STORAGE_DATE_FORMAT = 'YmdHis';

    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getForObjFi(int $obj_fi): ilObjTestScoreSettings
    {
        $where_part = 'WHERE obj_fi = ' . $this->db->quote($obj_fi, 'integer');
        return $this->doSelect($where_part);
    }

    public function getFor(int $test_id): ilObjTestScoreSettings
    {
        $where_part = 'WHERE test_id = ' . $this->db->quote($test_id, 'integer');
        return $this->doSelect($where_part);
    }

    protected function doSelect(string $where_part): ilObjTestScoreSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'test_id,' . PHP_EOL
            . 'count_system, score_cutting, pass_scoring,' . PHP_EOL
            . 'score_reporting, reporting_date,' . PHP_EOL
            . 'show_grading_status, show_grading_mark, pass_deletion_allowed,' . PHP_EOL
            . 'print_bs_with_res,' . PHP_EOL //print_bs_with_res_sp
            . 'examid_in_test_res,' . PHP_EOL
            . 'results_presentation,' . PHP_EOL
            . 'exportsettings,' . PHP_EOL
            . 'highscore_enabled, highscore_anon, highscore_achieved_ts, highscore_score, highscore_percentage, highscore_hints, highscore_wtime, highscore_own_table, highscore_top_table, highscore_top_num,' . PHP_EOL
            . 'result_tax_filters' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('no score settings: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);

        $reporting_date = $row['reporting_date'];
        if ($reporting_date) {
            $reporting_date = \DateTimeImmutable::createFromFormat(
                self::STORAGE_DATE_FORMAT,
                $reporting_date,
                new DateTimeZone('UTC')
            );
        } else {
            $reporting_date = null;
        }

        $test_id = (int) $row['test_id'];
        $tax_filter_ids = unserialize((string) ($row['result_tax_filters']));
        if ($tax_filter_ids === false) {
            $tax_filter_ids = [];
        }

        $settings = new ilObjTestScoreSettings(
            $test_id,
            (new ilObjTestSettingsScoring($test_id))
                ->withCountSystem((int) $row['count_system'])
                ->withScoreCutting((int) $row['score_cutting'])
                ->withPassScoring((int) $row['pass_scoring']),
            (new ilObjTestSettingsResultSummary($test_id))
                ->withScoreReporting((int) $row['score_reporting'])
                ->withReportingDate($reporting_date)
                ->withShowGradingStatusEnabled((bool) $row['show_grading_status'])
                ->withShowGradingMarkEnabled((bool) $row['show_grading_mark'])
                ->withPassDeletionAllowed((bool) $row['pass_deletion_allowed']),
            //->withShowPassDetails derived from results_presentation with bit RESULTPRES_BIT_PASS_DETAILS
            (new ilObjTestSettingsResultDetails($test_id))
                ->withResultsPresentation((int)$row['results_presentation'])
                ->withPrintBestSolutionWithResult((bool) $row['print_bs_with_res'])
                ->withShowExamIdInTestResults((bool) $row['examid_in_test_res'])
                ->withExportSettings((int) $row['exportsettings'])
                ->withTaxonomyFilterIds($tax_filter_ids),
            (new ilObjTestSettingsGamification($test_id))
                ->withHighscoreEnabled((bool) $row['highscore_enabled'])
                ->withHighscoreAnon((bool) $row['highscore_anon'])
                ->withHighscoreAchievedTS((bool) $row['highscore_achieved_ts'])
                ->withHighscoreScore((bool) $row['highscore_score'])
                ->withHighscorePercentage((bool) $row['highscore_percentage'])
                ->withHighscoreHints((bool) $row['highscore_hints'])
                ->withHighscoreWTime((bool) $row['highscore_wtime'])
                ->withHighscoreOwnTable((bool) $row['highscore_own_table'])
                ->withHighscoreTopTable((bool) $row['highscore_top_table'])
                ->withHighscoreTopNum((int) $row['highscore_top_num'])
        );

        return $settings;
    }

    public function store(ilObjTestScoreSettings $settings): void
    {
        $values = array_merge(
            $settings->getScoringSettings()->toStorage(),
            $settings->getResultSummarySettings()->toStorage(),
            $settings->getResultDetailsSettings()
            ->withShowPassDetails($settings->getResultSummarySettings()->getShowPassDetails())
            ->toStorage(),
            $settings->getGamificationSettings()->toStorage()
        );

        $this->db->update(
            self::TABLE_NAME,
            $values,
            ['test_id' => ['integer', $settings->getTestId()]]
        );
    }
}
