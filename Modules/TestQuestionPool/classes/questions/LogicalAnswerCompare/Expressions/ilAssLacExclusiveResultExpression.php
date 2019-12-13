<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacSolutionExpressionInterface.php";

/**
 * Class ExclusiveResultExpression for the expression *m,n,o,p*
 *
 * Date: 25.03.13
 * Time: 16:41
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacExclusiveResultExpression extends ilAssLacAbstractExpression implements ilAssLacSolutionExpressionInterface
{
    /**
     * The pattern <b>"/\*[0-9]+(?:,[0-9]+)*\* /"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>#n#</b>	"n" is a Placeholder for a numeric value
     * * </pre>
     * It is used to create a ilAssLacNumericResultExpression

     * @see NumericResultExpression
     * @var string
     */
    public static $pattern = '/\*[0-9]+(?:,[0-9]+)*\*/';

    /**
     * @var string
     */
    public static $identifier = "*n,m,o,p*";

    /**
     * An ordered array with numeric indices of elements
     *
     * @var int[]
     */
    protected $exclusive;

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
        $this->exclusive = array();

        foreach ($matches[0] as $match) {
            $this->exclusive[] = $match;
        }
    }

    /**
     * @return \int[]
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return "*" . join(",", $this->exclusive) . "*";
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return join(",", $this->exclusive) . " beantwortet ";
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
        $values = $result->getUserSolutionsByIdentifier("value");
        $exclusive = $this->getExclusive();
        sort($values);
        sort($exclusive);

        switch ($comperator) {
            case "=":
                return $values == $exclusive;
                break;
            case "<>":
                return $values != $exclusive;
                break;
            default:
                return false;
        }
    }
}
