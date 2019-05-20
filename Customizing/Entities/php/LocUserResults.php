<?php



/**
 * LocUserResults
 */
class LocUserResults
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $courseId = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var bool
     */
    private $type = '0';

    /**
     * @var bool|null
     */
    private $status = '0';

    /**
     * @var bool|null
     */
    private $resultPerc = '0';

    /**
     * @var bool|null
     */
    private $limitPerc = '0';

    /**
     * @var bool|null
     */
    private $tries = '0';

    /**
     * @var bool|null
     */
    private $isFinal = '0';

    /**
     * @var int|null
     */
    private $tstamp = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return LocUserResults
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set courseId.
     *
     * @param int $courseId
     *
     * @return LocUserResults
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * Get courseId.
     *
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return LocUserResults
     */
    public function setObjectiveId($objectiveId)
    {
        $this->objectiveId = $objectiveId;

        return $this;
    }

    /**
     * Get objectiveId.
     *
     * @return int
     */
    public function getObjectiveId()
    {
        return $this->objectiveId;
    }

    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return LocUserResults
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status.
     *
     * @param bool|null $status
     *
     * @return LocUserResults
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set resultPerc.
     *
     * @param bool|null $resultPerc
     *
     * @return LocUserResults
     */
    public function setResultPerc($resultPerc = null)
    {
        $this->resultPerc = $resultPerc;

        return $this;
    }

    /**
     * Get resultPerc.
     *
     * @return bool|null
     */
    public function getResultPerc()
    {
        return $this->resultPerc;
    }

    /**
     * Set limitPerc.
     *
     * @param bool|null $limitPerc
     *
     * @return LocUserResults
     */
    public function setLimitPerc($limitPerc = null)
    {
        $this->limitPerc = $limitPerc;

        return $this;
    }

    /**
     * Get limitPerc.
     *
     * @return bool|null
     */
    public function getLimitPerc()
    {
        return $this->limitPerc;
    }

    /**
     * Set tries.
     *
     * @param bool|null $tries
     *
     * @return LocUserResults
     */
    public function setTries($tries = null)
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * Get tries.
     *
     * @return bool|null
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Set isFinal.
     *
     * @param bool|null $isFinal
     *
     * @return LocUserResults
     */
    public function setIsFinal($isFinal = null)
    {
        $this->isFinal = $isFinal;

        return $this;
    }

    /**
     * Get isFinal.
     *
     * @return bool|null
     */
    public function getIsFinal()
    {
        return $this->isFinal;
    }

    /**
     * Set tstamp.
     *
     * @param int|null $tstamp
     *
     * @return LocUserResults
     */
    public function setTstamp($tstamp = null)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int|null
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
