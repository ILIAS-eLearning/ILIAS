<?php



/**
 * IlMetaKeyword
 */
class IlMetaKeyword
{
    /**
     * @var int
     */
    private $metaKeywordId = '0';

    /**
     * @var int|null
     */
    private $rbacId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $parentType;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var string|null
     */
    private $keyword;

    /**
     * @var string|null
     */
    private $keywordLanguage;


    /**
     * Get metaKeywordId.
     *
     * @return int
     */
    public function getMetaKeywordId()
    {
        return $this->metaKeywordId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaKeyword
     */
    public function setRbacId($rbacId = null)
    {
        $this->rbacId = $rbacId;

        return $this;
    }

    /**
     * Get rbacId.
     *
     * @return int|null
     */
    public function getRbacId()
    {
        return $this->rbacId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return IlMetaKeyword
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return IlMetaKeyword
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set parentType.
     *
     * @param string|null $parentType
     *
     * @return IlMetaKeyword
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
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return IlMetaKeyword
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set keyword.
     *
     * @param string|null $keyword
     *
     * @return IlMetaKeyword
     */
    public function setKeyword($keyword = null)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return string|null
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set keywordLanguage.
     *
     * @param string|null $keywordLanguage
     *
     * @return IlMetaKeyword
     */
    public function setKeywordLanguage($keywordLanguage = null)
    {
        $this->keywordLanguage = $keywordLanguage;

        return $this;
    }

    /**
     * Get keywordLanguage.
     *
     * @return string|null
     */
    public function getKeywordLanguage()
    {
        return $this->keywordLanguage;
    }
}
