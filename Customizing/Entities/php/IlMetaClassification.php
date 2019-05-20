<?php



/**
 * IlMetaClassification
 */
class IlMetaClassification
{
    /**
     * @var int
     */
    private $metaClassificationId = '0';

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
    private $purpose;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $descriptionLanguage;


    /**
     * Get metaClassificationId.
     *
     * @return int
     */
    public function getMetaClassificationId()
    {
        return $this->metaClassificationId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaClassification
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
     * @return IlMetaClassification
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
     * @return IlMetaClassification
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
     * Set purpose.
     *
     * @param string|null $purpose
     *
     * @return IlMetaClassification
     */
    public function setPurpose($purpose = null)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose.
     *
     * @return string|null
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlMetaClassification
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
     * @return IlMetaClassification
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
