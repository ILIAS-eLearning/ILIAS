<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class ExpressionNotSupportedByQuestion
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacExpressionNotSupportedByQuestion extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * @var int
     */
    protected $question_index;

    /**
     * @param string $expression
     * @param int    $question_index
     */
    public function __construct($expression, $question_index)
    {
        $this->expression = $expression;
        $this->question_index = $question_index;
        
        if ($this->getQuestionIndex() === null) {
            $msg = sprintf(
                'The expression "%s" is not supported by the current question',
                $this->getExpression()
            );
        } else {
            $msg = sprintf(
                'The expression "%s" is not supported by the question with index "Q%s"',
                $this->getExpression(),
                $this->getQuestionIndex()
            );
        }

        parent::__construct($msg);
    }

    /**
     * @return int
     */
    public function getQuestionIndex()
    {
        return $this->question_index;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        if ($this->getQuestionIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_expression_not_supported_by_cur_question"),
                $this->getExpression()
            );
        }
        
        return sprintf(
            $lng->txt("ass_lac_expression_not_supported_by_question"),
            $this->getQuestionIndex(),
            $this->getExpression()
        );
    }
}
