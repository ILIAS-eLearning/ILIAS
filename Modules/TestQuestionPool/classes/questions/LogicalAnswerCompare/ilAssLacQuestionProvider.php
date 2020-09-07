<?php

require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

/**
 * Class ilParserQuestionProvider
 *
 * Date: 04.12.13
 * Time: 15:04
 * @author Thomas Joußen <tjoussen@databay.de>
 */
class ilAssLacQuestionProvider
{
    /*
     * @var iQuestionCondition
     */
    protected $question;

    /**
     * @var integer
     */
    protected $questionId;

    /**
     * @param integer $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * @param iQuestionCondition $question
     */
    public function setQuestion(iQuestionCondition $question)
    {
        $this->question = $question;
    }

    /**
     * @return iQuestionCondition
     */
    public function getQuestion()
    {
        if ($this->question === null && $this->questionId) {
            require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
            $this->question = assQuestion::_instantiateQuestion($this->questionId);
        }
        
        return $this->question;
    }
}
