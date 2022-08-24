<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class EmptyAnswerExpression
 *
 * Date: 15.05.14
 * Time: 08:51
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacEmptyAnswerExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    public static $pattern = '/(\?)/';

    public static $identifier = "?";

    /**
     * @var boolean
     */
    protected $matched;

    protected function getPattern(): string
    {
        return '/(\?)/';
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return "?";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return " nicht beantwortet";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string $comperator
     * @param null|int $index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null): bool
    {
        if ($index == null) {
            switch ($comperator) {
                case "=":
                    return !$result->hasSolutions();
                    break;
                case "<>":
                    return $result->hasSolutions();
                    break;
                default:
                    return false;
            }
        } else {
            $solution = $result->getSolutionForKey($index);
            switch ($comperator) {
                case "=":
                    return $solution == null;
                    break;
                case "<>":
                    return $solution != null;
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches): void
    {
        $this->matched = true;
    }
}
