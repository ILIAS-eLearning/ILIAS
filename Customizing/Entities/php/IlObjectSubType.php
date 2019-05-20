<?php



/**
 * IlObjectSubType
 */
class IlObjectSubType
{
    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var string
     */
    private $subType = '';

    /**
     * @var bool
     */
    private $amet = '0';


    /**
     * Set objType.
     *
     * @param string $objType
     *
     * @return IlObjectSubType
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set subType.
     *
     * @param string $subType
     *
     * @return IlObjectSubType
     */
    public function setSubType($subType)
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * Get subType.
     *
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Set amet.
     *
     * @param bool $amet
     *
     * @return IlObjectSubType
     */
    public function setAmet($amet)
    {
        $this->amet = $amet;

        return $this;
    }

    /**
     * Get amet.
     *
     * @return bool
     */
    public function getAmet()
    {
        return $this->amet;
    }
}
