<?php



/**
 * LmReadEvent
 */
class LmReadEvent
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $readCount = '0';

    /**
     * @var int
     */
    private $spentSeconds = '0';

    /**
     * @var int
     */
    private $lastAccess = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return LmReadEvent
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return LmReadEvent
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

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
     * Set readCount.
     *
     * @param int $readCount
     *
     * @return LmReadEvent
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Get readCount.
     *
     * @return int
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Set spentSeconds.
     *
     * @param int $spentSeconds
     *
     * @return LmReadEvent
     */
    public function setSpentSeconds($spentSeconds)
    {
        $this->spentSeconds = $spentSeconds;

        return $this;
    }

    /**
     * Get spentSeconds.
     *
     * @return int
     */
    public function getSpentSeconds()
    {
        return $this->spentSeconds;
    }

    /**
     * Set lastAccess.
     *
     * @param int $lastAccess
     *
     * @return LmReadEvent
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess.
     *
     * @return int
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }
}
