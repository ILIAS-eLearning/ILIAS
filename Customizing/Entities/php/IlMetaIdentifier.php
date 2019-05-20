<?php



/**
 * IlMetaIdentifier
 */
class IlMetaIdentifier
{
    /**
     * @var int
     */
    private $metaIdentifierId = '0';

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
    private $catalog;

    /**
     * @var string|null
     */
    private $entry;


    /**
     * Get metaIdentifierId.
     *
     * @return int
     */
    public function getMetaIdentifierId()
    {
        return $this->metaIdentifierId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaIdentifier
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
     * @return IlMetaIdentifier
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
     * @return IlMetaIdentifier
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
     * @return IlMetaIdentifier
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
     * @return IlMetaIdentifier
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
     * Set catalog.
     *
     * @param string|null $catalog
     *
     * @return IlMetaIdentifier
     */
    public function setCatalog($catalog = null)
    {
        $this->catalog = $catalog;

        return $this;
    }

    /**
     * Get catalog.
     *
     * @return string|null
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Set entry.
     *
     * @param string|null $entry
     *
     * @return IlMetaIdentifier
     */
    public function setEntry($entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry.
     *
     * @return string|null
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
