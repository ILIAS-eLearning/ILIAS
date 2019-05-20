<?php



/**
 * IlMetaAnnotation
 */
class IlMetaAnnotation
{
    /**
     * @var int
     */
    private $metaAnnotationId = '0';

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
    private $entity;

    /**
     * @var string|null
     */
    private $aDate;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $descriptionLanguage;


    /**
     * Get metaAnnotationId.
     *
     * @return int
     */
    public function getMetaAnnotationId()
    {
        return $this->metaAnnotationId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaAnnotation
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
     * @return IlMetaAnnotation
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
     * @return IlMetaAnnotation
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
     * Set entity.
     *
     * @param string|null $entity
     *
     * @return IlMetaAnnotation
     */
    public function setEntity($entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity.
     *
     * @return string|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set aDate.
     *
     * @param string|null $aDate
     *
     * @return IlMetaAnnotation
     */
    public function setADate($aDate = null)
    {
        $this->aDate = $aDate;

        return $this;
    }

    /**
     * Get aDate.
     *
     * @return string|null
     */
    public function getADate()
    {
        return $this->aDate;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlMetaAnnotation
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
     * @return IlMetaAnnotation
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
