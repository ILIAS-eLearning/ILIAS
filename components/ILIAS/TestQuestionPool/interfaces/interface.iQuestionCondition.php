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
 * Class iQuestionCondition
 *
 * Date: 02.12.13
 * Time: 14:02
 * @author Thomas Joußen <tjoussen@databay.de>
 */
interface iQuestionCondition
{
    public const StringResultExpression = '~TEXT~';
    public const PercentageResultExpression = '%n%';
    public const NumericResultExpression = '#n#';
    public const MatchingResultExpression = ';n:m;';
    public const OrderingResultExpression = '$n,m,o,p$';
    public const NumberOfResultExpression = '+n+';
    public const ExclusiveResultExpression = '*n,m,o,p*';
    public const EmptyAnswerExpression = "?";

    /**
     * Get all available operations for a specific question
     *
     * @param $expression
     *
     * @internal param string $expression_type
     * @return array
     */
    public function getOperators($expression): array;

    /**
     * Get all available expression types for a specific question
     *
     * @return array
     */
    public function getExpressionTypes(): array;

    /**
     * Get the user solution for a question by active_id and the test pass
     *
     * @param int $active_id
     * @param int $pass
     *
     * @return ilUserQuestionResult
     */
    public function getUserQuestionResult($active_id, $pass): ilUserQuestionResult;

    /**
     * If index is null, the function returns an array with all anwser options
     * Else it returns the specific answer option
     *
     * @param null|int $index
     */
    public function getAvailableAnswerOptions($index = null);
}
