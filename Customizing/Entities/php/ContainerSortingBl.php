<?php



/**
 * ContainerSortingBl
 */
class ContainerSortingBl
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $blockIds;


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
     * Set blockIds.
     *
     * @param string|null $blockIds
     *
     * @return ContainerSortingBl
     */
    public function setBlockIds($blockIds = null)
    {
        $this->blockIds = $blockIds;

        return $this;
    }

    /**
     * Get blockIds.
     *
     * @return string|null
     */
    public function getBlockIds()
    {
        return $this->blockIds;
    }
}
