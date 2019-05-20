<?php



/**
 * IlMetaTar
 */
class IlMetaTar
{
    /**
     * @var int
     */
    private $metaTarId = '0';

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
    private $typicalAgeRange;

    /**
     * @var string|null
     */
    private $tarLanguage;

    /**
     * @var string|null
     */
    private $tarMin;

    /**
     * @var string|null
     */
    private $tarMax;


    /**
     * Get metaTarId.
     *
     * @return int
     */
    public function getMetaTarId()
    {
        return $this->metaTarId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaTar
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
     * @return IlMetaTar
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
     * @return IlMetaTar
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
     * @return IlMetaTar
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
     * @return IlMetaTar
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
     * Set typicalAgeRange.
     *
     * @param string|null $typicalAgeRange
     *
     * @return IlMetaTar
     */
    public function setTypicalAgeRange($typicalAgeRange = null)
    {
        $this->typicalAgeRange = $typicalAgeRange;

        return $this;
    }

    /**
     * Get typicalAgeRange.
     *
     * @return string|null
     */
    public function getTypicalAgeRange()
    {
        return $this->typicalAgeRange;
    }

    /**
     * Set tarLanguage.
     *
     * @param string|null $tarLanguage
     *
     * @return IlMetaTar
     */
    public function setTarLanguage($tarLanguage = null)
    {
        $this->tarLanguage = $tarLanguage;

        return $this;
    }

    /**
     * Get tarLanguage.
     *
     * @return string|null
     */
    public function getTarLanguage()
    {
        return $this->tarLanguage;
    }

    /**
     * Set tarMin.
     *
     * @param string|null $tarMin
     *
     * @return IlMetaTar
     */
    public function setTarMin($tarMin = null)
    {
        $this->tarMin = $tarMin;

        return $this;
    }

    /**
     * Get tarMin.
     *
     * @return string|null
     */
    public function getTarMin()
    {
        return $this->tarMin;
    }

    /**
     * Set tarMax.
     *
     * @param string|null $tarMax
     *
     * @return IlMetaTar
     */
    public function setTarMax($tarMax = null)
    {
        $this->tarMax = $tarMax;

        return $this;
    }

    /**
     * Get tarMax.
     *
     * @return string|null
     */
    public function getTarMax()
    {
        return $this->tarMax;
    }
}
