<?php



/**
 * PrgSettings
 */
class PrgSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var \DateTime
     */
    private $lastChange = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $subtypeId = '0';

    /**
     * @var int
     */
    private $points = '0';

    /**
     * @var bool
     */
    private $lpMode = '0';

    /**
     * @var bool
     */
    private $status = '0';


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
     * Set lastChange.
     *
     * @param \DateTime $lastChange
     *
     * @return PrgSettings
     */
    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * Get lastChange.
     *
     * @return \DateTime
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Set subtypeId.
     *
     * @param int $subtypeId
     *
     * @return PrgSettings
     */
    public function setSubtypeId($subtypeId)
    {
        $this->subtypeId = $subtypeId;

        return $this;
    }

    /**
     * Get subtypeId.
     *
     * @return int
     */
    public function getSubtypeId()
    {
        return $this->subtypeId;
    }

    /**
     * Set points.
     *
     * @param int $points
     *
     * @return PrgSettings
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set lpMode.
     *
     * @param bool $lpMode
     *
     * @return PrgSettings
     */
    public function setLpMode($lpMode)
    {
        $this->lpMode = $lpMode;

        return $this;
    }

    /**
     * Get lpMode.
     *
     * @return bool
     */
    public function getLpMode()
    {
        return $this->lpMode;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return PrgSettings
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
}
