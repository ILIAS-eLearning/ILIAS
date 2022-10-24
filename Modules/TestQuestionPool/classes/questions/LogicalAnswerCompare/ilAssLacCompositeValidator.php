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

use ILIAS\Refinery\Random\Group as RandomGroup;

/**
 * Class CompositeValidator
 *
 * Date: 04.12.13
 * Time: 14:19
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacCompositeValidator
{
    /**
     * @var ilAssLacQuestionProvider
     *
     * @todo Needs to be abstract or interface
     */
    protected $object_loader;

    private RandomGroup $randomGroup;

    /**
     * @param ilAssLacQuestionProvider $object_loader
     */
    public function __construct($object_loader)
    {
        global $DIC;

        $this->object_loader = $object_loader;
        $this->randomGroup = $DIC->refinery()->random();
    }

    public function validate(ilAssLacAbstractComposite $composite): void
    {
        if (count($composite->nodes) > 0) {
            $this->validate($composite->nodes[0]);
            $this->validate($composite->nodes[1]);
            $this->validateSubTree($composite);
        }

        return;
    }

    private function validateSubTree(ilAssLacAbstractComposite $composite): void
    {
        if ($composite->nodes[0] instanceof ilAssLacQuestionExpressionInterface &&
            $composite->nodes[1] instanceof ilAssLacSolutionExpressionInterface
        ) {
            $question_expression = $composite->nodes[0];
            $answer_expression = $composite->nodes[1];
            $question_index = $composite->nodes[0]->getQuestionIndex();
            $answer_index = null;
            $question = $this->object_loader->getQuestion($question_index);

            $this->checkQuestionExists($question, $question_index);
            //$this->checkQuestionIsReachable($question, $question_index);

            if ($this->isResultOfAnswerExpression($question_expression)) {
                $answer_index = $question_expression->getAnswerIndex() - 1;
                // @PHP8-CR I suspect this cluster of typizations is broken in some way. I still leave these remarks "intact"
                // to assist a more thorough analysis.
                $this->checkIfAnswerIndexOfQuestionExists($question, $question_index, $answer_index);
            }
            if ($answer_expression instanceof ilAssLacNumberOfResultExpression && !($question instanceof assClozeTest)) {
                // @PHP8-CR I suspect this cluster of typizations is broken in some way. I still leave these remarks "intact"
                // to assist a more thorough analysis.
                $this->checkIfAnswerIndexOfQuestionExists($question, $question_index, $answer_expression->getNumericValue() - 1);
            }

            $this->checkAnswerExpressionExist($question->getExpressionTypes(), $answer_expression, $question_index);
            $this->checkOperatorExistForExpression($question->getOperators($answer_expression::$identifier), $answer_expression, $composite::$pattern);

            if ($answer_expression instanceof ilAssLacOrderingResultExpression &&
                ($question instanceof assOrderingHorizontal || $question instanceof assOrderingQuestion)
            ) {
                foreach ($answer_expression->getOrdering() as $order) {
                    $count = 0;
                    foreach ($answer_expression->getOrdering() as $element) {
                        if ($element == $order) {
                            $count++;
                        }
                    }
                    if ($count > 1) {
                        throw new ilAssLacDuplicateElement($order);
                    }

                    $this->checkIfAnswerIndexOfQuestionExists($question, $question_index, $order - 1);
                }
            }
            if ($question instanceof assClozeTest) {
                $this->validateClozeTest($answer_index, $question, $answer_expression, $question_index);
            } elseif (
                $answer_expression instanceof ilAssLacPercentageResultExpression &&
                // @PHP8-CR I suspect this cluster of typizations is broken in some way. I still leave these remarks "intact"
                // to assist a more thorough analysis.
                $this->isResultOfAnswerExpression($question_expression) &&
                !($question instanceof assFormulaQuestion)
            ) {
                throw new ilAssLacExpressionNotSupportedByQuestion($answer_expression->getValue(), $question_index . "[" . ($answer_index + 1) . "]");
            }
        } elseif (
            ($composite->nodes[0] instanceof ilAssLacAbstractOperation &&
            $composite->nodes[1] instanceof ilAssLacExpressionInterface) ||
            ($composite->nodes[0] instanceof ilAssLacExpressionInterface &&
            $composite->nodes[1] instanceof ilAssLacAbstractOperation) ||
            ($composite->nodes[0] instanceof ilAssLacSolutionExpressionInterface)
        ) {
            throw new ilAssLacUnableToParseCondition("");
        }
    }

    /**
     * @param int                            $answer_index
     * @param assQuestion|iQuestionCondition $question
     * @param ilAssLacExpressionInterface            $answer_expression
     * @param int                            $question_index
     *
     * @throws ilAssLacAnswerValueNotExist
     */
    private function validateClozeTest($answer_index, $question, $answer_expression, $question_index): void
    {
        if ($answer_index !== null) {
            $options = $question->getAvailableAnswerOptions($answer_index);
            $found = false;
            switch ($options->getType()) {
                case 0: // text
                    if (
                        $answer_expression instanceof ilAssLacStringResultExpression
                    ) {
                        $found = true;
                    }

                    break;
                case 1: // select

                    if ($answer_expression instanceof ilAssLacStringResultExpression) {
                        foreach ($options->getItems($this->getNonShuffler()) as $item) {
                            if ($item->getAnswertext() == $answer_expression->getText()) {
                                $found = true;
                            }
                        }
                    } elseif ($answer_expression instanceof ilAssLacNumberOfResultExpression) {
                        foreach ($options->getItems($question->getShuffler()) as $item) {
                            if ($item->getOrder() == $answer_expression->getNumericValue() - 1) {
                                $found = true;
                            }
                        }
                    }
                break;
                case 2: // numeric
                    if ($answer_expression instanceof ilAssLacNumericResultExpression) {
                        $found = true;
                    }
                break;
            }

            if ($answer_expression instanceof ilAssLacEmptyAnswerExpression) {
                $found = true;
            }
            if (!$found && !($answer_expression instanceof ilAssLacPercentageResultExpression)) {
                throw new ilAssLacAnswerValueNotExist($question_index, $answer_expression->getValue(), $answer_index + 1);
            }
        }
    }

    /**
     * @param iQuestionCondition $question
     * @param int $question_index
     * @param int $answer_index
     *
     * @throws ilAssLacAnswerIndexNotExist
     */
    private function checkIfAnswerIndexOfQuestionExists($question, $question_index, $answer_index): void
    {
        $answer_options = $question->getAvailableAnswerOptions($answer_index);
        if ($answer_options == null) {
            throw new ilAssLacAnswerIndexNotExist($question_index, $answer_index + 1);
        }
    }

    /**
     * @param assQuestion|null $question
     * @param int $index
     *
     * @throws ilAssLacQuestionNotExist
     */
    private function checkQuestionExists($question, $index): void
    {
        if ($question == null) {
            throw new ilAssLacQuestionNotExist($index);
        }
    }

    /**
     * @param ilAssLacExpressionInterface $expression
     *
     * @return bool
     */
    private function isResultOfAnswerExpression($expression): bool
    {
        // @PHP8-CR I suspect this cluster of typizations is broken in some way. I still leave these remarks "intact"
        // to assist a more thorough analysis.
        if ($expression instanceof ilAssLacResultOfAnswerOfQuestionExpression) {
            return true;
        }

        if ($expression instanceof ilAssLacResultOfAnswerOfCurrentQuestionExpression) {
            return true;
        }

        return false;
    }

    /**
     * @param array $expressions
     * @param ilAssLacExpressionInterface $answer_expression
     * @param int $question_index
     *
     * @throws ilAssLacExpressionNotSupportedByQuestion
     */
    private function checkAnswerExpressionExist($expressions, $answer_expression, $question_index): void
    {
        if (!in_array($answer_expression::$identifier, $expressions)) {
            throw new ilAssLacExpressionNotSupportedByQuestion($answer_expression->getValue(), $question_index);
        }
    }

    /**
     * @param array $operators
     * @param ilAssLacExpressionInterface $answer_expression
     * @param string $pattern
     *
     * @throws ilAssLacOperatorNotSupportedByExpression
     */
    private function checkOperatorExistForExpression($operators, $answer_expression, $pattern): void
    {
        if (!in_array($pattern, $operators)) {
            throw new ilAssLacOperatorNotSupportedByExpression($answer_expression->getValue(), $pattern);
        }
    }

    protected function getNonShuffler(): \ILIAS\Refinery\Transformation
    {
        return $this->randomGroup->dontShuffle();
    }
}
