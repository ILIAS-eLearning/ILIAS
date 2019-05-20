<?php



/**
 * AdvMdfDefinition
 */
class AdvMdfDefinition
{
    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var int
     */
    private $recordId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var int
     */
    private $position = '0';

    /**
     * @var bool
     */
    private $fieldType = '0';

    /**
     * @var string|null
     */
    private $fieldValues;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool
     */
    private $searchable = '0';

    /**
     * @var bool
     */
    private $required = '0';


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
     * Set recordId.
     *
     * @param int $recordId
     *
     * @return AdvMdfDefinition
     */
    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;

        return $this;
    }

    /**
     * Get recordId.
     *
     * @return int
     */
    public function getRecordId()
    {
        return $this->recordId;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return AdvMdfDefinition
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return AdvMdfDefinition
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set fieldType.
     *
     * @param bool $fieldType
     *
     * @return AdvMdfDefinition
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType.
     *
     * @return bool
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * Set fieldValues.
     *
     * @param string|null $fieldValues
     *
     * @return AdvMdfDefinition
     */
    public function setFieldValues($fieldValues = null)
    {
        $this->fieldValues = $fieldValues;

        return $this;
    }

    /**
     * Get fieldValues.
     *
     * @return string|null
     */
    public function getFieldValues()
    {
        return $this->fieldValues;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return AdvMdfDefinition
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return AdvMdfDefinition
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
     * Set searchable.
     *
     * @param bool $searchable
     *
     * @return AdvMdfDefinition
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Get searchable.
     *
     * @return bool
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * Set required.
     *
     * @param bool $required
     *
     * @return AdvMdfDefinition
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }
}
