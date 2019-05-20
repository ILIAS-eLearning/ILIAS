<?php



/**
 * PersonalClipboard
 */
class PersonalClipboard
{
    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var \DateTime|null
     */
    private $insertTime;

    /**
     * @var int
     */
    private $parent = '0';

    /**
     * @var int
     */
    private $orderNr = '0';


    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return PersonalClipboard
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
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return PersonalClipboard
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return PersonalClipboard
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return PersonalClipboard
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
     * Set insertTime.
     *
     * @param \DateTime|null $insertTime
     *
     * @return PersonalClipboard
     */
    public function setInsertTime($insertTime = null)
    {
        $this->insertTime = $insertTime;

        return $this;
    }

    /**
     * Get insertTime.
     *
     * @return \DateTime|null
     */
    public function getInsertTime()
    {
        return $this->insertTime;
    }

    /**
     * Set parent.
     *
     * @param int $parent
     *
     * @return PersonalClipboard
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return PersonalClipboard
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }
}
