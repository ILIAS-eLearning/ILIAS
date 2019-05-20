<?php



/**
 * ExcMemAssStatus
 */
class ExcMemAssStatus
{
    /**
     * @var int
     */
    private $assId = '0';

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
     * @var string|null
     */
    private $mark;

    /**
     * @var string|null
     */
    private $uComment;


    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return ExcMemAssStatus
     */
    public function setAssId($assId)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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
     * @return ExcMemAssStatus
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

    /**
     * Set mark.
     *
     * @param string|null $mark
     *
     * @return ExcMemAssStatus
     */
    public function setMark($mark = null)
    {
        $this->mark = $mark;

        return $this;
    }

    /**
     * Get mark.
     *
     * @return string|null
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Set uComment.
     *
     * @param string|null $uComment
     *
     * @return ExcMemAssStatus
     */
    public function setUComment($uComment = null)
    {
        $this->uComment = $uComment;

        return $this;
    }

    /**
     * Get uComment.
     *
     * @return string|null
     */
    public function getUComment()
    {
        return $this->uComment;
    }
}
