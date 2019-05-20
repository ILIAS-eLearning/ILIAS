<?php



/**
 * IlDiskQuota
 */
class IlDiskQuota
{
    /**
     * @var int
     */
    private $ownerId = '0';

    /**
     * @var string
     */
    private $srcType = '';

    /**
     * @var int
     */
    private $srcObjId = '0';

    /**
     * @var int
     */
    private $srcSize = '0';


    /**
     * Set ownerId.
     *
     * @param int $ownerId
     *
     * @return IlDiskQuota
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get ownerId.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Set srcType.
     *
     * @param string $srcType
     *
     * @return IlDiskQuota
     */
    public function setSrcType($srcType)
    {
        $this->srcType = $srcType;

        return $this;
    }

    /**
     * Get srcType.
     *
     * @return string
     */
    public function getSrcType()
    {
        return $this->srcType;
    }

    /**
     * Set srcObjId.
     *
     * @param int $srcObjId
     *
     * @return IlDiskQuota
     */
    public function setSrcObjId($srcObjId)
    {
        $this->srcObjId = $srcObjId;

        return $this;
    }

    /**
     * Get srcObjId.
     *
     * @return int
     */
    public function getSrcObjId()
    {
        return $this->srcObjId;
    }

    /**
     * Set srcSize.
     *
     * @param int $srcSize
     *
     * @return IlDiskQuota
     */
    public function setSrcSize($srcSize)
    {
        $this->srcSize = $srcSize;

        return $this;
    }

    /**
     * Get srcSize.
     *
     * @return int
     */
    public function getSrcSize()
    {
        return $this->srcSize;
    }
}
