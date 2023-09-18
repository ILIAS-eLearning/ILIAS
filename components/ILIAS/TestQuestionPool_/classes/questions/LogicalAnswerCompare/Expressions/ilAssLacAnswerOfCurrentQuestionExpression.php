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
    protected function setMatches($matches): void
    {
    }

    /**
     * Get the question index
     *
     * @return int
     */
    public function getQuestionIndex(): ?int
    {
        return null;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return "R";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return "Aktuelle Frage";
    }

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern(): string
    {
        return '/.+/';
    }
}
