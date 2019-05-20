<?php



/**
 * ContainerReference
 */
class ContainerReference
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $targetObjId = '0';

    /**
     * @var bool
     */
    private $titleType = '1';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ContainerReference
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
     * Set targetObjId.
     *
     * @param int $targetObjId
     *
     * @return ContainerReference
     */
    public function setTargetObjId($targetObjId)
    {
        $this->targetObjId = $targetObjId;

        return $this;
    }

    /**
     * Get targetObjId.
     *
     * @return int
     */
    public function getTargetObjId()
    {
        return $this->targetObjId;
    }

    /**
     * Set titleType.
     *
     * @param bool $titleType
     *
     * @return ContainerReference
     */
    public function setTitleType($titleType)
    {
        $this->titleType = $titleType;

        return $this;
    }

    /**
     * Get titleType.
     *
     * @return bool
     */
    public function getTitleType()
    {
        return $this->titleType;
    }
}
