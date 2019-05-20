<?php



/**
 * IlNewsItem
 */
class IlNewsItem
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $priority = '1';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var int|null
     */
    private $contextObjId;

    /**
     * @var string|null
     */
    private $contextObjType;

    /**
     * @var int|null
     */
    private $contextSubObjId;

    /**
     * @var string|null
     */
    private $contextSubObjType;

    /**
     * @var string|null
     */
    private $contentType = 'text';

    /**
     * @var \DateTime|null
     */
    private $creationDate;

    /**
     * @var \DateTime|null
     */
    private $updateDate;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var string|null
     */
    private $visibility = 'users';

    /**
     * @var string|null
     */
    private $contentLong;

    /**
     * @var bool|null
     */
    private $contentIsLangVar = '0';

    /**
     * @var int|null
     */
    private $mobId;

    /**
     * @var string|null
     */
    private $playtime;

    /**
     * @var \DateTime|null
     */
    private $startDate;

    /**
     * @var \DateTime|null
     */
    private $endDate;

    /**
     * @var bool
     */
    private $contentTextIsLangVar = '0';

    /**
     * @var int
     */
    private $mobCntDownload = '0';

    /**
     * @var int
     */
    private $mobCntPlay = '0';

    /**
     * @var bool
     */
    private $contentHtml = '0';

    /**
     * @var int
     */
    private $updateUserId = '0';


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
     * Set priority.
     *
     * @param int|null $priority
     *
     * @return IlNewsItem
     */
    public function setPriority($priority = null)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return int|null
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlNewsItem
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return IlNewsItem
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set contextObjId.
     *
     * @param int|null $contextObjId
     *
     * @return IlNewsItem
     */
    public function setContextObjId($contextObjId = null)
    {
        $this->contextObjId = $contextObjId;

        return $this;
    }

    /**
     * Get contextObjId.
     *
     * @return int|null
     */
    public function getContextObjId()
    {
        return $this->contextObjId;
    }

    /**
     * Set contextObjType.
     *
     * @param string|null $contextObjType
     *
     * @return IlNewsItem
     */
    public function setContextObjType($contextObjType = null)
    {
        $this->contextObjType = $contextObjType;

        return $this;
    }

    /**
     * Get contextObjType.
     *
     * @return string|null
     */
    public function getContextObjType()
    {
        return $this->contextObjType;
    }

    /**
     * Set contextSubObjId.
     *
     * @param int|null $contextSubObjId
     *
     * @return IlNewsItem
     */
    public function setContextSubObjId($contextSubObjId = null)
    {
        $this->contextSubObjId = $contextSubObjId;

        return $this;
    }

    /**
     * Get contextSubObjId.
     *
     * @return int|null
     */
    public function getContextSubObjId()
    {
        return $this->contextSubObjId;
    }

    /**
     * Set contextSubObjType.
     *
     * @param string|null $contextSubObjType
     *
     * @return IlNewsItem
     */
    public function setContextSubObjType($contextSubObjType = null)
    {
        $this->contextSubObjType = $contextSubObjType;

        return $this;
    }

    /**
     * Get contextSubObjType.
     *
     * @return string|null
     */
    public function getContextSubObjType()
    {
        return $this->contextSubObjType;
    }

    /**
     * Set contentType.
     *
     * @param string|null $contentType
     *
     * @return IlNewsItem
     */
    public function setContentType($contentType = null)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get contentType.
     *
     * @return string|null
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime|null $creationDate
     *
     * @return IlNewsItem
     */
    public function setCreationDate($creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime|null $updateDate
     *
     * @return IlNewsItem
     */
    public function setUpdateDate($updateDate = null)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate.
     *
     * @return \DateTime|null
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set userId.
     *
     * @param int|null $userId
     *
     * @return IlNewsItem
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set visibility.
     *
     * @param string|null $visibility
     *
     * @return IlNewsItem
     */
    public function setVisibility($visibility = null)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility.
     *
     * @return string|null
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set contentLong.
     *
     * @param string|null $contentLong
     *
     * @return IlNewsItem
     */
    public function setContentLong($contentLong = null)
    {
        $this->contentLong = $contentLong;

        return $this;
    }

    /**
     * Get contentLong.
     *
     * @return string|null
     */
    public function getContentLong()
    {
        return $this->contentLong;
    }

    /**
     * Set contentIsLangVar.
     *
     * @param bool|null $contentIsLangVar
     *
     * @return IlNewsItem
     */
    public function setContentIsLangVar($contentIsLangVar = null)
    {
        $this->contentIsLangVar = $contentIsLangVar;

        return $this;
    }

    /**
     * Get contentIsLangVar.
     *
     * @return bool|null
     */
    public function getContentIsLangVar()
    {
        return $this->contentIsLangVar;
    }

    /**
     * Set mobId.
     *
     * @param int|null $mobId
     *
     * @return IlNewsItem
     */
    public function setMobId($mobId = null)
    {
        $this->mobId = $mobId;

        return $this;
    }

    /**
     * Get mobId.
     *
     * @return int|null
     */
    public function getMobId()
    {
        return $this->mobId;
    }

    /**
     * Set playtime.
     *
     * @param string|null $playtime
     *
     * @return IlNewsItem
     */
    public function setPlaytime($playtime = null)
    {
        $this->playtime = $playtime;

        return $this;
    }

    /**
     * Get playtime.
     *
     * @return string|null
     */
    public function getPlaytime()
    {
        return $this->playtime;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime|null $startDate
     *
     * @return IlNewsItem
     */
    public function setStartDate($startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime|null $endDate
     *
     * @return IlNewsItem
     */
    public function setEndDate($endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set contentTextIsLangVar.
     *
     * @param bool $contentTextIsLangVar
     *
     * @return IlNewsItem
     */
    public function setContentTextIsLangVar($contentTextIsLangVar)
    {
        $this->contentTextIsLangVar = $contentTextIsLangVar;

        return $this;
    }

    /**
     * Get contentTextIsLangVar.
     *
     * @return bool
     */
    public function getContentTextIsLangVar()
    {
        return $this->contentTextIsLangVar;
    }

    /**
     * Set mobCntDownload.
     *
     * @param int $mobCntDownload
     *
     * @return IlNewsItem
     */
    public function setMobCntDownload($mobCntDownload)
    {
        $this->mobCntDownload = $mobCntDownload;

        return $this;
    }

    /**
     * Get mobCntDownload.
     *
     * @return int
     */
    public function getMobCntDownload()
    {
        return $this->mobCntDownload;
    }

    /**
     * Set mobCntPlay.
     *
     * @param int $mobCntPlay
     *
     * @return IlNewsItem
     */
    public function setMobCntPlay($mobCntPlay)
    {
        $this->mobCntPlay = $mobCntPlay;

        return $this;
    }

    /**
     * Get mobCntPlay.
     *
     * @return int
     */
    public function getMobCntPlay()
    {
        return $this->mobCntPlay;
    }

    /**
     * Set contentHtml.
     *
     * @param bool $contentHtml
     *
     * @return IlNewsItem
     */
    public function setContentHtml($contentHtml)
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    /**
     * Get contentHtml.
     *
     * @return bool
     */
    public function getContentHtml()
    {
        return $this->contentHtml;
    }

    /**
     * Set updateUserId.
     *
     * @param int $updateUserId
     *
     * @return IlNewsItem
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
}
