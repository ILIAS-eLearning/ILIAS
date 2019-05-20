<?php



/**
 * FileBasedLm
 */
class FileBasedLm
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $isOnline = 'n';

    /**
     * @var string|null
     */
    private $startfile;

    /**
     * @var bool|null
     */
    private $showLic;

    /**
     * @var bool|null
     */
    private $showBib;


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
     * @param string|null $isOnline
     *
     * @return FileBasedLm
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return string|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set startfile.
     *
     * @param string|null $startfile
     *
     * @return FileBasedLm
     */
    public function setStartfile($startfile = null)
    {
        $this->startfile = $startfile;

        return $this;
    }

    /**
     * Get startfile.
     *
     * @return string|null
     */
    public function getStartfile()
    {
        return $this->startfile;
    }

    /**
     * Set showLic.
     *
     * @param bool|null $showLic
     *
     * @return FileBasedLm
     */
    public function setShowLic($showLic = null)
    {
        $this->showLic = $showLic;

        return $this;
    }

    /**
     * Get showLic.
     *
     * @return bool|null
     */
    public function getShowLic()
    {
        return $this->showLic;
    }

    /**
     * Set showBib.
     *
     * @param bool|null $showBib
     *
     * @return FileBasedLm
     */
    public function setShowBib($showBib = null)
    {
        $this->showBib = $showBib;

        return $this;
    }

    /**
     * Get showBib.
     *
     * @return bool|null
     */
    public function getShowBib()
    {
        return $this->showBib;
    }
}
