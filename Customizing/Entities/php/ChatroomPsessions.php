<?php



/**
 * ChatroomPsessions
 */
class ChatroomPsessions
{
    /**
     * @var int
     */
    private $psessId = '0';

    /**
     * @var int
     */
    private $proomId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $connected = '0';

    /**
     * @var int
     */
    private $disconnected = '0';


    /**
     * Get psessId.
     *
     * @return int
     */
    public function getPsessId()
    {
        return $this->psessId;
    }

    /**
     * Set proomId.
     *
     * @param int $proomId
     *
     * @return ChatroomPsessions
     */
    public function setProomId($proomId)
    {
        $this->proomId = $proomId;

        return $this;
    }

    /**
     * Get proomId.
     *
     * @return int
     */
    public function getProomId()
    {
        return $this->proomId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return ChatroomPsessions
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
     * Set connected.
     *
     * @param int $connected
     *
     * @return ChatroomPsessions
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
     * @return ChatroomPsessions
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
