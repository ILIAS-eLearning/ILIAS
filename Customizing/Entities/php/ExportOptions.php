<?php



/**
 * ExportOptions
 */
class ExportOptions
{
    /**
     * @var int
     */
    private $exportId = '0';

    /**
     * @var int
     */
    private $keyword = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var int
     */
    private $pos = '0';


    /**
     * Set exportId.
     *
     * @param int $exportId
     *
     * @return ExportOptions
     */
    public function setExportId($exportId)
    {
        $this->exportId = $exportId;

        return $this;
    }

    /**
     * Get exportId.
     *
     * @return int
     */
    public function getExportId()
    {
        return $this->exportId;
    }

    /**
     * Set keyword.
     *
     * @param int $keyword
     *
     * @return ExportOptions
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return int
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return ExportOptions
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ExportOptions
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
     * Set value.
     *
     * @param string|null $value
     *
     * @return ExportOptions
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
     * Set pos.
     *
     * @param int $pos
     *
     * @return ExportOptions
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }
}
