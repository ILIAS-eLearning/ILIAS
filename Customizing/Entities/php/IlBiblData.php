<?php



/**
 * IlBiblData
 */
class IlBiblData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var bool|null
     */
    private $isOnline;

    /**
     * @var bool
     */
    private $fileType = '1';


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
     * Set filename.
     *
     * @param string|null $filename
     *
     * @return IlBiblData
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return IlBiblData
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set fileType.
     *
     * @param bool $fileType
     *
     * @return IlBiblData
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;

        return $this;
    }

    /**
     * Get fileType.
     *
     * @return bool
     */
    public function getFileType()
    {
        return $this->fileType;
    }
}
