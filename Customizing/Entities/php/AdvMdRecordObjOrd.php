<?php



/**
 * AdvMdRecordObjOrd
 */
class AdvMdRecordObjOrd
{
    /**
     * @var int
     */
    private $recordId;

    /**
     * @var int
     */
    private $objId;

    /**
     * @var int
     */
    private $position;


    /**
     * Set recordId.
     *
     * @param int $recordId
     *
     * @return AdvMdRecordObjOrd
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return AdvMdRecordObjOrd
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
     * Set position.
     *
     * @param int $position
     *
     * @return AdvMdRecordObjOrd
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
}
