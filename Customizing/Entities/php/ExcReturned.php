<?php



/**
 * ExcReturned
 */
class ExcReturned
{
    /**
     * @var int
     */
    private $returnedId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $filename;

    /**
     * @var string|null
     */
    private $filetitle;

    /**
     * @var string|null
     */
    private $mimetype;

    /**
     * @var \DateTime|null
     */
    private $ts;

    /**
     * @var int|null
     */
    private $assId;

    /**
     * @var string|null
     */
    private $atext;

    /**
     * @var bool
     */
    private $late = '0';

    /**
     * @var int
     */
    private $teamId = '0';

    /**
     * @var \DateTime|null
     */
    private $webDirAccessTime;


    /**
     * Get returnedId.
     *
     * @return int
     */
    public function getReturnedId()
    {
        return $this->returnedId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ExcReturned
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return ExcReturned
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
     * Set filename.
     *
     * @param string|null $filename
     *
     * @return ExcReturned
     */
    public function setFilename($filename = null)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filetitle.
     *
     * @param string|null $filetitle
     *
     * @return ExcReturned
     */
    public function setFiletitle($filetitle = null)
    {
        $this->filetitle = $filetitle;

        return $this;
    }

    /**
     * Get filetitle.
     *
     * @return string|null
     */
    public function getFiletitle()
    {
        return $this->filetitle;
    }

    /**
     * Set mimetype.
     *
     * @param string|null $mimetype
     *
     * @return ExcReturned
     */
    public function setMimetype($mimetype = null)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype.
     *
     * @return string|null
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set ts.
     *
     * @param \DateTime|null $ts
     *
     * @return ExcReturned
     */
    public function setTs($ts = null)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return \DateTime|null
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set assId.
     *
     * @param int|null $assId
     *
     * @return ExcReturned
     */
    public function setAssId($assId = null)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int|null
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set atext.
     *
     * @param string|null $atext
     *
     * @return ExcReturned
     */
    public function setAtext($atext = null)
    {
        $this->atext = $atext;

        return $this;
    }

    /**
     * Get atext.
     *
     * @return string|null
     */
    public function getAtext()
    {
        return $this->atext;
    }

    /**
     * Set late.
     *
     * @param bool $late
     *
     * @return ExcReturned
     */
    public function setLate($late)
    {
        $this->late = $late;

        return $this;
    }

    /**
     * Get late.
     *
     * @return bool
     */
    public function getLate()
    {
        return $this->late;
    }

    /**
     * Set teamId.
     *
     * @param int $teamId
     *
     * @return ExcReturned
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * Get teamId.
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Set webDirAccessTime.
     *
     * @param \DateTime|null $webDirAccessTime
     *
     * @return ExcReturned
     */
    public function setWebDirAccessTime($webDirAccessTime = null)
    {
        $this->webDirAccessTime = $webDirAccessTime;

        return $this;
    }

    /**
     * Get webDirAccessTime.
     *
     * @return \DateTime|null
     */
    public function getWebDirAccessTime()
    {
        return $this->webDirAccessTime;
    }
}
