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

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilManScoringRecalculateReachedPoints implements Setup\Migration
{
    private const TABLE_NAME = 'tst_manual_fb';
    private const MIGRATION_ALLREADY_RUN_SETTING = 'assessment_man_scoring_fix_run';
    private const START_DATE = 1714521600;

    private \ilDBInterface $db;
    private \ilSetting $settings;
    private bool $manual_scoring_enabled;
    private bool $migration_already_run;

    public function getLabel(): string
    {
        return 'Update test attempt results on manual scoring after 1st of May 2024. We do this in one step to avoid creating tables and this might take a while.';
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 1;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseInitializedObjective(),
            new \ilSettingsFactoryExistsObjective()
        ];
    }

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        /** @var \ilSetting $settings */
        $this->settings = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY)
            ->settingsFor('assessment');
        $this->manual_scoring_enabled = $this->settings->get('assessment_manual_scoring', '') !== '';
        $this->migration_already_run = $this->settings->get(self::MIGRATION_ALLREADY_RUN_SETTING, '0') === '1';
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment): void
    {
        if (!$this->manual_scoring_enabled) {
            return;
        }

        $result = $this->db->query(
            'SELECT DISTINCT(active_fi), pass, question_set_type, (SELECT MAX(tstamp) FROM '
            . self::TABLE_NAME . ' ms2 WHERE ms1.active_fi = ms2.active_fi) tstamp FROM '
            . self::TABLE_NAME . ' ms1 '
            . 'INNER JOIN tst_active ta ON ms1.active_fi = ta.active_id '
            . 'INNER JOIN tst_tests tt ON ta.test_fi = tt.test_id '
            . 'WHERE tt.question_set_type = "' . \ilObjTest::QUESTION_SET_TYPE_RANDOM . '"'
        );

        while (($row = $this->db->fetchObject($result))) {
            if ($row->tstamp <= self::START_DATE) {
                continue;
            }

            $this->updateTestPassResults($row->active_fi, $row->pass);
        }

        $this->settings->set(self::MIGRATION_ALLREADY_RUN_SETTING, '1');
    }

    public function getRemainingAmountOfSteps(): int
    {
        if (!$this->manual_scoring_enabled || $this->migration_already_run) {
            return 0;
        }

        return 1;
    }

    private function updateTestPassResults(
        int $active_id,
        int $pass
    ): void {
        $result = $this->db->queryF(
            'SELECT		SUM(points) reachedpoints,
						SUM(hint_count) hint_count,
						SUM(hint_points) hint_points,
						COUNT(DISTINCT(question_fi)) answeredquestions
			FROM		tst_test_result
			WHERE		active_fi = %s
			AND			pass = %s',
            ['integer','integer'],
            [$active_id, $pass]
        );

        $row = $this->db->fetchAssoc($result);

        if ($row['reachedpoints'] === null
            || $row['reachedpoints'] < 0.0) {
            $row['reachedpoints'] = 0.0;
        }
        if ($row['hint_count'] === null) {
            $row['hint_count'] = 0;
        }
        if ($row['hint_points'] === null) {
            $row['hint_points'] = 0.0;
        }

        $data = $this->getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);

        $this->db->replace(
            'tst_pass_result',
            [
                'active_fi' => ['integer', $active_id],
                'pass' => ['integer', $pass]
            ],
            [
                'points' => ['float', $row['reachedpoints']],
                'maxpoints' => ['float', $data['points']],
                'questioncount' => ['integer', $data['count']],
                'answeredquestions' => ['integer', $row['answeredquestions']],
                'tstamp' => ['integer', time()],
                'hint_count' => ['integer', $row['hint_count']],
                'hint_points' => ['float', $row['hint_points']]
            ]
        );

        $this->updateTestResultCache($active_id, $pass);
    }

    private function updateTestResultCache(int $active_id, int $pass): void
    {
        $query = '
            SELECT		tst_pass_result.*,
                        tst_active.last_finished_pass,
                        tst_active.test_fi
            FROM		tst_pass_result
            INNER JOIN  tst_active
            on          tst_pass_result.active_fi = tst_active.active_id
            WHERE		active_fi = %s
            AND			pass = %s
        ';

        $result = $this->db->queryF(
            $query,
            ['integer','integer'],
            [$active_id, $pass]
        );

        $test_pass_result_row = $this->db->fetchAssoc($result);

        if (!is_array($test_pass_result_row)) {
            $test_pass_result_row = [];
        }
        $max = (float) ($test_pass_result_row['maxpoints'] ?? 0);
        $reached = (float) ($test_pass_result_row['points'] ?? 0);
        $percentage = ($max <= 0.0 || $reached <= 0.0) ? 0 : ($reached / $max) * 100.0;
        $obligations_answered = (int) ($test_pass_result_row['obligations_answered'] ?? 1);

        $mark_schema = new \ASS_MarkSchema($this->db, new class ('en') extends \ilLanguage {
            public function __construct(string $lc)
            {
            }
        }, 0);
        $mark_schema->loadFromDb($test_pass_result_row['test_fi']);
        $mark = $mark_schema->getMatchingMark($percentage);
        $is_passed = $pass <= $test_pass_result_row['last_finished_pass'] && $mark->getPassed();

        $hint_count = $test_pass_result_row['hint_count'] ?? 0;
        $hint_points = $test_pass_result_row['hint_points'] ?? 0.0;

        $passed_once_before = 0;
        $query = 'SELECT passed_once FROM tst_result_cache WHERE active_fi = %s';
        $res = $this->db->queryF($query, ['integer'], [$active_id]);
        while ($passed_once_result_row = $this->db->fetchAssoc($res)) {
            $passed_once_before = (int) $passed_once_result_row['passed_once'];
        }

        $passed_once = (int) ($is_passed || $passed_once_before);

        $this->db->manipulateF(
            'DELETE FROM tst_result_cache WHERE active_fi = %s',
            ['integer'],
            [$active_id]
        );

        if ($reached < 0.0) {
            $reached = 0.0;
        }

        $mark_short_name = $mark->getShortName();
        if ($mark_short_name === '') {
            $mark_short_name = ' ';
        }

        $mark_official_name = $mark->getOfficialName();
        if ($mark_official_name === '') {
            $mark_official_name = ' ';
        }

        $this->db->insert(
            'tst_result_cache',
            [
                'active_fi' => ['integer', $active_id],
                'pass' => ['integer', $pass ?? 0],
                'max_points' => ['float', $max],
                'reached_points' => ['float', $reached],
                'mark_short' => ['text', $mark_short_name],
                'mark_official' => ['text', $mark_official_name],
                'passed_once' => ['integer', $passed_once],
                'passed' => ['integer', (int) $is_passed],
                'failed' => ['integer', (int) !$is_passed],
                'tstamp' => ['integer', time()],
                'hint_count' => ['integer', $hint_count],
                'hint_points' => ['float', $hint_points],
                'obligations_answered' => ['integer', $obligations_answered]
            ]
        );
    }

    private function getQuestionCountAndPointsForPassOfParticipant($active_id, $pass): array
    {
        $res = $this->db->queryF(
            "
                SELECT		tst_test_rnd_qst.pass,
                            COUNT(tst_test_rnd_qst.question_fi) qcount,
                            SUM(qpl_questions.points) qsum

                FROM		tst_test_rnd_qst,
                            qpl_questions

                WHERE		tst_test_rnd_qst.question_fi = qpl_questions.question_id
                AND			tst_test_rnd_qst.active_fi = %s
                AND			pass = %s

                GROUP BY	tst_test_rnd_qst.active_fi,
                            tst_test_rnd_qst.pass
            ",
            ['integer', 'integer'],
            [$active_id, $pass]
        );

        $row = $this->db->fetchAssoc($res);

        if (is_array($row)) {
            return ["count" => $row["qcount"], "points" => $row["qsum"]];
        }

        return ["count" => 0, "points" => 0];
    }
}
