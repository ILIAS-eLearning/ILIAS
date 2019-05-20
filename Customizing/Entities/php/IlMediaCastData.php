<?php



/**
 * IlMediaCastData
 */
class IlMediaCastData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $isOnline = '0';

    /**
     * @var bool|null
     */
    private $publicFiles = '0';

    /**
     * @var bool|null
     */
    private $downloadable = '0';

    /**
     * @var bool|null
     */
    private $defAccess = '0';

    /**
     * @var bool|null
     */
    private $sortmode = '3';

    /**
     * @var string|null
     */
    private $viewmode;


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
     * Set isOnline.
     *
     * @param bool|null $isOnline
     *
     * @return IlMediaCastData
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set publicFiles.
     *
     * @param bool|null $publicFiles
     *
     * @return IlMediaCastData
     */
    public function setPublicFiles($publicFiles = null)
    {
        $this->publicFiles = $publicFiles;

        return $this;
    }

    /**
     * Get publicFiles.
     *
     * @return bool|null
     */
    public function getPublicFiles()
    {
        return $this->publicFiles;
    }

    /**
     * Set downloadable.
     *
     * @param bool|null $downloadable
     *
     * @return IlMediaCastData
     */
    public function setDownloadable($downloadable = null)
    {
        $this->downloadable = $downloadable;

        return $this;
    }

    /**
     * Get downloadable.
     *
     * @return bool|null
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * Set defAccess.
     *
     * @param bool|null $defAccess
     *
     * @return IlMediaCastData
     */
    public function setDefAccess($defAccess = null)
    {
        $this->defAccess = $defAccess;

        return $this;
    }

    /**
     * Get defAccess.
     *
     * @return bool|null
     */
    public function getDefAccess()
    {
        return $this->defAccess;
    }

    /**
     * Set sortmode.
     *
     * @param bool|null $sortmode
     *
     * @return IlMediaCastData
     */
    public function setSortmode($sortmode = null)
    {
        $this->sortmode = $sortmode;

        return $this;
    }

    /**
     * Get sortmode.
     *
     * @return bool|null
     */
    public function getSortmode()
    {
        return $this->sortmode;
    }

    /**
     * Set viewmode.
     *
     * @param string|null $viewmode
     *
     * @return IlMediaCastData
     */
    public function setViewmode($viewmode = null)
    {
        $this->viewmode = $viewmode;

        return $this;
    }

    /**
     * Get viewmode.
     *
     * @return string|null
     */
    public function getViewmode()
    {
        return $this->viewmode;
    }
}
