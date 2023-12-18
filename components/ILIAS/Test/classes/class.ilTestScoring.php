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

    public function __construct(
        private ilObjTest $test,
        private ilDBInterface $db
    ) {
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

    public function recalculateSolutions(): void
    {
        $participants = $this->test->getCompleteEvaluationData(false)->getParticipants();
        if (is_array($participants)) {
            foreach ($participants as $active_id => $userdata) {
                if (is_object($userdata) && is_array($userdata->getPasses())) {
                    $this->recalculatePasses($userdata, $active_id);
                }
                $this->test->updateTestResultCache($active_id);
            }
        }
    }

    public function recalculateSolution(int $active_id, int $pass): void
    {
        $user_data = $this
            ->test
            ->getCompleteEvaluationData(false)
            ->getParticipant($active_id)
            ->getPass($pass);

        $this->recalculatePass($user_data, $active_id, $pass);
        $this->test->updateTestResultCache($active_id);
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
    }

    public function recalculatePass(
        ilTestEvaluationPassData $passdata,
        int $active_id,
        int $pass
    ) {
        $questions = $passdata->getAnsweredQuestions();
        if (is_array($questions)) {
            foreach ($questions as $questiondata) {
                if ($this->getQuestionId() && $this->getQuestionId() != $questiondata['id']) {
                    continue;
                }

                $question_gui = $this->test->createQuestionGUI('', $questiondata['id']);
                $this->recalculateQuestionScore($question_gui, $active_id, $pass, $questiondata);
            }
        }
    }

    public function recalculateQuestionScore(
        assQuestionGUI $question_gui,
        int $active_id,
        int $pass,
        array $questiondata
    ): void {
        $reached = $question_gui->object->calculateReachedPoints($active_id, $pass);
        $actual_reached = $question_gui->object->adjustReachedPointsByScoringOptions($reached, $active_id, $pass);

        if ($this->preserve_manual_scores == true && $questiondata['manual'] == '1') {
            // Do we need processing here?
        } else {
            assQuestion::setForcePassResultUpdateEnabled(true);

            assQuestion::_setReachedPoints(
                $active_id,
                $questiondata['id'],
                $actual_reached,
                $question_gui->object->getMaximumPoints(),
                $pass,
                false,
                true
            );

            assQuestion::setForcePassResultUpdateEnabled(false);
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
