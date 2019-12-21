<?php

include_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacAbstractExpression.php";
require_once "Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacQuestionExpressionInterface.php";

/**
 * Class ResultOfAnswerOfQuestion for the expression Qn[m]
 *
 * Date: 25.03.13
 * Time: 16:40
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacResultOfAnswerOfQuestionExpression extends ilAssLacAbstractExpression implements ilAssLacQuestionExpressionInterface
{
    /**
     * The pattern <b>"/Q[0-9]+\\[[0-9]+\\]/"</b> should match the following expression in a condition <br />
     * <br />
     * <pre>
     * <b>Qn[m]</b>	"n" is a Placeholder for a numeric question index
     * 				"m" is a Placeholde for a numeric answer index of a question
     * </pre>
     * It is used to create a ResultOfAnswerOfQuestioExpression

     * @see ResultOfAnswerOfQuestionExpression
     * @var string
     */
    public static $pattern = "/Q[0-9]+\\[[0-9]+\\]/";

    /**
     * @var string
     */
    public static $identifier = "Qn[m]";

    /**
     * The index of a question
     *
     * @var int
     */
    protected $question_index;

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
        return '/(\d+)\[(\d+)\]/';
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
        $this->question_index = $matches[1][0];
        $this->answer_index = $matches[2][0];
    }

    /**
     * @return int
     */
    public function getQuestionIndex()
    {
        return $this->question_index;
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
        return "Q" . $this->question_index . '[' . $this->answer_index . ']';
    }

    /**
     * Get a human readable description of the Composite element
     * @return string
     */
    public function getDescription()
    {
        return "Frage " . $this->question_index . " mit Anwort " . $this->answer_index . " beantwortet ";
    }
}
