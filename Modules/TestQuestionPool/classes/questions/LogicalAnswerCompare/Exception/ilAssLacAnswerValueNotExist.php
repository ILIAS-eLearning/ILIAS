<?php

require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacException.php';
require_once 'Modules/TestQuestionPool/classes/questions/LogicalAnswerCompare/Exception/ilAssLacFormAlertProvider.php';

/**
 * Class ilAssLacQuestionNotReachable
 * @package
 *
 * Date: 25.03.13
 * Time: 15:15
 * @author Thomas Joußen <tjoussen@databay.de>
 * @author Björn Heyser <bheyser@databay.de>
 */
class ilAssLacAnswerValueNotExist extends ilAssLacException implements ilAssLacFormAlertProvider
{
    /**
     * @var int
     */
    protected $question_index;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int
     */
    protected $answer_index;

    /**
     * @param int $question_index
     * @param string $value
     * @param int $answer_index
     */
    public function __construct($question_index, $value, $answer_index = null)
    {
        $this->question_index = $question_index;
        $this->answer_index = $answer_index;
        $this->value = $value;
        
        if ($this->getQuestionIndex() === null && $this->getAnswerIndex() === null) {
            $msg = sprintf(
                'The value "%s" does not exist for the current question',
                $value
            );
        } elseif ($this->getQuestionIndex() === null) {
            $msg = sprintf(
                'The value "%s" does not exist for the answer with index "%s" of the current question',
                $value,
                $this->getAnswerIndex()
            );
        } elseif ($this->getAnswerIndex() === null) {
            $msg = sprintf(
                'The value "%s" does not exist for the question Q%s',
                $value,
                $this->getQuestionIndex()
            );
        } else {
            $msg = sprintf(
                'The value "%s" does not exist for the question Q%s[%s]',
                $value,
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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFormAlert(ilLanguage $lng)
    {
        if ($this->getQuestionIndex() === null && $this->getAnswerIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_answer_value_not_exists_cur_qst_one_answer"),
                $this->getValue()
            );
        }

        if ($this->getQuestionIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_answer_value_not_exists_cur_qst"),
                $this->getValue(),
                $this->getAnswerIndex()
            );
        }

        if ($this->getAnswerIndex() === null) {
            return sprintf(
                $lng->txt("ass_lac_answer_value_not_exists_one_answer"),
                $this->getValue(),
                $this->getQuestionIndex()
            );
        }

        return sprintf(
            $lng->txt("ass_lac_answer_value_not_exists"),
            $this->getValue(),
            $this->getQuestionIndex(),
            $this->getAnswerIndex()
        );
    }
}
