<?php



/**
 * UtLpCollections
 */
class UtLpCollections
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
    private $groupingId = '0';

    /**
     * @var int
     */
    private $numObligatory = '0';

    /**
     * @var bool
     */
    private $active = '1';

    /**
     * @var bool|null
     */
    private $lpmode = '5';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return UtLpCollections
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
     * @return UtLpCollections
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
     * Set groupingId.
     *
     * @param int $groupingId
     *
     * @return UtLpCollections
     */
    public function setGroupingId($groupingId)
    {
        $this->groupingId = $groupingId;

        return $this;
    }

    /**
     * Get groupingId.
     *
     * @return int
     */
    public function getGroupingId()
    {
        return $this->groupingId;
    }

    /**
     * Set numObligatory.
     *
     * @param int $numObligatory
     *
     * @return UtLpCollections
     */
    public function setNumObligatory($numObligatory)
    {
        $this->numObligatory = $numObligatory;

        return $this;
    }

    /**
     * Get numObligatory.
     *
     * @return int
     */
    public function getNumObligatory()
    {
        return $this->numObligatory;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return UtLpCollections
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set lpmode.
     *
     * @param bool|null $lpmode
     *
     * @return UtLpCollections
     */
    public function setLpmode($lpmode = null)
    {
        $this->lpmode = $lpmode;

        return $this;
    }

    /**
     * Get lpmode.
     *
     * @return bool|null
     */
    public function getLpmode()
    {
        return $this->lpmode;
    }
}
