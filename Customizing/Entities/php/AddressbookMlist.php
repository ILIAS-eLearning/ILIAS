<?php



/**
 * AddressbookMlist
 */
class AddressbookMlist
{
    /**
     * @var int
     */
    private $mlId = '0';

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
    private $description;

    /**
     * @var \DateTime|null
     */
    private $createdate;

    /**
     * @var \DateTime|null
     */
    private $changedate;

    /**
     * @var bool
     */
    private $lmode = '1';


    /**
     * Get mlId.
     *
     * @return int
     */
    public function getMlId()
    {
        return $this->mlId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return AddressbookMlist
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
     * @return AddressbookMlist
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return AddressbookMlist
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdate.
     *
     * @param \DateTime|null $createdate
     *
     * @return AddressbookMlist
     */
    public function setCreatedate($createdate = null)
    {
        $this->createdate = $createdate;

        return $this;
    }

    /**
     * Get createdate.
     *
     * @return \DateTime|null
     */
    public function getCreatedate()
    {
        return $this->createdate;
    }

    /**
     * Set changedate.
     *
     * @param \DateTime|null $changedate
     *
     * @return AddressbookMlist
     */
    public function setChangedate($changedate = null)
    {
        $this->changedate = $changedate;

        return $this;
    }

    /**
     * Get changedate.
     *
     * @return \DateTime|null
     */
    public function getChangedate()
    {
        return $this->changedate;
    }

    /**
     * Set lmode.
     *
     * @param bool $lmode
     *
     * @return AddressbookMlist
     */
    public function setLmode($lmode)
    {
        $this->lmode = $lmode;

        return $this;
    }

    /**
     * Get lmode.
     *
     * @return bool
     */
    public function getLmode()
    {
        return $this->lmode;
    }
}
