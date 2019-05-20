<?php



/**
 * UtLpUserStatus
 */
class UtLpUserStatus
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
     * @var bool
     */
    private $status = '0';

    /**
     * @var string|null
     */
    private $additionalInfo;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return UtLpUserStatus
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
     * @return UtLpUserStatus
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
     * Set status.
     *
     * @param bool $status
     *
     * @return UtLpUserStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set additionalInfo.
     *
     * @param string|null $additionalInfo
     *
     * @return UtLpUserStatus
     */
    public function setAdditionalInfo($additionalInfo = null)
    {
        $this->additionalInfo = $additionalInfo;

        return $this;
    }

    /**
     * Get additionalInfo.
     *
     * @return string|null
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }
}
