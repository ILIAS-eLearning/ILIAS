<?php



/**
 * UsrSearch
 */
class UsrSearch
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var bool
     */
    private $searchType = '0';

    /**
     * @var string|null
     */
    private $searchResult;

    /**
     * @var string|null
     */
    private $checked;

    /**
     * @var string|null
     */
    private $failed;

    /**
     * @var bool
     */
    private $page = '0';

    /**
     * @var string|null
     */
    private $query;

    /**
     * @var int|null
     */
    private $root = '1';

    /**
     * @var string|null
     */
    private $itemFilter;

    /**
     * @var string|null
     */
    private $mimeFilter;

    /**
     * @var string|null
     */
    private $creationFilter;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return UsrSearch
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set searchType.
     *
     * @param bool $searchType
     *
     * @return UsrSearch
     */
    public function setSearchType($searchType)
    {
        $this->searchType = $searchType;

        return $this;
    }

    /**
     * Get searchType.
     *
     * @return bool
     */
    public function getSearchType()
    {
        return $this->searchType;
    }

    /**
     * Set searchResult.
     *
     * @param string|null $searchResult
     *
     * @return UsrSearch
     */
    public function setSearchResult($searchResult = null)
    {
        $this->searchResult = $searchResult;

        return $this;
    }

    /**
     * Get searchResult.
     *
     * @return string|null
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * Set checked.
     *
     * @param string|null $checked
     *
     * @return UsrSearch
     */
    public function setChecked($checked = null)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked.
     *
     * @return string|null
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set failed.
     *
     * @param string|null $failed
     *
     * @return UsrSearch
     */
    public function setFailed($failed = null)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed.
     *
     * @return string|null
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set page.
     *
     * @param bool $page
     *
     * @return UsrSearch
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page.
     *
     * @return bool
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set query.
     *
     * @param string|null $query
     *
     * @return UsrSearch
     */
    public function setQuery($query = null)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query.
     *
     * @return string|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set root.
     *
     * @param int|null $root
     *
     * @return UsrSearch
     */
    public function setRoot($root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root.
     *
     * @return int|null
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set itemFilter.
     *
     * @param string|null $itemFilter
     *
     * @return UsrSearch
     */
    public function setItemFilter($itemFilter = null)
    {
        $this->itemFilter = $itemFilter;

        return $this;
    }

    /**
     * Get itemFilter.
     *
     * @return string|null
     */
    public function getItemFilter()
    {
        return $this->itemFilter;
    }

    /**
     * Set mimeFilter.
     *
     * @param string|null $mimeFilter
     *
     * @return UsrSearch
     */
    public function setMimeFilter($mimeFilter = null)
    {
        $this->mimeFilter = $mimeFilter;

        return $this;
    }

    /**
     * Get mimeFilter.
     *
     * @return string|null
     */
    public function getMimeFilter()
    {
        return $this->mimeFilter;
    }

    /**
     * Set creationFilter.
     *
     * @param string|null $creationFilter
     *
     * @return UsrSearch
     */
    public function setCreationFilter($creationFilter = null)
    {
        $this->creationFilter = $creationFilter;

        return $this;
    }

    /**
     * Get creationFilter.
     *
     * @return string|null
     */
    public function getCreationFilter()
    {
        return $this->creationFilter;
    }
}
