<?php

/**
 * Class CompositeEvaluator
 *
 * Date: 07.01.14
 * Time: 13:27
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacCompositeEvaluator
{

    /**
     * @var ilAssLacQuestionProvider
     */
    protected $object_loader;

    /**
     * @var integer
     */
    protected $activeId;

    /**
     * @var integer
     */
    protected $pass;

    /**
     * @param ilAssLacQuestionProvider $object_loader
     * @param $activeId
     * @param $pass
     */
    public function __construct($object_loader, $activeId, $pass)
    {
        $this->object_loader = $object_loader;
        $this->activeId = $activeId;
        $this->pass = $pass;
    }

    /**
     * @param ilAssLacAbstractComposite $composite
     *
     * @return bool
     */
    public function evaluate(ilAssLacAbstractComposite $composite)
    {
        if (count($composite->nodes) > 0) {
            $composite->nodes[0] = $this->evaluate($composite->nodes[0]);
            $composite->nodes[1] = $this->evaluate($composite->nodes[1]);
            $composite = $this->evaluateSubTree($composite);
        }
        return $composite;
    }

    /**
     * @param ilAssLacAbstractComposite $composite
     *
     * @return bool
     */
    private function evaluateSubTree(ilAssLacAbstractComposite $composite)
    {
        $result = false;
        if ($composite->nodes[0] instanceof ilAssLacExpressionInterface &&
            $composite->nodes[1] instanceof ilAssLacExpressionInterface
        ) {
            $question = $this->object_loader->getQuestion($composite->nodes[0]->getQuestionIndex());
            $rightNode = $composite->nodes[1];

            $index = $this->isInstanceOfAnswerIndexProvidingExpression($composite) ? $composite->nodes[0]->getAnswerIndex(): null;

            $solutions = $question->getUserQuestionResult($this->activeId, $this->pass);

            if ($question instanceof assClozeTest) {
                // @todo for Thomas J.: Move to interface / implement in concrete class (req. for future releases)
                /**
                 * @var $gap assClozeGap
                 * @var $answer assAnswerCloze
                 */
                $result = $solutions->getSolutionForKey($index);
                $gap    = $question->getAvailableAnswerOptions($index-1);

                if ($rightNode instanceof ilAssLacStringResultExpression) {
                    if ($gap->getType() == 1) {
                        $answer = $gap->getItem($result['value'] - 1);
                        $solutions->removeByKey($index);
                        $solutions->addKeyValue($index, $answer->getAnswertext());
                    }
                } elseif (
                    $rightNode instanceof ilAssLacPercentageResultExpression &&
                    $composite->nodes[0] instanceof ilAssLacResultOfAnswerOfQuestionExpression) {
                    /**
                     * @var $answers assAnswerCloze[]
                     */
                    $answers = $gap->getItems();
                    $max_points = 0;
                    foreach ($answers as $answer) {
                        if ($max_points < $answer->getPoints()) {
                            $max_points = $answer->getPoints();
                        }
                    }

                    $item           = null;
                    $reached_points = null;
                    // @todo for Thomas J.: Maybe handle identical scoring for every type
                    switch ($gap->getType()) {
                        case CLOZE_TEXT:
                            for ($order = 0; $order < $gap->getItemCount(); $order++) {
                                $answer      = $gap->getItem($order);
                                $item_points = $question->getTextgapPoints($answer->getAnswertext(), $result['value'], $answer->getPoints());
                                if ($item_points > $reached_points) {
                                    $reached_points = $item_points;
                                }
                            }
                            break;

                        case CLOZE_NUMERIC:
                            for ($order = 0; $order < $gap->getItemCount(); $order++) {
                                $answer      = $gap->getItem($order);
                                $item_points = $question->getNumericgapPoints($answer->getAnswertext(), $result["value"], $answer->getPoints(), $answer->getLowerBound(), $answer->getUpperBound());
                                if ($item_points > $reached_points) {
                                    $reached_points = $item_points;
                                }
                            }
                            break;

                        case CLOZE_SELECT:
                            if ($result['value'] != null) {
                                $answer         = $gap->getItem($result['value'] - 1);
                                $reached_points = $answer->getPoints();
                            }
                            break;
                    }

                    $percentage = 0;
                    if ($max_points != 0 && $reached_points !== null) {
                        $percentage = (int) (($reached_points / $max_points) * 100);
                    }
                    $solutions->setReachedPercentage($percentage);
                }
            }

            if (
                $question instanceof assFormulaQuestion &&
                $rightNode instanceof ilAssLacPercentageResultExpression &&
                $this->isInstanceOfAnswerIndexProvidingExpression($composite->nodes[0])
            ) {
                // @todo for Thomas J.: Move to interface / implement in concrete class (req. for future releases)
                $result = $solutions->getSolutionForKey($index);
                $answer = $question->getAvailableAnswerOptions($index-1);

                $unit = $solutions->getSolutionForKey($index . "_unit");
                $key = null;
                if (is_array($unit)) {
                    $key = $unit['value'];
                }

                $max_points = $answer->getPoints();
                $points = $answer->getReachedPoints($question->getVariables(), $question->getResults(), $result["value"], $key, $question->getUnitrepository()->getUnits());

                $percentage = 0;
                if ($max_points != 0) {
                    $percentage = (int) (($points/$max_points)*100);
                }
                $solutions->setReachedPercentage($percentage);
            }

            $result = $rightNode->checkResult($solutions, $composite->getPattern(), $index);
        } else {
            switch ($composite->getPattern()) {
                case "&":
                    $result = $composite->nodes[0] && $composite->nodes[1];
                    break;
                case "|":
                    $result = $composite->nodes[0] || $composite->nodes[1];
                    break;
                default:
                    $result = false;
            }
        }

        if ($composite->isNegated()) {
            return !$result;
        }
        return $result;
    }

    /**
     * @param ilAssLacAbstractComposite $composite
     * @return bool
     */
    private function isInstanceOfAnswerIndexProvidingExpression(ilAssLacAbstractComposite $composite)
    {
        if ($composite->nodes[0] instanceof ilAssLacResultOfAnswerOfQuestionExpression) {
            return true;
        }

        if ($composite->nodes[0] instanceof ilAssLacResultOfAnswerOfCurrentQuestionExpression) {
            return true;
        }
        
        return false;
    }
}
