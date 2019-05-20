<?php



/**
 * CpResource
 */
class CpResource
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $base;

    /**
     * @var string|null
     */
    private $href;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $scormtype;

    /**
     * @var string|null
     */
    private $cType;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set base.
     *
     * @param string|null $base
     *
     * @return CpResource
     */
    public function setBase($base = null)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get base.
     *
     * @return string|null
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Set href.
     *
     * @param string|null $href
     *
     * @return CpResource
     */
    public function setHref($href = null)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get href.
     *
     * @return string|null
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set id.
     *
     * @param string|null $id
     *
     * @return CpResource
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set scormtype.
     *
     * @param string|null $scormtype
     *
     * @return CpResource
     */
    public function setScormtype($scormtype = null)
    {
        $this->scormtype = $scormtype;

        return $this;
    }

    /**
     * Get scormtype.
     *
     * @return string|null
     */
    public function getScormtype()
    {
        return $this->scormtype;
    }

    /**
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return CpResource
     */
    public function setCType($cType = null)
    {
        $this->cType = $cType;

        return $this;
    }

    /**
     * Get cType.
     *
     * @return string|null
     */
    public function getCType()
    {
        return $this->cType;
    }
}
