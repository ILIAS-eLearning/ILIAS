<?php



/**
 * OrguTypes
 */
class OrguTypes
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $defaultLang = '';

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var int
     */
    private $owner = '0';

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;


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
     * @param string $defaultLang
     *
     * @return OrguTypes
     */
    public function setDefaultLang($defaultLang)
    {
        $this->defaultLang = $defaultLang;

        return $this;
    }

    /**
     * Get defaultLang.
     *
     * @return string
     */
    public function getDefaultLang()
    {
        return $this->defaultLang;
    }

    /**
     * Set icon.
     *
     * @param string|null $icon
     *
     * @return OrguTypes
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

    /**
     * Set owner.
     *
     * @param int $owner
     *
     * @return OrguTypes
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return OrguTypes
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
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
     * @return OrguTypes
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
}
