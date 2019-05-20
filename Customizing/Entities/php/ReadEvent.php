<?php



/**
 * ReadEvent
 */
class ReadEvent
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
     * @var int|null
     */
    private $lastAccess;

    /**
     * @var int
     */
    private $readCount = '0';

    /**
     * @var int
     */
    private $spentSeconds = '0';

    /**
     * @var \DateTime|null
     */
    private $firstAccess;

    /**
     * @var int
     */
    private $childsReadCount = '0';

    /**
     * @var int
     */
    private $childsSpentSeconds = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ReadEvent
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
     * @return ReadEvent
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
     * Set lastAccess.
     *
     * @param int|null $lastAccess
     *
     * @return ReadEvent
     */
    public function setLastAccess($lastAccess = null)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess.
     *
     * @return int|null
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set readCount.
     *
     * @param int $readCount
     *
     * @return ReadEvent
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
     * @return ReadEvent
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
     * Set firstAccess.
     *
     * @param \DateTime|null $firstAccess
     *
     * @return ReadEvent
     */
    public function setFirstAccess($firstAccess = null)
    {
        $this->firstAccess = $firstAccess;

        return $this;
    }

    /**
     * Get firstAccess.
     *
     * @return \DateTime|null
     */
    public function getFirstAccess()
    {
        return $this->firstAccess;
    }

    /**
     * Set childsReadCount.
     *
     * @param int $childsReadCount
     *
     * @return ReadEvent
     */
    public function setChildsReadCount($childsReadCount)
    {
        $this->childsReadCount = $childsReadCount;

        return $this;
    }

    /**
     * Get childsReadCount.
     *
     * @return int
     */
    public function getChildsReadCount()
    {
        return $this->childsReadCount;
    }

    /**
     * Set childsSpentSeconds.
     *
     * @param int $childsSpentSeconds
     *
     * @return ReadEvent
     */
    public function setChildsSpentSeconds($childsSpentSeconds)
    {
        $this->childsSpentSeconds = $childsSpentSeconds;

        return $this;
    }

    /**
     * Get childsSpentSeconds.
     *
     * @return int
     */
    public function getChildsSpentSeconds()
    {
        return $this->childsSpentSeconds;
    }
}
