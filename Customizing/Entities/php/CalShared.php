<?php



/**
 * CalShared
 */
class CalShared
{
    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $objType = '0';

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var bool|null
     */
    private $writable = '0';


    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalShared
     */
    public function setCalId($calId)
    {
        $this->calId = $calId;

        return $this;
    }

    /**
     * Get calId.
     *
     * @return int
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CalShared
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
     * Set objType.
     *
     * @param int $objType
     *
     * @return CalShared
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return int
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return CalShared
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set writable.
     *
     * @param bool|null $writable
     *
     * @return CalShared
     */
    public function setWritable($writable = null)
    {
        $this->writable = $writable;

        return $this;
    }

    /**
     * Get writable.
     *
     * @return bool|null
     */
    public function getWritable()
    {
        return $this->writable;
    }
}
