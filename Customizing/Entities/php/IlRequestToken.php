<?php



/**
 * IlRequestToken
 */
class IlRequestToken
{
    /**
     * @var string
     */
    private $token = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime|null
     */
    private $stamp;

    /**
     * @var string|null
     */
    private $sessionId;


    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlRequestToken
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
     * Set stamp.
     *
     * @param \DateTime|null $stamp
     *
     * @return IlRequestToken
     */
    public function setStamp($stamp = null)
    {
        $this->stamp = $stamp;

        return $this;
    }

    /**
     * Get stamp.
     *
     * @return \DateTime|null
     */
    public function getStamp()
    {
        return $this->stamp;
    }

    /**
     * Set sessionId.
     *
     * @param string|null $sessionId
     *
     * @return IlRequestToken
     */
    public function setSessionId($sessionId = null)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId.
     *
     * @return string|null
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }
}
