<?php



/**
 * IlWikiData
 */
class IlWikiData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $startpage;

    /**
     * @var string|null
     */
    private $short;

    /**
     * @var bool|null
     */
    private $isOnline = '0';

    /**
     * @var bool|null
     */
    private $rating = '0';

    /**
     * @var string|null
     */
    private $introduction;

    /**
     * @var bool|null
     */
    private $publicNotes = '1';

    /**
     * @var bool|null
     */
    private $impPages;

    /**
     * @var bool|null
     */
    private $pageToc;

    /**
     * @var bool
     */
    private $ratingSide = '0';

    /**
     * @var bool
     */
    private $ratingNew = '0';

    /**
     * @var bool
     */
    private $ratingExt = '0';

    /**
     * @var bool|null
     */
    private $ratingOverall = '0';

    /**
     * @var bool
     */
    private $emptyPageTempl = '1';

    /**
     * @var bool|null
     */
    private $linkMdValues = '0';


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
     * Set startpage.
     *
     * @param string|null $startpage
     *
     * @return IlWikiData
     */
    public function setStartpage($startpage = null)
    {
        $this->startpage = $startpage;

        return $this;
    }

    /**
     * Get startpage.
     *
     * @return string|null
     */
    public function getStartpage()
    {
        return $this->startpage;
    }

    /**
     * Set short.
     *
     * @param string|null $short
     *
     * @return IlWikiData
     */
    public function setShort($short = null)
    {
        $this->short = $short;

        return $this;
    }

    /**
     * Get short.
     *
     * @return string|null
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return IlWikiData
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set rating.
     *
     * @param bool|null $rating
     *
     * @return IlWikiData
     */
    public function setRating($rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return bool|null
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set introduction.
     *
     * @param string|null $introduction
     *
     * @return IlWikiData
     */
    public function setIntroduction($introduction = null)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction.
     *
     * @return string|null
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set publicNotes.
     *
     * @param bool|null $publicNotes
     *
     * @return IlWikiData
     */
    public function setPublicNotes($publicNotes = null)
    {
        $this->publicNotes = $publicNotes;

        return $this;
    }

    /**
     * Get publicNotes.
     *
     * @return bool|null
     */
    public function getPublicNotes()
    {
        return $this->publicNotes;
    }

    /**
     * Set impPages.
     *
     * @param bool|null $impPages
     *
     * @return IlWikiData
     */
    public function setImpPages($impPages = null)
    {
        $this->impPages = $impPages;

        return $this;
    }

    /**
     * Get impPages.
     *
     * @return bool|null
     */
    public function getImpPages()
    {
        return $this->impPages;
    }

    /**
     * Set pageToc.
     *
     * @param bool|null $pageToc
     *
     * @return IlWikiData
     */
    public function setPageToc($pageToc = null)
    {
        $this->pageToc = $pageToc;

        return $this;
    }

    /**
     * Get pageToc.
     *
     * @return bool|null
     */
    public function getPageToc()
    {
        return $this->pageToc;
    }

    /**
     * Set ratingSide.
     *
     * @param bool $ratingSide
     *
     * @return IlWikiData
     */
    public function setRatingSide($ratingSide)
    {
        $this->ratingSide = $ratingSide;

        return $this;
    }

    /**
     * Get ratingSide.
     *
     * @return bool
     */
    public function getRatingSide()
    {
        return $this->ratingSide;
    }

    /**
     * Set ratingNew.
     *
     * @param bool $ratingNew
     *
     * @return IlWikiData
     */
    public function setRatingNew($ratingNew)
    {
        $this->ratingNew = $ratingNew;

        return $this;
    }

    /**
     * Get ratingNew.
     *
     * @return bool
     */
    public function getRatingNew()
    {
        return $this->ratingNew;
    }

    /**
     * Set ratingExt.
     *
     * @param bool $ratingExt
     *
     * @return IlWikiData
     */
    public function setRatingExt($ratingExt)
    {
        $this->ratingExt = $ratingExt;

        return $this;
    }

    /**
     * Get ratingExt.
     *
     * @return bool
     */
    public function getRatingExt()
    {
        return $this->ratingExt;
    }

    /**
     * Set ratingOverall.
     *
     * @param bool|null $ratingOverall
     *
     * @return IlWikiData
     */
    public function setRatingOverall($ratingOverall = null)
    {
        $this->ratingOverall = $ratingOverall;

        return $this;
    }

    /**
     * Get ratingOverall.
     *
     * @return bool|null
     */
    public function getRatingOverall()
    {
        return $this->ratingOverall;
    }

    /**
     * Set emptyPageTempl.
     *
     * @param bool $emptyPageTempl
     *
     * @return IlWikiData
     */
    public function setEmptyPageTempl($emptyPageTempl)
    {
        $this->emptyPageTempl = $emptyPageTempl;

        return $this;
    }

    /**
     * Get emptyPageTempl.
     *
     * @return bool
     */
    public function getEmptyPageTempl()
    {
        return $this->emptyPageTempl;
    }

    /**
     * Set linkMdValues.
     *
     * @param bool|null $linkMdValues
     *
     * @return IlWikiData
     */
    public function setLinkMdValues($linkMdValues = null)
    {
        $this->linkMdValues = $linkMdValues;

        return $this;
    }

    /**
     * Get linkMdValues.
     *
     * @return bool|null
     */
    public function getLinkMdValues()
    {
        return $this->linkMdValues;
    }
}
