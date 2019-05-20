<?php



/**
 * CrsObjectiveTst
 */
class CrsObjectiveTst
{
    /**
     * @var int
     */
    private $testObjectiveId = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool|null
     */
    private $tstStatus;

    /**
     * @var bool|null
     */
    private $tstLimit;

    /**
     * @var int
     */
    private $tstLimitP = '0';


    /**
     * Get testObjectiveId.
     *
     * @return int
     */
    public function getTestObjectiveId()
    {
        return $this->testObjectiveId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return CrsObjectiveTst
     */
    public function setObjectiveId($objectiveId)
    {
        $this->objectiveId = $objectiveId;

        return $this;
    }

    /**
     * Get objectiveId.
     *
     * @return int
     */
    public function getObjectiveId()
    {
        return $this->objectiveId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return CrsObjectiveTst
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CrsObjectiveTst
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
     * Set tstStatus.
     *
     * @param bool|null $tstStatus
     *
     * @return CrsObjectiveTst
     */
    public function setTstStatus($tstStatus = null)
    {
        $this->tstStatus = $tstStatus;

        return $this;
    }

    /**
     * Get tstStatus.
     *
     * @return bool|null
     */
    public function getTstStatus()
    {
        return $this->tstStatus;
    }

    /**
     * Set tstLimit.
     *
     * @param bool|null $tstLimit
     *
     * @return CrsObjectiveTst
     */
    public function setTstLimit($tstLimit = null)
    {
        $this->tstLimit = $tstLimit;

        return $this;
    }

    /**
     * Get tstLimit.
     *
     * @return bool|null
     */
    public function getTstLimit()
    {
        return $this->tstLimit;
    }

    /**
     * Set tstLimitP.
     *
     * @param int $tstLimitP
     *
     * @return CrsObjectiveTst
     */
    public function setTstLimitP($tstLimitP)
    {
        $this->tstLimitP = $tstLimitP;

        return $this;
    }

    /**
     * Get tstLimitP.
     *
     * @return int
     */
    public function getTstLimitP()
    {
        return $this->tstLimitP;
    }
}
