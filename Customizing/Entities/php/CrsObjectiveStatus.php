<?php



/**
 * CrsObjectiveStatus
 */
class CrsObjectiveStatus
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
     * @var bool
     */
    private $status = '0';


    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return CrsObjectiveStatus
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
     * @return CrsObjectiveStatus
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
     * Set status.
     *
     * @param bool $status
     *
     * @return CrsObjectiveStatus
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
}
