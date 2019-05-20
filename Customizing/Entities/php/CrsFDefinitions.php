<?php



/**
 * CrsFDefinitions
 */
class CrsFDefinitions
{
    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * @var bool
     */
    private $fieldType = '0';

    /**
     * @var string|null
     */
    private $fieldValues;

    /**
     * @var bool
     */
    private $fieldRequired = '0';

    /**
     * @var string|null
     */
    private $fieldValuesOpt;


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
     * Set objId.
     *
     * @param int $objId
     *
     * @return CrsFDefinitions
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set fieldName.
     *
     * @param string|null $fieldName
     *
     * @return CrsFDefinitions
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
     * Set fieldType.
     *
     * @param bool $fieldType
     *
     * @return CrsFDefinitions
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
     * @return CrsFDefinitions
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
     * Set fieldRequired.
     *
     * @param bool $fieldRequired
     *
     * @return CrsFDefinitions
     */
    public function setFieldRequired($fieldRequired)
    {
        $this->fieldRequired = $fieldRequired;

        return $this;
    }

    /**
     * Get fieldRequired.
     *
     * @return bool
     */
    public function getFieldRequired()
    {
        return $this->fieldRequired;
    }

    /**
     * Set fieldValuesOpt.
     *
     * @param string|null $fieldValuesOpt
     *
     * @return CrsFDefinitions
     */
    public function setFieldValuesOpt($fieldValuesOpt = null)
    {
        $this->fieldValuesOpt = $fieldValuesOpt;

        return $this;
    }

    /**
     * Get fieldValuesOpt.
     *
     * @return string|null
     */
    public function getFieldValuesOpt()
    {
        return $this->fieldValuesOpt;
    }
}
