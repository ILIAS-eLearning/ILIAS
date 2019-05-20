<?php



/**
 * CalCategories
 */
class CalCategories
{
    /**
     * @var int
     */
    private $catId = '0';

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
    private $color;

    /**
     * @var bool
     */
    private $type = '0';

    /**
     * @var bool
     */
    private $locType = '1';

    /**
     * @var string|null
     */
    private $remoteUrl;

    /**
     * @var string|null
     */
    private $remoteUser;

    /**
     * @var string|null
     */
    private $remotePass;

    /**
     * @var \DateTime|null
     */
    private $remoteSync;


    /**
     * Get catId.
     *
     * @return int
     */
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CalCategories
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
     * Set title.
     *
     * @param string|null $title
     *
     * @return CalCategories
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
     * Set color.
     *
     * @param string|null $color
     *
     * @return CalCategories
     */
    public function setColor($color = null)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color.
     *
     * @return string|null
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return CalCategories
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set locType.
     *
     * @param bool $locType
     *
     * @return CalCategories
     */
    public function setLocType($locType)
    {
        $this->locType = $locType;

        return $this;
    }

    /**
     * Get locType.
     *
     * @return bool
     */
    public function getLocType()
    {
        return $this->locType;
    }

    /**
     * Set remoteUrl.
     *
     * @param string|null $remoteUrl
     *
     * @return CalCategories
     */
    public function setRemoteUrl($remoteUrl = null)
    {
        $this->remoteUrl = $remoteUrl;

        return $this;
    }

    /**
     * Get remoteUrl.
     *
     * @return string|null
     */
    public function getRemoteUrl()
    {
        return $this->remoteUrl;
    }

    /**
     * Set remoteUser.
     *
     * @param string|null $remoteUser
     *
     * @return CalCategories
     */
    public function setRemoteUser($remoteUser = null)
    {
        $this->remoteUser = $remoteUser;

        return $this;
    }

    /**
     * Get remoteUser.
     *
     * @return string|null
     */
    public function getRemoteUser()
    {
        return $this->remoteUser;
    }

    /**
     * Set remotePass.
     *
     * @param string|null $remotePass
     *
     * @return CalCategories
     */
    public function setRemotePass($remotePass = null)
    {
        $this->remotePass = $remotePass;

        return $this;
    }

    /**
     * Get remotePass.
     *
     * @return string|null
     */
    public function getRemotePass()
    {
        return $this->remotePass;
    }

    /**
     * Set remoteSync.
     *
     * @param \DateTime|null $remoteSync
     *
     * @return CalCategories
     */
    public function setRemoteSync($remoteSync = null)
    {
        $this->remoteSync = $remoteSync;

        return $this;
    }

    /**
     * Get remoteSync.
     *
     * @return \DateTime|null
     */
    public function getRemoteSync()
    {
        return $this->remoteSync;
    }
}
