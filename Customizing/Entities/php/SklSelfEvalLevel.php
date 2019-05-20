<?php



/**
 * SklSelfEvalLevel
 */
class SklSelfEvalLevel
{
    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var int
     */
    private $trefId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $topSkillId = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;


    /**
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return SklSelfEvalLevel
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
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SklSelfEvalLevel
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklSelfEvalLevel
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
     * Set topSkillId.
     *
     * @param int $topSkillId
     *
     * @return SklSelfEvalLevel
     */
    public function setTopSkillId($topSkillId)
    {
        $this->topSkillId = $topSkillId;

        return $this;
    }

    /**
     * Get topSkillId.
     *
     * @return int
     */
    public function getTopSkillId()
    {
        return $this->topSkillId;
    }

    /**
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SklSelfEvalLevel
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
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return SklSelfEvalLevel
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
