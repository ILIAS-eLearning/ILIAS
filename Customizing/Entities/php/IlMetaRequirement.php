<?php



/**
 * IlMetaRequirement
 */
class IlMetaRequirement
{
    /**
     * @var int
     */
    private $metaRequirementId = '0';

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
    private $parentType;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var string|null
     */
    private $operatingSystemName;

    /**
     * @var string|null
     */
    private $osMinVersion;

    /**
     * @var string|null
     */
    private $osMaxVersion;

    /**
     * @var string|null
     */
    private $browserName;

    /**
     * @var string|null
     */
    private $browserMinimumVersion;

    /**
     * @var string|null
     */
    private $browserMaximumVersion;

    /**
     * @var int
     */
    private $orCompositeId = '0';


    /**
     * Get metaRequirementId.
     *
     * @return int
     */
    public function getMetaRequirementId()
    {
        return $this->metaRequirementId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaRequirement
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
     * @return IlMetaRequirement
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
     * @return IlMetaRequirement
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
     * Set parentType.
     *
     * @param string|null $parentType
     *
     * @return IlMetaRequirement
     */
    public function setParentType($parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string|null
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return IlMetaRequirement
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set operatingSystemName.
     *
     * @param string|null $operatingSystemName
     *
     * @return IlMetaRequirement
     */
    public function setOperatingSystemName($operatingSystemName = null)
    {
        $this->operatingSystemName = $operatingSystemName;

        return $this;
    }

    /**
     * Get operatingSystemName.
     *
     * @return string|null
     */
    public function getOperatingSystemName()
    {
        return $this->operatingSystemName;
    }

    /**
     * Set osMinVersion.
     *
     * @param string|null $osMinVersion
     *
     * @return IlMetaRequirement
     */
    public function setOsMinVersion($osMinVersion = null)
    {
        $this->osMinVersion = $osMinVersion;

        return $this;
    }

    /**
     * Get osMinVersion.
     *
     * @return string|null
     */
    public function getOsMinVersion()
    {
        return $this->osMinVersion;
    }

    /**
     * Set osMaxVersion.
     *
     * @param string|null $osMaxVersion
     *
     * @return IlMetaRequirement
     */
    public function setOsMaxVersion($osMaxVersion = null)
    {
        $this->osMaxVersion = $osMaxVersion;

        return $this;
    }

    /**
     * Get osMaxVersion.
     *
     * @return string|null
     */
    public function getOsMaxVersion()
    {
        return $this->osMaxVersion;
    }

    /**
     * Set browserName.
     *
     * @param string|null $browserName
     *
     * @return IlMetaRequirement
     */
    public function setBrowserName($browserName = null)
    {
        $this->browserName = $browserName;

        return $this;
    }

    /**
     * Get browserName.
     *
     * @return string|null
     */
    public function getBrowserName()
    {
        return $this->browserName;
    }

    /**
     * Set browserMinimumVersion.
     *
     * @param string|null $browserMinimumVersion
     *
     * @return IlMetaRequirement
     */
    public function setBrowserMinimumVersion($browserMinimumVersion = null)
    {
        $this->browserMinimumVersion = $browserMinimumVersion;

        return $this;
    }

    /**
     * Get browserMinimumVersion.
     *
     * @return string|null
     */
    public function getBrowserMinimumVersion()
    {
        return $this->browserMinimumVersion;
    }

    /**
     * Set browserMaximumVersion.
     *
     * @param string|null $browserMaximumVersion
     *
     * @return IlMetaRequirement
     */
    public function setBrowserMaximumVersion($browserMaximumVersion = null)
    {
        $this->browserMaximumVersion = $browserMaximumVersion;

        return $this;
    }

    /**
     * Get browserMaximumVersion.
     *
     * @return string|null
     */
    public function getBrowserMaximumVersion()
    {
        return $this->browserMaximumVersion;
    }

    /**
     * Set orCompositeId.
     *
     * @param int $orCompositeId
     *
     * @return IlMetaRequirement
     */
    public function setOrCompositeId($orCompositeId)
    {
        $this->orCompositeId = $orCompositeId;

        return $this;
    }

    /**
     * Get orCompositeId.
     *
     * @return int
     */
    public function getOrCompositeId()
    {
        return $this->orCompositeId;
    }
}
