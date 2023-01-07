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
 * Class ExpressionNotSupportedByQuestion
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacExpressionNotSupportedByQuestion extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var ?int
     */
    protected ?int $question_index;

    /**
     * @var ?int
     */
    protected ?int $answer_index;

    /**
     * @param string $expression
     * @param ?int    $question_index
     */
    public function __construct(string $expression, ?int $question_index, ?int $answer_index = null)
    {
        $this->expression = $expression;
        $this->question_index = $question_index;
        $this->answer_index = $answer_index;

        if ($this->getQuestionIndex() === null) {
            if ($this->getAnswerIndex() === null) {
                $msg = sprintf(
                    'The expression "%s" is not supported by the current question',
                    $this->getExpression()
                );
            } else {
                $msg = sprintf(
                    'The expression "%s" is not supported by the current question (answer index %d)',
                    $this->getExpression(),
                    $this->getAnswerIndex()
                );
            }
        } else {
            if ($this->getAnswerIndex() === null) {
                $msg = sprintf(
                    'The expression "%s" is not supported by the question with index "Q%d"',
                    $this->getExpression(),
                    $this->getQuestionIndex()
                );
            } else {
                $msg = sprintf(
                    'The expression "%s" is not supported by the question with index "Q%d" (answer index %d)',
                    $this->getExpression(),
                    $this->getQuestionIndex(),
                    $this->getAnswerIndex()
                );
            }
        }

        parent::__construct($msg);
    }

    /**
     * @return ?int
     */
    public function getQuestionIndex(): ?int
    {
        return $this->question_index;
    }

    /**
     * @return ?int
     */
    public function getAnswerIndex(): ?int
    {
        return $this->answer_index;
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng): string
    {
        if ($this->getQuestionIndex() === null) {
            if ($this->getAnswerIndex() === null) {
                return sprintf(
                    $lng->txt("ass_lac_expression_not_supported_by_cur_question"),
                    $this->getExpression()
                );
            } else {
                return sprintf(
                    $lng->txt("ass_lac_expression_not_supported_by_cur_question_with_answer_idx"),
                    $this->getExpression(),
                    $this->getAnswerIndex()
                );
            }
        }

        if ($this->getAnswerIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_expression_not_supported_by_question"),
                $this->getQuestionIndex(),
                $this->getExpression()
            );
        } else {
            return sprintf(
                $lng->txt("ass_lac_expression_not_supported_by_question_with_answer_idx"),
                $this->getQuestionIndex(),
                $this->getExpression(),
                $this->getAnswerIndex()
            );
        }
    }
}
