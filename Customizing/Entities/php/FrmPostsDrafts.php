<?php



/**
 * FrmPostsDrafts
 */
class FrmPostsDrafts
{
    /**
     * @var int
     */
    private $draftId = '0';

    /**
     * @var int
     */
    private $postId = '0';

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
    private $postAuthorId = '0';

    /**
     * @var string
     */
    private $postSubject = '';

    /**
     * @var string
     */
    private $postMessage;

    /**
     * @var bool
     */
    private $postNotify = '0';

    /**
     * @var \DateTime
     */
    private $postDate = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $postUpdate = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $updateUserId = '0';

    /**
     * @var string|null
     */
    private $postUserAlias;

    /**
     * @var int
     */
    private $posDisplayUsrId = '0';

    /**
     * @var bool
     */
    private $notify = '0';


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
     * Set postId.
     *
     * @param int $postId
     *
     * @return FrmPostsDrafts
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;

        return $this;
    }

    /**
     * Get postId.
     *
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Set threadId.
     *
     * @param int $threadId
     *
     * @return FrmPostsDrafts
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
     * @return FrmPostsDrafts
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
     * Set postAuthorId.
     *
     * @param int $postAuthorId
     *
     * @return FrmPostsDrafts
     */
    public function setPostAuthorId($postAuthorId)
    {
        $this->postAuthorId = $postAuthorId;

        return $this;
    }

    /**
     * Get postAuthorId.
     *
     * @return int
     */
    public function getPostAuthorId()
    {
        return $this->postAuthorId;
    }

    /**
     * Set postSubject.
     *
     * @param string $postSubject
     *
     * @return FrmPostsDrafts
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
     * @return FrmPostsDrafts
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
     * Set postNotify.
     *
     * @param bool $postNotify
     *
     * @return FrmPostsDrafts
     */
    public function setPostNotify($postNotify)
    {
        $this->postNotify = $postNotify;

        return $this;
    }

    /**
     * Get postNotify.
     *
     * @return bool
     */
    public function getPostNotify()
    {
        return $this->postNotify;
    }

    /**
     * Set postDate.
     *
     * @param \DateTime $postDate
     *
     * @return FrmPostsDrafts
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
     * Set postUpdate.
     *
     * @param \DateTime $postUpdate
     *
     * @return FrmPostsDrafts
     */
    public function setPostUpdate($postUpdate)
    {
        $this->postUpdate = $postUpdate;

        return $this;
    }

    /**
     * Get postUpdate.
     *
     * @return \DateTime
     */
    public function getPostUpdate()
    {
        return $this->postUpdate;
    }

    /**
     * Set updateUserId.
     *
     * @param int $updateUserId
     *
     * @return FrmPostsDrafts
     */
    public function setUpdateUserId($updateUserId)
    {
        $this->updateUserId = $updateUserId;

        return $this;
    }

    /**
     * Get updateUserId.
     *
     * @return int
     */
    public function getUpdateUserId()
    {
        return $this->updateUserId;
    }

    /**
     * Set postUserAlias.
     *
     * @param string|null $postUserAlias
     *
     * @return FrmPostsDrafts
     */
    public function setPostUserAlias($postUserAlias = null)
    {
        $this->postUserAlias = $postUserAlias;

        return $this;
    }

    /**
     * Get postUserAlias.
     *
     * @return string|null
     */
    public function getPostUserAlias()
    {
        return $this->postUserAlias;
    }

    /**
     * Set posDisplayUsrId.
     *
     * @param int $posDisplayUsrId
     *
     * @return FrmPostsDrafts
     */
    public function setPosDisplayUsrId($posDisplayUsrId)
    {
        $this->posDisplayUsrId = $posDisplayUsrId;

        return $this;
    }

    /**
     * Get posDisplayUsrId.
     *
     * @return int
     */
    public function getPosDisplayUsrId()
    {
        return $this->posDisplayUsrId;
    }

    /**
     * Set notify.
     *
     * @param bool $notify
     *
     * @return FrmPostsDrafts
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * Get notify.
     *
     * @return bool
     */
    public function getNotify()
    {
        return $this->notify;
    }
}
