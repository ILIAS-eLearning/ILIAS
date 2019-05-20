<?php



/**
 * LoAccess
 */
class LoAccess
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $lmId = '0';

    /**
     * @var \DateTime|null
     */
    private $timestamp;

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $lmTitle;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return LoAccess
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
     * Set lmId.
     *
     * @param int $lmId
     *
     * @return LoAccess
     */
    public function setLmId($lmId)
    {
        $this->lmId = $lmId;

        return $this;
    }

    /**
     * Get lmId.
     *
     * @return int
     */
    public function getLmId()
    {
        return $this->lmId;
    }

    /**
     * Set timestamp.
     *
     * @param \DateTime|null $timestamp
     *
     * @return LoAccess
     */
    public function setTimestamp($timestamp = null)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return \DateTime|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return LoAccess
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
     * Set lmTitle.
     *
     * @param string|null $lmTitle
     *
     * @return LoAccess
     */
    public function setLmTitle($lmTitle = null)
    {
        $this->lmTitle = $lmTitle;

        return $this;
    }

    /**
     * Get lmTitle.
     *
     * @return string|null
     */
    public function getLmTitle()
    {
        return $this->lmTitle;
    }
}
