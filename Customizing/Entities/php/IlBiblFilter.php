<?php



/**
 * IlBiblFilter
 */
class IlBiblFilter
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $fieldId;

    /**
     * @var int
     */
    private $objectId;

    /**
     * @var bool|null
     */
    private $filterType;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return IlBiblFilter
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Get fieldId.
     *
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return IlBiblFilter
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set filterType.
     *
     * @param bool|null $filterType
     *
     * @return IlBiblFilter
     */
    public function setFilterType($filterType = null)
    {
        $this->filterType = $filterType;

        return $this;
    }

    /**
     * Get filterType.
     *
     * @return bool|null
     */
    public function getFilterType()
    {
        return $this->filterType;
    }
}
