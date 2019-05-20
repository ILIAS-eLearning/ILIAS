<?php



/**
 * ObjUserStat
 */
class ObjUserStat
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
    private $counter;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjUserStat
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
     * @return ObjUserStat
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
     * Set yyyy.
     *
     * @param int $yyyy
     *
     * @return ObjUserStat
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
     * @return ObjUserStat
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
     * @return ObjUserStat
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
     * Set counter.
     *
     * @param int|null $counter
     *
     * @return ObjUserStat
     */
    public function setCounter($counter = null)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter.
     *
     * @return int|null
     */
    public function getCounter()
    {
        return $this->counter;
    }
}
