<?php



/**
 * TstDynQuestSetCfg
 */
class TstDynQuestSetCfg
{
    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var int
     */
    private $sourceQplFi = '0';

    /**
     * @var bool
     */
    private $taxFilterEnabled = '0';

    /**
     * @var int|null
     */
    private $orderTax;

    /**
     * @var string|null
     */
    private $sourceQplTitle;

    /**
     * @var bool|null
     */
    private $answerFilterEnabled;


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
     * Set sourceQplFi.
     *
     * @param int $sourceQplFi
     *
     * @return TstDynQuestSetCfg
     */
    public function setSourceQplFi($sourceQplFi)
    {
        $this->sourceQplFi = $sourceQplFi;

        return $this;
    }

    /**
     * Get sourceQplFi.
     *
     * @return int
     */
    public function getSourceQplFi()
    {
        return $this->sourceQplFi;
    }

    /**
     * Set taxFilterEnabled.
     *
     * @param bool $taxFilterEnabled
     *
     * @return TstDynQuestSetCfg
     */
    public function setTaxFilterEnabled($taxFilterEnabled)
    {
        $this->taxFilterEnabled = $taxFilterEnabled;

        return $this;
    }

    /**
     * Get taxFilterEnabled.
     *
     * @return bool
     */
    public function getTaxFilterEnabled()
    {
        return $this->taxFilterEnabled;
    }

    /**
     * Set orderTax.
     *
     * @param int|null $orderTax
     *
     * @return TstDynQuestSetCfg
     */
    public function setOrderTax($orderTax = null)
    {
        $this->orderTax = $orderTax;

        return $this;
    }

    /**
     * Get orderTax.
     *
     * @return int|null
     */
    public function getOrderTax()
    {
        return $this->orderTax;
    }

    /**
     * Set sourceQplTitle.
     *
     * @param string|null $sourceQplTitle
     *
     * @return TstDynQuestSetCfg
     */
    public function setSourceQplTitle($sourceQplTitle = null)
    {
        $this->sourceQplTitle = $sourceQplTitle;

        return $this;
    }

    /**
     * Get sourceQplTitle.
     *
     * @return string|null
     */
    public function getSourceQplTitle()
    {
        return $this->sourceQplTitle;
    }

    /**
     * Set answerFilterEnabled.
     *
     * @param bool|null $answerFilterEnabled
     *
     * @return TstDynQuestSetCfg
     */
    public function setAnswerFilterEnabled($answerFilterEnabled = null)
    {
        $this->answerFilterEnabled = $answerFilterEnabled;

        return $this;
    }

    /**
     * Get answerFilterEnabled.
     *
     * @return bool|null
     */
    public function getAnswerFilterEnabled()
    {
        return $this->answerFilterEnabled;
    }
}
