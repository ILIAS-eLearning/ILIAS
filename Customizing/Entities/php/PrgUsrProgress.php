<?php



/**
 * PrgUsrProgress
 */
class PrgUsrProgress
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $assignmentId = '0';

    /**
     * @var int
     */
    private $prgId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $points = '0';

    /**
     * @var int
     */
    private $pointsCur = '0';

    /**
     * @var bool
     */
    private $status = '0';

    /**
     * @var int|null
     */
    private $completionBy;

    /**
     * @var \DateTime
     */
    private $lastChange = '1970-01-01 00:00:00';

    /**
     * @var int|null
     */
    private $lastChangeBy;

    /**
     * @var string|null
     */
    private $deadline;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set assignmentId.
     *
     * @param int $assignmentId
     *
     * @return PrgUsrProgress
     */
    public function setAssignmentId($assignmentId)
    {
        $this->assignmentId = $assignmentId;

        return $this;
    }

    /**
     * Get assignmentId.
     *
     * @return int
     */
    public function getAssignmentId()
    {
        return $this->assignmentId;
    }

    /**
     * Set prgId.
     *
     * @param int $prgId
     *
     * @return PrgUsrProgress
     */
    public function setPrgId($prgId)
    {
        $this->prgId = $prgId;

        return $this;
    }

    /**
     * Get prgId.
     *
     * @return int
     */
    public function getPrgId()
    {
        return $this->prgId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return PrgUsrProgress
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
     * Set points.
     *
     * @param int $points
     *
     * @return PrgUsrProgress
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set pointsCur.
     *
     * @param int $pointsCur
     *
     * @return PrgUsrProgress
     */
    public function setPointsCur($pointsCur)
    {
        $this->pointsCur = $pointsCur;

        return $this;
    }

    /**
     * Get pointsCur.
     *
     * @return int
     */
    public function getPointsCur()
    {
        return $this->pointsCur;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return PrgUsrProgress
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
     * Set completionBy.
     *
     * @param int|null $completionBy
     *
     * @return PrgUsrProgress
     */
    public function setCompletionBy($completionBy = null)
    {
        $this->completionBy = $completionBy;

        return $this;
    }

    /**
     * Get completionBy.
     *
     * @return int|null
     */
    public function getCompletionBy()
    {
        return $this->completionBy;
    }

    /**
     * Set lastChange.
     *
     * @param \DateTime $lastChange
     *
     * @return PrgUsrProgress
     */
    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * Get lastChange.
     *
     * @return \DateTime
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Set lastChangeBy.
     *
     * @param int|null $lastChangeBy
     *
     * @return PrgUsrProgress
     */
    public function setLastChangeBy($lastChangeBy = null)
    {
        $this->lastChangeBy = $lastChangeBy;

        return $this;
    }

    /**
     * Get lastChangeBy.
     *
     * @return int|null
     */
    public function getLastChangeBy()
    {
        return $this->lastChangeBy;
    }

    /**
     * Set deadline.
     *
     * @param string|null $deadline
     *
     * @return PrgUsrProgress
     */
    public function setDeadline($deadline = null)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get deadline.
     *
     * @return string|null
     */
    public function getDeadline()
    {
        return $this->deadline;
    }
}
