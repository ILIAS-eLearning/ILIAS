<?php



/**
 * AdvMdValuesLocation
 */
class AdvMdValuesLocation
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
     * @var float|null
     */
    private $locLat;

    /**
     * @var float|null
     */
    private $locLong;

    /**
     * @var bool|null
     */
    private $locZoom;

    /**
     * @var bool
     */
    private $disabled = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdValuesLocation
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
     * @return AdvMdValuesLocation
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
     * @return AdvMdValuesLocation
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
     * @return AdvMdValuesLocation
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
     * Set locLat.
     *
     * @param float|null $locLat
     *
     * @return AdvMdValuesLocation
     */
    public function setLocLat($locLat = null)
    {
        $this->locLat = $locLat;

        return $this;
    }

    /**
     * Get locLat.
     *
     * @return float|null
     */
    public function getLocLat()
    {
        return $this->locLat;
    }

    /**
     * Set locLong.
     *
     * @param float|null $locLong
     *
     * @return AdvMdValuesLocation
     */
    public function setLocLong($locLong = null)
    {
        $this->locLong = $locLong;

        return $this;
    }

    /**
     * Get locLong.
     *
     * @return float|null
     */
    public function getLocLong()
    {
        return $this->locLong;
    }

    /**
     * Set locZoom.
     *
     * @param bool|null $locZoom
     *
     * @return AdvMdValuesLocation
     */
    public function setLocZoom($locZoom = null)
    {
        $this->locZoom = $locZoom;

        return $this;
    }

    /**
     * Get locZoom.
     *
     * @return bool|null
     */
    public function getLocZoom()
    {
        return $this->locZoom;
    }

    /**
     * Set disabled.
     *
     * @param bool $disabled
     *
     * @return AdvMdValuesLocation
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
