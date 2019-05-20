<?php



/**
 * IlBlog
 */
class IlBlog
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $bgColor;

    /**
     * @var string|null
     */
    private $fontColor;

    /**
     * @var string|null
     */
    private $img;

    /**
     * @var bool|null
     */
    private $ppic;

    /**
     * @var bool|null
     */
    private $rssActive = '0';

    /**
     * @var bool|null
     */
    private $approval = '0';

    /**
     * @var bool|null
     */
    private $absShorten = '0';

    /**
     * @var int|null
     */
    private $absShortenLen = '0';

    /**
     * @var bool|null
     */
    private $absImage = '0';

    /**
     * @var int|null
     */
    private $absImgWidth = '0';

    /**
     * @var int|null
     */
    private $absImgHeight = '0';

    /**
     * @var bool
     */
    private $keywords = '1';

    /**
     * @var bool
     */
    private $authors = '1';

    /**
     * @var bool
     */
    private $navMode = '1';

    /**
     * @var int
     */
    private $navListPost = '10';

    /**
     * @var int|null
     */
    private $navListMon = '0';

    /**
     * @var int|null
     */
    private $ovPost = '0';

    /**
     * @var string|null
     */
    private $navOrder;

    /**
     * @var int|null
     */
    private $navListMonWithPost = '3';


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
     * Set bgColor.
     *
     * @param string|null $bgColor
     *
     * @return IlBlog
     */
    public function setBgColor($bgColor = null)
    {
        $this->bgColor = $bgColor;

        return $this;
    }

    /**
     * Get bgColor.
     *
     * @return string|null
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * Set fontColor.
     *
     * @param string|null $fontColor
     *
     * @return IlBlog
     */
    public function setFontColor($fontColor = null)
    {
        $this->fontColor = $fontColor;

        return $this;
    }

    /**
     * Get fontColor.
     *
     * @return string|null
     */
    public function getFontColor()
    {
        return $this->fontColor;
    }

    /**
     * Set img.
     *
     * @param string|null $img
     *
     * @return IlBlog
     */
    public function setImg($img = null)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img.
     *
     * @return string|null
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set ppic.
     *
     * @param bool|null $ppic
     *
     * @return IlBlog
     */
    public function setPpic($ppic = null)
    {
        $this->ppic = $ppic;

        return $this;
    }

    /**
     * Get ppic.
     *
     * @return bool|null
     */
    public function getPpic()
    {
        return $this->ppic;
    }

    /**
     * Set rssActive.
     *
     * @param bool|null $rssActive
     *
     * @return IlBlog
     */
    public function setRssActive($rssActive = null)
    {
        $this->rssActive = $rssActive;

        return $this;
    }

    /**
     * Get rssActive.
     *
     * @return bool|null
     */
    public function getRssActive()
    {
        return $this->rssActive;
    }

    /**
     * Set approval.
     *
     * @param bool|null $approval
     *
     * @return IlBlog
     */
    public function setApproval($approval = null)
    {
        $this->approval = $approval;

        return $this;
    }

    /**
     * Get approval.
     *
     * @return bool|null
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * Set absShorten.
     *
     * @param bool|null $absShorten
     *
     * @return IlBlog
     */
    public function setAbsShorten($absShorten = null)
    {
        $this->absShorten = $absShorten;

        return $this;
    }

    /**
     * Get absShorten.
     *
     * @return bool|null
     */
    public function getAbsShorten()
    {
        return $this->absShorten;
    }

    /**
     * Set absShortenLen.
     *
     * @param int|null $absShortenLen
     *
     * @return IlBlog
     */
    public function setAbsShortenLen($absShortenLen = null)
    {
        $this->absShortenLen = $absShortenLen;

        return $this;
    }

    /**
     * Get absShortenLen.
     *
     * @return int|null
     */
    public function getAbsShortenLen()
    {
        return $this->absShortenLen;
    }

    /**
     * Set absImage.
     *
     * @param bool|null $absImage
     *
     * @return IlBlog
     */
    public function setAbsImage($absImage = null)
    {
        $this->absImage = $absImage;

        return $this;
    }

    /**
     * Get absImage.
     *
     * @return bool|null
     */
    public function getAbsImage()
    {
        return $this->absImage;
    }

    /**
     * Set absImgWidth.
     *
     * @param int|null $absImgWidth
     *
     * @return IlBlog
     */
    public function setAbsImgWidth($absImgWidth = null)
    {
        $this->absImgWidth = $absImgWidth;

        return $this;
    }

    /**
     * Get absImgWidth.
     *
     * @return int|null
     */
    public function getAbsImgWidth()
    {
        return $this->absImgWidth;
    }

    /**
     * Set absImgHeight.
     *
     * @param int|null $absImgHeight
     *
     * @return IlBlog
     */
    public function setAbsImgHeight($absImgHeight = null)
    {
        $this->absImgHeight = $absImgHeight;

        return $this;
    }

    /**
     * Get absImgHeight.
     *
     * @return int|null
     */
    public function getAbsImgHeight()
    {
        return $this->absImgHeight;
    }

    /**
     * Set keywords.
     *
     * @param bool $keywords
     *
     * @return IlBlog
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords.
     *
     * @return bool
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set authors.
     *
     * @param bool $authors
     *
     * @return IlBlog
     */
    public function setAuthors($authors)
    {
        $this->authors = $authors;

        return $this;
    }

    /**
     * Get authors.
     *
     * @return bool
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set navMode.
     *
     * @param bool $navMode
     *
     * @return IlBlog
     */
    public function setNavMode($navMode)
    {
        $this->navMode = $navMode;

        return $this;
    }

    /**
     * Get navMode.
     *
     * @return bool
     */
    public function getNavMode()
    {
        return $this->navMode;
    }

    /**
     * Set navListPost.
     *
     * @param int $navListPost
     *
     * @return IlBlog
     */
    public function setNavListPost($navListPost)
    {
        $this->navListPost = $navListPost;

        return $this;
    }

    /**
     * Get navListPost.
     *
     * @return int
     */
    public function getNavListPost()
    {
        return $this->navListPost;
    }

    /**
     * Set navListMon.
     *
     * @param int|null $navListMon
     *
     * @return IlBlog
     */
    public function setNavListMon($navListMon = null)
    {
        $this->navListMon = $navListMon;

        return $this;
    }

    /**
     * Get navListMon.
     *
     * @return int|null
     */
    public function getNavListMon()
    {
        return $this->navListMon;
    }

    /**
     * Set ovPost.
     *
     * @param int|null $ovPost
     *
     * @return IlBlog
     */
    public function setOvPost($ovPost = null)
    {
        $this->ovPost = $ovPost;

        return $this;
    }

    /**
     * Get ovPost.
     *
     * @return int|null
     */
    public function getOvPost()
    {
        return $this->ovPost;
    }

    /**
     * Set navOrder.
     *
     * @param string|null $navOrder
     *
     * @return IlBlog
     */
    public function setNavOrder($navOrder = null)
    {
        $this->navOrder = $navOrder;

        return $this;
    }

    /**
     * Get navOrder.
     *
     * @return string|null
     */
    public function getNavOrder()
    {
        return $this->navOrder;
    }

    /**
     * Set navListMonWithPost.
     *
     * @param int|null $navListMonWithPost
     *
     * @return IlBlog
     */
    public function setNavListMonWithPost($navListMonWithPost = null)
    {
        $this->navListMonWithPost = $navListMonWithPost;

        return $this;
    }

    /**
     * Get navListMonWithPost.
     *
     * @return int|null
     */
    public function getNavListMonWithPost()
    {
        return $this->navListMonWithPost;
    }
}
