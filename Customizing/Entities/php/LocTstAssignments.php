<?php



/**
 * LocTstAssignments
 */
class LocTstAssignments
{
    /**
     * @var int
     */
    private $assignmentId = '0';

    /**
     * @var int
     */
    private $containerId = '0';

    /**
     * @var bool
     */
    private $assignmentType = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int
     */
    private $tstRefId = '0';


    /**
     * Get assignmentId.
     *
     * @return int
     */
    public function getAssignmentId()
    {
        return $this->assignmentId;
    }

    /**
     * Set containerId.
     *
     * @param int $containerId
     *
     * @return LocTstAssignments
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId.
     *
     * @return int
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set assignmentType.
     *
     * @param bool $assignmentType
     *
     * @return LocTstAssignments
     */
    public function setAssignmentType($assignmentType)
    {
        $this->assignmentType = $assignmentType;

        return $this;
    }

    /**
     * Get assignmentType.
     *
     * @return bool
     */
    public function getAssignmentType()
    {
        return $this->assignmentType;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return LocTstAssignments
     */
    public function setObjectiveId($objectiveId)
    {
        $this->objectiveId = $objectiveId;

        return $this;
    }

    /**
     * Get objectiveId.
     *
     * @return int
     */
    public function getObjectiveId()
    {
        return $this->objectiveId;
    }

    /**
     * Set tstRefId.
     *
     * @param int $tstRefId
     *
     * @return LocTstAssignments
     */
    public function setTstRefId($tstRefId)
    {
        $this->tstRefId = $tstRefId;

        return $this;
    }

    /**
     * Get tstRefId.
     *
     * @return int
     */
    public function getTstRefId()
    {
        return $this->tstRefId;
    }
}
