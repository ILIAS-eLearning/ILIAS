<?php



/**
 * ContainerSortingSet
 */
class ContainerSortingSet
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool
     */
    private $sortMode = '0';

    /**
     * @var bool
     */
    private $sortDirection = '0';

    /**
     * @var bool
     */
    private $newItemsPosition = '1';

    /**
     * @var bool
     */
    private $newItemsOrder = '0';


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
     * Set sortMode.
     *
     * @param bool $sortMode
     *
     * @return ContainerSortingSet
     */
    public function setSortMode($sortMode)
    {
        $this->sortMode = $sortMode;

        return $this;
    }

    /**
     * Get sortMode.
     *
     * @return bool
     */
    public function getSortMode()
    {
        return $this->sortMode;
    }

    /**
     * Set sortDirection.
     *
     * @param bool $sortDirection
     *
     * @return ContainerSortingSet
     */
    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $sortDirection;

        return $this;
    }

    /**
     * Get sortDirection.
     *
     * @return bool
     */
    public function getSortDirection()
    {
        return $this->sortDirection;
    }

    /**
     * Set newItemsPosition.
     *
     * @param bool $newItemsPosition
     *
     * @return ContainerSortingSet
     */
    public function setNewItemsPosition($newItemsPosition)
    {
        $this->newItemsPosition = $newItemsPosition;

        return $this;
    }

    /**
     * Get newItemsPosition.
     *
     * @return bool
     */
    public function getNewItemsPosition()
    {
        return $this->newItemsPosition;
    }

    /**
     * Set newItemsOrder.
     *
     * @param bool $newItemsOrder
     *
     * @return ContainerSortingSet
     */
    public function setNewItemsOrder($newItemsOrder)
    {
        $this->newItemsOrder = $newItemsOrder;

        return $this;
    }

    /**
     * Get newItemsOrder.
     *
     * @return bool
     */
    public function getNewItemsOrder()
    {
        return $this->newItemsOrder;
    }
}
