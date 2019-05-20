<?php



/**
 * SklUserHasLevel
 */
class SklUserHasLevel
{
    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var int
     */
    private $userId = '0';

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
     * @var \DateTime
     */
    private $statusDate = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $skillId = '0';

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
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SklUserHasLevel
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklUserHasLevel
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
     * Set triggerObjId.
     *
     * @param int $triggerObjId
     *
     * @return SklUserHasLevel
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
     * @return SklUserHasLevel
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
     * @return SklUserHasLevel
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
     * Set statusDate.
     *
     * @param \DateTime $statusDate
     *
     * @return SklUserHasLevel
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
     * @return SklUserHasLevel
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
     * Set triggerRefId.
     *
     * @param int $triggerRefId
     *
     * @return SklUserHasLevel
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
     * @return SklUserHasLevel
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
     * @return SklUserHasLevel
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
}
