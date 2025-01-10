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
class ilTestScoring
{
    private bool $preserve_manual_scores = false;
    private array $recalculated_passes = [];
    private int $question_id = 0;

    protected ilLanguage $lng;

    /**
     * @var array<int, assQuestionGUI> $question_cache
     */
    protected array $question_cache = [];

    /**
     * @var ilTestEvaluationUserData[] $participants
     */
    protected array $participants = [];

    protected string $initiator_name;
    protected int $initiator_id;

    public function __construct(
        private ilObjTest $test,
        private ilDBInterface $db
    ) {
        global $DIC;
        $this->lng = $DIC->language();
        $this->initiator_name = $DIC->user()->getFullname() . " (" . $DIC->user()->getLogin() . ")";
        $this->initiator_id = $DIC->user()->getId();
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

    /**
     * @return ilTestEvaluationUserData[]
     */
    public function recalculateSolutions(): array
    {
        $factory = new ilTestEvaluationFactory($this->db, $this->test);
        $this->participants = $factory->getCorrectionsEvaluationData()->getParticipants();

        foreach ($this->participants as $active_id => $userdata) {
            if (is_object($userdata) && is_array($userdata->getPasses())) {
                $this->recalculatePasses($userdata, $active_id);
            }
        }

        return $this->participants;
    }

    public function recalculatePasses(ilTestEvaluationUserData $userdata, int $active_id): void
    {
        $passes = $userdata->getPasses();
        foreach ($passes as $pass => $passdata) {
            if (is_object($passdata)) {
                $this->recalculatePass($passdata, $active_id, $pass);
                $this->addRecalculatedPassByActive($active_id, $pass);
            }
        }
        $this->test->updateTestResultCache($active_id);
    }

    public function recalculatePass(
        ilTestEvaluationPassData $passdata,
        int $active_id,
        int $pass
    ) {
        $questions = $passdata->getAnsweredQuestions();
        if (is_array($questions)) {
            foreach ($questions as $question_data) {
                $q_id = $question_data['id'];
                if (!$this->getQuestionId() || $this->getQuestionId() == $q_id) {
                    $this->recalculateQuestionScore($q_id, $active_id, $pass, $question_data);
                }
            }
        }
    }

    public function recalculateQuestionScore(int $q_id, $active_id, $pass, $questiondata)
    {
        if (!isset($this->question_cache[$q_id])) {
            $this->question_cache[$q_id] = $this->test->createQuestionGUI("", $q_id)->object;
        }
        $question = $this->question_cache[$q_id];

        $old_points = $question->getReachedPoints($active_id, $pass);
        $reached = $question->calculateReachedPoints($active_id, $pass);
        $actual_reached = $question->adjustReachedPointsByScoringOptions($reached, $active_id, $pass);

        if ($this->preserve_manual_scores == true && $questiondata['manual'] == '1') {
            // Do we need processing here?
        } else {
            $this->updateReachedPoints(
                $active_id,
                $questiondata['id'],
                $old_points,
                $actual_reached,
                $question->getMaximumPoints(),
                $pass,
            );
        }

    }

    /**
     * This is an optimized version of \assQuestion::_setReachedPoints that only executes updates in the database if
     * necessary. In addition, unlike the original, this method does NOT update the test cache, so this must also be called
     * afterward.
     *
     * @see assQuestion::_setReachedPoints
     */
    public function updateReachedPoints(int $active_id, int $question_id, float $old_points, float $points, float $max_points, int $pass)
    {
        // Only update the test results if necessary
        $has_changed = $old_points != $points;
        if ($has_changed && $points <= $max_points) {
            $this->db->update(
                "tst_test_result",
                [
                    'points' => ['float', $points],
                    'tstamp' => ['integer', time()],
                ],
                [
                    'active_fi' => ['integer', $active_id],
                    'question_fi' => ['integer', $question_id],
                    'pass' => ['integer', $pass]
                ]
            );
        }

        // Always update the pass result as the maximum points might have changed
        $data = ilObjTest::_getQuestionCountAndPointsForPassOfParticipant($active_id, $pass);
        $values = [
            'maxpoints' => ['float', $data['points']],
            'tstamp' => ['integer', time()],
        ];

        if ($has_changed) {
            $result = $this->db->queryF(
                "SELECT SUM(points) reachedpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s",
                ['integer', 'integer'],
                [$active_id, $pass]
            );
            $values['points'] = ['float', (float) $result->fetchAssoc()['reachedpoints'] || 0];
        }

        $this->db->update(
            'tst_pass_result',
            $values,
            ['active_fi' => ['integer', $active_id], 'pass' => ['integer', $pass]]
        );

        ilCourseObjectiveResult::_updateObjectiveResult(ilObjTest::_getUserIdFromActiveId($active_id), $active_id, $question_id);
        if (ilObjAssessmentFolder::_enabledAssessmentLogging()) {
            $msg = $this->lng->txtlng('assessment', 'log_answer_changed_points', ilObjAssessmentFolder::_getLogLanguage());
            $msg = sprintf(
                $msg,
                $this->participants[$active_id] ? $this->participants[$active_id]->getName() : '',
                $old_points,
                $points,
                $this->initiator_name
            );
            ilObjAssessmentFolder::_addLog(
                $this->initiator_id,
                $this->test->getId(),
                $msg,
                $question_id
            );
        }
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
            $solution .= '<h1>' . $question_gui->object->getTitle() . '</h1>';
            $solution .= $question_gui->getSolutionOutput(0, null, true, true, false, false, true, false);
        }

        return $solution;
    }

    public function resetRecalculatedPassesByActives()
    {
        $this->recalculated_passes = [];
    }

    public function getRecalculatedPassesByActives(): array
    {
        return $this->recalculated_passes;
    }

    public function addRecalculatedPassByActive(int $active_id, int $pass): void
    {
        if (! array_key_exists($active_id, $this->recalculated_passes)
            || !is_array($this->recalculated_passes[$active_id])
        ) {
            $this->recalculated_passes[$active_id] = [];
        }

        $this->recalculated_passes[$active_id][] = $pass;
    }

    public function removeAllQuestionResults($question_id)
    {
        $query = "DELETE FROM tst_test_result WHERE question_fi = %s";
        $this->db->manipulateF($query, array('integer'), array($question_id));
    }

    /**
     *
     * @param array<int> $active_ids
     */
    public function updatePassAndTestResults(array $active_ids): void
    {
        foreach ($active_ids as $active_id) {
            $passSelector = new ilTestPassesSelector($this->db, $this->test);
            $passSelector->setActiveId($active_id);

            foreach ($passSelector->getExistingPasses() as $pass) {
                $this->test->updateTestPassResults($active_id, $pass, $this->test->areObligationsEnabled());
            }

            $this->test->updateTestResultCache($active_id);
        }
    }

    public function getNumManualScorings(): int
    {
        $query = "
			SELECT COUNT(*) num_manual_scorings
			FROM tst_test_result tres

			INNER JOIN tst_active tact
			ON tact.active_id = tres.active_fi
			AND tact.test_fi = %s

			WHERE tres.manual = 1
		";

        $types = array('integer');
        $values = array($this->test->getTestId());

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
