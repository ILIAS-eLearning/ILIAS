<?php



/**
 * MailObjData
 */
class MailObjData
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $mType;


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
     * Set userId.
     *
     * @param int $userId
     *
     * @return MailObjData
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
     * Set title.
     *
     * @param string|null $title
     *
     * @return MailObjData
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set mType.
     *
     * @param string|null $mType
     *
     * @return MailObjData
     */
    public function setMType($mType = null)
    {
        $this->mType = $mType;

        return $this;
    }

    /**
     * Get mType.
     *
     * @return string|null
     */
    public function getMType()
    {
        return $this->mType;
    }
}
