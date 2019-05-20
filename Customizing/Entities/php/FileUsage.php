<?php



/**
 * FileUsage
 */
class FileUsage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $usageType = ' ';

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
     * Set id.
     *
     * @param int $id
     *
     * @return FileUsage
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set usageType.
     *
     * @param string $usageType
     *
     * @return FileUsage
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
     * @return FileUsage
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
     * @return FileUsage
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
     * @return FileUsage
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
