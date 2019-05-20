<?php



/**
 * Notification
 */
class Notification
{
    /**
     * @var bool
     */
    private $type = '0';

    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime|null
     */
    private $lastMail;

    /**
     * @var int|null
     */
    private $pageId = '0';

    /**
     * @var bool|null
     */
    private $activated = '0';


    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return Notification
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return Notification
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set userId.
     *
     * @param int $userId
     *
     * @return Notification
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
     * Set lastMail.
     *
     * @param \DateTime|null $lastMail
     *
     * @return Notification
     */
    public function setLastMail($lastMail = null)
    {
        $this->lastMail = $lastMail;

        return $this;
    }

    /**
     * Get lastMail.
     *
     * @return \DateTime|null
     */
    public function getLastMail()
    {
        return $this->lastMail;
    }

    /**
     * Set pageId.
     *
     * @param int|null $pageId
     *
     * @return Notification
     */
    public function setPageId($pageId = null)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId.
     *
     * @return int|null
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set activated.
     *
     * @param bool|null $activated
     *
     * @return Notification
     */
    public function setActivated($activated = null)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated.
     *
     * @return bool|null
     */
    public function getActivated()
    {
        return $this->activated;
    }
}
