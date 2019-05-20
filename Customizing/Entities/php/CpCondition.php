<?php



/**
 * CpCondition
 */
class CpCondition
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $cCondition;

    /**
     * @var string|null
     */
    private $measurethreshold;

    /**
     * @var string|null
     */
    private $cOperator;

    /**
     * @var string|null
     */
    private $referencedobjective;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set cCondition.
     *
     * @param string|null $cCondition
     *
     * @return CpCondition
     */
    public function setCCondition($cCondition = null)
    {
        $this->cCondition = $cCondition;

        return $this;
    }

    /**
     * Get cCondition.
     *
     * @return string|null
     */
    public function getCCondition()
    {
        return $this->cCondition;
    }

    /**
     * Set measurethreshold.
     *
     * @param string|null $measurethreshold
     *
     * @return CpCondition
     */
    public function setMeasurethreshold($measurethreshold = null)
    {
        $this->measurethreshold = $measurethreshold;

        return $this;
    }

    /**
     * Get measurethreshold.
     *
     * @return string|null
     */
    public function getMeasurethreshold()
    {
        return $this->measurethreshold;
    }

    /**
     * Set cOperator.
     *
     * @param string|null $cOperator
     *
     * @return CpCondition
     */
    public function setCOperator($cOperator = null)
    {
        $this->cOperator = $cOperator;

        return $this;
    }

    /**
     * Get cOperator.
     *
     * @return string|null
     */
    public function getCOperator()
    {
        return $this->cOperator;
    }

    /**
     * Set referencedobjective.
     *
     * @param string|null $referencedobjective
     *
     * @return CpCondition
     */
    public function setReferencedobjective($referencedobjective = null)
    {
        $this->referencedobjective = $referencedobjective;

        return $this;
    }

    /**
     * Get referencedobjective.
     *
     * @return string|null
     */
    public function getReferencedobjective()
    {
        return $this->referencedobjective;
    }
}
