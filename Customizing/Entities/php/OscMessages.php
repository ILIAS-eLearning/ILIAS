<?php



/**
 * OscMessages
 */
class OscMessages
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $conversationId = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $message;

    /**
     * @var int
     */
    private $timestamp = '0';


    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set conversationId.
     *
     * @param string $conversationId
     *
     * @return OscMessages
     */
    public function setConversationId($conversationId)
    {
        $this->conversationId = $conversationId;

        return $this;
    }

    /**
     * Get conversationId.
     *
     * @return string
     */
    public function getConversationId()
    {
        return $this->conversationId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return OscMessages
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
     * Set message.
     *
     * @param string|null $message
     *
     * @return OscMessages
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
     * @return OscMessages
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
}
