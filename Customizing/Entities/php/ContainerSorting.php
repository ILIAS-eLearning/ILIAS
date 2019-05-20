<?php



/**
 * ContainerSorting
 */
class ContainerSorting
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $childId = '0';

    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var int
     */
    private $position = '0';

    /**
     * @var string|null
     */
    private $parentType;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ContainerSorting
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
     * Set childId.
     *
     * @param int $childId
     *
     * @return ContainerSorting
     */
    public function setChildId($childId)
    {
        $this->childId = $childId;

        return $this;
    }

    /**
     * Get childId.
     *
     * @return int
     */
    public function getChildId()
    {
        return $this->childId;
    }

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return ContainerSorting
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return ContainerSorting
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

    /**
     * Set parentType.
     *
     * @param string|null $parentType
     *
     * @return ContainerSorting
     */
    public function setParentType($parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string|null
     */
    public function getParentType()
    {
        return $this->parentType;
    }
}
