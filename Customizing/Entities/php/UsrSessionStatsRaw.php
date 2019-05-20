<?php



/**
 * UsrSessionStatsRaw
 */
class UsrSessionStatsRaw
{
    /**
     * @var string
     */
    private $sessionId = '';

    /**
     * @var int
     */
    private $type = '0';

    /**
     * @var int
     */
    private $startTime = '0';

    /**
     * @var int|null
     */
    private $endTime;

    /**
     * @var int|null
     */
    private $endContext;

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Get sessionId.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return UsrSessionStatsRaw
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set startTime.
     *
     * @param int $startTime
     *
     * @return UsrSessionStatsRaw
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime.
     *
     * @param int|null $endTime
     *
     * @return UsrSessionStatsRaw
     */
    public function setEndTime($endTime = null)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return int|null
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set endContext.
     *
     * @param int|null $endContext
     *
     * @return UsrSessionStatsRaw
     */
    public function setEndContext($endContext = null)
    {
        $this->endContext = $endContext;

        return $this;
    }

    /**
     * Get endContext.
     *
     * @return int|null
     */
    public function getEndContext()
    {
        return $this->endContext;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrSessionStatsRaw
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
