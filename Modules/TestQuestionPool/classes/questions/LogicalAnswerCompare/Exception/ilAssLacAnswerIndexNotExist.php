<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class AnswerIndexNotExist
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacAnswerIndexNotExist extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected $question_index;

    /**
     * @var int
     */
    protected $answer_index;

    /**
     * @param int $question_index
     * @param int $answer_index
     */
    public function __construct($question_index, $answer_index)
    {
        $this->question_index = $question_index;
        $this->answer_index = $answer_index;

        if ($this->getQuestionIndex() === null) {
            $msg = sprintf(
                'The Current Question does not have an answer with the index "%s"',
                $this->getAnswerIndex()
            );
        } else {
            $msg = sprintf(
                'The Question with index "Q%s" does not have an answer with the index "%s" ',
                $this->getQuestionIndex(),
                $this->getAnswerIndex()
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
     * @return int
     */
    public function getAnswerIndex()
    {
        return $this->answer_index;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        if ($this->getQuestionIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_answer_index_not_exist_cur_qst"),
                $this->getAnswerIndex()
            );
        }
        
        return sprintf(
            $lng->txt("ass_lac_answer_index_not_exist"),
            $this->getQuestionIndex(),
            $this->getAnswerIndex()
        );
    }
}
