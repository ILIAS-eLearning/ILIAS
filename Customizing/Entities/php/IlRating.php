<?php



/**
 * IlRating
 */
class IlRating
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
     * @var int
     */
    private $categoryId = '0';

    /**
     * @var int
     */
    private $rating = '0';

    /**
     * @var int|null
     */
    private $tstamp;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlRating
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
     * @return IlRating
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
     * @return IlRating
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
     * @return IlRating
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
     * @return IlRating
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
     * Set categoryId.
     *
     * @param int $categoryId
     *
     * @return IlRating
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set rating.
     *
     * @param int $rating
     *
     * @return IlRating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating.
     *
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set tstamp.
     *
     * @param int|null $tstamp
     *
     * @return IlRating
     */
    public function setTstamp($tstamp = null)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int|null
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
