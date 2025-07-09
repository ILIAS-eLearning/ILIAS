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

namespace ILIAS\Test\Settings\ScoreReporting;

use ILIAS\Test\Scoring\Settings\Settings as SettingsScoring;

class ScoreSettingsDatabaseRepository implements ScoreSettingsRepository
{
    public const string STORAGE_DATE_FORMAT = 'YmdHis';

    /** @var array<int, ScoreSettings> Test ID -> Settings DTO */
    private array $settings_instances = [];

    public function __construct(protected \ilDBInterface $db)
    {
    }

    public function getFor(int $test_id): ScoreSettings
    {
        if (isset($this->settings_instances[$test_id])) {
            return $this->settings_instances[$test_id];
        }

        $where_part = 'WHERE test_id = ' . $this->db->quote($test_id, \ilDBConstants::T_INTEGER);
        return $this->doSelect($where_part);
    }

    protected function doSelect(string $where_part): ScoreSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'id,' . PHP_EOL
            . 'count_system, score_cutting, pass_scoring,' . PHP_EOL
            . 'score_reporting, reporting_date,' . PHP_EOL
            . 'show_grading_status, show_grading_mark, pass_deletion_allowed,' . PHP_EOL
            . 'print_bs_with_res,' . PHP_EOL //print_bs_with_res_sp
            . 'examid_in_test_res,' . PHP_EOL
            . 'results_presentation,' . PHP_EOL
            . 'exportsettings,' . PHP_EOL
            . 'highscore_enabled, highscore_anon, highscore_achieved_ts, highscore_score, highscore_percentage, highscore_wtime, highscore_own_table, highscore_top_table, highscore_top_num,' . PHP_EOL
            . 'tst_tests.test_id AS test_id' . PHP_EOL
            . 'FROM tst_test_settings' . PHP_EOL
            . 'INNER JOIN tst_tests ON tst_tests.settings_id = tst_test_settings.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('no score settings: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);

        $settings = new ScoreSettings(
            $row['id'],
            (new SettingsScoring())
                ->withCountSystem((int) $row['count_system'])
                ->withScoreCutting((int) $row['score_cutting'])
                ->withPassScoring((int) $row['pass_scoring']),
            (new SettingsResultSummary())
                ->withScoreReporting(ScoreReportingTypes::from($row['score_reporting']))
                ->withReportingDate($this->buildDateFromString($row['reporting_date']))
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

        $this->settings_instances[$row['test_id']] = $settings;

        return $settings;
    }

    public function store(ScoreSettings $settings): void
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
            'tst_test_settings',
            $values,
            ['id' => [\ilDBConstants::T_INTEGER, $settings->getId()]]
        );

        $this->settings_instances = array_filter(
            $this->settings_instances,
            fn($value) => $value->getId() !== $settings->getId()
        );
    }

    public function getSettingsResultSummaryByObjIds(array $obj_ids): array
    {
        $result = $this->db->query(
            'SELECT ' . PHP_EOL
            . 'score_reporting, reporting_date,' . PHP_EOL
            . 'show_grading_status, show_grading_mark, pass_deletion_allowed' . PHP_EOL
            . 'tst_tests.obj_fi AS obj_fi' . PHP_EOL
            . 'FROM tst_test_settings' . PHP_EOL
            . 'INNER JOIN tst_tests ON tst_tests.settings_id = tst_test_settings.id' . PHP_EOL
            . 'WHERE ' . $this->db->in('obj_fi', $obj_ids, false, \ilDBConstants::T_INTEGER)
        );

        $settings_summary = [];
        while (($row = $this->db->fetchAssoc($result)) !== null) {
            $settings_summary[$row['obj_fi']] = (new SettingsResultSummary())
                ->withScoreReporting(ScoreReportingTypes::from($row['score_reporting']))
                ->withReportingDate($this->buildDateFromString($row['reporting_date']))
                ->withShowGradingStatusEnabled((bool) $row['show_grading_status'])
                ->withShowGradingMarkEnabled((bool) $row['show_grading_mark'])
                ->withPassDeletionAllowed((bool) $row['pass_deletion_allowed']);
        }
        return $settings_summary;
    }

    private function buildDateFromString(?string $reporting_date): ?\DateTimeImmutable
    {
        if ($reporting_date === null
            || $reporting_date === '') {
            return null;
        }

        return \DateTimeImmutable::createFromFormat(
            self::STORAGE_DATE_FORMAT,
            $reporting_date,
            new \DateTimeZone('UTC')
        );
    }
}
