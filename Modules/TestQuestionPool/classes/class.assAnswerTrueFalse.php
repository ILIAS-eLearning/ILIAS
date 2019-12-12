<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assAnswerSimple.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for true/false or yes/no answers
 *
 * ASS_AnswerTrueFalse is a class for true/false or yes/no answers used for example in multiple choice tests.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @TODO: Get rid of integers in favor of booleans here.
 */
class ASS_AnswerTrueFalse extends ASS_AnswerSimple
{
    /**
     * Correctness of the answer
     *
     * A boolean value indicating the correctness of the answer. Either the answer is correct (TRUE) or incorrect (FALSE)
     *
     * @var boolean
     */
    public $correctness;

    /**
     * ASS_AnswerTrueFalse constructor
     *
     * The constructor takes possible arguments an creates an instance of the ASS_AnswerTrueFalse object.
     *
     * @param string $answertext A string defining the answer text
     * @param double $points The number of points given for the selected answer
     * @param boolean $correctness A boolean value indicating the correctness of the answer
     * @param integer $order A nonnegative value representing a possible display or sort order
     */
    public function __construct($answertext = "", $points = 0.0, $order = 0, $correctness = false)
    {
        parent::__construct($answertext, $points, $order);

        // force $this->correctness to be a string
        // ilDB->quote makes 1 from true and saving it to ENUM('1','0') makes that '0'!!!
        // (maybe that only happens for certain mysql versions)
        $this->correctness = $correctness . "";
    }

    /**
     * Gets the correctness
     *
     * @return boolean correctness
     */
    public function getCorrectness()
    {
        return $this->correctness;
    }

    /**
     * Gets the correctness
     *
     * @return boolean correctness
     */
    public function isCorrect()
    {
        return $this->correctness;
    }

    /**
     * Gets the correctness
     *
     * @return boolean correctness
     */
    public function isIncorrect()
    {
        return !$this->correctness;
    }

    /**
     * Gets the correctness
     *
     * @deprecated Use isCorrect instead.
     *
     * @return boolean correctness
     */
    public function isTrue()
    {
        return $this->correctness;
    }

    /**
     * Gets the correctness
     *
     * @deprecated Use isIncorrect instead.
     *
     * @return boolean correctness
     */
    public function isFalse()
    {
        return !$this->correctness;
    }

    /**
     * Sets the correctness
     *
     * @param boolean $correctness A boolean value indicating the correctness of the answer
     */
    public function setCorrectness($correctness = false)
    {
        // force $this->correctness to be a string
        // ilDB->quote makes 1 from true and saving it to ENUM('1','0') makes that '0'!!!
        // (maybe that only happens for certain mysql versions)
        $this->correctness = $correctness . "";
    }

    /**
     * Sets the answer as a correct answer
     *
     * @deprecated Use setCorrectness instead.
     */
    public function setTrue()
    {
        $this->correctness = "1";
    }

    /**
     * Sets the answer as a incorrect answer
     *
     *@deprecated Use setCorrectness instead.
     */
    public function setFalse()
    {
        $this->correctness = "0";
    }
}
