<?php



/**
 * OscActivity
 */
class OscActivity
{
    /**
     * @var string
     */
    private $conversationId = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $timestamp = '0';

    /**
     * @var bool
     */
    private $isClosed = '0';


    /**
     * Set conversationId.
     *
     * @param string $conversationId
     *
     * @return OscActivity
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
     * @return OscActivity
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
     * @return OscActivity
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
     * Set isClosed.
     *
     * @param bool $isClosed
     *
     * @return OscActivity
     */
    public function setIsClosed($isClosed)
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    /**
     * Get isClosed.
     *
     * @return bool
     */
    public function getIsClosed()
    {
        return $this->isClosed;
    }
}
