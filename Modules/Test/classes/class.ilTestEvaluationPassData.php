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
    * Answered questions
    *
    * @var array
    */
    public $answeredQuestions;

    /**
    * Working time
    *
    * @var int
    */
    private $workingtime;

    /**
    * Question count
    *
    * @var int
    */
    private $questioncount;

    /**
    * Maximum points
    *
    * @var int
    */
    private $maxpoints;

    /**
    * Reached points
    *
    * @var int
    */
    private $reachedpoints;

    /**
    * Number of answered questions
    *
    * @var int
    */
    private $nrOfAnsweredQuestions;

    /**
    * Test pass
    *
    * @var integer
    */
    public $pass;

    /**
     * the number of hints that was requested by participant
     *
     * @var integer
     */
    private $requestedHintsCount = null;

    /**
     * the points that were deducted for the hints requested by participant
     *
     * @var integer
     */
    private $deductedHintPoints = null;

    /**
     * the fact wether all obligatory questions are answered
     *
     * @var boolean
     */
    private $obligationsAnswered = null;

    /** @var string */
    private $examId = '';

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
        $this->answeredQuestions = array();
    }

    public function getNrOfAnsweredQuestions(): int
    {
        return $this->nrOfAnsweredQuestions;
    }

    public function setNrOfAnsweredQuestions($nrOfAnsweredQuestions)
    {
        $this->nrOfAnsweredQuestions = $nrOfAnsweredQuestions;
    }

    public function getReachedPoints(): int
    {
        return $this->reachedpoints;
    }

    public function setReachedPoints($reachedpoints)
    {
        $this->reachedpoints = $reachedpoints;
    }

    public function getMaxPoints(): float
    {
        return $this->maxpoints;
    }

    public function setMaxPoints($maxpoints)
    {
        $this->maxpoints = $maxpoints;
    }

    public function getQuestionCount(): int
    {
        return $this->questioncount;
    }

    public function setQuestionCount($questioncount)
    {
        $this->questioncount = $questioncount;
    }

    public function getWorkingTime(): int
    {
        return $this->workingtime;
    }

    public function setWorkingTime($workingtime)
    {
        $this->workingtime = $workingtime;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function setPass($a_pass)
    {
        $this->pass = $a_pass;
    }

    public function getAnsweredQuestions(): array
    {
        return $this->answeredQuestions;
    }

    public function addAnsweredQuestion($question_id, $max_points, $reached_points, $isAnswered, $sequence = null, $manual = 0)
    {
        $this->answeredQuestions[] = array(
            "id" => $question_id,
            "points" => round($max_points, 2),
            "reached" => round($reached_points, 2),
            'isAnswered' => $isAnswered,
            "sequence" => $sequence,
            'manual' => $manual
        );
    }

    public function getAnsweredQuestion($index)
    {
        if (array_key_exists($index, $this->answeredQuestions)) {
            return $this->answeredQuestions[$index];
        } else {
            return null;
        }
    }

    public function getAnsweredQuestionByQuestionId($question_id)
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

    /**
     * Getter for $requestedHintsCount
     *
     * @return integer $requestedHintsCount
     */
    public function getRequestedHintsCount(): ?int
    {
        return $this->requestedHintsCount;
    }

    /**
     * Setter for $requestedHintsCount
     *
     * @param integer $requestedHintsCount
     */
    public function setRequestedHintsCount($requestedHintsCount)
    {
        $this->requestedHintsCount = $requestedHintsCount;
    }

    /**
     * Getter for $deductedHintPoints
     *
     * @return integer $deductedHintPoints
     */
    public function getDeductedHintPoints(): ?int
    {
        return $this->deductedHintPoints;
    }

    /**
     * Setter for $deductedHintPoints
     *
     * @param integer $deductedHintPoints
     */
    public function setDeductedHintPoints($deductedHintPoints)
    {
        $this->deductedHintPoints = $deductedHintPoints;
    }

    /**
     * setter for property obligationsAnswered
     *
     * @param boolean $obligationsAnswered
     */
    public function setObligationsAnswered($obligationsAnswered)
    {
        $this->obligationsAnswered = (bool) $obligationsAnswered;
    }

    /**
     * @return string
     */
    public function getExamId(): string
    {
        return $this->examId;
    }

    /**
     * @param string $examId
     */
    public function setExamId(string $examId): void
    {
        $this->examId = $examId;
    }

    /**
     * getter for property obligationsAnswered.
     * if property wasn't set yet the method is trying
     * to determine this information by iterating
     * over the added questions.
     * if both wasn't possible the method throws an exception
     *
     * @throws ilTestEvaluationException
     * @return boolean
     */
    public function areObligationsAnswered(): ?bool
    {
        if (!is_null($this->obligationsAnswered)) {
            return $this->obligationsAnswered;
        }

        if (is_array($this->answeredQuestions) && count($this->answeredQuestions)) {
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
} // END ilTestEvaluationPassData
