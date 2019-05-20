<?php



/**
 * EcsCmapRule
 */
class EcsCmapRule
{
    /**
     * @var int
     */
    private $rid = '0';

    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var string|null
     */
    private $attribute;

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var bool
     */
    private $isFilter = '0';

    /**
     * @var string|null
     */
    private $filter;

    /**
     * @var bool
     */
    private $createSubdir = '0';

    /**
     * @var bool
     */
    private $subdirType = '0';

    /**
     * @var string|null
     */
    private $directory;


    /**
     * Get rid.
     *
     * @return int
     */
    public function getRid()
    {
        return $this->rid;
    }

    /**
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsCmapRule
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsCmapRule
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set attribute.
     *
     * @param string|null $attribute
     *
     * @return EcsCmapRule
     */
    public function setAttribute($attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute.
     *
     * @return string|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return EcsCmapRule
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set isFilter.
     *
     * @param bool $isFilter
     *
     * @return EcsCmapRule
     */
    public function setIsFilter($isFilter)
    {
        $this->isFilter = $isFilter;

        return $this;
    }

    /**
     * Get isFilter.
     *
     * @return bool
     */
    public function getIsFilter()
    {
        return $this->isFilter;
    }

    /**
     * Set filter.
     *
     * @param string|null $filter
     *
     * @return EcsCmapRule
     */
    public function setFilter($filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter.
     *
     * @return string|null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set createSubdir.
     *
     * @param bool $createSubdir
     *
     * @return EcsCmapRule
     */
    public function setCreateSubdir($createSubdir)
    {
        $this->createSubdir = $createSubdir;

        return $this;
    }

    /**
     * Get createSubdir.
     *
     * @return bool
     */
    public function getCreateSubdir()
    {
        return $this->createSubdir;
    }

    /**
     * Set subdirType.
     *
     * @param bool $subdirType
     *
     * @return EcsCmapRule
     */
    public function setSubdirType($subdirType)
    {
        $this->subdirType = $subdirType;

        return $this;
    }

    /**
     * Get subdirType.
     *
     * @return bool
     */
    public function getSubdirType()
    {
        return $this->subdirType;
    }

    /**
     * Set directory.
     *
     * @param string|null $directory
     *
     * @return EcsCmapRule
     */
    public function setDirectory($directory = null)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory.
     *
     * @return string|null
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
