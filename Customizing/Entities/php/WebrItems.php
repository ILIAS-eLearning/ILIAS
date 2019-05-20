<?php



/**
 * WebrItems
 */
class WebrItems
{
    /**
     * @var int
     */
    private $linkId = '0';

    /**
     * @var int
     */
    private $webrId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $target;

    /**
     * @var bool|null
     */
    private $active;

    /**
     * @var bool|null
     */
    private $disableCheck;

    /**
     * @var int
     */
    private $createDate = '0';

    /**
     * @var int
     */
    private $lastUpdate = '0';

    /**
     * @var int|null
     */
    private $lastCheck;

    /**
     * @var bool
     */
    private $valid = '0';

    /**
     * @var bool|null
     */
    private $internal;


    /**
     * Get linkId.
     *
     * @return int
     */
    public function getLinkId()
    {
        return $this->linkId;
    }

    /**
     * Set webrId.
     *
     * @param int $webrId
     *
     * @return WebrItems
     */
    public function setWebrId($webrId)
    {
        $this->webrId = $webrId;

        return $this;
    }

    /**
     * Get webrId.
     *
     * @return int
     */
    public function getWebrId()
    {
        return $this->webrId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return WebrItems
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
     * @return WebrItems
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
     * Set target.
     *
     * @param string|null $target
     *
     * @return WebrItems
     */
    public function setTarget($target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target.
     *
     * @return string|null
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return WebrItems
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set disableCheck.
     *
     * @param bool|null $disableCheck
     *
     * @return WebrItems
     */
    public function setDisableCheck($disableCheck = null)
    {
        $this->disableCheck = $disableCheck;

        return $this;
    }

    /**
     * Get disableCheck.
     *
     * @return bool|null
     */
    public function getDisableCheck()
    {
        return $this->disableCheck;
    }

    /**
     * Set createDate.
     *
     * @param int $createDate
     *
     * @return WebrItems
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return int
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set lastUpdate.
     *
     * @param int $lastUpdate
     *
     * @return WebrItems
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set lastCheck.
     *
     * @param int|null $lastCheck
     *
     * @return WebrItems
     */
    public function setLastCheck($lastCheck = null)
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    /**
     * Get lastCheck.
     *
     * @return int|null
     */
    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    /**
     * Set valid.
     *
     * @param bool $valid
     *
     * @return WebrItems
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid.
     *
     * @return bool
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * Set internal.
     *
     * @param bool|null $internal
     *
     * @return WebrItems
     */
    public function setInternal($internal = null)
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * Get internal.
     *
     * @return bool|null
     */
    public function getInternal()
    {
        return $this->internal;
    }
}
