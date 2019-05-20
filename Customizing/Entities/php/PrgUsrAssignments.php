<?php



/**
 * PrgUsrAssignments
 */
class PrgUsrAssignments
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $rootPrgId = '0';

    /**
     * @var \DateTime
     */
    private $lastChange = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $lastChangeBy = '0';


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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return PrgUsrAssignments
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set rootPrgId.
     *
     * @param int $rootPrgId
     *
     * @return PrgUsrAssignments
     */
    public function setRootPrgId($rootPrgId)
    {
        $this->rootPrgId = $rootPrgId;

        return $this;
    }

    /**
     * Get rootPrgId.
     *
     * @return int
     */
    public function getRootPrgId()
    {
        return $this->rootPrgId;
    }

    /**
     * Set lastChange.
     *
     * @param \DateTime $lastChange
     *
     * @return PrgUsrAssignments
     */
    public function setLastChange($lastChange)
    {
        $this->lastChange = $lastChange;

        return $this;
    }

    /**
     * Get lastChange.
     *
     * @return \DateTime
     */
    public function getLastChange()
    {
        return $this->lastChange;
    }

    /**
     * Set lastChangeBy.
     *
     * @param int $lastChangeBy
     *
     * @return PrgUsrAssignments
     */
    public function setLastChangeBy($lastChangeBy)
    {
        $this->lastChangeBy = $lastChangeBy;

        return $this;
    }

    /**
     * Get lastChangeBy.
     *
     * @return int
     */
    public function getLastChangeBy()
    {
        return $this->lastChangeBy;
    }
}
