<?php



/**
 * FrmDraftsHistory
 */
class FrmDraftsHistory
{
    /**
     * @var int
     */
    private $historyId = '0';

    /**
     * @var int
     */
    private $draftId = '0';

    /**
     * @var string
     */
    private $postSubject = '';

    /**
     * @var string
     */
    private $postMessage;

    /**
     * @var \DateTime
     */
    private $draftDate = '1970-01-01 00:00:00';


    /**
     * Get historyId.
     *
     * @return int
     */
    public function getHistoryId()
    {
        return $this->historyId;
    }

    /**
     * Set draftId.
     *
     * @param int $draftId
     *
     * @return FrmDraftsHistory
     */
    public function setDraftId($draftId)
    {
        $this->draftId = $draftId;

        return $this;
    }

    /**
     * Get draftId.
     *
     * @return int
     */
    public function getDraftId()
    {
        return $this->draftId;
    }

    /**
     * Set postSubject.
     *
     * @param string $postSubject
     *
     * @return FrmDraftsHistory
     */
    public function setPostSubject($postSubject)
    {
        $this->postSubject = $postSubject;

        return $this;
    }

    /**
     * Get postSubject.
     *
     * @return string
     */
    public function getPostSubject()
    {
        return $this->postSubject;
    }

    /**
     * Set postMessage.
     *
     * @param string $postMessage
     *
     * @return FrmDraftsHistory
     */
    public function setPostMessage($postMessage)
    {
        $this->postMessage = $postMessage;

        return $this;
    }

    /**
     * Get postMessage.
     *
     * @return string
     */
    public function getPostMessage()
    {
        return $this->postMessage;
    }

    /**
     * Set draftDate.
     *
     * @param \DateTime $draftDate
     *
     * @return FrmDraftsHistory
     */
    public function setDraftDate($draftDate)
    {
        $this->draftDate = $draftDate;

        return $this;
    }

    /**
     * Get draftDate.
     *
     * @return \DateTime
     */
    public function getDraftDate()
    {
        return $this->draftDate;
    }
}
