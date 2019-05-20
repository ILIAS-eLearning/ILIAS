<?php



/**
 * OrguObjPosSettings
 */
class OrguObjPosSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool|null
     */
    private $active = '0';


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
     * Set active.
     *
     * @param bool|null $active
     *
     * @return OrguObjPosSettings
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }
}
