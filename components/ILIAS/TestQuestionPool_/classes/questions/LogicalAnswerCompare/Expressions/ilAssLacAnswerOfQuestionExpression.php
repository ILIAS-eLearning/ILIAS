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
    protected function setMatches($matches): void
    {
        $this->question_index = $matches[0][0];
    }

    /**
     * Get the question index
     *
     * @return int
     */
    public function getQuestionIndex(): int
    {
        return $this->question_index;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return "Q" . $this->question_index;
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return "Frage " . $this->question_index . " ";
    }

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern(): string
    {
        return '/-?[0-9]+/';
    }
}
