<?php



/**
 * SklSelfEval
 */
class SklSelfEval
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $topSkillId = '0';

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $lastUpdate = '1970-01-01 00:00:00';


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
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklSelfEval
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
     * @return SklSelfEval
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return SklSelfEval
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime $lastUpdate
     *
     * @return SklSelfEval
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
