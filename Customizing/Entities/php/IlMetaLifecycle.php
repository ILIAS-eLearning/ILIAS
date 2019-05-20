<?php



/**
 * IlMetaLifecycle
 */
class IlMetaLifecycle
{
    /**
     * @var int
     */
    private $metaLifecycleId = '0';

    /**
     * @var int|null
     */
    private $rbacId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $lifecycleStatus;

    /**
     * @var string|null
     */
    private $metaVersion;

    /**
     * @var string|null
     */
    private $versionLanguage;


    /**
     * Get metaLifecycleId.
     *
     * @return int
     */
    public function getMetaLifecycleId()
    {
        return $this->metaLifecycleId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaLifecycle
     */
    public function setRbacId($rbacId = null)
    {
        $this->rbacId = $rbacId;

        return $this;
    }

    /**
     * Get rbacId.
     *
     * @return int|null
     */
    public function getRbacId()
    {
        return $this->rbacId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return IlMetaLifecycle
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
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
     * @return IlMetaLifecycle
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
     * Set lifecycleStatus.
     *
     * @param string|null $lifecycleStatus
     *
     * @return IlMetaLifecycle
     */
    public function setLifecycleStatus($lifecycleStatus = null)
    {
        $this->lifecycleStatus = $lifecycleStatus;

        return $this;
    }

    /**
     * Get lifecycleStatus.
     *
     * @return string|null
     */
    public function getLifecycleStatus()
    {
        return $this->lifecycleStatus;
    }

    /**
     * Set metaVersion.
     *
     * @param string|null $metaVersion
     *
     * @return IlMetaLifecycle
     */
    public function setMetaVersion($metaVersion = null)
    {
        $this->metaVersion = $metaVersion;

        return $this;
    }

    /**
     * Get metaVersion.
     *
     * @return string|null
     */
    public function getMetaVersion()
    {
        return $this->metaVersion;
    }

    /**
     * Set versionLanguage.
     *
     * @param string|null $versionLanguage
     *
     * @return IlMetaLifecycle
     */
    public function setVersionLanguage($versionLanguage = null)
    {
        $this->versionLanguage = $versionLanguage;

        return $this;
    }

    /**
     * Get versionLanguage.
     *
     * @return string|null
     */
    public function getVersionLanguage()
    {
        return $this->versionLanguage;
    }
}
