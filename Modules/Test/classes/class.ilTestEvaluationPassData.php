<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('Modules/Test/exceptions/class.ilTestEvaluationException.php');

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
    
    public function __sleep()
    {
        return array('answeredQuestions', 'pass', 'nrOfAnsweredQuestions', 'reachedpoints',
            'maxpoints', 'questioncount', 'workingtime');
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
    
    public function getNrOfAnsweredQuestions()
    {
        return $this->nrOfAnsweredQuestions;
    }
    
    public function setNrOfAnsweredQuestions($nrOfAnsweredQuestions)
    {
        $this->nrOfAnsweredQuestions = $nrOfAnsweredQuestions;
    }
    
    public function getReachedPoints()
    {
        return $this->reachedpoints;
    }
    
    public function setReachedPoints($reachedpoints)
    {
        $this->reachedpoints = $reachedpoints;
    }
    
    public function getMaxPoints()
    {
        return $this->maxpoints;
    }
    
    public function setMaxPoints($maxpoints)
    {
        $this->maxpoints = $maxpoints;
    }
    
    public function getQuestionCount()
    {
        return $this->questioncount;
    }
    
    public function setQuestionCount($questioncount)
    {
        $this->questioncount = $questioncount;
    }
    
    public function getWorkingTime()
    {
        return $this->workingtime;
    }
    
    public function setWorkingTime($workingtime)
    {
        $this->workingtime = $workingtime;
    }
    
    public function getPass()
    {
        return $this->pass;
    }
    
    public function setPass($a_pass)
    {
        $this->pass = $a_pass;
    }
    
    public function getAnsweredQuestions()
    {
        return $this->answeredQuestions;
    }
    
    public function addAnsweredQuestion($question_id, $max_points, $reached_points, $isAnswered, $sequence = null, $manual = 0)
    {
        $this->answeredQuestions[] = array(
            "id"			=> $question_id,
            "points"		=> round($max_points, 2),
            "reached"		=> round($reached_points, 2),
            'isAnswered'	=> $isAnswered,
            "sequence"		=> $sequence,
            'manual'		=> $manual
        );
    }
    
    public function &getAnsweredQuestion($index)
    {
        if (array_key_exists($index, $this->answeredQuestions)) {
            return $this->answeredQuestions[$index];
        } else {
            return null;
        }
    }
    
    public function &getAnsweredQuestionByQuestionId($question_id)
    {
        foreach ($this->answeredQuestions as $question) {
            if ($question["id"] == $question_id) {
                return $question;
            }
        }
        return null;
    }
    
    public function getAnsweredQuestionCount()
    {
        return count($this->answeredQuestions);
    }
    
    /**
     * Getter for $requestedHintsCount
     *
     * @return integer $requestedHintsCount
     */
    public function getRequestedHintsCount()
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
    public function getDeductedHintPoints()
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
     * getter for property obligationsAnswered.
     * if property wasn't set yet the method is trying
     * to determine this information by iterating
     * over the added questions.
     * if both wasn't possible the method throws an exception
     *
     * @throws ilTestEvaluationException
     * @return boolean
     */
    public function areObligationsAnswered()
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
