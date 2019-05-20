<?php



/**
 * ExportFileInfo
 */
class ExportFileInfo
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $exportType = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $fileName = '';

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var \DateTime
     */
    private $createDate = '1970-01-01 00:00:00';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ExportFileInfo
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
     * Set exportType.
     *
     * @param string $exportType
     *
     * @return ExportFileInfo
     */
    public function setExportType($exportType)
    {
        $this->exportType = $exportType;

        return $this;
    }

    /**
     * Get exportType.
     *
     * @return string
     */
    public function getExportType()
    {
        return $this->exportType;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return ExportFileInfo
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set version.
     *
     * @param string|null $version
     *
     * @return ExportFileInfo
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
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return ExportFileInfo
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }
}
