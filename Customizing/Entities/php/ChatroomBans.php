<?php



/**
 * ChatroomBans
 */
class ChatroomBans
{
    /**
     * @var int
     */
    private $roomId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $timestamp = '0';

    /**
     * @var string|null
     */
    private $remark;

    /**
     * @var int|null
     */
    private $actorId;


    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return ChatroomBans
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return ChatroomBans
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
     * Set timestamp.
     *
     * @param int $timestamp
     *
     * @return ChatroomBans
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set remark.
     *
     * @param string|null $remark
     *
     * @return ChatroomBans
     */
    public function setRemark($remark = null)
    {
        $this->remark = $remark;

        return $this;
    }

    /**
     * Get remark.
     *
     * @return string|null
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Set actorId.
     *
     * @param int|null $actorId
     *
     * @return ChatroomBans
     */
    public function setActorId($actorId = null)
    {
        $this->actorId = $actorId;

        return $this;
    }

    /**
     * Get actorId.
     *
     * @return int|null
     */
    public function getActorId()
    {
        return $this->actorId;
    }
}
