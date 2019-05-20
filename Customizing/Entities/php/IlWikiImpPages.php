<?php



/**
 * IlWikiImpPages
 */
class IlWikiImpPages
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
     * @var int
     */
    private $ord = '0';

    /**
     * @var bool
     */
    private $indent = '0';


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return IlWikiImpPages
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
     * @return IlWikiImpPages
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
     * Set ord.
     *
     * @param int $ord
     *
     * @return IlWikiImpPages
     */
    public function setOrd($ord)
    {
        $this->ord = $ord;

        return $this;
    }

    /**
     * Get ord.
     *
     * @return int
     */
    public function getOrd()
    {
        return $this->ord;
    }

    /**
     * Set indent.
     *
     * @param bool $indent
     *
     * @return IlWikiImpPages
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;

        return $this;
    }

    /**
     * Get indent.
     *
     * @return bool
     */
    public function getIndent()
    {
        return $this->indent;
    }
}
