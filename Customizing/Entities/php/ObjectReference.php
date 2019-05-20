<?php



/**
 * ObjectReference
 */
class ObjectReference
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var \DateTime|null
     */
    private $deleted;


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
     * @return ObjectReference
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
     * Set deleted.
     *
     * @param \DateTime|null $deleted
     *
     * @return ObjectReference
     */
    public function setDeleted($deleted = null)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return \DateTime|null
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
