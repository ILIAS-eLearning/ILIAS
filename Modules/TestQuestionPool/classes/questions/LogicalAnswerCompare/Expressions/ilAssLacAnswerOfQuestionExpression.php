<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacQuestionExpressionInterface.php";

/**
 * Class AnswerOfQuestionExpression for the expression Qn
 *
 * Date: 25.03.13
 * Time: 16:39
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacAnswerOfQuestionExpression extends ilAssLacAbstractExpression implements ilAssLacQuestionExpressionInterface
{

    /**
     * The pattern <b>'/Q[0-9]+([^\[|0-9]|$)/'</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>Qn</b>	"n" is a Placeholder for a numeric question index
     * </pre>
     * It is used to create a AnswerOfQuestionExpression

     * @see AnswerOfQuestionExpression
     * @var string
     */


    //	public static $pattern = '/Q[0-9]+([^\[|0-9]|$)/';
    public static $pattern = '/(Q\d+)(?=\=|<|>|\s|$)/';

    /**
     * @var string
     */
    public static $identifier = "Qn";

    /**
     * The Index of the a question
     *
     * @var int
     */
    protected $question_index;

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches)
    {
        $this->question_index = $matches[0][0];
    }

    /**
     * Get the question index
     *
     * @return int
     */
    public function getQuestionIndex()
    {
        return $this->question_index;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return "Q" . $this->question_index;
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "Frage " . $this->question_index . " ";
    }

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern()
    {
        return '/-?[0-9]+/';
    }
}
