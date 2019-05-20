<?php



/**
 * IlMediaCastDataOrd
 */
class IlMediaCastDataOrd
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var int
     */
    private $pos = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlMediaCastDataOrd
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
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return IlMediaCastDataOrd
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set pos.
     *
     * @param int $pos
     *
     * @return IlMediaCastDataOrd
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }
}
