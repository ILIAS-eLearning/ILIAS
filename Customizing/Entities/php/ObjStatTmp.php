<?php



/**
 * ObjStatTmp
 */
class ObjStatTmp
{
    /**
     * @var int
     */
    private $logId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var int|null
     */
    private $tstamp;

    /**
     * @var int|null
     */
    private $yyyy;

    /**
     * @var bool|null
     */
    private $mm;

    /**
     * @var bool|null
     */
    private $dd;

    /**
     * @var bool|null
     */
    private $hh;

    /**
     * @var int|null
     */
    private $readCount;

    /**
     * @var int|null
     */
    private $childsReadCount;

    /**
     * @var int|null
     */
    private $spentSeconds;

    /**
     * @var int|null
     */
    private $childsSpentSeconds;


    /**
     * Get logId.
     *
     * @return int
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjStatTmp
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
     * Set objType.
     *
     * @param string $objType
     *
     * @return ObjStatTmp
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set tstamp.
     *
     * @param int|null $tstamp
     *
     * @return ObjStatTmp
     */
    public function setTstamp($tstamp = null)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int|null
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set yyyy.
     *
     * @param int|null $yyyy
     *
     * @return ObjStatTmp
     */
    public function setYyyy($yyyy = null)
    {
        $this->yyyy = $yyyy;

        return $this;
    }

    /**
     * Get yyyy.
     *
     * @return int|null
     */
    public function getYyyy()
    {
        return $this->yyyy;
    }

    /**
     * Set mm.
     *
     * @param bool|null $mm
     *
     * @return ObjStatTmp
     */
    public function setMm($mm = null)
    {
        $this->mm = $mm;

        return $this;
    }

    /**
     * Get mm.
     *
     * @return bool|null
     */
    public function getMm()
    {
        return $this->mm;
    }

    /**
     * Set dd.
     *
     * @param bool|null $dd
     *
     * @return ObjStatTmp
     */
    public function setDd($dd = null)
    {
        $this->dd = $dd;

        return $this;
    }

    /**
     * Get dd.
     *
     * @return bool|null
     */
    public function getDd()
    {
        return $this->dd;
    }

    /**
     * Set hh.
     *
     * @param bool|null $hh
     *
     * @return ObjStatTmp
     */
    public function setHh($hh = null)
    {
        $this->hh = $hh;

        return $this;
    }

    /**
     * Get hh.
     *
     * @return bool|null
     */
    public function getHh()
    {
        return $this->hh;
    }

    /**
     * Set readCount.
     *
     * @param int|null $readCount
     *
     * @return ObjStatTmp
     */
    public function setReadCount($readCount = null)
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Get readCount.
     *
     * @return int|null
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Set childsReadCount.
     *
     * @param int|null $childsReadCount
     *
     * @return ObjStatTmp
     */
    public function setChildsReadCount($childsReadCount = null)
    {
        $this->childsReadCount = $childsReadCount;

        return $this;
    }

    /**
     * Get childsReadCount.
     *
     * @return int|null
     */
    public function getChildsReadCount()
    {
        return $this->childsReadCount;
    }

    /**
     * Set spentSeconds.
     *
     * @param int|null $spentSeconds
     *
     * @return ObjStatTmp
     */
    public function setSpentSeconds($spentSeconds = null)
    {
        $this->spentSeconds = $spentSeconds;

        return $this;
    }

    /**
     * Get spentSeconds.
     *
     * @return int|null
     */
    public function getSpentSeconds()
    {
        return $this->spentSeconds;
    }

    /**
     * Set childsSpentSeconds.
     *
     * @param int|null $childsSpentSeconds
     *
     * @return ObjStatTmp
     */
    public function setChildsSpentSeconds($childsSpentSeconds = null)
    {
        $this->childsSpentSeconds = $childsSpentSeconds;

        return $this;
    }

    /**
     * Get childsSpentSeconds.
     *
     * @return int|null
     */
    public function getChildsSpentSeconds()
    {
        return $this->childsSpentSeconds;
    }
}
