<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class NumberOfResultExpression fot the expression +n+
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacNumberOfResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/\\+[0-9]+\\+/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>+n+</b>	"n" is a Placeholder for a numeric value
     * </pre>
     * It is used to create a NumberOfResultExpression

     * @see NumberOfResultExpression
     * @var string
     */
    public static $pattern = "/\\+[0-9]+\\+/";

    /**
     * @var string
     */
    public static $identifier = "+n+";

    /**
     * A numeric value to identify a specific answer which should be compared
     *
     * @var int
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
     * @return int
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
        return '+' . $this->numeric_value . "+";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "Anwort " . $this->numeric_value . " beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string $comperator
     * @param null|int $index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null)
    {
        $isTrue = false;
        if ($index == null) {
            $values = $result->getUserSolutionsByIdentifier("key");

            foreach ($values as $value) {
                $isTrue = $isTrue || $this->compare($comperator, $value);
            }
        } else {
            $solution = $result->getSolutionForKey($index);
            $isTrue = $this->compare($comperator, $solution["value"]);
        }

        return $isTrue;
    }

    private function compare($comperator, $value)
    {
        switch ($comperator) {
            case "=":
                return $value == $this->getNumericValue();
                break;
            case "<>":
                return $value != $this->getNumericValue();
                break;
            default:
                return false;
        }
    }
}
