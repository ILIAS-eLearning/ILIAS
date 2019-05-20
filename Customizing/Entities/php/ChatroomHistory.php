<?php



/**
 * ChatroomHistory
 */
class ChatroomHistory
{
    /**
     * @var int
     */
    private $histId = '0';

    /**
     * @var int
     */
    private $roomId = '0';

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var int
     */
    private $timestamp = '0';

    /**
     * @var int
     */
    private $subRoom = '0';


    /**
     * Get histId.
     *
     * @return int
     */
    public function getHistId()
    {
        return $this->histId;
    }

    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return ChatroomHistory
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
     * Set message.
     *
     * @param string|null $message
     *
     * @return ChatroomHistory
     */
    public function setMessage($message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set timestamp.
     *
     * @param int $timestamp
     *
     * @return ChatroomHistory
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
     * Set subRoom.
     *
     * @param int $subRoom
     *
     * @return ChatroomHistory
     */
    public function setSubRoom($subRoom)
    {
        $this->subRoom = $subRoom;

        return $this;
    }

    /**
     * Get subRoom.
     *
     * @return int
     */
    public function getSubRoom()
    {
        return $this->subRoom;
    }
}
