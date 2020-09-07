<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacQuestionExpressionInterface.php";

/**
 * Class AnswerOfCurrentQuestionExpression for the expression R
 *
 * Date: 25.03.13
 * Time: 16:39
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacAnswerOfCurrentQuestionExpression extends ilAssLacAbstractExpression implements ilAssLacQuestionExpressionInterface
{

    /**
     * The pattern <b>'/R([^\[|0-9]|$)/'</b> should match the following expression in a condition <br />
     * <br />
     * It is used to create a AnswerOfCurrentQuestionExpression

     * @see AnswerOfCurrentQuestionExpression
     * @var string
     */
    public static $pattern = '/(R)(?=\=|<|>|\s|$)/';

    /**
     * @var string
     */
    public static $identifier = "R";

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches)
    {
    }

    /**
     * Get the question index
     *
     * @return int
     */
    public function getQuestionIndex()
    {
        return null;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return "R";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "Aktuelle Frage";
    }

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern()
    {
        return '/.+/';
    }
}
