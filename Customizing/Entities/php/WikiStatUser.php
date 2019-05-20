<?php



/**
 * WikiStatUser
 */
class WikiStatUser
{
    /**
     * @var int
     */
    private $wikiId = '0';

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
    private $newPages = '0';

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
     * @return WikiStatUser
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return WikiStatUser
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
     * @return WikiStatUser
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
     * Set newPages.
     *
     * @param int $newPages
     *
     * @return WikiStatUser
     */
    public function setNewPages($newPages)
    {
        $this->newPages = $newPages;

        return $this;
    }

    /**
     * Get newPages.
     *
     * @return int
     */
    public function getNewPages()
    {
        return $this->newPages;
    }

    /**
     * Set tsDay.
     *
     * @param string|null $tsDay
     *
     * @return WikiStatUser
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
     * @return WikiStatUser
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
