<?php



/**
 * QplFbGeneric
 */
class QplFbGeneric
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
     * @var string|null
     */
    private $correctness = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $feedback;


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
     * @return QplFbGeneric
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
     * Set correctness.
     *
     * @param string|null $correctness
     *
     * @return QplFbGeneric
     */
    public function setCorrectness($correctness = null)
    {
        $this->correctness = $correctness;

        return $this;
    }

    /**
     * Get correctness.
     *
     * @return string|null
     */
    public function getCorrectness()
    {
        return $this->correctness;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplFbGeneric
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
     * @return QplFbGeneric
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
}
