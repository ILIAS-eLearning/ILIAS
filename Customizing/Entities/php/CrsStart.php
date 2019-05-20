<?php



/**
 * CrsStart
 */
class CrsStart
{
    /**
     * @var int
     */
    private $crsStartId = '0';

    /**
     * @var int
     */
    private $crsId = '0';

    /**
     * @var int
     */
    private $itemRefId = '0';

    /**
     * @var int|null
     */
    private $pos;


    /**
     * Get crsStartId.
     *
     * @return int
     */
    public function getCrsStartId()
    {
        return $this->crsStartId;
    }

    /**
     * Set crsId.
     *
     * @param int $crsId
     *
     * @return CrsStart
     */
    public function setCrsId($crsId)
    {
        $this->crsId = $crsId;

        return $this;
    }

    /**
     * Get crsId.
     *
     * @return int
     */
    public function getCrsId()
    {
        return $this->crsId;
    }

    /**
     * Set itemRefId.
     *
     * @param int $itemRefId
     *
     * @return CrsStart
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

    /**
     * Set pos.
     *
     * @param int|null $pos
     *
     * @return CrsStart
     */
    public function setPos($pos = null)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get pos.
     *
     * @return int|null
     */
    public function getPos()
    {
        return $this->pos;
    }
}
