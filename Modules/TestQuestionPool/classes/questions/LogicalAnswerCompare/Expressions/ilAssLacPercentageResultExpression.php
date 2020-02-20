<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class PercentageResultExpression for the expression %n%
 *
 * Date: 25.03.13
 * Time: 16:40
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacPercentageResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/%[0-9]+%/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>%n%</b>	"n" is a Placeholder for a numeric value
     * </pre>
     * It is used to create a PercentageResultExpression

     * @see PercentageResultExpression
     * @var string
     */
    public static $pattern = '/%[0-9\.]+%/';

    /**
     * @var string
     */
    public static $identifier = "%n%";

    /**
     * An numeric value whicht should be compared as percentage
     *
     * @var float
     */
    protected $numeric_value;

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    protected function setMatches($matches)
    {
        $this->numeric_value = $matches[0][0];
    }

    /**
     * @return float
     */
    public function getNumericValue()
    {
        return $this->numeric_value;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return "%" . $this->numeric_value . "%";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return $this->numeric_value . "% beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string               $comperator
     * @param null					$index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null)
    {
        $percentage = $result->getReachedPercentage();
        switch ($comperator) {
            case "<":
                return $percentage < $this->getNumericValue();
                break;
            case "<=":
                return $percentage <= $this->getNumericValue();
                break;
            case "=":
                return $percentage == $this->getNumericValue();
                break;
            case ">=":
                return $percentage >= $this->getNumericValue();
                break;
            case ">":
                return $percentage > $this->getNumericValue();
                break;
            case "<>":
                return $percentage != $this->getNumericValue();
                break;
            default:
                return false;
        }
    }
}
