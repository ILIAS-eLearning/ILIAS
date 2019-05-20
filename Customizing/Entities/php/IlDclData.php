<?php



/**
 * IlDclData
 */
class IlDclData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $isOnline;

    /**
     * @var bool|null
     */
    private $rating;

    /**
     * @var bool|null
     */
    private $publicNotes;

    /**
     * @var bool|null
     */
    private $approval;

    /**
     * @var bool|null
     */
    private $notification;


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
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return IlDclData
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
     * Set rating.
     *
     * @param bool|null $rating
     *
     * @return IlDclData
     */
    public function setRating($rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return bool|null
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set publicNotes.
     *
     * @param bool|null $publicNotes
     *
     * @return IlDclData
     */
    public function setPublicNotes($publicNotes = null)
    {
        $this->publicNotes = $publicNotes;

        return $this;
    }

    /**
     * Get publicNotes.
     *
     * @return bool|null
     */
    public function getPublicNotes()
    {
        return $this->publicNotes;
    }

    /**
     * Set approval.
     *
     * @param bool|null $approval
     *
     * @return IlDclData
     */
    public function setApproval($approval = null)
    {
        $this->approval = $approval;

        return $this;
    }

    /**
     * Get approval.
     *
     * @return bool|null
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * Set notification.
     *
     * @param bool|null $notification
     *
     * @return IlDclData
     */
    public function setNotification($notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification.
     *
     * @return bool|null
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
