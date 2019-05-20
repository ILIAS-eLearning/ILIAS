<?php



/**
 * TaxData
 */
class TaxData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $sortingMode = '0';

    /**
     * @var bool
     */
    private $itemSorting = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sortingMode.
     *
     * @param int $sortingMode
     *
     * @return TaxData
     */
    public function setSortingMode($sortingMode)
    {
        $this->sortingMode = $sortingMode;

        return $this;
    }

    /**
     * Get sortingMode.
     *
     * @return int
     */
    public function getSortingMode()
    {
        return $this->sortingMode;
    }

    /**
     * Set itemSorting.
     *
     * @param bool $itemSorting
     *
     * @return TaxData
     */
    public function setItemSorting($itemSorting)
    {
        $this->itemSorting = $itemSorting;

        return $this;
    }

    /**
     * Get itemSorting.
     *
     * @return bool
     */
    public function getItemSorting()
    {
        return $this->itemSorting;
    }
}
