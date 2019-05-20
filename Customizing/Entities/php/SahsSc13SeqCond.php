<?php



/**
 * SahsSc13SeqCond
 */
class SahsSc13SeqCond
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string|null
     */
    private $cond;

    /**
     * @var string|null
     */
    private $measurethreshold;

    /**
     * @var string|null
     */
    private $operator;

    /**
     * @var string|null
     */
    private $referencedobjective;


    /**
     * Get seqnodeid.
     *
     * @return int
     */
    public function getSeqnodeid()
    {
        return $this->seqnodeid;
    }

    /**
     * Set cond.
     *
     * @param string|null $cond
     *
     * @return SahsSc13SeqCond
     */
    public function setCond($cond = null)
    {
        $this->cond = $cond;

        return $this;
    }

    /**
     * Get cond.
     *
     * @return string|null
     */
    public function getCond()
    {
        return $this->cond;
    }

    /**
     * Set measurethreshold.
     *
     * @param string|null $measurethreshold
     *
     * @return SahsSc13SeqCond
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
     * Set operator.
     *
     * @param string|null $operator
     *
     * @return SahsSc13SeqCond
     */
    public function setOperator($operator = null)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator.
     *
     * @return string|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set referencedobjective.
     *
     * @param string|null $referencedobjective
     *
     * @return SahsSc13SeqCond
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
