<?php



/**
 * AdvMdRecordObjs
 */
class AdvMdRecordObjs
{
    /**
     * @var int
     */
    private $recordId = '0';

    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var string
     */
    private $subType = '-';

    /**
     * @var bool
     */
    private $optional = '0';


    /**
     * Set recordId.
     *
     * @param int $recordId
     *
     * @return AdvMdRecordObjs
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
     * Set objType.
     *
     * @param string $objType
     *
     * @return AdvMdRecordObjs
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set subType.
     *
     * @param string $subType
     *
     * @return AdvMdRecordObjs
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
     * Set optional.
     *
     * @param bool $optional
     *
     * @return AdvMdRecordObjs
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * Get optional.
     *
     * @return bool
     */
    public function getOptional()
    {
        return $this->optional;
    }
}
