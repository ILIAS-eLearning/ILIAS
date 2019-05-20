<?php



/**
 * WikiStatPageUser
 */
class WikiStatPageUser
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime
     */
    private $ts = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $changes = '0';

    /**
     * @var int
     */
    private $readEvents = '0';

    /**
     * @var string|null
     */
    private $tsDay;

    /**
     * @var bool|null
     */
    private $tsHour;


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return WikiStatPageUser
     */
    public function setWikiId($wikiId)
    {
        $this->wikiId = $wikiId;

        return $this;
    }

    /**
     * Get wikiId.
     *
     * @return int
     */
    public function getWikiId()
    {
        return $this->wikiId;
    }

    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return WikiStatPageUser
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return WikiStatPageUser
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
     * Set ts.
     *
     * @param \DateTime $ts
     *
     * @return WikiStatPageUser
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set changes.
     *
     * @param int $changes
     *
     * @return WikiStatPageUser
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * Get changes.
     *
     * @return int
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Set readEvents.
     *
     * @param int $readEvents
     *
     * @return WikiStatPageUser
     */
    public function setReadEvents($readEvents)
    {
        $this->readEvents = $readEvents;

        return $this;
    }

    /**
     * Get readEvents.
     *
     * @return int
     */
    public function getReadEvents()
    {
        return $this->readEvents;
    }

    /**
     * Set tsDay.
     *
     * @param string|null $tsDay
     *
     * @return WikiStatPageUser
     */
    public function setTsDay($tsDay = null)
    {
        $this->tsDay = $tsDay;

        return $this;
    }

    /**
     * Get tsDay.
     *
     * @return string|null
     */
    public function getTsDay()
    {
        return $this->tsDay;
    }

    /**
     * Set tsHour.
     *
     * @param bool|null $tsHour
     *
     * @return WikiStatPageUser
     */
    public function setTsHour($tsHour = null)
    {
        $this->tsHour = $tsHour;

        return $this;
    }

    /**
     * Get tsHour.
     *
     * @return bool|null
     */
    public function getTsHour()
    {
        return $this->tsHour;
    }
}
