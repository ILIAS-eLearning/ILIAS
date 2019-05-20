<?php



/**
 * LocRndQpl
 */
class LocRndQpl
{
    /**
     * @var int
     */
    private $containerId = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var bool
     */
    private $tstType = '0';

    /**
     * @var int
     */
    private $tstId = '0';

    /**
     * @var int
     */
    private $qpSeq = '0';

    /**
     * @var int
     */
    private $percentage = '0';


    /**
     * Set containerId.
     *
     * @param int $containerId
     *
     * @return LocRndQpl
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId.
     *
     * @return int
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return LocRndQpl
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
     * Set tstType.
     *
     * @param bool $tstType
     *
     * @return LocRndQpl
     */
    public function setTstType($tstType)
    {
        $this->tstType = $tstType;

        return $this;
    }

    /**
     * Get tstType.
     *
     * @return bool
     */
    public function getTstType()
    {
        return $this->tstType;
    }

    /**
     * Set tstId.
     *
     * @param int $tstId
     *
     * @return LocRndQpl
     */
    public function setTstId($tstId)
    {
        $this->tstId = $tstId;

        return $this;
    }

    /**
     * Get tstId.
     *
     * @return int
     */
    public function getTstId()
    {
        return $this->tstId;
    }

    /**
     * Set qpSeq.
     *
     * @param int $qpSeq
     *
     * @return LocRndQpl
     */
    public function setQpSeq($qpSeq)
    {
        $this->qpSeq = $qpSeq;

        return $this;
    }

    /**
     * Get qpSeq.
     *
     * @return int
     */
    public function getQpSeq()
    {
        return $this->qpSeq;
    }

    /**
     * Set percentage.
     *
     * @param int $percentage
     *
     * @return LocRndQpl
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage.
     *
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }
}
