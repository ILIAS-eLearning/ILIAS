<?php



/**
 * MailCronOrphaned
 */
class MailCronOrphaned
{
    /**
     * @var int
     */
    private $mailId = '0';

    /**
     * @var int
     */
    private $folderId = '0';

    /**
     * @var int
     */
    private $tsDoDelete = '0';


    /**
     * Set mailId.
     *
     * @param int $mailId
     *
     * @return MailCronOrphaned
     */
    public function setMailId($mailId)
    {
        $this->mailId = $mailId;

        return $this;
    }

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
     * Set folderId.
     *
     * @param int $folderId
     *
     * @return MailCronOrphaned
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;

        return $this;
    }

    /**
     * Get folderId.
     *
     * @return int
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * Set tsDoDelete.
     *
     * @param int $tsDoDelete
     *
     * @return MailCronOrphaned
     */
    public function setTsDoDelete($tsDoDelete)
    {
        $this->tsDoDelete = $tsDoDelete;

        return $this;
    }

    /**
     * Get tsDoDelete.
     *
     * @return int
     */
    public function getTsDoDelete()
    {
        return $this->tsDoDelete;
    }
}
