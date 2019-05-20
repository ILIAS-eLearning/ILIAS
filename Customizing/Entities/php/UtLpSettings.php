<?php



/**
 * UtLpSettings
 */
class UtLpSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var bool
     */
    private $uMode = '0';

    /**
     * @var int|null
     */
    private $visits = '0';


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
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return UtLpSettings
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set uMode.
     *
     * @param bool $uMode
     *
     * @return UtLpSettings
     */
    public function setUMode($uMode)
    {
        $this->uMode = $uMode;

        return $this;
    }

    /**
     * Get uMode.
     *
     * @return bool
     */
    public function getUMode()
    {
        return $this->uMode;
    }

    /**
     * Set visits.
     *
     * @param int|null $visits
     *
     * @return UtLpSettings
     */
    public function setVisits($visits = null)
    {
        $this->visits = $visits;

        return $this;
    }

    /**
     * Get visits.
     *
     * @return int|null
     */
    public function getVisits()
    {
        return $this->visits;
    }
}
