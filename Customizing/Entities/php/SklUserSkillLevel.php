<?php



/**
 * SklUserSkillLevel
 */
class SklUserSkillLevel
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime
     */
    private $statusDate = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var bool
     */
    private $status = '0';

    /**
     * @var int
     */
    private $triggerObjId = '0';

    /**
     * @var int
     */
    private $trefId = '0';

    /**
     * @var bool
     */
    private $selfEval = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var bool
     */
    private $valid = '0';

    /**
     * @var int
     */
    private $triggerRefId = '0';

    /**
     * @var string|null
     */
    private $triggerTitle;

    /**
     * @var string|null
     */
    private $triggerObjType = 'crs';

    /**
     * @var string|null
     */
    private $uniqueIdentifier;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklUserSkillLevel
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
     * Set statusDate.
     *
     * @param \DateTime $statusDate
     *
     * @return SklUserSkillLevel
     */
    public function setStatusDate($statusDate)
    {
        $this->statusDate = $statusDate;

        return $this;
    }

    /**
     * Get statusDate.
     *
     * @return \DateTime
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return SklUserSkillLevel
     */
    public function setSkillId($skillId)
    {
        $this->skillId = $skillId;

        return $this;
    }

    /**
     * Get skillId.
     *
     * @return int
     */
    public function getSkillId()
    {
        return $this->skillId;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return SklUserSkillLevel
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
     * Set triggerObjId.
     *
     * @param int $triggerObjId
     *
     * @return SklUserSkillLevel
     */
    public function setTriggerObjId($triggerObjId)
    {
        $this->triggerObjId = $triggerObjId;

        return $this;
    }

    /**
     * Get triggerObjId.
     *
     * @return int
     */
    public function getTriggerObjId()
    {
        return $this->triggerObjId;
    }

    /**
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SklUserSkillLevel
     */
    public function setTrefId($trefId)
    {
        $this->trefId = $trefId;

        return $this;
    }

    /**
     * Get trefId.
     *
     * @return int
     */
    public function getTrefId()
    {
        return $this->trefId;
    }

    /**
     * Set selfEval.
     *
     * @param bool $selfEval
     *
     * @return SklUserSkillLevel
     */
    public function setSelfEval($selfEval)
    {
        $this->selfEval = $selfEval;

        return $this;
    }

    /**
     * Get selfEval.
     *
     * @return bool
     */
    public function getSelfEval()
    {
        return $this->selfEval;
    }

    /**
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SklUserSkillLevel
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;

        return $this;
    }

    /**
     * Get levelId.
     *
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set valid.
     *
     * @param bool $valid
     *
     * @return SklUserSkillLevel
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid.
     *
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set triggerRefId.
     *
     * @param int $triggerRefId
     *
     * @return SklUserSkillLevel
     */
    public function setTriggerRefId($triggerRefId)
    {
        $this->triggerRefId = $triggerRefId;

        return $this;
    }

    /**
     * Get triggerRefId.
     *
     * @return int
     */
    public function getTriggerRefId()
    {
        return $this->triggerRefId;
    }

    /**
     * Set triggerTitle.
     *
     * @param string|null $triggerTitle
     *
     * @return SklUserSkillLevel
     */
    public function setTriggerTitle($triggerTitle = null)
    {
        $this->triggerTitle = $triggerTitle;

        return $this;
    }

    /**
     * Get triggerTitle.
     *
     * @return string|null
     */
    public function getTriggerTitle()
    {
        return $this->triggerTitle;
    }

    /**
     * Set triggerObjType.
     *
     * @param string|null $triggerObjType
     *
     * @return SklUserSkillLevel
     */
    public function setTriggerObjType($triggerObjType = null)
    {
        $this->triggerObjType = $triggerObjType;

        return $this;
    }

    /**
     * Get triggerObjType.
     *
     * @return string|null
     */
    public function getTriggerObjType()
    {
        return $this->triggerObjType;
    }

    /**
     * Set uniqueIdentifier.
     *
     * @param string|null $uniqueIdentifier
     *
     * @return SklUserSkillLevel
     */
    public function setUniqueIdentifier($uniqueIdentifier = null)
    {
        $this->uniqueIdentifier = $uniqueIdentifier;

        return $this;
    }

    /**
     * Get uniqueIdentifier.
     *
     * @return string|null
     */
    public function getUniqueIdentifier()
    {
        return $this->uniqueIdentifier;
    }
}
