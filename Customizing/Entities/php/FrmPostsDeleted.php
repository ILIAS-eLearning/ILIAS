<?php



/**
 * FrmPostsDeleted
 */
class FrmPostsDeleted
{
    /**
     * @var int
     */
    private $deletedId = '0';

    /**
     * @var \DateTime
     */
    private $deletedDate = '1970-01-01 00:00:00';

    /**
     * @var string
     */
    private $deletedBy = '';

    /**
     * @var string
     */
    private $forumTitle = '';

    /**
     * @var string
     */
    private $threadTitle = '';

    /**
     * @var string
     */
    private $postTitle = '';

    /**
     * @var string
     */
    private $postMessage;

    /**
     * @var \DateTime
     */
    private $postDate = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $threadId = '0';

    /**
     * @var int
     */
    private $forumId = '0';

    /**
     * @var int
     */
    private $posDisplayUserId = '0';

    /**
     * @var string|null
     */
    private $posUsrAlias;

    /**
     * @var bool
     */
    private $isThreadDeleted = '0';


    /**
     * Get deletedId.
     *
     * @return int
     */
    public function getDeletedId()
    {
        return $this->deletedId;
    }

    /**
     * Set deletedDate.
     *
     * @param \DateTime $deletedDate
     *
     * @return FrmPostsDeleted
     */
    public function setDeletedDate($deletedDate)
    {
        $this->deletedDate = $deletedDate;

        return $this;
    }

    /**
     * Get deletedDate.
     *
     * @return \DateTime
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
    }

    /**
     * Set deletedBy.
     *
     * @param string $deletedBy
     *
     * @return FrmPostsDeleted
     */
    public function setDeletedBy($deletedBy)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get deletedBy.
     *
     * @return string
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set forumTitle.
     *
     * @param string $forumTitle
     *
     * @return FrmPostsDeleted
     */
    public function setForumTitle($forumTitle)
    {
        $this->forumTitle = $forumTitle;

        return $this;
    }

    /**
     * Get forumTitle.
     *
     * @return string
     */
    public function getForumTitle()
    {
        return $this->forumTitle;
    }

    /**
     * Set threadTitle.
     *
     * @param string $threadTitle
     *
     * @return FrmPostsDeleted
     */
    public function setThreadTitle($threadTitle)
    {
        $this->threadTitle = $threadTitle;

        return $this;
    }

    /**
     * Get threadTitle.
     *
     * @return string
     */
    public function getThreadTitle()
    {
        return $this->threadTitle;
    }

    /**
     * Set postTitle.
     *
     * @param string $postTitle
     *
     * @return FrmPostsDeleted
     */
    public function setPostTitle($postTitle)
    {
        $this->postTitle = $postTitle;

        return $this;
    }

    /**
     * Get postTitle.
     *
     * @return string
     */
    public function getPostTitle()
    {
        return $this->postTitle;
    }

    /**
     * Set postMessage.
     *
     * @param string $postMessage
     *
     * @return FrmPostsDeleted
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
     * Set postDate.
     *
     * @param \DateTime $postDate
     *
     * @return FrmPostsDeleted
     */
    public function setPostDate($postDate)
    {
        $this->postDate = $postDate;

        return $this;
    }

    /**
     * Get postDate.
     *
     * @return \DateTime
     */
    public function getPostDate()
    {
        return $this->postDate;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return FrmPostsDeleted
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return FrmPostsDeleted
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set threadId.
     *
     * @param int $threadId
     *
     * @return FrmPostsDeleted
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * Get threadId.
     *
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Set forumId.
     *
     * @param int $forumId
     *
     * @return FrmPostsDeleted
     */
    public function setForumId($forumId)
    {
        $this->forumId = $forumId;

        return $this;
    }

    /**
     * Get forumId.
     *
     * @return int
     */
    public function getForumId()
    {
        return $this->forumId;
    }

    /**
     * Set posDisplayUserId.
     *
     * @param int $posDisplayUserId
     *
     * @return FrmPostsDeleted
     */
    public function setPosDisplayUserId($posDisplayUserId)
    {
        $this->posDisplayUserId = $posDisplayUserId;

        return $this;
    }

    /**
     * Get posDisplayUserId.
     *
     * @return int
     */
    public function getPosDisplayUserId()
    {
        return $this->posDisplayUserId;
    }

    /**
     * Set posUsrAlias.
     *
     * @param string|null $posUsrAlias
     *
     * @return FrmPostsDeleted
     */
    public function setPosUsrAlias($posUsrAlias = null)
    {
        $this->posUsrAlias = $posUsrAlias;

        return $this;
    }

    /**
     * Get posUsrAlias.
     *
     * @return string|null
     */
    public function getPosUsrAlias()
    {
        return $this->posUsrAlias;
    }

    /**
     * Set isThreadDeleted.
     *
     * @param bool $isThreadDeleted
     *
     * @return FrmPostsDeleted
     */
    public function setIsThreadDeleted($isThreadDeleted)
    {
        $this->isThreadDeleted = $isThreadDeleted;

        return $this;
    }

    /**
     * Get isThreadDeleted.
     *
     * @return bool
     */
    public function getIsThreadDeleted()
    {
        return $this->isThreadDeleted;
    }
}
