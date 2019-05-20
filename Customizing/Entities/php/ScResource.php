<?php



/**
 * ScResource
 */
class ScResource
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var string|null
     */
    private $resourcetype;

    /**
     * @var string|null
     */
    private $scormtype;

    /**
     * @var string|null
     */
    private $href;

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
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return ScResource
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set resourcetype.
     *
     * @param string|null $resourcetype
     *
     * @return ScResource
     */
    public function setResourcetype($resourcetype = null)
    {
        $this->resourcetype = $resourcetype;

        return $this;
    }

    /**
     * Get resourcetype.
     *
     * @return string|null
     */
    public function getResourcetype()
    {
        return $this->resourcetype;
    }

    /**
     * Set scormtype.
     *
     * @param string|null $scormtype
     *
     * @return ScResource
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
     * Set href.
     *
     * @param string|null $href
     *
     * @return ScResource
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
     * Set xmlBase.
     *
     * @param string|null $xmlBase
     *
     * @return ScResource
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
