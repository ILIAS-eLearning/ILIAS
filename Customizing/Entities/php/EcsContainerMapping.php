<?php



/**
 * EcsContainerMapping
 */
class EcsContainerMapping
{
    /**
     * @var int
     */
    private $mappingId = '0';

    /**
     * @var int
     */
    private $containerId = '0';

    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * @var bool
     */
    private $mappingType = '0';

    /**
     * @var string|null
     */
    private $mappingValue;

    /**
     * @var int
     */
    private $dateRangeStart = '0';

    /**
     * @var int
     */
    private $dateRangeEnd = '0';


    /**
     * Get mappingId.
     *
     * @return int
     */
    public function getMappingId()
    {
        return $this->mappingId;
    }

    /**
     * Set containerId.
     *
     * @param int $containerId
     *
     * @return EcsContainerMapping
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId.
     *
     * @return int
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set fieldName.
     *
     * @param string|null $fieldName
     *
     * @return EcsContainerMapping
     */
    public function setFieldName($fieldName = null)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName.
     *
     * @return string|null
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set mappingType.
     *
     * @param bool $mappingType
     *
     * @return EcsContainerMapping
     */
    public function setMappingType($mappingType)
    {
        $this->mappingType = $mappingType;

        return $this;
    }

    /**
     * Get mappingType.
     *
     * @return bool
     */
    public function getMappingType()
    {
        return $this->mappingType;
    }

    /**
     * Set mappingValue.
     *
     * @param string|null $mappingValue
     *
     * @return EcsContainerMapping
     */
    public function setMappingValue($mappingValue = null)
    {
        $this->mappingValue = $mappingValue;

        return $this;
    }

    /**
     * Get mappingValue.
     *
     * @return string|null
     */
    public function getMappingValue()
    {
        return $this->mappingValue;
    }

    /**
     * Set dateRangeStart.
     *
     * @param int $dateRangeStart
     *
     * @return EcsContainerMapping
     */
    public function setDateRangeStart($dateRangeStart)
    {
        $this->dateRangeStart = $dateRangeStart;

        return $this;
    }

    /**
     * Get dateRangeStart.
     *
     * @return int
     */
    public function getDateRangeStart()
    {
        return $this->dateRangeStart;
    }

    /**
     * Set dateRangeEnd.
     *
     * @param int $dateRangeEnd
     *
     * @return EcsContainerMapping
     */
    public function setDateRangeEnd($dateRangeEnd)
    {
        $this->dateRangeEnd = $dateRangeEnd;

        return $this;
    }

    /**
     * Get dateRangeEnd.
     *
     * @return int
     */
    public function getDateRangeEnd()
    {
        return $this->dateRangeEnd;
    }
}
