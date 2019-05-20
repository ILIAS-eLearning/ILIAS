<?php



/**
 * UtLpCollManual
 */
class UtLpCollManual
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
    private $subitemId = '0';

    /**
     * @var bool
     */
    private $completed = '0';

    /**
     * @var int
     */
    private $lastChange = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return UtLpCollManual
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
     * @return UtLpCollManual
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
     * Set subitemId.
     *
     * @param int $subitemId
     *
     * @return UtLpCollManual
     */
    public function setSubitemId($subitemId)
    {
        $this->subitemId = $subitemId;

        return $this;
    }

    /**
     * Get subitemId.
     *
     * @return int
     */
    public function getSubitemId()
    {
        return $this->subitemId;
    }

    /**
     * Set completed.
     *
     * @param bool $completed
     *
     * @return UtLpCollManual
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed.
     *
     * @return bool
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set lastChange.
     *
     * @param int $lastChange
     *
     * @return UtLpCollManual
     */
    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * Get lastChange.
     *
     * @return int
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }
}
