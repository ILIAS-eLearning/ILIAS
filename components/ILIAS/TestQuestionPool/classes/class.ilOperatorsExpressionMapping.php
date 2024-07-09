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
    private static $mappings = [
        iQuestionCondition::PercentageResultExpression => ["<", "<=", "=", ">=", ">", "<>"],
        iQuestionCondition::NumericResultExpression => ["<", "<=", "=", ">=", ">", "<>"],
        iQuestionCondition::StringResultExpression => ["=", "<>"],
        iQuestionCondition::MatchingResultExpression => ["=", "<>"],
        iQuestionCondition::OrderingResultExpression => ["=", "<>"],
        iQuestionCondition::NumberOfResultExpression => ["=", "<>"],
        iQuestionCondition::ExclusiveResultExpression => ["=", "<>"],
        iQuestionCondition::EmptyAnswerExpression => ["=", "<>"]
    ];

    public static function getOperatorsByExpression(string $expression): array
    {
        return self::$mappings[$expression];
    }

    public static function getAll(): array
    {
        return self::$mappings;
    }
}
