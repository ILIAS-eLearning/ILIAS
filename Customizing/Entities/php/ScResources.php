<?php



/**
 * ScResources
 */
class ScResources
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $xmlBase;


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
     * Set xmlBase.
     *
     * @param string|null $xmlBase
     *
     * @return ScResources
     */
    public function setXmlBase($xmlBase = null)
    {
        $this->xmlBase = $xmlBase;

        return $this;
    }

    /**
     * Get xmlBase.
     *
     * @return string|null
     */
    public function getXmlBase()
    {
        return $this->xmlBase;
    }
}
