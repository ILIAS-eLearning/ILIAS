<?php



/**
 * PagePcUsage
 */
class PagePcUsage
{
    /**
     * @var string
     */
    private $pcType = '';

    /**
     * @var int
     */
    private $pcId = '0';

    /**
     * @var string
     */
    private $usageType = '';

    /**
     * @var int
     */
    private $usageId = '0';

    /**
     * @var int
     */
    private $usageHistNr = '0';

    /**
     * @var string
     */
    private $usageLang = '-';


    /**
     * Set pcType.
     *
     * @param string $pcType
     *
     * @return PagePcUsage
     */
    public function setPcType($pcType)
    {
        $this->pcType = $pcType;

        return $this;
    }

    /**
     * Get pcType.
     *
     * @return string
     */
    public function getPcType()
    {
        return $this->pcType;
    }

    /**
     * Set pcId.
     *
     * @param int $pcId
     *
     * @return PagePcUsage
     */
    public function setPcId($pcId)
    {
        $this->pcId = $pcId;

        return $this;
    }

    /**
     * Get pcId.
     *
     * @return int
     */
    public function getPcId()
    {
        return $this->pcId;
    }

    /**
     * Set usageType.
     *
     * @param string $usageType
     *
     * @return PagePcUsage
     */
    public function setUsageType($usageType)
    {
        $this->usageType = $usageType;

        return $this;
    }

    /**
     * Get usageType.
     *
     * @return string
     */
    public function getUsageType()
    {
        return $this->usageType;
    }

    /**
     * Set usageId.
     *
     * @param int $usageId
     *
     * @return PagePcUsage
     */
    public function setUsageId($usageId)
    {
        $this->usageId = $usageId;

        return $this;
    }

    /**
     * Get usageId.
     *
     * @return int
     */
    public function getUsageId()
    {
        return $this->usageId;
    }

    /**
     * Set usageHistNr.
     *
     * @param int $usageHistNr
     *
     * @return PagePcUsage
     */
    public function setUsageHistNr($usageHistNr)
    {
        $this->usageHistNr = $usageHistNr;

        return $this;
    }

    /**
     * Get usageHistNr.
     *
     * @return int
     */
    public function getUsageHistNr()
    {
        return $this->usageHistNr;
    }

    /**
     * Set usageLang.
     *
     * @param string $usageLang
     *
     * @return PagePcUsage
     */
    public function setUsageLang($usageLang)
    {
        $this->usageLang = $usageLang;

        return $this;
    }

    /**
     * Get usageLang.
     *
     * @return string
     */
    public function getUsageLang()
    {
        return $this->usageLang;
    }
}
