<?php



/**
 * TstRndQuestSetQpls
 */
class TstRndQuestSetQpls
{
    /**
     * @var int
     */
    private $defId = '0';

    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var int
     */
    private $poolFi = '0';

    /**
     * @var string|null
     */
    private $poolTitle;

    /**
     * @var string|null
     */
    private $poolPath;

    /**
     * @var int|null
     */
    private $poolQuestCount;

    /**
     * @var int|null
     */
    private $originTaxFi;

    /**
     * @var int|null
     */
    private $originNodeFi;

    /**
     * @var int|null
     */
    private $mappedTaxFi;

    /**
     * @var int|null
     */
    private $mappedNodeFi;

    /**
     * @var int|null
     */
    private $questAmount;

    /**
     * @var int|null
     */
    private $sequencePos;

    /**
     * @var string|null
     */
    private $originTaxFilter;

    /**
     * @var string|null
     */
    private $mappedTaxFilter;

    /**
     * @var string|null
     */
    private $typeFilter;

    /**
     * @var string|null
     */
    private $lifecycleFilter;


    /**
     * Get defId.
     *
     * @return int
     */
    public function getDefId()
    {
        return $this->defId;
    }

    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setTestFi($testFi)
    {
        $this->testFi = $testFi;

        return $this;
    }

    /**
     * Get testFi.
     *
     * @return int
     */
    public function getTestFi()
    {
        return $this->testFi;
    }

    /**
     * Set poolFi.
     *
     * @param int $poolFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setPoolFi($poolFi)
    {
        $this->poolFi = $poolFi;

        return $this;
    }

    /**
     * Get poolFi.
     *
     * @return int
     */
    public function getPoolFi()
    {
        return $this->poolFi;
    }

    /**
     * Set poolTitle.
     *
     * @param string|null $poolTitle
     *
     * @return TstRndQuestSetQpls
     */
    public function setPoolTitle($poolTitle = null)
    {
        $this->poolTitle = $poolTitle;

        return $this;
    }

    /**
     * Get poolTitle.
     *
     * @return string|null
     */
    public function getPoolTitle()
    {
        return $this->poolTitle;
    }

    /**
     * Set poolPath.
     *
     * @param string|null $poolPath
     *
     * @return TstRndQuestSetQpls
     */
    public function setPoolPath($poolPath = null)
    {
        $this->poolPath = $poolPath;

        return $this;
    }

    /**
     * Get poolPath.
     *
     * @return string|null
     */
    public function getPoolPath()
    {
        return $this->poolPath;
    }

    /**
     * Set poolQuestCount.
     *
     * @param int|null $poolQuestCount
     *
     * @return TstRndQuestSetQpls
     */
    public function setPoolQuestCount($poolQuestCount = null)
    {
        $this->poolQuestCount = $poolQuestCount;

        return $this;
    }

    /**
     * Get poolQuestCount.
     *
     * @return int|null
     */
    public function getPoolQuestCount()
    {
        return $this->poolQuestCount;
    }

    /**
     * Set originTaxFi.
     *
     * @param int|null $originTaxFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setOriginTaxFi($originTaxFi = null)
    {
        $this->originTaxFi = $originTaxFi;

        return $this;
    }

    /**
     * Get originTaxFi.
     *
     * @return int|null
     */
    public function getOriginTaxFi()
    {
        return $this->originTaxFi;
    }

    /**
     * Set originNodeFi.
     *
     * @param int|null $originNodeFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setOriginNodeFi($originNodeFi = null)
    {
        $this->originNodeFi = $originNodeFi;

        return $this;
    }

    /**
     * Get originNodeFi.
     *
     * @return int|null
     */
    public function getOriginNodeFi()
    {
        return $this->originNodeFi;
    }

    /**
     * Set mappedTaxFi.
     *
     * @param int|null $mappedTaxFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setMappedTaxFi($mappedTaxFi = null)
    {
        $this->mappedTaxFi = $mappedTaxFi;

        return $this;
    }

    /**
     * Get mappedTaxFi.
     *
     * @return int|null
     */
    public function getMappedTaxFi()
    {
        return $this->mappedTaxFi;
    }

    /**
     * Set mappedNodeFi.
     *
     * @param int|null $mappedNodeFi
     *
     * @return TstRndQuestSetQpls
     */
    public function setMappedNodeFi($mappedNodeFi = null)
    {
        $this->mappedNodeFi = $mappedNodeFi;

        return $this;
    }

    /**
     * Get mappedNodeFi.
     *
     * @return int|null
     */
    public function getMappedNodeFi()
    {
        return $this->mappedNodeFi;
    }

    /**
     * Set questAmount.
     *
     * @param int|null $questAmount
     *
     * @return TstRndQuestSetQpls
     */
    public function setQuestAmount($questAmount = null)
    {
        $this->questAmount = $questAmount;

        return $this;
    }

    /**
     * Get questAmount.
     *
     * @return int|null
     */
    public function getQuestAmount()
    {
        return $this->questAmount;
    }

    /**
     * Set sequencePos.
     *
     * @param int|null $sequencePos
     *
     * @return TstRndQuestSetQpls
     */
    public function setSequencePos($sequencePos = null)
    {
        $this->sequencePos = $sequencePos;

        return $this;
    }

    /**
     * Get sequencePos.
     *
     * @return int|null
     */
    public function getSequencePos()
    {
        return $this->sequencePos;
    }

    /**
     * Set originTaxFilter.
     *
     * @param string|null $originTaxFilter
     *
     * @return TstRndQuestSetQpls
     */
    public function setOriginTaxFilter($originTaxFilter = null)
    {
        $this->originTaxFilter = $originTaxFilter;

        return $this;
    }

    /**
     * Get originTaxFilter.
     *
     * @return string|null
     */
    public function getOriginTaxFilter()
    {
        return $this->originTaxFilter;
    }

    /**
     * Set mappedTaxFilter.
     *
     * @param string|null $mappedTaxFilter
     *
     * @return TstRndQuestSetQpls
     */
    public function setMappedTaxFilter($mappedTaxFilter = null)
    {
        $this->mappedTaxFilter = $mappedTaxFilter;

        return $this;
    }

    /**
     * Get mappedTaxFilter.
     *
     * @return string|null
     */
    public function getMappedTaxFilter()
    {
        return $this->mappedTaxFilter;
    }

    /**
     * Set typeFilter.
     *
     * @param string|null $typeFilter
     *
     * @return TstRndQuestSetQpls
     */
    public function setTypeFilter($typeFilter = null)
    {
        $this->typeFilter = $typeFilter;

        return $this;
    }

    /**
     * Get typeFilter.
     *
     * @return string|null
     */
    public function getTypeFilter()
    {
        return $this->typeFilter;
    }

    /**
     * Set lifecycleFilter.
     *
     * @param string|null $lifecycleFilter
     *
     * @return TstRndQuestSetQpls
     */
    public function setLifecycleFilter($lifecycleFilter = null)
    {
        $this->lifecycleFilter = $lifecycleFilter;

        return $this;
    }

    /**
     * Get lifecycleFilter.
     *
     * @return string|null
     */
    public function getLifecycleFilter()
    {
        return $this->lifecycleFilter;
    }
}
