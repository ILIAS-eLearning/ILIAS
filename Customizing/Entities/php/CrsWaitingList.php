<?php



/**
 * CrsWaitingList
 */
class CrsWaitingList
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
    private $subTime = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CrsWaitingList
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
     * @return CrsWaitingList
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
     * Set subTime.
     *
     * @param int $subTime
     *
     * @return CrsWaitingList
     */
    public function setSubTime($subTime)
    {
        $this->subTime = $subTime;

        return $this;
    }

    /**
     * Get subTime.
     *
     * @return int
     */
    public function getSubTime()
    {
        return $this->subTime;
    }
}
