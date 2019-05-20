<?php



/**
 * ExcMembers
 */
class ExcMembers
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string|null
     */
    private $notice;

    /**
     * @var bool
     */
    private $returned = '0';

    /**
     * @var bool|null
     */
    private $solved;

    /**
     * @var \DateTime|null
     */
    private $statusTime;

    /**
     * @var bool|null
     */
    private $sent;

    /**
     * @var \DateTime|null
     */
    private $sentTime;

    /**
     * @var \DateTime|null
     */
    private $feedbackTime;

    /**
     * @var bool
     */
    private $feedback = '0';

    /**
     * @var string|null
     */
    private $status = 'notgraded';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ExcMembers
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return ExcMembers
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set notice.
     *
     * @param string|null $notice
     *
     * @return ExcMembers
     */
    public function setNotice($notice = null)
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * Get notice.
     *
     * @return string|null
     */
    public function getNotice()
    {
        return $this->notice;
    }

    /**
     * Set returned.
     *
     * @param bool $returned
     *
     * @return ExcMembers
     */
    public function setReturned($returned)
    {
        $this->returned = $returned;

        return $this;
    }

    /**
     * Get returned.
     *
     * @return bool
     */
    public function getReturned()
    {
        return $this->returned;
    }

    /**
     * Set solved.
     *
     * @param bool|null $solved
     *
     * @return ExcMembers
     */
    public function setSolved($solved = null)
    {
        $this->solved = $solved;

        return $this;
    }

    /**
     * Get solved.
     *
     * @return bool|null
     */
    public function getSolved()
    {
        return $this->solved;
    }

    /**
     * Set statusTime.
     *
     * @param \DateTime|null $statusTime
     *
     * @return ExcMembers
     */
    public function setStatusTime($statusTime = null)
    {
        $this->statusTime = $statusTime;

        return $this;
    }

    /**
     * Get statusTime.
     *
     * @return \DateTime|null
     */
    public function getStatusTime()
    {
        return $this->statusTime;
    }

    /**
     * Set sent.
     *
     * @param bool|null $sent
     *
     * @return ExcMembers
     */
    public function setSent($sent = null)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent.
     *
     * @return bool|null
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set sentTime.
     *
     * @param \DateTime|null $sentTime
     *
     * @return ExcMembers
     */
    public function setSentTime($sentTime = null)
    {
        $this->sentTime = $sentTime;

        return $this;
    }

    /**
     * Get sentTime.
     *
     * @return \DateTime|null
     */
    public function getSentTime()
    {
        return $this->sentTime;
    }

    /**
     * Set feedbackTime.
     *
     * @param \DateTime|null $feedbackTime
     *
     * @return ExcMembers
     */
    public function setFeedbackTime($feedbackTime = null)
    {
        $this->feedbackTime = $feedbackTime;

        return $this;
    }

    /**
     * Get feedbackTime.
     *
     * @return \DateTime|null
     */
    public function getFeedbackTime()
    {
        return $this->feedbackTime;
    }

    /**
     * Set feedback.
     *
     * @param bool $feedback
     *
     * @return ExcMembers
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get feedback.
     *
     * @return bool
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Set status.
     *
     * @param string|null $status
     *
     * @return ExcMembers
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }
}
