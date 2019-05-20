<?php



/**
 * DavProperty
 */
class DavProperty
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $nodeId = '0';

    /**
     * @var string
     */
    private $ns = 'DAV:';

    /**
     * @var string
     */
    private $name = ' ';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return DavProperty
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
     * Set nodeId.
     *
     * @param int $nodeId
     *
     * @return DavProperty
     */
    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;

        return $this;
    }

    /**
     * Get nodeId.
     *
     * @return int
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * Set ns.
     *
     * @param string $ns
     *
     * @return DavProperty
     */
    public function setNs($ns)
    {
        $this->ns = $ns;

        return $this;
    }

    /**
     * Get ns.
     *
     * @return string
     */
    public function getNs()
    {
        return $this->ns;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return DavProperty
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return DavProperty
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
