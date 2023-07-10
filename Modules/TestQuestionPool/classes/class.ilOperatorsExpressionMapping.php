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
 * Class ilOperatorsExpressionMapping
 *
 * Date: 03.12.13
 * Time: 13:16
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilOperatorsExpressionMapping
{
    /**
     * @var array
     */
    private static $mappings = array(
        iQuestionCondition::PercentageResultExpression => array("<", "<=", "=", ">=", ">", "<>"),
        iQuestionCondition::NumericResultExpression => array("<", "<=", "=", ">=", ">", "<>"),
        iQuestionCondition::StringResultExpression => array("=", "<>"),
        iQuestionCondition::MatchingResultExpression => array("=", "<>"),
        iQuestionCondition::OrderingResultExpression => array("=", "<>"),
        iQuestionCondition::NumberOfResultExpression => array("=", "<>"),
        iQuestionCondition::ExclusiveResultExpression => array("=", "<>"),
        iQuestionCondition::EmptyAnswerExpression => array("=", "<>")
    );

    /**
     * @param string $expression
     *
     * @return array
     */
    public static function getOperatorsByExpression($expression): array
    {
        return self::$mappings[$expression];
    }

    public static function getAll(): array
    {
        return self::$mappings;
    }
}
