<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Class for simple answers
 *
 * ASS_AnswerSimple is a class for simple answers used for example in cloze tests with text gap.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 */
class ASS_AnswerSimple
{
    /**
     * Answer text
     *
     * The answer text string of the ASS_AnswerSimple object
     *
     * @var string
     */
    protected $answertext;

    /**
     * Points for selected answer
     *
     * The number of points given for the selected answer
     *
     * @var double
     */
    protected $points;

    /**
     * A sort or display order
     *
     * A nonnegative integer value indicating a sort or display order of the answer. This value can be used by objects containing ASS_AnswerSimple objects.
     *
     * @var integer
     */
    protected $order;

    /**
     * The database id of the answer
     *
     * The database id of the answer
     *
     * @var integer
     */
    protected $id;

    /**
     * ASS_AnswerSimple constructor
     *
     * The constructor takes possible arguments an creates an instance of the ASS_AnswerSimple object.
     *
     * @param string  $answertext A string defining the answer text
     * @param double  $points     The number of points given for the selected answer
     * @param integer $order      A nonnegative value representing a possible display or sort order
     * @param integer $id         The database id of the answer
     *
     * @return ASS_AnswerSimple
     */
    public function __construct($answertext = "", $points = 0.0, $order = 0, $id = -1)
    {
        $this->answertext = $answertext;
        $this->setPoints($points);
        $this->order = $order;
        $this->id = $id;
    }

    /**
     * Gets the answer id
     *
     * Returns the answer id
     *
     * @return integer answer id
     *
     * @see $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the answer text
     *
     * Returns the answer text
     *
     * @return string answer text
     *
     * @see $answertext
     */
    public function getAnswertext()
    {
        return $this->answertext;
    }

    /**
     * Gets the points
     *
     * Returns the points
     *
     * @return double points
     *
     * @see $points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Checks, if the point value is numeric
     *
     * @param $a_points double Points which are checked if they're numeric.
     *
     * @return boolean true if the point value is numeric, false otherwise
     *
     * @see $points
     *
     * @TODO Find usages and see if this method can be set deprecated due to the simpleton-pattern it is on is_numeric.
     */
    public function checkPoints($a_points)
    {
        return is_numeric($a_points);
    }

    /**
     * Gets the sort/display order
     *
     * Returns a nonnegative order value for display or sorting
     *
     * @return integer order
     *
     * @see $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the order
     *
     * Sets the nonnegative order value which can be used for sorting or displaying multiple answers
     *
     * @param integer $order A nonnegative integer
     *
     * @see $order
     *
     * @TODO Find usage and see if we can get rid of "magic ignorance" of the input value.
     */
    public function setOrder($order = 0)
    {
        if ($order >= 0) {
            $this->order = $order;
        }
    }

    /**
     * Sets the answer id
     *
     * @param integer $id answer id
     *
     * @see $id
     */
    public function setId($id = -1)
    {
        $this->id = $id;
    }

    /**
     * Sets the answer text
     *
     * Sets the answer text
     *
     * @param string $answertext The answer text
     *
     * @see $answertext
     */
    public function setAnswertext($answertext = "")
    {
        $this->answertext = $answertext;
    }

    /**
     * Sets the points
     *
     * Sets the points given for selecting the answer. You can even use negative values for wrong answers.
     *
     * @param double $points The points given for the answer
     *
     * @see $points
     *
     * @TODO Find usages and see if we can get rid of "magic nullification" here.
     */
    public function setPoints($points = 0.0)
    {
        $new_points = str_replace(",", ".", $points);
        if ($this->checkPoints($new_points)) {
            $this->points = $new_points;
        } else {
            $this->points = 0.0;
        }
    }
}
