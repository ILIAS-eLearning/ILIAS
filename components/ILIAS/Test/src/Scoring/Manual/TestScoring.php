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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\Test\Logging\TestScoringInteraction;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ILIAS\Test\Results\Data\Repository as TestResultRepository;

/**
 * Class ilTestScoring
 *
 * This class holds a mechanism to get the scoring for
 * - a test,
 * - a user in a test,
 * - a pass in a users passes in a test, or
 * - a question in a pass in a users passes in a test.
 *
 * Warning:
 * Please use carefully, this is one of the classes that may cause funny spikes on your servers load graph on large
 * datasets in the test.
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTest
 */
class TestScoring
{
    private bool $preserve_manual_scores = false;
    private int $question_id = 0;
    private \ilTestEvaluationFactory $evaluation_factory;

    /**
     * @var array<int, \assQuestionGUI> $question_cache
     */
    protected array $question_cache = [];

    public function __construct(
        private \ilObjTest $test,
        private \ilObjUser $scorer,
        private \ilDBInterface $db,
        private readonly TestResultRepository $test_result_repository
    ) {
        $this->evaluation_factory = new \ilTestEvaluationFactory($this->db, $this->test);
    }

    public function setPreserveManualScores(bool $preserve_manual_scores): void
    {
        $this->preserve_manual_scores = $preserve_manual_scores;
    }

    public function getPreserveManualScores(): bool
    {
        return $this->preserve_manual_scores;
    }

    public function getQuestionId(): int
    {
        return $this->question_id;
    }

    public function setQuestionId(int $question_id): void
    {
        $this->question_id = $question_id;
    }

    public function recalculateSolutions(): array
    {
        $participants = $this->evaluation_factory->getCorrectionsEvaluationData()->getParticipants();

        foreach ($participants as $active_id => $userdata) {
            if ($userdata instanceof \ilTestEvaluationUserData) {
                $this->recalculatePasses($userdata, $active_id);
                \ilLPStatusWrapper::_updateStatus($this->test->getId(), $userdata->getUserID());
            }
        }

        return $participants;

    }

    public function recalculateSolution(int $active_id, int $pass): void
    {
        $user_data = $this
            ->test
            ->getCompleteEvaluationData()
            ->getParticipant($active_id);

        $this->recalculatePass(
            $user_data->getPass($pass),
            $user_data->getUserID(),
            $active_id,
            $pass
        );
        $this->test_result_repository->updateTestResultCache($active_id);
    }

    private function recalculatePasses(\ilTestEvaluationUserData $userdata, int $active_id): void
    {
        foreach ($userdata->getPasses() as $pass => $passdata) {
            if ($passdata instanceof \ilTestEvaluationPassData) {
                $this->recalculatePass($passdata, $userdata->getUserID(), $active_id, $pass);
            }
        }
        $this->test_result_repository->updateTestResultCache($active_id);
    }

    private function recalculatePass(
        \ilTestEvaluationPassData $passdata,
        int $user_id,
        int $active_id,
        int $pass
    ): void {
        $reached_points_changed = false;
        foreach ($passdata->getAnsweredQuestions() as $question_data) {
            if ($this->getQuestionId() !== 0 || $this->getQuestionId() === $question_data['id']) {
                $reached_points_changed = $reached_points_changed || $this->recalculateQuestionScore($user_id, $active_id, $pass, $question_data);
            }
        }
        $this->updatePassResultsTable($active_id, $pass, $reached_points_changed);
    }

    private function recalculateQuestionScore(
        int $user_id,
        int $active_id,
        int $pass,
        array $questiondata
    ): bool {
        if ($this->preserve_manual_scores && $questiondata['manual'] === 1) {
            return false;
        }

        $q_id = $questiondata['id'];
        $this->question_cache[$q_id] ??= $this->test->createQuestionGUI('', $q_id)->getObject();
        /** @var \assQuestion $question */
        $question = $this->question_cache[$q_id];

        $old_points = $question->getReachedPoints($active_id, $pass);
        $reached = $question->adjustReachedPointsByScoringOptions(
            $question->calculateReachedPoints($active_id, $pass),
            $active_id,
        );

        return $this->updateReachedPoints(
            $user_id,
            $active_id,
            $questiondata['id'],
            $old_points,
            $reached,
            $question->getMaximumPoints(),
            $pass
        );
    }

    /**
     * This is an optimized version of \assQuestion::_setReachedPoints that only executes updates in the database if
     * necessary. In addition, unlike the original, this method does NOT update the test cache, so this must also be called
     * afterward.
     */
    private function updateReachedPoints(
        int $user_id,
        int $active_id,
        int $question_id,
        float $old_points,
        float $points,
        float $max_points,
        int $pass
    ): bool {
        // Only update the test results if necessary
        $has_changed = $old_points !== $points;
        if ($has_changed && $points <= $max_points) {
            $this->db->update(
                'tst_test_result',
                [
                    'points' => [\ilDBConstants::T_FLOAT, $points],
                    'tstamp' => [\ilDBConstants::T_INTEGER, time()],
                ],
                [
                    'active_fi' => [\ilDBConstants::T_INTEGER, $active_id],
                    'question_fi' => [\ilDBConstants::T_INTEGER, $question_id],
                    'pass' => [\ilDBConstants::T_INTEGER, $pass]
                ]
            );
        }

        \ilCourseObjectiveResult::_updateObjectiveResult($user_id, $active_id, $question_id);
        $logger = $this->test->getTestLogger();
        if ($logger->isLoggingEnabled()) {
            $logger->logScoringInteraction(
                new TestScoringInteraction(
                    $this->test->getRefId(),
                    $question_id,
                    $this->scorer->getId(),
                    $user_id,
                    TestScoringInteractionTypes::QUESTION_GRADING_RESET,
                    time(),
                    []
                )
            );
        }

        return $has_changed;
    }

    private function updatePassResultsTable(
        int $active_id,
        int $pass,
        bool $reached_points_changed
    ): void {
        // Always update the pass result as the maximum points might have changed
        $data = $this->test->getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
        $values = [
            'maxpoints' => [\ilDBConstants::T_FLOAT, $data['points']],
            'tstamp' => [\ilDBConstants::T_INTEGER, time()],
        ];

        if ($reached_points_changed) {
            $result = $this->db->queryF(
                'SELECT SUM(points) reachedpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s',
                [\ilDBConstants::T_INTEGER, \ilDBConstants::T_INTEGER],
                [$active_id, $pass]
            );
            $values['points'] = [\ilDBConstants::T_FLOAT, max($result->fetchAssoc()['reachedpoints'] ?? 0.0, 0.0)];
        }

        $this->db->update(
            'tst_pass_result',
            $values,
            ['active_fi' => [\ilDBConstants::T_INTEGER, $active_id], 'pass' => [\ilDBConstants::T_INTEGER, $pass]]
        );
    }

    /**
     * @return string HTML with the best solution output.
     */
    public function calculateBestSolutionForTest(): string
    {
        $solution = '';
        foreach ($this->test->getAllQuestions() as $question) {
            /** @var AssQuestionGUI $question_gui */
            $question_gui = $this->test->createQuestionGUI("", $question['question_id']);
            $solution .= '<h1>' . $question_gui->getObject()->getTitleForHTMLOutput() . '</h1>';
            $solution .= $question_gui->getSolutionOutput(0, null, true, true, false, false, true, false);
        }

        return $solution;
    }

    public function removeAllQuestionResults($question_id)
    {
        $query = "DELETE FROM tst_test_result WHERE question_fi = %s";
        $this->db->manipulateF($query, ['integer'], [$question_id]);
    }

    /**
     *
     * @param array<int> $active_ids
     */
    public function updatePassAndTestResults(array $active_ids): void
    {
        foreach ($active_ids as $active_id) {
            $passSelector = new \ilTestPassesSelector($this->db, $this->test);
            $passSelector->setActiveId($active_id);

            foreach ($passSelector->getExistingPasses() as $pass) {
                $this->test_result_repository->updateTestAttemptResult(
                    $active_id,
                    $pass,
                    null,
                    null,
                    false
                );
            }

            $this->test_result_repository->updateTestResultCache($active_id);
        }
    }

    public function getNumManualScorings(): int
    {
        $query = "
			SELECT COUNT(*) num_manual_scorings
                FROM tst_test_result tres
			INNER JOIN tst_active tact
                ON tact.active_id = tres.active_fi
			WHERE tact.test_fi = %s
			AND tres.manual = 1
		";

        $types = ['integer'];
        $values = [$this->test->getTestId()];

        if ($this->getQuestionId()) {
            $query .= "
				AND tres.question_fi = %s
			";

            $types[] = 'integer';
            $values[] = $this->getQuestionId();
        }

        $res = $this->db->queryF($query, $types, $values);

        while ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['num_manual_scorings'];
        }

        return 0;
    }
}
