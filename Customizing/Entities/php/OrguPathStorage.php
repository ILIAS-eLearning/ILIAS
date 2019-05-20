<?php



/**
 * OrguPathStorage
 */
class OrguPathStorage
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $path;


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
     * @param int|null $objId
     *
     * @return OrguPathStorage
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set path.
     *
     * @param string|null $path
     *
     * @return OrguPathStorage
     */
    public function setPath($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
