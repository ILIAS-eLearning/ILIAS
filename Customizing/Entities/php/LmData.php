<?php



/**
 * LmData
 */
class LmData
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var int
     */
    private $lmId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var string|null
     */
    private $publicAccess = 'n';

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;

    /**
     * @var string|null
     */
    private $active = 'y';

    /**
     * @var string|null
     */
    private $layout;

    /**
     * @var string|null
     */
    private $shortTitle;


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return LmData
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return LmData
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set lmId.
     *
     * @param int $lmId
     *
     * @return LmData
     */
    public function setLmId($lmId)
    {
        $this->lmId = $lmId;

        return $this;
    }

    /**
     * Get lmId.
     *
     * @return int
     */
    public function getLmId()
    {
        return $this->lmId;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return LmData
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set publicAccess.
     *
     * @param string|null $publicAccess
     *
     * @return LmData
     */
    public function setPublicAccess($publicAccess = null)
    {
        $this->publicAccess = $publicAccess;

        return $this;
    }

    /**
     * Get publicAccess.
     *
     * @return string|null
     */
    public function getPublicAccess()
    {
        return $this->publicAccess;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return LmData
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
     * @return LmData
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
     * Set active.
     *
     * @param string|null $active
     *
     * @return LmData
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return string|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set layout.
     *
     * @param string|null $layout
     *
     * @return LmData
     */
    public function setLayout($layout = null)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout.
     *
     * @return string|null
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set shortTitle.
     *
     * @param string|null $shortTitle
     *
     * @return LmData
     */
    public function setShortTitle($shortTitle = null)
    {
        $this->shortTitle = $shortTitle;

        return $this;
    }

    /**
     * Get shortTitle.
     *
     * @return string|null
     */
    public function getShortTitle()
    {
        return $this->shortTitle;
    }
}
