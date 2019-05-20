<?php



/**
 * SklPersonalSkill
 */
class SklPersonalSkill
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $skillNodeId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return SklPersonalSkill
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
     * Set skillNodeId.
     *
     * @param int $skillNodeId
     *
     * @return SklPersonalSkill
     */
    public function setSkillNodeId($skillNodeId)
    {
        $this->skillNodeId = $skillNodeId;

        return $this;
    }

    /**
     * Get skillNodeId.
     *
     * @return int
     */
    public function getSkillNodeId()
    {
        return $this->skillNodeId;
    }
}
