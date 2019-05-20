<?php



/**
 * PrgType
 */
class PrgType
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $defaultLang;

    /**
     * @var int|null
     */
    private $owner;

    /**
     * @var \DateTime
     */
    private $createDate = '1970-01-01 00:00:00';

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var string|null
     */
    private $icon;


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
     * Set defaultLang.
     *
     * @param string|null $defaultLang
     *
     * @return PrgType
     */
    public function setDefaultLang($defaultLang = null)
    {
        $this->defaultLang = $defaultLang;

        return $this;
    }

    /**
     * Get defaultLang.
     *
     * @return string|null
     */
    public function getDefaultLang()
    {
        return $this->defaultLang;
    }

    /**
     * Set owner.
     *
     * @param int|null $owner
     *
     * @return PrgType
     */
    public function setOwner($owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return int|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return PrgType
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return PrgType
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set icon.
     *
     * @param string|null $icon
     *
     * @return PrgType
     */
    public function setIcon($icon = null)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon.
     *
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }
}
