<?php



/**
 * ChatroomSessions
 */
class ChatroomSessions
{
    /**
     * @var int
     */
    private $sessId = '0';

    /**
     * @var int
     */
    private $roomId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $userdata;

    /**
     * @var int
     */
    private $connected = '0';

    /**
     * @var int
     */
    private $disconnected = '0';


    /**
     * Get sessId.
     *
     * @return int
     */
    public function getSessId()
    {
        return $this->sessId;
    }

    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return ChatroomSessions
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
     * @return ChatroomSessions
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
     * @param string|null $userdata
     *
     * @return ChatroomSessions
     */
    public function setUserdata($userdata = null)
    {
        $this->userdata = $userdata;

        return $this;
    }

    /**
     * Get userdata.
     *
     * @return string|null
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
     * @return ChatroomSessions
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

    /**
     * Set disconnected.
     *
     * @param int $disconnected
     *
     * @return ChatroomSessions
     */
    public function setDisconnected($disconnected)
    {
        $this->disconnected = $disconnected;

        return $this;
    }

    /**
     * Get disconnected.
     *
     * @return int
     */
    public function getDisconnected()
    {
        return $this->disconnected;
    }
}
