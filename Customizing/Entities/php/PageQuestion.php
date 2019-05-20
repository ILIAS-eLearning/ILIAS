<?php



/**
 * PageQuestion
 */
class PageQuestion
{
    /**
     * @var string
     */
    private $pageParentType = '';

    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var int
     */
    private $questionId = '0';

    /**
     * @var string
     */
    private $pageLang = '-';


    /**
     * Set pageParentType.
     *
     * @param string $pageParentType
     *
     * @return PageQuestion
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
     * @return PageQuestion
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
     * Set questionId.
     *
     * @param int $questionId
     *
     * @return PageQuestion
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * Get questionId.
     *
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set pageLang.
     *
     * @param string $pageLang
     *
     * @return PageQuestion
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
