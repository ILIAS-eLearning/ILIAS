<?php



/**
 * ObjStat
 */
class ObjStat
{
    /**
     * @var int
     */
    private $objId = '0';

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
     * @var bool
     */
    private $hh = '0';

    /**
     * @var string
     */
    private $objType = '';

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
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjStat
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
     * Set yyyy.
     *
     * @param int $yyyy
     *
     * @return ObjStat
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
     * @return ObjStat
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
     * @return ObjStat
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
     * Set hh.
     *
     * @param bool $hh
     *
     * @return ObjStat
     */
    public function setHh($hh)
    {
        $this->hh = $hh;

        return $this;
    }

    /**
     * Get hh.
     *
     * @return bool
     */
    public function getHh()
    {
        return $this->hh;
    }

    /**
     * Set objType.
     *
     * @param string $objType
     *
     * @return ObjStat
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
     * Set readCount.
     *
     * @param int|null $readCount
     *
     * @return ObjStat
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
     * @return ObjStat
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
     * @return ObjStat
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
     * @return ObjStat
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
