<?php



/**
 * ContMemberSkills
 */
class ContMemberSkills
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $trefId = '0';

    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var bool
     */
    private $published = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ContMemberSkills
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return ContMemberSkills
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
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return ContMemberSkills
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
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return ContMemberSkills
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
     * @return ContMemberSkills
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
     * Set published.
     *
     * @param bool $published
     *
     * @return ContMemberSkills
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function getPublished()
    {
        return $this->published;
    }
}
