<?php



/**
 * IlMetaRights
 */
class IlMetaRights
{
    /**
     * @var int
     */
    private $metaRightsId = '0';

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
    private $costs;

    /**
     * @var string|null
     */
    private $cprAndOr;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $descriptionLanguage;


    /**
     * Get metaRightsId.
     *
     * @return int
     */
    public function getMetaRightsId()
    {
        return $this->metaRightsId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaRights
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
     * @return IlMetaRights
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
     * @return IlMetaRights
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
     * Set costs.
     *
     * @param string|null $costs
     *
     * @return IlMetaRights
     */
    public function setCosts($costs = null)
    {
        $this->costs = $costs;

        return $this;
    }

    /**
     * Get costs.
     *
     * @return string|null
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * Set cprAndOr.
     *
     * @param string|null $cprAndOr
     *
     * @return IlMetaRights
     */
    public function setCprAndOr($cprAndOr = null)
    {
        $this->cprAndOr = $cprAndOr;

        return $this;
    }

    /**
     * Get cprAndOr.
     *
     * @return string|null
     */
    public function getCprAndOr()
    {
        return $this->cprAndOr;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlMetaRights
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set descriptionLanguage.
     *
     * @param string|null $descriptionLanguage
     *
     * @return IlMetaRights
     */
    public function setDescriptionLanguage($descriptionLanguage = null)
    {
        $this->descriptionLanguage = $descriptionLanguage;

        return $this;
    }

    /**
     * Get descriptionLanguage.
     *
     * @return string|null
     */
    public function getDescriptionLanguage()
    {
        return $this->descriptionLanguage;
    }
}
