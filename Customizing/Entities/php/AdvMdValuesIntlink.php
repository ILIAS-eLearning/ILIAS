<?php



/**
 * AdvMdValuesIntlink
 */
class AdvMdValuesIntlink
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
     * @var int
     */
    private $value = '0';

    /**
     * @var bool
     */
    private $disabled = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdValuesIntlink
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
     * @return AdvMdValuesIntlink
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
     * @return AdvMdValuesIntlink
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
     * @return AdvMdValuesIntlink
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
     * @param int $value
     *
     * @return AdvMdValuesIntlink
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return int
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
     * @return AdvMdValuesIntlink
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
