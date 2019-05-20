<?php



/**
 * CpPackage
 */
class CpPackage
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $created;

    /**
     * @var string|null
     */
    private $cIdentifier;

    /**
     * @var string|null
     */
    private $jsdata;

    /**
     * @var string|null
     */
    private $modified;

    /**
     * @var int|null
     */
    private $persistprevattempts;

    /**
     * @var string|null
     */
    private $cSettings;

    /**
     * @var string|null
     */
    private $xmldata;

    /**
     * @var string|null
     */
    private $activitytree;

    /**
     * @var bool
     */
    private $globalToSystem = '1';

    /**
     * @var bool|null
     */
    private $sharedDataGlobalToSystem = '1';


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
     * Set created.
     *
     * @param string|null $created
     *
     * @return CpPackage
     */
    public function setCreated($created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return string|null
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set cIdentifier.
     *
     * @param string|null $cIdentifier
     *
     * @return CpPackage
     */
    public function setCIdentifier($cIdentifier = null)
    {
        $this->cIdentifier = $cIdentifier;

        return $this;
    }

    /**
     * Get cIdentifier.
     *
     * @return string|null
     */
    public function getCIdentifier()
    {
        return $this->cIdentifier;
    }

    /**
     * Set jsdata.
     *
     * @param string|null $jsdata
     *
     * @return CpPackage
     */
    public function setJsdata($jsdata = null)
    {
        $this->jsdata = $jsdata;

        return $this;
    }

    /**
     * Get jsdata.
     *
     * @return string|null
     */
    public function getJsdata()
    {
        return $this->jsdata;
    }

    /**
     * Set modified.
     *
     * @param string|null $modified
     *
     * @return CpPackage
     */
    public function setModified($modified = null)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return string|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set persistprevattempts.
     *
     * @param int|null $persistprevattempts
     *
     * @return CpPackage
     */
    public function setPersistprevattempts($persistprevattempts = null)
    {
        $this->persistprevattempts = $persistprevattempts;

        return $this;
    }

    /**
     * Get persistprevattempts.
     *
     * @return int|null
     */
    public function getPersistprevattempts()
    {
        return $this->persistprevattempts;
    }

    /**
     * Set cSettings.
     *
     * @param string|null $cSettings
     *
     * @return CpPackage
     */
    public function setCSettings($cSettings = null)
    {
        $this->cSettings = $cSettings;

        return $this;
    }

    /**
     * Get cSettings.
     *
     * @return string|null
     */
    public function getCSettings()
    {
        return $this->cSettings;
    }

    /**
     * Set xmldata.
     *
     * @param string|null $xmldata
     *
     * @return CpPackage
     */
    public function setXmldata($xmldata = null)
    {
        $this->xmldata = $xmldata;

        return $this;
    }

    /**
     * Get xmldata.
     *
     * @return string|null
     */
    public function getXmldata()
    {
        return $this->xmldata;
    }

    /**
     * Set activitytree.
     *
     * @param string|null $activitytree
     *
     * @return CpPackage
     */
    public function setActivitytree($activitytree = null)
    {
        $this->activitytree = $activitytree;

        return $this;
    }

    /**
     * Get activitytree.
     *
     * @return string|null
     */
    public function getActivitytree()
    {
        return $this->activitytree;
    }

    /**
     * Set globalToSystem.
     *
     * @param bool $globalToSystem
     *
     * @return CpPackage
     */
    public function setGlobalToSystem($globalToSystem)
    {
        $this->globalToSystem = $globalToSystem;

        return $this;
    }

    /**
     * Get globalToSystem.
     *
     * @return bool
     */
    public function getGlobalToSystem()
    {
        return $this->globalToSystem;
    }

    /**
     * Set sharedDataGlobalToSystem.
     *
     * @param bool|null $sharedDataGlobalToSystem
     *
     * @return CpPackage
     */
    public function setSharedDataGlobalToSystem($sharedDataGlobalToSystem = null)
    {
        $this->sharedDataGlobalToSystem = $sharedDataGlobalToSystem;

        return $this;
    }

    /**
     * Get sharedDataGlobalToSystem.
     *
     * @return bool|null
     */
    public function getSharedDataGlobalToSystem()
    {
        return $this->sharedDataGlobalToSystem;
    }
}
