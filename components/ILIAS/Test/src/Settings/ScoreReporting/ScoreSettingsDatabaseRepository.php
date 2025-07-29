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

use ILIAS\Test\Settings\SettingsFactory;

class ScoreSettingsDatabaseRepository implements ScoreSettingsRepository
{
    /** @var array<int, ScoreSettings> Test ID -> Settings DTO */
    private array $settings_instances = [];

    public function __construct(
        protected \ilDBInterface $db,
        protected SettingsFactory $factory
    ) {
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
            . 'st.id,' . PHP_EOL
            . 'st.count_system, st.score_cutting, st.pass_scoring,' . PHP_EOL
            . 'st.score_reporting, st.reporting_date,' . PHP_EOL
            . 'st.show_grading_status, st.show_grading_mark, st.pass_deletion_allowed,' . PHP_EOL
            . 'st.print_bs_with_res,' . PHP_EOL //print_bs_with_res_sp
            . 'st.examid_in_test_res,' . PHP_EOL
            . 'st.results_presentation,' . PHP_EOL
            . 'st.exportsettings,' . PHP_EOL
            . 'st.highscore_enabled, st.highscore_anon, st.highscore_achieved_ts, st.highscore_score, st.highscore_percentage, st.highscore_wtime, st.highscore_own_table, st.highscore_top_table, st.highscore_top_num,' . PHP_EOL
            . 'tst.test_id AS test_id' . PHP_EOL
            . 'FROM tst_test_settings AS st' . PHP_EOL
            . 'INNER JOIN tst_tests AS tst ON tst.settings_id = st.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception('no score settings: ' . $where_part);
        }

        $row = $this->db->fetchAssoc($res);
        $settings = $this->factory->createScoreSettings($row);

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
                ->withReportingDate($row['reporting_date'] !== 0
                    ? \DateTimeImmutable::createFromFormat('U', (string) $row['reporting_date'])
                    : null)
                ->withShowGradingStatusEnabled((bool) $row['show_grading_status'])
                ->withShowGradingMarkEnabled((bool) $row['show_grading_mark'])
                ->withPassDeletionAllowed((bool) $row['pass_deletion_allowed']);
        }
        return $settings_summary;
    }
}
