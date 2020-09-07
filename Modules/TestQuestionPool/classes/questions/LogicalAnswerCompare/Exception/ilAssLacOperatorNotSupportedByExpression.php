<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class OperatorNotSupportedByExpression
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacOperatorNotSupportedByExpression extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     * @param string $operator
     */
    public function __construct($expression, $operator)
    {
        $this->expression = $expression;
        $this->operator = $operator;

        parent::__construct(sprintf(
            'The expression "%s" is not supported by the operator "%s"',
            $this->getExpression(),
            $this->getOperator()
        ));
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        return sprintf(
            $lng->txt("ass_lac_operator_not_supported_by_expression"),
            $this->getOperator(),
            $this->getExpression()
        );
    }
}
