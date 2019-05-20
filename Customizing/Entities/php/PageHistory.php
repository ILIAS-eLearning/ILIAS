<?php



/**
 * PageHistory
 */
class PageHistory
{
    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string
     */
    private $parentType = ' ';

    /**
     * @var \DateTime
     */
    private $hdate = '1970-01-01 00:00:00';

    /**
     * @var string
     */
    private $lang = '-';

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var int|null
     */
    private $nr;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $iliasVersion;


    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return PageHistory
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * Get pageId.
     *
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * Set parentType.
     *
     * @param string $parentType
     *
     * @return PageHistory
     */
    public function setParentType($parentType)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set hdate.
     *
     * @param \DateTime $hdate
     *
     * @return PageHistory
     */
    public function setHdate($hdate)
    {
        $this->hdate = $hdate;

        return $this;
    }

    /**
     * Get hdate.
     *
     * @return \DateTime
     */
    public function getHdate()
    {
        return $this->hdate;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return PageHistory
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return PageHistory
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set nr.
     *
     * @param int|null $nr
     *
     * @return PageHistory
     */
    public function setNr($nr = null)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int|null
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set userId.
     *
     * @param int|null $userId
     *
     * @return PageHistory
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
     * Set content.
     *
     * @param string|null $content
     *
     * @return PageHistory
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
     * Set iliasVersion.
     *
     * @param string|null $iliasVersion
     *
     * @return PageHistory
     */
    public function setIliasVersion($iliasVersion = null)
    {
        $this->iliasVersion = $iliasVersion;

        return $this;
    }

    /**
     * Get iliasVersion.
     *
     * @return string|null
     */
    public function getIliasVersion()
    {
        return $this->iliasVersion;
    }
}
