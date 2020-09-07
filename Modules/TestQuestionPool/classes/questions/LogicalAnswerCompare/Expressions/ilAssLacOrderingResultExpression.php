<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class OrderingResultExpression for the expression $a,..,n,m$
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacOrderingResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/\$[0-9]+(?:,[0-9]+)*\$/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>$a,..,n,m$</b>	all characters are placeholders for numeric indices
     * </pre>
     * It is used to create a OrderingResultExpression
     *
     * @var string
     */
    public static $pattern = '/\$[0-9]+(?:,[0-9]+)*\$/';

    /**
     * @var string
     */
    public static $identifier = '$n,m,o,p$';

    /**
     * An ordered array with numeric indices of elements
     *
     * @var int[]
     */
    protected $ordering;

    protected function getPattern()
    {
        return '/(\d+)/';
    }

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches)
    {
        $this->ordering = array();

        foreach ($matches[0] as $match) {
            $this->ordering[] = $match;
        }
    }

    /**
     * @return \int[]
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return "$" . join(",", $this->ordering) . "$";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return join(",", $this->ordering) . " beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string               $comperator
     * @param null|int				$index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null)
    {
        $keys = $result->getUserSolutionsByIdentifier("key");
        $keys = array_filter($keys, function ($element) {
            return $element != null;
        });

        switch ($comperator) {
            case "=":
                return $keys == $this->getOrdering();
                break;
            case "<>":
                return $keys != $this->getOrdering();
                break;
            default:
                return false;
        }
    }
}
