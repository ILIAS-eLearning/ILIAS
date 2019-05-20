<?php



/**
 * FileData
 */
class FileData
{
    /**
     * @var int
     */
    private $fileId = '0';

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * @var string|null
     */
    private $fileType;

    /**
     * @var int
     */
    private $fileSize = '0';

    /**
     * @var int|null
     */
    private $version;

    /**
     * @var string|null
     */
    private $fMode = 'object';

    /**
     * @var bool
     */
    private $rating = '0';

    /**
     * @var int|null
     */
    private $pageCount;

    /**
     * @var int|null
     */
    private $maxVersion;


    /**
     * Get fileId.
     *
     * @return int
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return FileData
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileType.
     *
     * @param string|null $fileType
     *
     * @return FileData
     */
    public function setFileType($fileType = null)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType.
     *
     * @return string|null
     */
    public function getFileType()
    {
        return $this->fileType;
    }

    /**
     * Set fileSize.
     *
     * @param int $fileSize
     *
     * @return FileData
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * Get fileSize.
     *
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * Set version.
     *
     * @param int|null $version
     *
     * @return FileData
     */
    public function setVersion($version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set fMode.
     *
     * @param string|null $fMode
     *
     * @return FileData
     */
    public function setFMode($fMode = null)
    {
        $this->fMode = $fMode;

        return $this;
    }

    /**
     * Get fMode.
     *
     * @return string|null
     */
    public function getFMode()
    {
        return $this->fMode;
    }

    /**
     * Set rating.
     *
     * @param bool $rating
     *
     * @return FileData
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return bool
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set pageCount.
     *
     * @param int|null $pageCount
     *
     * @return FileData
     */
    public function setPageCount($pageCount = null)
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    /**
     * Get pageCount.
     *
     * @return int|null
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * Set maxVersion.
     *
     * @param int|null $maxVersion
     *
     * @return FileData
     */
    public function setMaxVersion($maxVersion = null)
    {
        $this->maxVersion = $maxVersion;

        return $this;
    }

    /**
     * Get maxVersion.
     *
     * @return int|null
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }
}
