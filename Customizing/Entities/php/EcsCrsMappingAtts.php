<?php



/**
 * EcsCrsMappingAtts
 */
class EcsCrsMappingAtts
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var string|null
     */
    private $name;


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
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsCrsMappingAtts
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsCrsMappingAtts
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return EcsCrsMappingAtts
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
