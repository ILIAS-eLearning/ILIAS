<?php



/**
 * AdvMdValuesExtlink
 */
class AdvMdValuesExtlink
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
     * @var string|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var bool
     */
    private $disabled = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdValuesExtlink
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
     * @return AdvMdValuesExtlink
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
     * @return AdvMdValuesExtlink
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
     * @return AdvMdValuesExtlink
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
     * @param string|null $value
     *
     * @return AdvMdValuesExtlink
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return AdvMdValuesExtlink
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
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return AdvMdValuesExtlink
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
