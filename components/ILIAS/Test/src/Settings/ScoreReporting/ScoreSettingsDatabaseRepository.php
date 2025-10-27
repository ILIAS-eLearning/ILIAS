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
use ILIAS\Test\Settings\SettingsNotFoundException;

class ScoreSettingsDatabaseRepository implements ScoreSettingsRepository
{
    /** @var array<int, int> Test ID -> Settings ID */
    private array $settings_by_test_fi = [];

    /** @var array<int, ScoreSettings> Settings ID -> Settings DTO */
    private array $settings_instances = [];

    public function __construct(
        protected \ilDBInterface $db,
        protected SettingsFactory $factory
    ) {
    }

    public function getFor(int $test_id): ScoreSettings
    {
        return isset($this->settings_by_test_fi[$test_id])
            ? $this->settings_instances[$this->settings_by_test_fi[$test_id]]
            : $this->doSelect("WHERE test_id = {$this->db->quote($test_id, \ilDBConstants::T_INTEGER)}");
    }

    public function getById(int $settings_id): ScoreSettings
    {
        if (isset($this->settings_instances[$settings_id])) {
            return $this->settings_instances[$settings_id];
        }

        $res = $this->db->queryF(
            "SELECT * FROM tst_test_settings WHERE id = %s",
            [\ilDBConstants::T_INTEGER],
            [$settings_id]
        );

        if ($this->db->numRows($res) === 0) {
            throw new SettingsNotFoundException("No score settings with id: {$settings_id}");
        }

        $settings = $this->factory->createScoreSettingsFromDBRow($this->db->fetchAssoc($res));
        $this->settings_instances[$settings->getId()] = $settings;

        return $settings;
    }

    protected function doSelect(string $where_part): ScoreSettings
    {
        $query = 'SELECT ' . PHP_EOL
            . 'tst_set.id,' . PHP_EOL
            . 'tst_set.count_system, tst_set.score_cutting, tst_set.pass_scoring,' . PHP_EOL
            . 'tst_set.score_reporting, tst_set.reporting_date,' . PHP_EOL
            . 'tst_set.show_grading_status, tst_set.show_grading_mark, tst_set.pass_deletion_allowed,' . PHP_EOL
            . 'tst_set.print_bs_with_res,' . PHP_EOL //print_bs_with_res_sp
            . 'tst_set.examid_in_test_res,' . PHP_EOL
            . 'tst_set.results_presentation,' . PHP_EOL
            . 'tst_set.exportsettings,' . PHP_EOL
            . 'tst_set.highscore_enabled, tst_set.highscore_anon, tst_set.highscore_achieved_ts, tst_set.highscore_score, tst_set.highscore_percentage, tst_set.highscore_wtime, tst_set.highscore_own_table, tst_set.highscore_top_table, tst_set.highscore_top_num,' . PHP_EOL
            . 'tst.test_id AS test_id' . PHP_EOL
            . 'FROM tst_test_settings AS tst_set' . PHP_EOL
            . 'INNER JOIN tst_tests AS tst ON tst.settings_id = tst_set.id' . PHP_EOL
            . $where_part;

        $res = $this->db->query($query);

        if ($this->db->numRows($res) === 0) {
            throw new SettingsNotFoundException("No score settings for: {$where_part}");
        }

        $row = $this->db->fetchAssoc($res);
        $settings = $this->factory->createScoreSettingsFromDBRow($row);

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
            static fn(ScoreSettings $value): bool => $value->getId() !== $settings->getId(),
        );
    }

    public function getSettingsResultSummaryByObjIds(array $obj_ids): array
    {
        $result = $this->db->query(
            'SELECT ' . PHP_EOL
            . 'tst_set.score_reporting, tst_set.reporting_date,' . PHP_EOL
            . 'tst_set.show_grading_status, tst_set.show_grading_mark, tst_set.pass_deletion_allowed,' . PHP_EOL
            . 'tst_tests.obj_fi AS obj_fi' . PHP_EOL
            . 'FROM tst_test_settings AS tst_set' . PHP_EOL
            . 'INNER JOIN tst_tests ON tst_tests.settings_id = tst_set.id' . PHP_EOL
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
