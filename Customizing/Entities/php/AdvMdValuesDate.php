<?php



/**
 * AdvMdValuesDate
 */
class AdvMdValuesDate
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $subType = '-';

    /**
     * @var int
     */
    private $subId = '0';

    /**
     * @var int
     */
    private $fieldId = '0';

    /**
     * @var \DateTime|null
     */
    private $value;

    /**
     * @var bool
     */
    private $disabled = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdValuesDate
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
     * Set subType.
     *
     * @param string $subType
     *
     * @return AdvMdValuesDate
     */
    public function setSubType($subType)
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * Get subType.
     *
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Set subId.
     *
     * @param int $subId
     *
     * @return AdvMdValuesDate
     */
    public function setSubId($subId)
    {
        $this->subId = $subId;

        return $this;
    }

    /**
     * Get subId.
     *
     * @return int
     */
    public function getSubId()
    {
        return $this->subId;
    }

    /**
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return AdvMdValuesDate
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
     * Set value.
     *
     * @param \DateTime|null $value
     *
     * @return AdvMdValuesDate
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return \DateTime|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return AdvMdValuesDate
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled.
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }
}
