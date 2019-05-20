<?php



/**
 * WriteEvent
 */
class WriteEvent
{
    /**
     * @var int
     */
    private $writeId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $parentObjId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $action = ' ';

    /**
     * @var \DateTime
     */
    private $ts = '1970-01-01 00:00:00';


    /**
     * Get writeId.
     *
     * @return int
     */
    public function getWriteId()
    {
        return $this->writeId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return WriteEvent
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
     * Set parentObjId.
     *
     * @param int $parentObjId
     *
     * @return WriteEvent
     */
    public function setParentObjId($parentObjId)
    {
        $this->parentObjId = $parentObjId;

        return $this;
    }

    /**
     * Get parentObjId.
     *
     * @return int
     */
    public function getParentObjId()
    {
        return $this->parentObjId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return WriteEvent
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
     * Set action.
     *
     * @param string $action
     *
     * @return WriteEvent
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set ts.
     *
     * @param \DateTime $ts
     *
     * @return WriteEvent
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime
     */
    public function getTs()
    {
        return $this->ts;
    }
}
