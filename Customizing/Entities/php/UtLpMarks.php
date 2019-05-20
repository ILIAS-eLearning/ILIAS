<?php



/**
 * UtLpMarks
 */
class UtLpMarks
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
     * @var int
     */
    private $completed = '0';

    /**
     * @var string|null
     */
    private $mark;

    /**
     * @var string|null
     */
    private $uComment;

    /**
     * @var bool
     */
    private $status = '0';

    /**
     * @var \DateTime|null
     */
    private $statusChanged;

    /**
     * @var bool
     */
    private $statusDirty = '0';

    /**
     * @var bool
     */
    private $percentage = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return UtLpMarks
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
     * @return UtLpMarks
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
     * Set completed.
     *
     * @param int $completed
     *
     * @return UtLpMarks
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed.
     *
     * @return int
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set mark.
     *
     * @param string|null $mark
     *
     * @return UtLpMarks
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
     * @return UtLpMarks
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

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return UtLpMarks
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set statusChanged.
     *
     * @param \DateTime|null $statusChanged
     *
     * @return UtLpMarks
     */
    public function setStatusChanged($statusChanged = null)
    {
        $this->statusChanged = $statusChanged;

        return $this;
    }

    /**
     * Get statusChanged.
     *
     * @return \DateTime|null
     */
    public function getStatusChanged()
    {
        return $this->statusChanged;
    }

    /**
     * Set statusDirty.
     *
     * @param bool $statusDirty
     *
     * @return UtLpMarks
     */
    public function setStatusDirty($statusDirty)
    {
        $this->statusDirty = $statusDirty;

        return $this;
    }

    /**
     * Get statusDirty.
     *
     * @return bool
     */
    public function getStatusDirty()
    {
        return $this->statusDirty;
    }

    /**
     * Set percentage.
     *
     * @param bool $percentage
     *
     * @return UtLpMarks
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage.
     *
     * @return bool
     */
    public function getPercentage()
    {
        return $this->percentage;
    }
}
