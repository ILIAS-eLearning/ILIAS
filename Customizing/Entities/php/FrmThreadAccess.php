<?php



/**
 * FrmThreadAccess
 */
class FrmThreadAccess
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $threadId = '0';

    /**
     * @var int
     */
    private $accessOld = '0';

    /**
     * @var int
     */
    private $accessLast = '0';

    /**
     * @var \DateTime|null
     */
    private $accessOldTs;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return FrmThreadAccess
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return FrmThreadAccess
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
     * Set threadId.
     *
     * @param int $threadId
     *
     * @return FrmThreadAccess
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * Get threadId.
     *
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Set accessOld.
     *
     * @param int $accessOld
     *
     * @return FrmThreadAccess
     */
    public function setAccessOld($accessOld)
    {
        $this->accessOld = $accessOld;

        return $this;
    }

    /**
     * Get accessOld.
     *
     * @return int
     */
    public function getAccessOld()
    {
        return $this->accessOld;
    }

    /**
     * Set accessLast.
     *
     * @param int $accessLast
     *
     * @return FrmThreadAccess
     */
    public function setAccessLast($accessLast)
    {
        $this->accessLast = $accessLast;

        return $this;
    }

    /**
     * Get accessLast.
     *
     * @return int
     */
    public function getAccessLast()
    {
        return $this->accessLast;
    }

    /**
     * Set accessOldTs.
     *
     * @param \DateTime|null $accessOldTs
     *
     * @return FrmThreadAccess
     */
    public function setAccessOldTs($accessOldTs = null)
    {
        $this->accessOldTs = $accessOldTs;

        return $this;
    }

    /**
     * Get accessOldTs.
     *
     * @return \DateTime|null
     */
    public function getAccessOldTs()
    {
        return $this->accessOldTs;
    }
}
