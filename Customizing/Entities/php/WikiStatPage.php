<?php



/**
 * WikiStatPage
 */
class WikiStatPage
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
     * @var \DateTime
     */
    private $ts = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $intLinks = '0';

    /**
     * @var int
     */
    private $extLinks = '0';

    /**
     * @var int
     */
    private $footnotes = '0';

    /**
     * @var int
     */
    private $numRatings = '0';

    /**
     * @var int
     */
    private $numWords = '0';

    /**
     * @var int
     */
    private $numChars = '0';

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
     * @var bool
     */
    private $deleted = '0';


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return WikiStatPage
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
     * @return WikiStatPage
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
     * Set ts.
     *
     * @param \DateTime $ts
     *
     * @return WikiStatPage
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
     * Set intLinks.
     *
     * @param int $intLinks
     *
     * @return WikiStatPage
     */
    public function setIntLinks($intLinks)
    {
        $this->intLinks = $intLinks;

        return $this;
    }

    /**
     * Get intLinks.
     *
     * @return int
     */
    public function getIntLinks()
    {
        return $this->intLinks;
    }

    /**
     * Set extLinks.
     *
     * @param int $extLinks
     *
     * @return WikiStatPage
     */
    public function setExtLinks($extLinks)
    {
        $this->extLinks = $extLinks;

        return $this;
    }

    /**
     * Get extLinks.
     *
     * @return int
     */
    public function getExtLinks()
    {
        return $this->extLinks;
    }

    /**
     * Set footnotes.
     *
     * @param int $footnotes
     *
     * @return WikiStatPage
     */
    public function setFootnotes($footnotes)
    {
        $this->footnotes = $footnotes;

        return $this;
    }

    /**
     * Get footnotes.
     *
     * @return int
     */
    public function getFootnotes()
    {
        return $this->footnotes;
    }

    /**
     * Set numRatings.
     *
     * @param int $numRatings
     *
     * @return WikiStatPage
     */
    public function setNumRatings($numRatings)
    {
        $this->numRatings = $numRatings;

        return $this;
    }

    /**
     * Get numRatings.
     *
     * @return int
     */
    public function getNumRatings()
    {
        return $this->numRatings;
    }

    /**
     * Set numWords.
     *
     * @param int $numWords
     *
     * @return WikiStatPage
     */
    public function setNumWords($numWords)
    {
        $this->numWords = $numWords;

        return $this;
    }

    /**
     * Get numWords.
     *
     * @return int
     */
    public function getNumWords()
    {
        return $this->numWords;
    }

    /**
     * Set numChars.
     *
     * @param int $numChars
     *
     * @return WikiStatPage
     */
    public function setNumChars($numChars)
    {
        $this->numChars = $numChars;

        return $this;
    }

    /**
     * Get numChars.
     *
     * @return int
     */
    public function getNumChars()
    {
        return $this->numChars;
    }

    /**
     * Set avgRating.
     *
     * @param int $avgRating
     *
     * @return WikiStatPage
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
     * @return WikiStatPage
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
     * @return WikiStatPage
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

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return WikiStatPage
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
}
