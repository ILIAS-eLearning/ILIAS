<?php



/**
 * CmiObjective
 */
class CmiObjective
{
    /**
     * @var int
     */
    private $cmiObjectiveId = '0';

    /**
     * @var int|null
     */
    private $cmiInteractionId;

    /**
     * @var int|null
     */
    private $cmiNodeId;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var float|null
     */
    private $cMax;

    /**
     * @var float|null
     */
    private $cMin;

    /**
     * @var float|null
     */
    private $cRaw;

    /**
     * @var float|null
     */
    private $scaled;

    /**
     * @var float|null
     */
    private $progressMeasure;

    /**
     * @var string|null
     */
    private $successStatus;

    /**
     * @var string|null
     */
    private $scope;

    /**
     * @var string|null
     */
    private $completionStatus;


    /**
     * Get cmiObjectiveId.
     *
     * @return int
     */
    public function getCmiObjectiveId()
    {
        return $this->cmiObjectiveId;
    }

    /**
     * Set cmiInteractionId.
     *
     * @param int|null $cmiInteractionId
     *
     * @return CmiObjective
     */
    public function setCmiInteractionId($cmiInteractionId = null)
    {
        $this->cmiInteractionId = $cmiInteractionId;

        return $this;
    }

    /**
     * Get cmiInteractionId.
     *
     * @return int|null
     */
    public function getCmiInteractionId()
    {
        return $this->cmiInteractionId;
    }

    /**
     * Set cmiNodeId.
     *
     * @param int|null $cmiNodeId
     *
     * @return CmiObjective
     */
    public function setCmiNodeId($cmiNodeId = null)
    {
        $this->cmiNodeId = $cmiNodeId;

        return $this;
    }

    /**
     * Get cmiNodeId.
     *
     * @return int|null
     */
    public function getCmiNodeId()
    {
        return $this->cmiNodeId;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return CmiObjective
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set id.
     *
     * @param string|null $id
     *
     * @return CmiObjective
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cMax.
     *
     * @param float|null $cMax
     *
     * @return CmiObjective
     */
    public function setCMax($cMax = null)
    {
        $this->cMax = $cMax;

        return $this;
    }

    /**
     * Get cMax.
     *
     * @return float|null
     */
    public function getCMax()
    {
        return $this->cMax;
    }

    /**
     * Set cMin.
     *
     * @param float|null $cMin
     *
     * @return CmiObjective
     */
    public function setCMin($cMin = null)
    {
        $this->cMin = $cMin;

        return $this;
    }

    /**
     * Get cMin.
     *
     * @return float|null
     */
    public function getCMin()
    {
        return $this->cMin;
    }

    /**
     * Set cRaw.
     *
     * @param float|null $cRaw
     *
     * @return CmiObjective
     */
    public function setCRaw($cRaw = null)
    {
        $this->cRaw = $cRaw;

        return $this;
    }

    /**
     * Get cRaw.
     *
     * @return float|null
     */
    public function getCRaw()
    {
        return $this->cRaw;
    }

    /**
     * Set scaled.
     *
     * @param float|null $scaled
     *
     * @return CmiObjective
     */
    public function setScaled($scaled = null)
    {
        $this->scaled = $scaled;

        return $this;
    }

    /**
     * Get scaled.
     *
     * @return float|null
     */
    public function getScaled()
    {
        return $this->scaled;
    }

    /**
     * Set progressMeasure.
     *
     * @param float|null $progressMeasure
     *
     * @return CmiObjective
     */
    public function setProgressMeasure($progressMeasure = null)
    {
        $this->progressMeasure = $progressMeasure;

        return $this;
    }

    /**
     * Get progressMeasure.
     *
     * @return float|null
     */
    public function getProgressMeasure()
    {
        return $this->progressMeasure;
    }

    /**
     * Set successStatus.
     *
     * @param string|null $successStatus
     *
     * @return CmiObjective
     */
    public function setSuccessStatus($successStatus = null)
    {
        $this->successStatus = $successStatus;

        return $this;
    }

    /**
     * Get successStatus.
     *
     * @return string|null
     */
    public function getSuccessStatus()
    {
        return $this->successStatus;
    }

    /**
     * Set scope.
     *
     * @param string|null $scope
     *
     * @return CmiObjective
     */
    public function setScope($scope = null)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope.
     *
     * @return string|null
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set completionStatus.
     *
     * @param string|null $completionStatus
     *
     * @return CmiObjective
     */
    public function setCompletionStatus($completionStatus = null)
    {
        $this->completionStatus = $completionStatus;

        return $this;
    }

    /**
     * Get completionStatus.
     *
     * @return string|null
     */
    public function getCompletionStatus()
    {
        return $this->completionStatus;
    }
}
