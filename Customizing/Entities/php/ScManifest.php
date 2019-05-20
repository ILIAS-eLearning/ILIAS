<?php



/**
 * ScManifest
 */
class ScManifest
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
    private $version;

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
     * @return ScManifest
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
     * Set version.
     *
     * @param string|null $version
     *
     * @return ScManifest
     */
    public function setVersion($version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set xmlBase.
     *
     * @param string|null $xmlBase
     *
     * @return ScManifest
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
