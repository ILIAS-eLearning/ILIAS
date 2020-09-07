<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class QuestionNotExist
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacQuestionNotExist extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected $question_index;

    /**
     * @param int $question_index
     */
    public function __construct($question_index)
    {
        $this->question_index = $question_index;
        
        parent::__construct(sprintf(
            'The Question with index "Q%s" does not exist',
            $this->getQuestionIndex()
        ));
    }

    /**
     * @return int
     */
    public function getQuestionIndex()
    {
        return $this->question_index;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        return sprintf(
            $lng->txt("ass_lac_question_not_exist"),
            $this->getQuestionIndex()
        );
    }
}
