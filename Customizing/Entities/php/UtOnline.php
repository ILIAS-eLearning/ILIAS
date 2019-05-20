<?php



/**
 * UtOnline
 */
class UtOnline
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $onlineTime = '0';

    /**
     * @var int
     */
    private $accessTime = '0';


    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set onlineTime.
     *
     * @param int $onlineTime
     *
     * @return UtOnline
     */
    public function setOnlineTime($onlineTime)
    {
        $this->onlineTime = $onlineTime;

        return $this;
    }

    /**
     * Get onlineTime.
     *
     * @return int
     */
    public function getOnlineTime()
    {
        return $this->onlineTime;
    }

    /**
     * Set accessTime.
     *
     * @param int $accessTime
     *
     * @return UtOnline
     */
    public function setAccessTime($accessTime)
    {
        $this->accessTime = $accessTime;

        return $this;
    }

    /**
     * Get accessTime.
     *
     * @return int
     */
    public function getAccessTime()
    {
        return $this->accessTime;
    }
}
