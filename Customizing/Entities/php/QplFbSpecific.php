<?php



/**
 * QplFbSpecific
 */
class QplFbSpecific
{
    /**
     * @var int
     */
    private $feedbackId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $answer = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $feedback;

    /**
     * @var int
     */
    private $question = '0';


    /**
     * Get feedbackId.
     *
     * @return int
     */
    public function getFeedbackId()
    {
        return $this->feedbackId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplFbSpecific
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set answer.
     *
     * @param int $answer
     *
     * @return QplFbSpecific
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer.
     *
     * @return int
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplFbSpecific
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set feedback.
     *
     * @param string|null $feedback
     *
     * @return QplFbSpecific
     */
    public function setFeedback($feedback = null)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get feedback.
     *
     * @return string|null
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Set question.
     *
     * @param int $question
     *
     * @return QplFbSpecific
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question.
     *
     * @return int
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
