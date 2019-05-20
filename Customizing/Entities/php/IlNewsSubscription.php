<?php



/**
 * IlNewsSubscription
 */
class IlNewsSubscription
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $refId = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlNewsSubscription
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
     * Set refId.
     *
     * @param int $refId
     *
     * @return IlNewsSubscription
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }
}
