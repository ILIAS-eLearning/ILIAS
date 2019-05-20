<?php



/**
 * ContentObject
 */
class ContentObject
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $defaultLayout = 'toc2win';

    /**
     * @var int
     */
    private $stylesheet = '0';

    /**
     * @var string|null
     */
    private $pageHeader = 'st_title';

    /**
     * @var string|null
     */
    private $isOnline = 'n';

    /**
     * @var string|null
     */
    private $tocActive = 'y';

    /**
     * @var string|null
     */
    private $lmMenuActive = 'y';

    /**
     * @var string|null
     */
    private $tocMode = 'chapters';

    /**
     * @var string|null
     */
    private $cleanFrames = 'n';

    /**
     * @var string|null
     */
    private $printViewActive = 'y';

    /**
     * @var string|null
     */
    private $numbering = 'n';

    /**
     * @var string|null
     */
    private $histUserComments = 'n';

    /**
     * @var string|null
     */
    private $publicAccessMode = 'complete';

    /**
     * @var string|null
     */
    private $publicHtmlFile;

    /**
     * @var string|null
     */
    private $publicXmlFile;

    /**
     * @var string|null
     */
    private $downloadsActive = 'n';

    /**
     * @var string|null
     */
    private $downloadsPublicActive = 'y';

    /**
     * @var int
     */
    private $headerPage = '0';

    /**
     * @var int
     */
    private $footerPage = '0';

    /**
     * @var string|null
     */
    private $noGloAppendix = 'n';

    /**
     * @var bool|null
     */
    private $layoutPerPage;

    /**
     * @var string|null
     */
    private $publicScormFile;

    /**
     * @var bool
     */
    private $rating = '0';

    /**
     * @var bool
     */
    private $hideHeadFootPrint = '0';

    /**
     * @var int
     */
    private $disableDefFeedback = '0';

    /**
     * @var bool|null
     */
    private $ratingPages = '0';

    /**
     * @var bool
     */
    private $progrIcons = '0';

    /**
     * @var bool
     */
    private $storeTries = '0';

    /**
     * @var bool
     */
    private $restrictForwNav = '0';

    /**
     * @var bool
     */
    private $forTranslation = '0';


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
     * Set defaultLayout.
     *
     * @param string|null $defaultLayout
     *
     * @return ContentObject
     */
    public function setDefaultLayout($defaultLayout = null)
    {
        $this->defaultLayout = $defaultLayout;

        return $this;
    }

    /**
     * Get defaultLayout.
     *
     * @return string|null
     */
    public function getDefaultLayout()
    {
        return $this->defaultLayout;
    }

    /**
     * Set stylesheet.
     *
     * @param int $stylesheet
     *
     * @return ContentObject
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;

        return $this;
    }

    /**
     * Get stylesheet.
     *
     * @return int
     */
    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    /**
     * Set pageHeader.
     *
     * @param string|null $pageHeader
     *
     * @return ContentObject
     */
    public function setPageHeader($pageHeader = null)
    {
        $this->pageHeader = $pageHeader;

        return $this;
    }

    /**
     * Get pageHeader.
     *
     * @return string|null
     */
    public function getPageHeader()
    {
        return $this->pageHeader;
    }

    /**
     * Set isOnline.
     *
     * @param string|null $isOnline
     *
     * @return ContentObject
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return string|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set tocActive.
     *
     * @param string|null $tocActive
     *
     * @return ContentObject
     */
    public function setTocActive($tocActive = null)
    {
        $this->tocActive = $tocActive;

        return $this;
    }

    /**
     * Get tocActive.
     *
     * @return string|null
     */
    public function getTocActive()
    {
        return $this->tocActive;
    }

    /**
     * Set lmMenuActive.
     *
     * @param string|null $lmMenuActive
     *
     * @return ContentObject
     */
    public function setLmMenuActive($lmMenuActive = null)
    {
        $this->lmMenuActive = $lmMenuActive;

        return $this;
    }

    /**
     * Get lmMenuActive.
     *
     * @return string|null
     */
    public function getLmMenuActive()
    {
        return $this->lmMenuActive;
    }

    /**
     * Set tocMode.
     *
     * @param string|null $tocMode
     *
     * @return ContentObject
     */
    public function setTocMode($tocMode = null)
    {
        $this->tocMode = $tocMode;

        return $this;
    }

    /**
     * Get tocMode.
     *
     * @return string|null
     */
    public function getTocMode()
    {
        return $this->tocMode;
    }

    /**
     * Set cleanFrames.
     *
     * @param string|null $cleanFrames
     *
     * @return ContentObject
     */
    public function setCleanFrames($cleanFrames = null)
    {
        $this->cleanFrames = $cleanFrames;

        return $this;
    }

    /**
     * Get cleanFrames.
     *
     * @return string|null
     */
    public function getCleanFrames()
    {
        return $this->cleanFrames;
    }

    /**
     * Set printViewActive.
     *
     * @param string|null $printViewActive
     *
     * @return ContentObject
     */
    public function setPrintViewActive($printViewActive = null)
    {
        $this->printViewActive = $printViewActive;

        return $this;
    }

    /**
     * Get printViewActive.
     *
     * @return string|null
     */
    public function getPrintViewActive()
    {
        return $this->printViewActive;
    }

    /**
     * Set numbering.
     *
     * @param string|null $numbering
     *
     * @return ContentObject
     */
    public function setNumbering($numbering = null)
    {
        $this->numbering = $numbering;

        return $this;
    }

    /**
     * Get numbering.
     *
     * @return string|null
     */
    public function getNumbering()
    {
        return $this->numbering;
    }

    /**
     * Set histUserComments.
     *
     * @param string|null $histUserComments
     *
     * @return ContentObject
     */
    public function setHistUserComments($histUserComments = null)
    {
        $this->histUserComments = $histUserComments;

        return $this;
    }

    /**
     * Get histUserComments.
     *
     * @return string|null
     */
    public function getHistUserComments()
    {
        return $this->histUserComments;
    }

    /**
     * Set publicAccessMode.
     *
     * @param string|null $publicAccessMode
     *
     * @return ContentObject
     */
    public function setPublicAccessMode($publicAccessMode = null)
    {
        $this->publicAccessMode = $publicAccessMode;

        return $this;
    }

    /**
     * Get publicAccessMode.
     *
     * @return string|null
     */
    public function getPublicAccessMode()
    {
        return $this->publicAccessMode;
    }

    /**
     * Set publicHtmlFile.
     *
     * @param string|null $publicHtmlFile
     *
     * @return ContentObject
     */
    public function setPublicHtmlFile($publicHtmlFile = null)
    {
        $this->publicHtmlFile = $publicHtmlFile;

        return $this;
    }

    /**
     * Get publicHtmlFile.
     *
     * @return string|null
     */
    public function getPublicHtmlFile()
    {
        return $this->publicHtmlFile;
    }

    /**
     * Set publicXmlFile.
     *
     * @param string|null $publicXmlFile
     *
     * @return ContentObject
     */
    public function setPublicXmlFile($publicXmlFile = null)
    {
        $this->publicXmlFile = $publicXmlFile;

        return $this;
    }

    /**
     * Get publicXmlFile.
     *
     * @return string|null
     */
    public function getPublicXmlFile()
    {
        return $this->publicXmlFile;
    }

    /**
     * Set downloadsActive.
     *
     * @param string|null $downloadsActive
     *
     * @return ContentObject
     */
    public function setDownloadsActive($downloadsActive = null)
    {
        $this->downloadsActive = $downloadsActive;

        return $this;
    }

    /**
     * Get downloadsActive.
     *
     * @return string|null
     */
    public function getDownloadsActive()
    {
        return $this->downloadsActive;
    }

    /**
     * Set downloadsPublicActive.
     *
     * @param string|null $downloadsPublicActive
     *
     * @return ContentObject
     */
    public function setDownloadsPublicActive($downloadsPublicActive = null)
    {
        $this->downloadsPublicActive = $downloadsPublicActive;

        return $this;
    }

    /**
     * Get downloadsPublicActive.
     *
     * @return string|null
     */
    public function getDownloadsPublicActive()
    {
        return $this->downloadsPublicActive;
    }

    /**
     * Set headerPage.
     *
     * @param int $headerPage
     *
     * @return ContentObject
     */
    public function setHeaderPage($headerPage)
    {
        $this->headerPage = $headerPage;

        return $this;
    }

    /**
     * Get headerPage.
     *
     * @return int
     */
    public function getHeaderPage()
    {
        return $this->headerPage;
    }

    /**
     * Set footerPage.
     *
     * @param int $footerPage
     *
     * @return ContentObject
     */
    public function setFooterPage($footerPage)
    {
        $this->footerPage = $footerPage;

        return $this;
    }

    /**
     * Get footerPage.
     *
     * @return int
     */
    public function getFooterPage()
    {
        return $this->footerPage;
    }

    /**
     * Set noGloAppendix.
     *
     * @param string|null $noGloAppendix
     *
     * @return ContentObject
     */
    public function setNoGloAppendix($noGloAppendix = null)
    {
        $this->noGloAppendix = $noGloAppendix;

        return $this;
    }

    /**
     * Get noGloAppendix.
     *
     * @return string|null
     */
    public function getNoGloAppendix()
    {
        return $this->noGloAppendix;
    }

    /**
     * Set layoutPerPage.
     *
     * @param bool|null $layoutPerPage
     *
     * @return ContentObject
     */
    public function setLayoutPerPage($layoutPerPage = null)
    {
        $this->layoutPerPage = $layoutPerPage;

        return $this;
    }

    /**
     * Get layoutPerPage.
     *
     * @return bool|null
     */
    public function getLayoutPerPage()
    {
        return $this->layoutPerPage;
    }

    /**
     * Set publicScormFile.
     *
     * @param string|null $publicScormFile
     *
     * @return ContentObject
     */
    public function setPublicScormFile($publicScormFile = null)
    {
        $this->publicScormFile = $publicScormFile;

        return $this;
    }

    /**
     * Get publicScormFile.
     *
     * @return string|null
     */
    public function getPublicScormFile()
    {
        return $this->publicScormFile;
    }

    /**
     * Set rating.
     *
     * @param bool $rating
     *
     * @return ContentObject
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
     * Set hideHeadFootPrint.
     *
     * @param bool $hideHeadFootPrint
     *
     * @return ContentObject
     */
    public function setHideHeadFootPrint($hideHeadFootPrint)
    {
        $this->hideHeadFootPrint = $hideHeadFootPrint;

        return $this;
    }

    /**
     * Get hideHeadFootPrint.
     *
     * @return bool
     */
    public function getHideHeadFootPrint()
    {
        return $this->hideHeadFootPrint;
    }

    /**
     * Set disableDefFeedback.
     *
     * @param int $disableDefFeedback
     *
     * @return ContentObject
     */
    public function setDisableDefFeedback($disableDefFeedback)
    {
        $this->disableDefFeedback = $disableDefFeedback;

        return $this;
    }

    /**
     * Get disableDefFeedback.
     *
     * @return int
     */
    public function getDisableDefFeedback()
    {
        return $this->disableDefFeedback;
    }

    /**
     * Set ratingPages.
     *
     * @param bool|null $ratingPages
     *
     * @return ContentObject
     */
    public function setRatingPages($ratingPages = null)
    {
        $this->ratingPages = $ratingPages;

        return $this;
    }

    /**
     * Get ratingPages.
     *
     * @return bool|null
     */
    public function getRatingPages()
    {
        return $this->ratingPages;
    }

    /**
     * Set progrIcons.
     *
     * @param bool $progrIcons
     *
     * @return ContentObject
     */
    public function setProgrIcons($progrIcons)
    {
        $this->progrIcons = $progrIcons;

        return $this;
    }

    /**
     * Get progrIcons.
     *
     * @return bool
     */
    public function getProgrIcons()
    {
        return $this->progrIcons;
    }

    /**
     * Set storeTries.
     *
     * @param bool $storeTries
     *
     * @return ContentObject
     */
    public function setStoreTries($storeTries)
    {
        $this->storeTries = $storeTries;

        return $this;
    }

    /**
     * Get storeTries.
     *
     * @return bool
     */
    public function getStoreTries()
    {
        return $this->storeTries;
    }

    /**
     * Set restrictForwNav.
     *
     * @param bool $restrictForwNav
     *
     * @return ContentObject
     */
    public function setRestrictForwNav($restrictForwNav)
    {
        $this->restrictForwNav = $restrictForwNav;

        return $this;
    }

    /**
     * Get restrictForwNav.
     *
     * @return bool
     */
    public function getRestrictForwNav()
    {
        return $this->restrictForwNav;
    }

    /**
     * Set forTranslation.
     *
     * @param bool $forTranslation
     *
     * @return ContentObject
     */
    public function setForTranslation($forTranslation)
    {
        $this->forTranslation = $forTranslation;

        return $this;
    }

    /**
     * Get forTranslation.
     *
     * @return bool
     */
    public function getForTranslation()
    {
        return $this->forTranslation;
    }
}
