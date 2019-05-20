<?php



/**
 * LikeData
 */
class LikeData
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType;

    /**
     * @var int
     */
    private $subObjId = '0';

    /**
     * @var string
     */
    private $subObjType;

    /**
     * @var int
     */
    private $newsId = '0';

    /**
     * @var int
     */
    private $likeType = '0';

    /**
     * @var \DateTime
     */
    private $expTs;


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return LikeData
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return LikeData
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
     * @return LikeData
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
     * @return LikeData
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
     * @return LikeData
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
     * Set newsId.
     *
     * @param int $newsId
     *
     * @return LikeData
     */
    public function setNewsId($newsId)
    {
        $this->newsId = $newsId;

        return $this;
    }

    /**
     * Get newsId.
     *
     * @return int
     */
    public function getNewsId()
    {
        return $this->newsId;
    }

    /**
     * Set likeType.
     *
     * @param int $likeType
     *
     * @return LikeData
     */
    public function setLikeType($likeType)
    {
        $this->likeType = $likeType;

        return $this;
    }

    /**
     * Get likeType.
     *
     * @return int
     */
    public function getLikeType()
    {
        return $this->likeType;
    }

    /**
     * Set expTs.
     *
     * @param \DateTime $expTs
     *
     * @return LikeData
     */
    public function setExpTs($expTs)
    {
        $this->expTs = $expTs;

        return $this;
    }

    /**
     * Get expTs.
     *
     * @return \DateTime
     */
    public function getExpTs()
    {
        return $this->expTs;
    }
}
