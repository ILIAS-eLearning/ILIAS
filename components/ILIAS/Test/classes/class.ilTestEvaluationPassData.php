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
* Class ilTestEvaluationPassData
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Björn Heyser <bheyser@databay.de>
* @version		$Id$
*
* @throws		ilTestEvaluationException
*
* @defgroup ModulesTest Modules/Test
* @extends ilObject
*/

class ilTestEvaluationPassData
{
    /**
    * @var array<int>
    */
    public array $answeredQuestions;
    private int $workingtime;
    private int $questioncount;
    private float $maxpoints;
    private float $reachedpoints;
    private int $nrOfAnsweredQuestions;
    private int $pass;
    private ?int $requestedHintsCount = null;
    private ?float $deductedHintPoints = null;
    private bool $obligationsAnswered = false;
    private string $exam_id = '';

    public function __sleep()
    {
        return array('answeredQuestions', 'pass', 'nrOfAnsweredQuestions', 'reachedpoints',
            'maxpoints', 'questioncount', 'workingtime', 'examId');
    }

    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct()
    {
        $this->answeredQuestions = [];
    }

    public function getNrOfAnsweredQuestions(): int
    {
        return $this->nrOfAnsweredQuestions;
    }

    public function setNrOfAnsweredQuestions(int $nrOfAnsweredQuestions): void
    {
        $this->nrOfAnsweredQuestions = $nrOfAnsweredQuestions;
    }

    public function getReachedPoints(): float
    {
        return $this->reachedpoints;
    }

    public function setReachedPoints(float $reachedpoints): void
    {
        $this->reachedpoints = $reachedpoints;
    }

    public function getMaxPoints(): float
    {
        return $this->maxpoints;
    }

    public function setMaxPoints(float $maxpoints): void
    {
        $this->maxpoints = $maxpoints;
    }

    public function getQuestionCount(): int
    {
        return $this->questioncount;
    }

    public function setQuestionCount(int $questioncount): void
    {
        $this->questioncount = $questioncount;
    }

    public function getWorkingTime(): int
    {
        return $this->workingtime;
    }

    public function setWorkingTime(int $workingtime): void
    {
        $this->workingtime = $workingtime;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function setPass(int $pass): void
    {
        $this->pass = $pass;
    }

    public function getAnsweredQuestions(): array
    {
        return $this->answeredQuestions;
    }

    public function addAnsweredQuestion(
        int $question_id,
        float $max_points,
        float $reached_points,
        bool $isAnswered,
        ?int $sequence = null,
        int $manual = 0
    ): void {
        $this->answeredQuestions[] = [
            "id" => $question_id,
            "points" => round($max_points, 2),
            "reached" => round($reached_points, 2),
            'isAnswered' => $isAnswered,
            "sequence" => $sequence,
            'manual' => $manual
        ];
    }

    public function getAnsweredQuestion(int $index): ?array
    {
        if (array_key_exists($index, $this->answeredQuestions)) {
            return $this->answeredQuestions[$index];
        }

        return null;
    }

    public function getAnsweredQuestionByQuestionId(int $question_id): ?array
    {
        foreach ($this->answeredQuestions as $question) {
            if ($question["id"] == $question_id) {
                return $question;
            }
        }
        return null;
    }

    public function getAnsweredQuestionCount(): int
    {
        return count($this->answeredQuestions);
    }

    public function getRequestedHintsCount(): ?int
    {
        return $this->requestedHintsCount;
    }

    public function setRequestedHintsCount(int $requestedHintsCount): void
    {
        $this->requestedHintsCount = $requestedHintsCount;
    }

    public function getDeductedHintPoints(): ?float
    {
        return $this->deductedHintPoints;
    }

    public function setDeductedHintPoints(float $deductedHintPoints): void
    {
        $this->deductedHintPoints = $deductedHintPoints;
    }

    public function setObligationsAnswered(bool $obligationsAnswered): void
    {
        $this->obligationsAnswered = $obligationsAnswered;
    }

    public function getExamId(): string
    {
        return $this->exam_id;
    }

    public function setExamId(string $exam_id): void
    {
        $this->exam_id = $exam_id;
    }

    /**
     * getter for property obligationsAnswered.
     * if property wasn't set yet the method is trying
     * to determine this information by iterating
     * over the added questions.
     * if both wasn't possible the method throws an exception
     */
    public function areObligationsAnswered(): ?bool
    {
        if (!is_null($this->obligationsAnswered)) {
            return $this->obligationsAnswered;
        }

        if (is_array($this->answeredQuestions) && $this->answeredQuestions !== []) {
            foreach ($this->answeredQuestions as $question) {
                if (!$question['isAnswered']) {
                    return false;
                }
            }

            return true;
        }

        throw new ilTestEvaluationException(
            'Neither the boolean property ilTestEvaluationPassData::obligationsAnswered was set, ' .
            'nor the property array property ilTestEvaluationPassData::answeredQuestions contains elements!'
        );
    }
}
