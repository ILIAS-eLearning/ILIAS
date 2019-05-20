<?php



/**
 * ItemGroupItem
 */
class ItemGroupItem
{
    /**
     * @var int
     */
    private $itemGroupId = '0';

    /**
     * @var int
     */
    private $itemRefId = '0';


    /**
     * Set itemGroupId.
     *
     * @param int $itemGroupId
     *
     * @return ItemGroupItem
     */
    public function setItemGroupId($itemGroupId)
    {
        $this->itemGroupId = $itemGroupId;

        return $this;
    }

    /**
     * Get itemGroupId.
     *
     * @return int
     */
    public function getItemGroupId()
    {
        return $this->itemGroupId;
    }

    /**
     * Set itemRefId.
     *
     * @param int $itemRefId
     *
     * @return ItemGroupItem
     */
    public function setItemRefId($itemRefId)
    {
        $this->itemRefId = $itemRefId;

        return $this;
    }

    /**
     * Get itemRefId.
     *
     * @return int
     */
    public function getItemRefId()
    {
        return $this->itemRefId;
    }
}
