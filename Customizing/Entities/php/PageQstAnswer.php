<?php



/**
 * PageQstAnswer
 */
class PageQstAnswer
{
    /**
     * @var int
     */
    private $qstId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var bool
     */
    private $try = '0';

    /**
     * @var bool
     */
    private $passed = '0';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var bool
     */
    private $unlocked = '0';


    /**
     * Set qstId.
     *
     * @param int $qstId
     *
     * @return PageQstAnswer
     */
    public function setQstId($qstId)
    {
        $this->qstId = $qstId;

        return $this;
    }

    /**
     * Get qstId.
     *
     * @return int
     */
    public function getQstId()
    {
        return $this->qstId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return PageQstAnswer
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
     * Set try.
     *
     * @param bool $try
     *
     * @return PageQstAnswer
     */
    public function setTry($try)
    {
        $this->try = $try;

        return $this;
    }

    /**
     * Get try.
     *
     * @return bool
     */
    public function getTry()
    {
        return $this->try;
    }

    /**
     * Set passed.
     *
     * @param bool $passed
     *
     * @return PageQstAnswer
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;

        return $this;
    }

    /**
     * Get passed.
     *
     * @return bool
     */
    public function getPassed()
    {
        return $this->passed;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return PageQstAnswer
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set unlocked.
     *
     * @param bool $unlocked
     *
     * @return PageQstAnswer
     */
    public function setUnlocked($unlocked)
    {
        $this->unlocked = $unlocked;

        return $this;
    }

    /**
     * Get unlocked.
     *
     * @return bool
     */
    public function getUnlocked()
    {
        return $this->unlocked;
    }
}
