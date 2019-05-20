<?php



/**
 * UdfDefinition
 */
class UdfDefinition
{
    /**
     * @var int
     */
    private $fieldId = '0';

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
    private $visible = '0';

    /**
     * @var bool
     */
    private $changeable = '0';

    /**
     * @var bool
     */
    private $required = '0';

    /**
     * @var bool
     */
    private $searchable = '0';

    /**
     * @var bool
     */
    private $export = '0';

    /**
     * @var bool
     */
    private $courseExport = '0';

    /**
     * @var bool|null
     */
    private $registrationVisible = '0';

    /**
     * @var bool
     */
    private $visibleLua = '0';

    /**
     * @var bool
     */
    private $changeableLua = '0';

    /**
     * @var bool|null
     */
    private $groupExport = '0';

    /**
     * @var bool
     */
    private $certificate = '0';


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
     * Set fieldName.
     *
     * @param string|null $fieldName
     *
     * @return UdfDefinition
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
     * @return UdfDefinition
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
     * @return UdfDefinition
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
     * Set visible.
     *
     * @param bool $visible
     *
     * @return UdfDefinition
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set changeable.
     *
     * @param bool $changeable
     *
     * @return UdfDefinition
     */
    public function setChangeable($changeable)
    {
        $this->changeable = $changeable;

        return $this;
    }

    /**
     * Get changeable.
     *
     * @return bool
     */
    public function getChangeable()
    {
        return $this->changeable;
    }

    /**
     * Set required.
     *
     * @param bool $required
     *
     * @return UdfDefinition
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

    /**
     * Set searchable.
     *
     * @param bool $searchable
     *
     * @return UdfDefinition
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
     * Set export.
     *
     * @param bool $export
     *
     * @return UdfDefinition
     */
    public function setExport($export)
    {
        $this->export = $export;

        return $this;
    }

    /**
     * Get export.
     *
     * @return bool
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * Set courseExport.
     *
     * @param bool $courseExport
     *
     * @return UdfDefinition
     */
    public function setCourseExport($courseExport)
    {
        $this->courseExport = $courseExport;

        return $this;
    }

    /**
     * Get courseExport.
     *
     * @return bool
     */
    public function getCourseExport()
    {
        return $this->courseExport;
    }

    /**
     * Set registrationVisible.
     *
     * @param bool|null $registrationVisible
     *
     * @return UdfDefinition
     */
    public function setRegistrationVisible($registrationVisible = null)
    {
        $this->registrationVisible = $registrationVisible;

        return $this;
    }

    /**
     * Get registrationVisible.
     *
     * @return bool|null
     */
    public function getRegistrationVisible()
    {
        return $this->registrationVisible;
    }

    /**
     * Set visibleLua.
     *
     * @param bool $visibleLua
     *
     * @return UdfDefinition
     */
    public function setVisibleLua($visibleLua)
    {
        $this->visibleLua = $visibleLua;

        return $this;
    }

    /**
     * Get visibleLua.
     *
     * @return bool
     */
    public function getVisibleLua()
    {
        return $this->visibleLua;
    }

    /**
     * Set changeableLua.
     *
     * @param bool $changeableLua
     *
     * @return UdfDefinition
     */
    public function setChangeableLua($changeableLua)
    {
        $this->changeableLua = $changeableLua;

        return $this;
    }

    /**
     * Get changeableLua.
     *
     * @return bool
     */
    public function getChangeableLua()
    {
        return $this->changeableLua;
    }

    /**
     * Set groupExport.
     *
     * @param bool|null $groupExport
     *
     * @return UdfDefinition
     */
    public function setGroupExport($groupExport = null)
    {
        $this->groupExport = $groupExport;

        return $this;
    }

    /**
     * Get groupExport.
     *
     * @return bool|null
     */
    public function getGroupExport()
    {
        return $this->groupExport;
    }

    /**
     * Set certificate.
     *
     * @param bool $certificate
     *
     * @return UdfDefinition
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * Get certificate.
     *
     * @return bool
     */
    public function getCertificate()
    {
        return $this->certificate;
    }
}
