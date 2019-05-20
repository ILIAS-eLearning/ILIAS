<?php



/**
 * IlMetaTaxonPath
 */
class IlMetaTaxonPath
{
    /**
     * @var int
     */
    private $metaTaxonPathId = '0';

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
    private $source;

    /**
     * @var string|null
     */
    private $sourceLanguage;


    /**
     * Get metaTaxonPathId.
     *
     * @return int
     */
    public function getMetaTaxonPathId()
    {
        return $this->metaTaxonPathId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaTaxonPath
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
     * @return IlMetaTaxonPath
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
     * @return IlMetaTaxonPath
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
     * @return IlMetaTaxonPath
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
     * @return IlMetaTaxonPath
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
     * Set source.
     *
     * @param string|null $source
     *
     * @return IlMetaTaxonPath
     */
    public function setSource($source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string|null
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set sourceLanguage.
     *
     * @param string|null $sourceLanguage
     *
     * @return IlMetaTaxonPath
     */
    public function setSourceLanguage($sourceLanguage = null)
    {
        $this->sourceLanguage = $sourceLanguage;

        return $this;
    }

    /**
     * Get sourceLanguage.
     *
     * @return string|null
     */
    public function getSourceLanguage()
    {
        return $this->sourceLanguage;
    }
}
