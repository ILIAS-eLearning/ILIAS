<?php



/**
 * TstManualFb
 */
class TstManualFb
{
    /**
     * @var int
     */
    private $manualFeedbackId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $feedback;


    /**
     * Get manualFeedbackId.
     *
     * @return int
     */
    public function getManualFeedbackId()
    {
        return $this->manualFeedbackId;
    }

    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstManualFb
     */
    public function setActiveFi($activeFi)
    {
        $this->activeFi = $activeFi;

        return $this;
    }

    /**
     * Get activeFi.
     *
     * @return int
     */
    public function getActiveFi()
    {
        return $this->activeFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return TstManualFb
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
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstManualFb
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get pass.
     *
     * @return int
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstManualFb
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
     * @return TstManualFb
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
