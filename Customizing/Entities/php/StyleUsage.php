<?php



/**
 * StyleUsage
 */
class StyleUsage
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $styleId = '0';


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
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleUsage
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int
     */
    public function getStyleId()
    {
        return $this->styleId;
    }
}
