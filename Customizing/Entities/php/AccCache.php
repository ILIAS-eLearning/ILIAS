<?php



/**
 * AccCache
 */
class AccCache
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $time = '0';

    /**
     * @var string|null
     */
    private $result;


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
     * Set time.
     *
     * @param int $time
     *
     * @return AccCache
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set result.
     *
     * @param string|null $result
     *
     * @return AccCache
     */
    public function setResult($result = null)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result.
     *
     * @return string|null
     */
    public function getResult()
    {
        return $this->result;
    }
}
