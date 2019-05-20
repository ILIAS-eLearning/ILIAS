<?php



/**
 * ObjLpStat
 */
class ObjLpStat
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $fulldate = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var int
     */
    private $yyyy = '0';

    /**
     * @var bool
     */
    private $mm = '0';

    /**
     * @var bool
     */
    private $dd = '0';

    /**
     * @var int|null
     */
    private $memCnt;

    /**
     * @var int|null
     */
    private $inProgress;

    /**
     * @var int|null
     */
    private $completed;

    /**
     * @var int|null
     */
    private $failed;

    /**
     * @var int|null
     */
    private $notAttempted;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjLpStat
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
     * Set fulldate.
     *
     * @param int $fulldate
     *
     * @return ObjLpStat
     */
    public function setFulldate($fulldate)
    {
        $this->fulldate = $fulldate;

        return $this;
    }

    /**
     * Get fulldate.
     *
     * @return int
     */
    public function getFulldate()
    {
        return $this->fulldate;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ObjLpStat
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set yyyy.
     *
     * @param int $yyyy
     *
     * @return ObjLpStat
     */
    public function setYyyy($yyyy)
    {
        $this->yyyy = $yyyy;

        return $this;
    }

    /**
     * Get yyyy.
     *
     * @return int
     */
    public function getYyyy()
    {
        return $this->yyyy;
    }

    /**
     * Set mm.
     *
     * @param bool $mm
     *
     * @return ObjLpStat
     */
    public function setMm($mm)
    {
        $this->mm = $mm;

        return $this;
    }

    /**
     * Get mm.
     *
     * @return bool
     */
    public function getMm()
    {
        return $this->mm;
    }

    /**
     * Set dd.
     *
     * @param bool $dd
     *
     * @return ObjLpStat
     */
    public function setDd($dd)
    {
        $this->dd = $dd;

        return $this;
    }

    /**
     * Get dd.
     *
     * @return bool
     */
    public function getDd()
    {
        return $this->dd;
    }

    /**
     * Set memCnt.
     *
     * @param int|null $memCnt
     *
     * @return ObjLpStat
     */
    public function setMemCnt($memCnt = null)
    {
        $this->memCnt = $memCnt;

        return $this;
    }

    /**
     * Get memCnt.
     *
     * @return int|null
     */
    public function getMemCnt()
    {
        return $this->memCnt;
    }

    /**
     * Set inProgress.
     *
     * @param int|null $inProgress
     *
     * @return ObjLpStat
     */
    public function setInProgress($inProgress = null)
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    /**
     * Get inProgress.
     *
     * @return int|null
     */
    public function getInProgress()
    {
        return $this->inProgress;
    }

    /**
     * Set completed.
     *
     * @param int|null $completed
     *
     * @return ObjLpStat
     */
    public function setCompleted($completed = null)
    {
        $this->completed = $completed;

        return $this;
    }

    /**
     * Get completed.
     *
     * @return int|null
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set failed.
     *
     * @param int|null $failed
     *
     * @return ObjLpStat
     */
    public function setFailed($failed = null)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed.
     *
     * @return int|null
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set notAttempted.
     *
     * @param int|null $notAttempted
     *
     * @return ObjLpStat
     */
    public function setNotAttempted($notAttempted = null)
    {
        $this->notAttempted = $notAttempted;

        return $this;
    }

    /**
     * Get notAttempted.
     *
     * @return int|null
     */
    public function getNotAttempted()
    {
        return $this->notAttempted;
    }
}
