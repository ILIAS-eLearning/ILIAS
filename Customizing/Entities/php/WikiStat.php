<?php



/**
 * WikiStat
 */
class WikiStat
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var \DateTime
     */
    private $ts = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $numPages = '0';

    /**
     * @var int
     */
    private $delPages = '0';

    /**
     * @var int
     */
    private $avgRating = '0';

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
     * @return WikiStat
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
     * Set ts.
     *
     * @param \DateTime $ts
     *
     * @return WikiStat
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
     * Set numPages.
     *
     * @param int $numPages
     *
     * @return WikiStat
     */
    public function setNumPages($numPages)
    {
        $this->numPages = $numPages;

        return $this;
    }

    /**
     * Get numPages.
     *
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * Set delPages.
     *
     * @param int $delPages
     *
     * @return WikiStat
     */
    public function setDelPages($delPages)
    {
        $this->delPages = $delPages;

        return $this;
    }

    /**
     * Get delPages.
     *
     * @return int
     */
    public function getDelPages()
    {
        return $this->delPages;
    }

    /**
     * Set avgRating.
     *
     * @param int $avgRating
     *
     * @return WikiStat
     */
    public function setAvgRating($avgRating)
    {
        $this->avgRating = $avgRating;

        return $this;
    }

    /**
     * Get avgRating.
     *
     * @return int
     */
    public function getAvgRating()
    {
        return $this->avgRating;
    }

    /**
     * Set tsDay.
     *
     * @param string|null $tsDay
     *
     * @return WikiStat
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
     * @return WikiStat
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
