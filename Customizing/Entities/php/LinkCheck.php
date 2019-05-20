<?php



/**
 * LinkCheck
 */
class LinkCheck
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $pageId = '0';

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $parentType;

    /**
     * @var int
     */
    private $httpStatusCode = '0';

    /**
     * @var int
     */
    private $lastCheck = '0';


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
     * Set objId.
     *
     * @param int $objId
     *
     * @return LinkCheck
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set pageId.
     *
     * @param int $pageId
     *
     * @return LinkCheck
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
     * Set url.
     *
     * @param string|null $url
     *
     * @return LinkCheck
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set parentType.
     *
     * @param string|null $parentType
     *
     * @return LinkCheck
     */
    public function setParentType($parentType = null)
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parentType.
     *
     * @return string|null
     */
    public function getParentType()
    {
        return $this->parentType;
    }

    /**
     * Set httpStatusCode.
     *
     * @param int $httpStatusCode
     *
     * @return LinkCheck
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * Get httpStatusCode.
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Set lastCheck.
     *
     * @param int $lastCheck
     *
     * @return LinkCheck
     */
    public function setLastCheck($lastCheck)
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    /**
     * Get lastCheck.
     *
     * @return int
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }
}
