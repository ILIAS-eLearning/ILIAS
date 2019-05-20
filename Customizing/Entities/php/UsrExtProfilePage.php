<?php



/**
 * UsrExtProfilePage
 */
class UsrExtProfilePage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $orderNr = '0';

    /**
     * @var string|null
     */
    private $title;


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
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrExtProfilePage
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
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return UsrExtProfilePage
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

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return UsrExtProfilePage
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
}
