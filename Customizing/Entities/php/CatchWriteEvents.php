<?php



/**
 * CatchWriteEvents
 */
class CatchWriteEvents
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
     * @var \DateTime|null
     */
    private $ts;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CatchWriteEvents
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
     * @return CatchWriteEvents
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
     * Set ts.
     *
     * @param \DateTime|null $ts
     *
     * @return CatchWriteEvents
     */
    public function setTs($ts = null)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime|null
     */
    public function getTs()
    {
        return $this->ts;
    }
}
