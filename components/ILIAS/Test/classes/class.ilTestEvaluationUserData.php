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
* Class ilTestEvaluationUserData
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

class ilTestEvaluationUserData
{
    private $questionTitles;
    public string $name;
    public string $login;
    public int $user_id;
    protected bool $submitted;
    public float $reached;
    public float $maxpoints;
    public string $mark;
    public string $mark_official;
    public int $questionsWorkedThrough;
    public int $numberOfQuestions;
    public string $timeOfWork;
    public int $firstVisit;
    public int $lastVisit;
    public bool $passed;

    /**
    * @var array<int, ilTestEvaluationPassData>
    */
    public array $passes;
    public ?int $lastFinishedPass;
    public array $questions;

    /**
    * Pass Scoring (Last pass = 0, Best pass = 1)
    */
    private int $passScoring;

    public function __sleep()
    {
        return array('questions', 'passes', 'passed', 'lastVisit', 'firstVisit', 'timeOfWork', 'numberOfQuestions',
        'questionsWorkedThrough', 'mark_official', 'mark', 'maxpoints', 'reached', 'user_id', 'login',
        'name', 'passScoring');
    }

    public function __construct(int $passScoring)
    {
        $this->passes = [];
        $this->questions = [];
        $this->passed = false;
        $this->passScoring = $passScoring;
    }

    public function getPassScoring(): int
    {
        return $this->passScoring;
    }

    public function setPassScoring(int $passScoring): void
    {
        $this->passScoring = $passScoring;
    }

    public function getPassed(): bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed): void
    {
        $this->passed = $passed;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getLogin(): string
    {
        return $this->login ?? '';
    }

    public function setLogin($login): void
    {
        $this->login = $login;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function setSubmitted(bool $submitted): void
    {
        $this->submitted = $submitted;
    }

    public function getReached(): float
    {
        return $this->getReachedPoints($this->getScoredPass());
    }

    public function setReached(float $reached): void
    {
        $this->reached = $reached;
    }

    public function getMaxpoints(): float
    {
        return $this->getAvailablePoints($this->getScoredPass());
    }

    public function setMaxpoints(float $max_points): void
    {
        $this->maxpoints = $max_points;
    }

    public function getReachedPointsInPercent(): float
    {
        return $this->getMaxPoints() ? $this->getReached() / $this->getMaxPoints() * 100.0 : 0;
    }

    public function getMark(): string
    {
        return $this->mark;
    }

    public function setMark(string $a_mark): void
    {
        $this->mark = $a_mark;
    }

    public function getQuestionsWorkedThrough(): int
    {
        $questionpass = $this->getScoredPass();
        if (!is_object($this->passes[$questionpass])) {
            $questionpass = 0;
        }
        if (is_object($this->passes[$questionpass])) {
            return $this->passes[$questionpass]->getNrOfAnsweredQuestions();
        }
        return 0;
    }

    public function setQuestionsWorkedThrough(int $nr): void
    {
        $this->questionsWorkedThrough = $nr;
    }

    public function getNumberOfQuestions(): int
    {
        $questionpass = $this->getScoredPass();
        if (!is_object($this->passes[$questionpass])) {
            $questionpass = 0;
        }
        if (is_object($this->passes[$questionpass])) {
            return $this->passes[$questionpass]->getQuestionCount();
        }
        return 0;
    }

    public function setNumberOfQuestions(int $nr): void
    {
        $this->numberOfQuestions = $nr;
    }

    public function getQuestionsWorkedThroughInPercent(): float
    {
        return $this->getNumberOfQuestions() ? $this->getQuestionsWorkedThrough() / $this->getNumberOfQuestions() * 100.0 : 0;
    }

    public function getTimeOfWork(): int
    {
        $time = 0;
        foreach ($this->passes as $pass) {
            $time += $pass->getWorkingTime();
        }
        return $time;
    }

    public function setTimeOfWork(string $time_of_work): void
    {
        $this->timeOfWork = $time_of_work;
    }

    public function getFirstVisit(): int
    {
        return $this->firstVisit;
    }

    public function setFirstVisit(int $time): void
    {
        $this->firstVisit = $time;
    }

    public function getLastVisit(): int
    {
        return $this->lastVisit;
    }

    public function setLastVisit(int $time): void
    {
        $this->lastVisit = $time;
    }

    public function getPasses(): array
    {
        return $this->passes;
    }

    public function addPass(int $pass_nr, ilTestEvaluationPassData $pass): void
    {
        $this->passes[$pass_nr] = $pass;
    }

    public function getPass(int $pass_nr): ?ilTestEvaluationPassData
    {
        if (array_key_exists($pass_nr, $this->passes)) {
            return $this->passes[$pass_nr];
        } else {
            return null;
        }
    }

    public function getPassCount(): int
    {
        return count($this->passes);
    }

    public function getScoredPass(): int
    {
        if ($this->getPassScoring() == 1) {
            return $this->getBestPass();
        } else {
            return $this->getLastPass();
        }
    }
    /**
     * This is used in the export of test results
     * Aligned with ilObjTest::_getBestPass: from passes with equal points the first one wins
    */
    public function getBestPass(): int
    {
        $bestpoints = 0;
        $bestpass = null;

        $obligationsAnsweredPassExists = $this->doesObligationsAnsweredPassExist();

        foreach ($this->passes as $pass) {
            $reached = $this->getReachedPointsInPercentForPass($pass->getPass());

            if (($reached > $bestpoints
                && ($pass->areObligationsAnswered() || !$obligationsAnsweredPassExists))
                || !isset($bestpass)) {
                $bestpoints = $reached;
                $bestpass = $pass->getPass();
            }
        }

        return (int) $bestpass;
    }

    public function getLastPass(): int
    {
        $lastpass = 0;
        foreach (array_keys($this->passes) as $pass) {
            if ($pass > $lastpass) {
                $lastpass = $pass;
            }
        }
        return $lastpass;
    }

    public function getFinishedPasses(): int
    {
        return $this->getLastFinishedPass() === null ? 0 : $this->getLastFinishedPass() + 1;
    }

    public function getLastFinishedPass(): ?int
    {
        return $this->lastFinishedPass;
    }

    public function setLastFinishedPass(?int $pass = null): void
    {
        $this->lastFinishedPass = $pass;
    }
    public function addQuestionTitle(int $question_id, string $question_title): void
    {
        $this->questionTitles[$question_id] = $question_title;
    }

    /**
     *
     * @return array<string>
     */
    public function getQuestionTitles(): array
    {
        return $this->questionTitles;
    }

    public function getQuestions(int $pass = 0): ?array
    {
        if (array_key_exists($pass, $this->questions)) {
            return $this->questions[$pass];
        } else {
            return null;
        }
    }

    public function addQuestion(int $original_id, int $question_id, float $max_points, int $sequence = null, int $pass = 0): void
    {
        if (!isset($this->questions[$pass])) {
            $this->questions[$pass] = [];
        }

        $this->questions[$pass][] = [
            "id" => $question_id, // the so called "aid" from any historical time
            "o_id" => $original_id, // when the "aid" was valid this was the "id"
            "points" => $max_points,
            "sequence" => $sequence
        ];
    }

    public function getQuestion(int $index, int $pass = 0): ?array
    {
        if (array_key_exists($index, $this->questions[$pass])) {
            return $this->questions[$pass][$index];
        } else {
            return null;
        }
    }

    public function getQuestionCount(int $pass = 0): int
    {
        $count = 0;
        if (array_key_exists($pass, $this->passes)) {
            $count = $this->passes[$pass]->getQuestionCount();
        }
        return $count;
    }

    public function getReachedPoints(int $pass = 0): float
    {
        $reached = 0;
        if (array_key_exists($pass, $this->passes)) {
            $reached = $this->passes[$pass]->getReachedPoints();
        }
        $reached = ($reached < 0) ? 0 : $reached;
        $reached = round($reached, 2);
        return $reached;
    }

    public function getAvailablePoints(int $pass = 0): float
    {
        $available = 0;
        if (!is_object($this->passes[$pass])) {
            $pass = 0;
        }
        if (!is_object($this->passes[$pass])) {
            return 0;
        }
        $available = $this->passes[$pass]->getMaxPoints();
        $available = round($available, 2);
        return $available;
    }

    public function getReachedPointsInPercentForPass(int $pass = 0): float
    {
        $reached = $this->getReachedPoints($pass);
        $available = $this->getAvailablePoints($pass);
        $percent = ($available > 0) ? $reached / $available : 0;
        return $percent;
    }

    public function setUserID(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getUserID(): ?int
    {
        return $this->user_id;
    }

    public function setMarkOfficial(string $a_mark_official): void
    {
        $this->mark_official = $a_mark_official;
    }

    public function getMarkOfficial(): string
    {
        return $this->mark_official;
    }

    /**
     * returns the object of class ilTestEvaluationPassData
     * that relates to the the scored test pass (best pass / last pass)
     */
    public function getScoredPassObject(): ilTestEvaluationPassData
    {
        if ($this->getPassScoring() == 1) {
            return $this->getBestPassObject();
        } else {
            return $this->getLastPassObject();
        }
    }

    /**
     * returns the count of hints requested by participant for scored testpass
     */
    public function getRequestedHintsCountFromScoredPass(): int
    {
        return $this->getRequestedHintsCount($this->getScoredPass());
    }

    public function getExamIdFromScoredPass(): string
    {
        $examId = '';
        $scoredPass = $this->getScoredPass();

        if (isset($this->passes[$scoredPass]) && $this->passes[$scoredPass] instanceof ilTestEvaluationPassData) {
            $examId = $this->passes[$scoredPass]->getExamId();
        }

        return $examId;
    }

    /**
     * returns the count of hints requested by participant for given testpass
     *
     * @throws ilTestException
     */
    public function getRequestedHintsCount(int $pass): int
    {
        if (!isset($this->passes[$pass]) || !($this->passes[$pass] instanceof ilTestEvaluationPassData)) {
            throw new ilTestException("invalid pass index given: $pass");
        }

        $requestedHintsCount = $this->passes[$pass]->getRequestedHintsCount();

        return $requestedHintsCount;
    }

    /**
     * returns the object of class ilTestEvaluationPassData
     * that relates to the the best test pass
     */
    public function getBestPassObject(): ilTestEvaluationPassData
    {
        $bestpoints = 0;
        $bestpassObject = 0;

        $obligationsAnsweredPassExists = $this->doesObligationsAnsweredPassExist();

        foreach ($this->passes as $pass) {
            $reached = $this->getReachedPointsInPercentForPass($pass->getPass());

            if ($reached >= $bestpoints && ($pass->areObligationsAnswered() || !$obligationsAnsweredPassExists)) {
                $bestpoints = $reached;
                $bestpassObject = $pass;
            }
        }

        return $bestpassObject;
    }

    /**
     * returns the object of class ilTestEvaluationPassData
     * that relates to the the last test pass
     */
    public function getLastPassObject(): ilTestEvaluationPassData
    {
        $lastpassIndex = 0;

        foreach (array_keys($this->passes) as $passIndex) {
            if ($passIndex > $lastpassIndex) {
                $lastpassIndex = $passIndex;
            }
        }

        $lastpassObject = $this->passes[$lastpassIndex];

        return $lastpassObject;
    }

    /**
     * returns the fact wether a test pass
     * with all obligations answered exists or not
     */
    public function doesObligationsAnsweredPassExist(): bool
    {
        foreach ($this->passes as $pass) {
            if ($pass->areObligationsAnswered()) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns the fact wether all obligations
     * in the scored test pass are answered or not
     */
    public function areObligationsAnswered(): bool
    {
        return $this->getScoredPassObject()->areObligationsAnswered();
    }
}
