<?php



/**
 * IlWikiPage
 */
class IlWikiPage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var bool|null
     */
    private $blocked;

    /**
     * @var bool
     */
    private $rating = '0';

    /**
     * @var bool|null
     */
    private $hideAdvMd = '0';


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlWikiPage
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
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return IlWikiPage
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
     * Set blocked.
     *
     * @param bool|null $blocked
     *
     * @return IlWikiPage
     */
    public function setBlocked($blocked = null)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked.
     *
     * @return bool|null
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Set rating.
     *
     * @param bool $rating
     *
     * @return IlWikiPage
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return bool
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set hideAdvMd.
     *
     * @param bool|null $hideAdvMd
     *
     * @return IlWikiPage
     */
    public function setHideAdvMd($hideAdvMd = null)
    {
        $this->hideAdvMd = $hideAdvMd;

        return $this;
    }

    /**
     * Get hideAdvMd.
     *
     * @return bool|null
     */
    public function getHideAdvMd()
    {
        return $this->hideAdvMd;
    }
}
