<?php



/**
 * AdvMdObjRecSelect
 */
class AdvMdObjRecSelect
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
    private $recId = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdObjRecSelect
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
     * @return AdvMdObjRecSelect
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
     * Set recId.
     *
     * @param int $recId
     *
     * @return AdvMdObjRecSelect
     */
    public function setRecId($recId)
    {
        $this->recId = $recId;

        return $this;
    }

    /**
     * Get recId.
     *
     * @return int
     */
    public function getRecId()
    {
        return $this->recId;
    }
}
