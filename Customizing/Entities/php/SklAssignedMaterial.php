<?php



/**
 * SklAssignedMaterial
 */
class SklAssignedMaterial
{
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
    private $skillId = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var int
     */
    private $wspId = '0';

    /**
     * @var int
     */
    private $trefId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklAssignedMaterial
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
     * @return SklAssignedMaterial
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
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return SklAssignedMaterial
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
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SklAssignedMaterial
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
     * Set wspId.
     *
     * @param int $wspId
     *
     * @return SklAssignedMaterial
     */
    public function setWspId($wspId)
    {
        $this->wspId = $wspId;

        return $this;
    }

    /**
     * Get wspId.
     *
     * @return int
     */
    public function getWspId()
    {
        return $this->wspId;
    }

    /**
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SklAssignedMaterial
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
}
