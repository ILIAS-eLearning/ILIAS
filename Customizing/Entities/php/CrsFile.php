<?php



/**
 * CrsFile
 */
class CrsFile
{
    /**
     * @var int
     */
    private $fileId = '0';

    /**
     * @var int
     */
    private $courseId = '0';

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
     * Get fileId.
     *
     * @return int
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set courseId.
     *
     * @param int $courseId
     *
     * @return CrsFile
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * Get courseId.
     *
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return CrsFile
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
     * @return CrsFile
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
     * @return CrsFile
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
}
