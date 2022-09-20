<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class MatchingResultExpression for the expression ;n:m;
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacMatchingResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/;[0-9]+:[0-9]+;/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>;n:m;</b>	"n" is a Placeholder for a left numeric index
     * 				"m" is a Placeholder for a right numeric index
     * </pre>
     * It is used to create a ilAssLacNumericResultExpression

     * @see MatchingResultExpression
     * @var string
     */
    public static $pattern = "/;[0-9]+:[0-9]+;/";

    /**
     * @var string
     */
    public static $identifier = ";n:m;";

    /**
     * A numeric value which should be the left index of an element
     *
     * @var float
     */
    protected $left_numeric_value;

    /**
     * A numeric value which should be the right index of an element
     *
     * @var float
     */
    protected $right_numeric_value;

    protected function getPattern(): string
    {
        return '/;(\d+):(\d+);/';
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
        $this->left_numeric_value = $matches[1][0];
        $this->right_numeric_value = $matches[2][0];
    }

    /**
     * @return float
     */
    public function getRightNumericValue(): float
    {
        return $this->right_numeric_value;
    }

    /**
     * @return float
     */
    public function getLeftNumericValue(): float
    {
        return $this->left_numeric_value;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue(): string
    {
        return ";" . $this->left_numeric_value . ":" . $this->right_numeric_value . ";";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription(): string
    {
        return "0 beantwortet ";
    }

    /**
     * @param ilUserQuestionResult $result
     * @param string               $comperator
     * @param null|int				$index
     *
     * @return bool
     */
    public function checkResult($result, $comperator, $index = null): bool
    {
        $solutions = $result->getSolutions();
        $isTrue = false;
        foreach ($solutions as $solution) {
            $isTrue = $isTrue || $this->compare($comperator, $solution["key"], $solution["value"]);
        }
        return $isTrue;
    }

    /**
     * @param string $comperator
     * @param int $left
     * @param int $right
     *
     * @return bool
     */
    private function compare($comperator, $left, $right): bool
    {
        switch ($comperator) {
            case "=":
                return $this->getLeftNumericValue() == $left && $this->getRightNumericValue() == $right;
                break;
            case "<>":
                return $this->getLeftNumericValue() != $left || $this->getRightNumericValue() != $right;
                break;
            default:
                return false;
        }
    }
}
