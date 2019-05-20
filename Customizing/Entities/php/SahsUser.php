<?php



/**
 * SahsUser
 */
class SahsUser
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int|null
     */
    private $packageAttempts;

    /**
     * @var int|null
     */
    private $moduleVersion;

    /**
     * @var string|null
     */
    private $lastVisited;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var \DateTime|null
     */
    private $hashEnd;

    /**
     * @var string|null
     */
    private $offlineMode;

    /**
     * @var \DateTime|null
     */
    private $lastAccess;

    /**
     * @var int|null
     */
    private $totalTimeSec;

    /**
     * @var int|null
     */
    private $scoTotalTimeSec;

    /**
     * @var bool|null
     */
    private $status;

    /**
     * @var bool|null
     */
    private $percentageCompleted;

    /**
     * @var \DateTime|null
     */
    private $firstAccess;

    /**
     * @var \DateTime|null
     */
    private $lastStatusChange;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return SahsUser
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return SahsUser
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
     * Set packageAttempts.
     *
     * @param int|null $packageAttempts
     *
     * @return SahsUser
     */
    public function setPackageAttempts($packageAttempts = null)
    {
        $this->packageAttempts = $packageAttempts;

        return $this;
    }

    /**
     * Get packageAttempts.
     *
     * @return int|null
     */
    public function getPackageAttempts()
    {
        return $this->packageAttempts;
    }

    /**
     * Set moduleVersion.
     *
     * @param int|null $moduleVersion
     *
     * @return SahsUser
     */
    public function setModuleVersion($moduleVersion = null)
    {
        $this->moduleVersion = $moduleVersion;

        return $this;
    }

    /**
     * Get moduleVersion.
     *
     * @return int|null
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * Set lastVisited.
     *
     * @param string|null $lastVisited
     *
     * @return SahsUser
     */
    public function setLastVisited($lastVisited = null)
    {
        $this->lastVisited = $lastVisited;

        return $this;
    }

    /**
     * Get lastVisited.
     *
     * @return string|null
     */
    public function getLastVisited()
    {
        return $this->lastVisited;
    }

    /**
     * Set hash.
     *
     * @param string|null $hash
     *
     * @return SahsUser
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set hashEnd.
     *
     * @param \DateTime|null $hashEnd
     *
     * @return SahsUser
     */
    public function setHashEnd($hashEnd = null)
    {
        $this->hashEnd = $hashEnd;

        return $this;
    }

    /**
     * Get hashEnd.
     *
     * @return \DateTime|null
     */
    public function getHashEnd()
    {
        return $this->hashEnd;
    }

    /**
     * Set offlineMode.
     *
     * @param string|null $offlineMode
     *
     * @return SahsUser
     */
    public function setOfflineMode($offlineMode = null)
    {
        $this->offlineMode = $offlineMode;

        return $this;
    }

    /**
     * Get offlineMode.
     *
     * @return string|null
     */
    public function getOfflineMode()
    {
        return $this->offlineMode;
    }

    /**
     * Set lastAccess.
     *
     * @param \DateTime|null $lastAccess
     *
     * @return SahsUser
     */
    public function setLastAccess($lastAccess = null)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess.
     *
     * @return \DateTime|null
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set totalTimeSec.
     *
     * @param int|null $totalTimeSec
     *
     * @return SahsUser
     */
    public function setTotalTimeSec($totalTimeSec = null)
    {
        $this->totalTimeSec = $totalTimeSec;

        return $this;
    }

    /**
     * Get totalTimeSec.
     *
     * @return int|null
     */
    public function getTotalTimeSec()
    {
        return $this->totalTimeSec;
    }

    /**
     * Set scoTotalTimeSec.
     *
     * @param int|null $scoTotalTimeSec
     *
     * @return SahsUser
     */
    public function setScoTotalTimeSec($scoTotalTimeSec = null)
    {
        $this->scoTotalTimeSec = $scoTotalTimeSec;

        return $this;
    }

    /**
     * Get scoTotalTimeSec.
     *
     * @return int|null
     */
    public function getScoTotalTimeSec()
    {
        return $this->scoTotalTimeSec;
    }

    /**
     * Set status.
     *
     * @param bool|null $status
     *
     * @return SahsUser
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set percentageCompleted.
     *
     * @param bool|null $percentageCompleted
     *
     * @return SahsUser
     */
    public function setPercentageCompleted($percentageCompleted = null)
    {
        $this->percentageCompleted = $percentageCompleted;

        return $this;
    }

    /**
     * Get percentageCompleted.
     *
     * @return bool|null
     */
    public function getPercentageCompleted()
    {
        return $this->percentageCompleted;
    }

    /**
     * Set firstAccess.
     *
     * @param \DateTime|null $firstAccess
     *
     * @return SahsUser
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
     * Set lastStatusChange.
     *
     * @param \DateTime|null $lastStatusChange
     *
     * @return SahsUser
     */
    public function setLastStatusChange($lastStatusChange = null)
    {
        $this->lastStatusChange = $lastStatusChange;

        return $this;
    }

    /**
     * Get lastStatusChange.
     *
     * @return \DateTime|null
     */
    public function getLastStatusChange()
    {
        return $this->lastStatusChange;
    }
}
