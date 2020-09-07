<?php

include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/ilAssLacAbstractComposite.php';
include_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Expressions/ilAssLacExpressionInterface.php';

/**
 * Class AbstractExpression
 *
 * Date: 25.03.13
 * Time: 15:42
 * @author Thomas Joußen <tjoussen@databay.de>
 */
abstract class ilAssLacAbstractExpression extends ilAssLacAbstractComposite implements ilAssLacExpressionInterface
{


    /**
     * Get the Pattern to match relevant informations for an Expression
     * @return string
     */
    protected function getPattern()
    {
        return '/-?[0-9\.]+/';
    }

    /**
     * Parses the delivered Value and sets the relevant information for an Expression as attributes
     *
     * @param string $value
     */
    public function parseValue($value)
    {
        $result = array();
        preg_match_all($this->getPattern(), $value, $result);
        $this->setMatches($result);
    }

    /**
     * Sets the result of the parsed value by a specific expression pattern
     * @see ExpressionInterface::parseValue()
     * @see ExpressionInterface::getPattern()
     *
     * @param array $matches
     */
    abstract protected function setMatches($matches);
}
