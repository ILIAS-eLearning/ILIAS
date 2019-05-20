<?php



/**
 * CrsObjectiveStatusP
 */
class CrsObjectiveStatusP
{
    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return CrsObjectiveStatusP
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return CrsObjectiveStatusP
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
}
