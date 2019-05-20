<?php



/**
 * WikiPageTemplate
 */
class WikiPageTemplate
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var int
     */
    private $wpageId = '0';

    /**
     * @var bool
     */
    private $newPages = '0';

    /**
     * @var bool
     */
    private $addToPage = '0';


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return WikiPageTemplate
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
     * Set wpageId.
     *
     * @param int $wpageId
     *
     * @return WikiPageTemplate
     */
    public function setWpageId($wpageId)
    {
        $this->wpageId = $wpageId;

        return $this;
    }

    /**
     * Get wpageId.
     *
     * @return int
     */
    public function getWpageId()
    {
        return $this->wpageId;
    }

    /**
     * Set newPages.
     *
     * @param bool $newPages
     *
     * @return WikiPageTemplate
     */
    public function setNewPages($newPages)
    {
        $this->newPages = $newPages;

        return $this;
    }

    /**
     * Get newPages.
     *
     * @return bool
     */
    public function getNewPages()
    {
        return $this->newPages;
    }

    /**
     * Set addToPage.
     *
     * @param bool $addToPage
     *
     * @return WikiPageTemplate
     */
    public function setAddToPage($addToPage)
    {
        $this->addToPage = $addToPage;

        return $this;
    }

    /**
     * Get addToPage.
     *
     * @return bool
     */
    public function getAddToPage()
    {
        return $this->addToPage;
    }
}
