<?php



/**
 * ObjectReferenceWs
 */
class ObjectReferenceWs
{
    /**
     * @var int
     */
    private $wspId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var \DateTime|null
     */
    private $deleted;


    /**
     * Get wspId.
     *
     * @return int
     */
    public function getWspId()
    {
        return $this->wspId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjectReferenceWs
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
     * @return ObjectReferenceWs
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
