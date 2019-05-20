<?php



/**
 * EventFile
 */
class EventFile
{
    /**
     * @var int
     */
    private $fileId = '0';

    /**
     * @var int
     */
    private $eventId = '0';

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
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventFile
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return EventFile
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
     * @return EventFile
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
     * @return EventFile
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
