<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacQuestionExpressionInterface.php";

/**
 * Class ResultOfAnswerOfCurrentQuestion for the expression R[m]
 *
 * Date: 25.03.13
 * Time: 16:40
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacResultOfAnswerOfCurrentQuestionExpression extends ilAssLacAbstractExpression implements ilAssLacQuestionExpressionInterface
{
    /**
     * The pattern <b>"/R\\[[0-9]+\\]/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>R[m]</b>	"m" is a Placeholde for a numeric answer index of a question
     * </pre>
     * It is used to create a ResultOfAnswerOfCurrentQuestionExpression

     * @see ResultOfAnswerOfCurrentQuestionExpression
     * @var string
     */
    public static $pattern = "/R\\[[0-9]+\\]/";

    /**
     * @var string
     */
    public static $identifier = "R[m]";

    /**
     * The indes of an answer of a question
     *
     * @var int
     */
    protected $answer_index;

    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    public function getPattern()
    {
        return '/\[(\d+)\]/';
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
        $this->answer_index = $matches[1][0];
    }

    /**
     * @return int
     */
    public function getQuestionIndex()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getAnswerIndex()
    {
        return $this->answer_index;
    }

    /**
     * Get the value of this Expression
     * @return string
     */
    public function getValue()
    {
        return 'R[' . $this->answer_index . ']';
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "Aktuelle Frage mit Anwort " . $this->answer_index . " beantwortet ";
    }
}
