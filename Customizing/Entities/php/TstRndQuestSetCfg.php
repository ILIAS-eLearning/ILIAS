<?php



/**
 * TstRndQuestSetCfg
 */
class TstRndQuestSetCfg
{
    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var bool
     */
    private $reqPoolsHomoScored = '0';

    /**
     * @var string|null
     */
    private $questAmountCfgMode;

    /**
     * @var int|null
     */
    private $questAmountPerTest;

    /**
     * @var int
     */
    private $questSyncTimestamp = '0';


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
     * Set reqPoolsHomoScored.
     *
     * @param bool $reqPoolsHomoScored
     *
     * @return TstRndQuestSetCfg
     */
    public function setReqPoolsHomoScored($reqPoolsHomoScored)
    {
        $this->reqPoolsHomoScored = $reqPoolsHomoScored;

        return $this;
    }

    /**
     * Get reqPoolsHomoScored.
     *
     * @return bool
     */
    public function getReqPoolsHomoScored()
    {
        return $this->reqPoolsHomoScored;
    }

    /**
     * Set questAmountCfgMode.
     *
     * @param string|null $questAmountCfgMode
     *
     * @return TstRndQuestSetCfg
     */
    public function setQuestAmountCfgMode($questAmountCfgMode = null)
    {
        $this->questAmountCfgMode = $questAmountCfgMode;

        return $this;
    }

    /**
     * Get questAmountCfgMode.
     *
     * @return string|null
     */
    public function getQuestAmountCfgMode()
    {
        return $this->questAmountCfgMode;
    }

    /**
     * Set questAmountPerTest.
     *
     * @param int|null $questAmountPerTest
     *
     * @return TstRndQuestSetCfg
     */
    public function setQuestAmountPerTest($questAmountPerTest = null)
    {
        $this->questAmountPerTest = $questAmountPerTest;

        return $this;
    }

    /**
     * Get questAmountPerTest.
     *
     * @return int|null
     */
    public function getQuestAmountPerTest()
    {
        return $this->questAmountPerTest;
    }

    /**
     * Set questSyncTimestamp.
     *
     * @param int $questSyncTimestamp
     *
     * @return TstRndQuestSetCfg
     */
    public function setQuestSyncTimestamp($questSyncTimestamp)
    {
        $this->questSyncTimestamp = $questSyncTimestamp;

        return $this;
    }

    /**
     * Get questSyncTimestamp.
     *
     * @return int
     */
    public function getQuestSyncTimestamp()
    {
        return $this->questSyncTimestamp;
    }
}
