<?php



/**
 * ChatroomUsers
 */
class ChatroomUsers
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
     * @var string
     */
    private $userdata = '';

    /**
     * @var int
     */
    private $connected = '0';


    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return ChatroomUsers
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
     * @return ChatroomUsers
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
     * Set userdata.
     *
     * @param string $userdata
     *
     * @return ChatroomUsers
     */
    public function setUserdata($userdata)
    {
        $this->userdata = $userdata;

        return $this;
    }

    /**
     * Get userdata.
     *
     * @return string
     */
    public function getUserdata()
    {
        return $this->userdata;
    }

    /**
     * Set connected.
     *
     * @param int $connected
     *
     * @return ChatroomUsers
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;

        return $this;
    }

    /**
     * Get connected.
     *
     * @return int
     */
    public function getConnected()
    {
        return $this->connected;
    }
}
