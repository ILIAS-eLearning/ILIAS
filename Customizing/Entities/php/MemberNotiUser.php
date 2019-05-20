<?php



/**
 * MemberNotiUser
 */
class MemberNotiUser
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var bool
     */
    private $status = '0';


    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return MemberNotiUser
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

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return MemberNotiUser
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
     * @return MemberNotiUser
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
