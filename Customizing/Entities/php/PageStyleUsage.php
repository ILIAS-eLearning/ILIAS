<?php



/**
 * PageStyleUsage
 */
class PageStyleUsage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string
     */
    private $pageType = '';

    /**
     * @var int
     */
    private $pageNr = '0';

    /**
     * @var bool
     */
    private $template = '0';

    /**
     * @var string|null
     */
    private $stype;

    /**
     * @var string|null
     */
    private $sname;

    /**
     * @var string
     */
    private $pageLang = '-';


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
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return PageStyleUsage
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
     * Set pageType.
     *
     * @param string $pageType
     *
     * @return PageStyleUsage
     */
    public function setPageType($pageType)
    {
        $this->pageType = $pageType;

        return $this;
    }

    /**
     * Get pageType.
     *
     * @return string
     */
    public function getPageType()
    {
        return $this->pageType;
    }

    /**
     * Set pageNr.
     *
     * @param int $pageNr
     *
     * @return PageStyleUsage
     */
    public function setPageNr($pageNr)
    {
        $this->pageNr = $pageNr;

        return $this;
    }

    /**
     * Get pageNr.
     *
     * @return int
     */
    public function getPageNr()
    {
        return $this->pageNr;
    }

    /**
     * Set template.
     *
     * @param bool $template
     *
     * @return PageStyleUsage
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template.
     *
     * @return bool
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set stype.
     *
     * @param string|null $stype
     *
     * @return PageStyleUsage
     */
    public function setStype($stype = null)
    {
        $this->stype = $stype;

        return $this;
    }

    /**
     * Get stype.
     *
     * @return string|null
     */
    public function getStype()
    {
        return $this->stype;
    }

    /**
     * Set sname.
     *
     * @param string|null $sname
     *
     * @return PageStyleUsage
     */
    public function setSname($sname = null)
    {
        $this->sname = $sname;

        return $this;
    }

    /**
     * Get sname.
     *
     * @return string|null
     */
    public function getSname()
    {
        return $this->sname;
    }

    /**
     * Set pageLang.
     *
     * @param string $pageLang
     *
     * @return PageStyleUsage
     */
    public function setPageLang($pageLang)
    {
        $this->pageLang = $pageLang;

        return $this;
    }

    /**
     * Get pageLang.
     *
     * @return string
     */
    public function getPageLang()
    {
        return $this->pageLang;
    }
}
