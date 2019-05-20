<?php



/**
 * IlTag
 */
class IlTag
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var int
     */
    private $subObjId = '0';

    /**
     * @var string
     */
    private $subObjType = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $tag = ' ';

    /**
     * @var bool
     */
    private $isOffline = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlTag
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
     * Set objType.
     *
     * @param string $objType
     *
     * @return IlTag
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set subObjId.
     *
     * @param int $subObjId
     *
     * @return IlTag
     */
    public function setSubObjId($subObjId)
    {
        $this->subObjId = $subObjId;

        return $this;
    }

    /**
     * Get subObjId.
     *
     * @return int
     */
    public function getSubObjId()
    {
        return $this->subObjId;
    }

    /**
     * Set subObjType.
     *
     * @param string $subObjType
     *
     * @return IlTag
     */
    public function setSubObjType($subObjType)
    {
        $this->subObjType = $subObjType;

        return $this;
    }

    /**
     * Get subObjType.
     *
     * @return string
     */
    public function getSubObjType()
    {
        return $this->subObjType;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlTag
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set tag.
     *
     * @param string $tag
     *
     * @return IlTag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set isOffline.
     *
     * @param bool $isOffline
     *
     * @return IlTag
     */
    public function setIsOffline($isOffline)
    {
        $this->isOffline = $isOffline;

        return $this;
    }

    /**
     * Get isOffline.
     *
     * @return bool
     */
    public function getIsOffline()
    {
        return $this->isOffline;
    }
}
