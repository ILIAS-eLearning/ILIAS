<?php



/**
 * CmiInteraction
 */
class CmiInteraction
{
    /**
     * @var int
     */
    private $cmiInteractionId = '0';

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
     * @var string|null
     */
    private $latency;

    /**
     * @var string|null
     */
    private $result;

    /**
     * @var string|null
     */
    private $cTimestamp;

    /**
     * @var string|null
     */
    private $cType;

    /**
     * @var float|null
     */
    private $weighting;

    /**
     * @var string|null
     */
    private $learnerResponse;


    /**
     * Get cmiInteractionId.
     *
     * @return int
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
     * @return CmiInteraction
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
     * @return CmiInteraction
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
     * @return CmiInteraction
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
     * Set latency.
     *
     * @param string|null $latency
     *
     * @return CmiInteraction
     */
    public function setLatency($latency = null)
    {
        $this->latency = $latency;

        return $this;
    }

    /**
     * Get latency.
     *
     * @return string|null
     */
    public function getLatency()
    {
        return $this->latency;
    }

    /**
     * Set result.
     *
     * @param string|null $result
     *
     * @return CmiInteraction
     */
    public function setResult($result = null)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result.
     *
     * @return string|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set cTimestamp.
     *
     * @param string|null $cTimestamp
     *
     * @return CmiInteraction
     */
    public function setCTimestamp($cTimestamp = null)
    {
        $this->cTimestamp = $cTimestamp;

        return $this;
    }

    /**
     * Get cTimestamp.
     *
     * @return string|null
     */
    public function getCTimestamp()
    {
        return $this->cTimestamp;
    }

    /**
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return CmiInteraction
     */
    public function setCType($cType = null)
    {
        $this->cType = $cType;

        return $this;
    }

    /**
     * Get cType.
     *
     * @return string|null
     */
    public function getCType()
    {
        return $this->cType;
    }

    /**
     * Set weighting.
     *
     * @param float|null $weighting
     *
     * @return CmiInteraction
     */
    public function setWeighting($weighting = null)
    {
        $this->weighting = $weighting;

        return $this;
    }

    /**
     * Get weighting.
     *
     * @return float|null
     */
    public function getWeighting()
    {
        return $this->weighting;
    }

    /**
     * Set learnerResponse.
     *
     * @param string|null $learnerResponse
     *
     * @return CmiInteraction
     */
    public function setLearnerResponse($learnerResponse = null)
    {
        $this->learnerResponse = $learnerResponse;

        return $this;
    }

    /**
     * Get learnerResponse.
     *
     * @return string|null
     */
    public function getLearnerResponse()
    {
        return $this->learnerResponse;
    }
}
