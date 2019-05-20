<?php



/**
 * ChatroomProomaccess
 */
class ChatroomProomaccess
{
    /**
     * @var int
     */
    private $proomId = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Set proomId.
     *
     * @param int $proomId
     *
     * @return ChatroomProomaccess
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
     * @return ChatroomProomaccess
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
