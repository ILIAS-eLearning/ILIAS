<?php



/**
 * ObjTypeStat
 */
class ObjTypeStat
{
    /**
     * @var string
     */
    private $type = '';

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
    private $cntReferences;

    /**
     * @var int|null
     */
    private $cntObjects;

    /**
     * @var int|null
     */
    private $cntDeleted;


    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ObjTypeStat
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
     * Set fulldate.
     *
     * @param int $fulldate
     *
     * @return ObjTypeStat
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
     * @return ObjTypeStat
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
     * @return ObjTypeStat
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
     * @return ObjTypeStat
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
     * Set cntReferences.
     *
     * @param int|null $cntReferences
     *
     * @return ObjTypeStat
     */
    public function setCntReferences($cntReferences = null)
    {
        $this->cntReferences = $cntReferences;

        return $this;
    }

    /**
     * Get cntReferences.
     *
     * @return int|null
     */
    public function getCntReferences()
    {
        return $this->cntReferences;
    }

    /**
     * Set cntObjects.
     *
     * @param int|null $cntObjects
     *
     * @return ObjTypeStat
     */
    public function setCntObjects($cntObjects = null)
    {
        $this->cntObjects = $cntObjects;

        return $this;
    }

    /**
     * Get cntObjects.
     *
     * @return int|null
     */
    public function getCntObjects()
    {
        return $this->cntObjects;
    }

    /**
     * Set cntDeleted.
     *
     * @param int|null $cntDeleted
     *
     * @return ObjTypeStat
     */
    public function setCntDeleted($cntDeleted = null)
    {
        $this->cntDeleted = $cntDeleted;

        return $this;
    }

    /**
     * Get cntDeleted.
     *
     * @return int|null
     */
    public function getCntDeleted()
    {
        return $this->cntDeleted;
    }
}
