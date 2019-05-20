<?php



/**
 * CrsLmHistory
 */
class CrsLmHistory
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $crsRefId = '0';

    /**
     * @var int
     */
    private $lmRefId = '0';

    /**
     * @var int
     */
    private $lmPageId = '0';

    /**
     * @var int
     */
    private $lastAccess = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CrsLmHistory
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
     * Set crsRefId.
     *
     * @param int $crsRefId
     *
     * @return CrsLmHistory
     */
    public function setCrsRefId($crsRefId)
    {
        $this->crsRefId = $crsRefId;

        return $this;
    }

    /**
     * Get crsRefId.
     *
     * @return int
     */
    public function getCrsRefId()
    {
        return $this->crsRefId;
    }

    /**
     * Set lmRefId.
     *
     * @param int $lmRefId
     *
     * @return CrsLmHistory
     */
    public function setLmRefId($lmRefId)
    {
        $this->lmRefId = $lmRefId;

        return $this;
    }

    /**
     * Get lmRefId.
     *
     * @return int
     */
    public function getLmRefId()
    {
        return $this->lmRefId;
    }

    /**
     * Set lmPageId.
     *
     * @param int $lmPageId
     *
     * @return CrsLmHistory
     */
    public function setLmPageId($lmPageId)
    {
        $this->lmPageId = $lmPageId;

        return $this;
    }

    /**
     * Get lmPageId.
     *
     * @return int
     */
    public function getLmPageId()
    {
        return $this->lmPageId;
    }

    /**
     * Set lastAccess.
     *
     * @param int $lastAccess
     *
     * @return CrsLmHistory
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
