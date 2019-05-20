<?php



/**
 * MailAttachment
 */
class MailAttachment
{
    /**
     * @var int
     */
    private $mailId = '0';

    /**
     * @var string|null
     */
    private $path;


    /**
     * Get mailId.
     *
     * @return int
     */
    public function getMailId()
    {
        return $this->mailId;
    }

    /**
     * Set path.
     *
     * @param string|null $path
     *
     * @return MailAttachment
     */
    public function setPath($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }
}
