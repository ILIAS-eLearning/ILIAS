<?php



/**
 * ChatroomUploads
 */
class ChatroomUploads
{
    /**
     * @var int
     */
    private $uploadId = '0';

    /**
     * @var int
     */
    private $roomId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $filetype = '';

    /**
     * @var int
     */
    private $timestamp = '0';


    /**
     * Get uploadId.
     *
     * @return int
     */
    public function getUploadId()
    {
        return $this->uploadId;
    }

    /**
     * Set roomId.
     *
     * @param int $roomId
     *
     * @return ChatroomUploads
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return ChatroomUploads
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return ChatroomUploads
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
     * Set filetype.
     *
     * @param string $filetype
     *
     * @return ChatroomUploads
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;

        return $this;
    }

    /**
     * Get filetype.
     *
     * @return string
     */
    public function getFiletype()
    {
        return $this->filetype;
    }

    /**
     * Set timestamp.
     *
     * @param int $timestamp
     *
     * @return ChatroomUploads
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
