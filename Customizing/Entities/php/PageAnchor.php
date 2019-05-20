<?php



/**
 * PageAnchor
 */
class PageAnchor
{
    /**
     * @var string
     */
    private $pageParentType = ' ';

    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string
     */
    private $anchorName = ' ';

    /**
     * @var string
     */
    private $pageLang = '-';


    /**
     * Set pageParentType.
     *
     * @param string $pageParentType
     *
     * @return PageAnchor
     */
    public function setPageParentType($pageParentType)
    {
        $this->pageParentType = $pageParentType;

        return $this;
    }

    /**
     * Get pageParentType.
     *
     * @return string
     */
    public function getPageParentType()
    {
        return $this->pageParentType;
    }

    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return PageAnchor
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
     * Set anchorName.
     *
     * @param string $anchorName
     *
     * @return PageAnchor
     */
    public function setAnchorName($anchorName)
    {
        $this->anchorName = $anchorName;

        return $this;
    }

    /**
     * Get anchorName.
     *
     * @return string
     */
    public function getAnchorName()
    {
        return $this->anchorName;
    }

    /**
     * Set pageLang.
     *
     * @param string $pageLang
     *
     * @return PageAnchor
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
