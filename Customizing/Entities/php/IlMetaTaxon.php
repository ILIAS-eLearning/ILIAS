<?php



/**
 * IlMetaTaxon
 */
class IlMetaTaxon
{
    /**
     * @var int
     */
    private $metaTaxonId = '0';

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
    private $taxon;

    /**
     * @var string|null
     */
    private $taxonLanguage;

    /**
     * @var string|null
     */
    private $taxonId;


    /**
     * Get metaTaxonId.
     *
     * @return int
     */
    public function getMetaTaxonId()
    {
        return $this->metaTaxonId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaTaxon
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
     * @return IlMetaTaxon
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
     * @return IlMetaTaxon
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
     * @return IlMetaTaxon
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
     * @return IlMetaTaxon
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
     * Set taxon.
     *
     * @param string|null $taxon
     *
     * @return IlMetaTaxon
     */
    public function setTaxon($taxon = null)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxon.
     *
     * @return string|null
     */
    public function getTaxon()
    {
        return $this->taxon;
    }

    /**
     * Set taxonLanguage.
     *
     * @param string|null $taxonLanguage
     *
     * @return IlMetaTaxon
     */
    public function setTaxonLanguage($taxonLanguage = null)
    {
        $this->taxonLanguage = $taxonLanguage;

        return $this;
    }

    /**
     * Get taxonLanguage.
     *
     * @return string|null
     */
    public function getTaxonLanguage()
    {
        return $this->taxonLanguage;
    }

    /**
     * Set taxonId.
     *
     * @param string|null $taxonId
     *
     * @return IlMetaTaxon
     */
    public function setTaxonId($taxonId = null)
    {
        $this->taxonId = $taxonId;

        return $this;
    }

    /**
     * Get taxonId.
     *
     * @return string|null
     */
    public function getTaxonId()
    {
        return $this->taxonId;
    }
}
