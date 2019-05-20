<?php



/**
 * TstSklThresholds
 */
class TstSklThresholds
{
    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var int
     */
    private $skillBaseFi = '0';

    /**
     * @var int
     */
    private $skillTrefFi = '0';

    /**
     * @var int
     */
    private $skillLevelFi = '0';

    /**
     * @var int
     */
    private $threshold = '0';


    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstSklThresholds
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
     * Set skillBaseFi.
     *
     * @param int $skillBaseFi
     *
     * @return TstSklThresholds
     */
    public function setSkillBaseFi($skillBaseFi)
    {
        $this->skillBaseFi = $skillBaseFi;

        return $this;
    }

    /**
     * Get skillBaseFi.
     *
     * @return int
     */
    public function getSkillBaseFi()
    {
        return $this->skillBaseFi;
    }

    /**
     * Set skillTrefFi.
     *
     * @param int $skillTrefFi
     *
     * @return TstSklThresholds
     */
    public function setSkillTrefFi($skillTrefFi)
    {
        $this->skillTrefFi = $skillTrefFi;

        return $this;
    }

    /**
     * Get skillTrefFi.
     *
     * @return int
     */
    public function getSkillTrefFi()
    {
        return $this->skillTrefFi;
    }

    /**
     * Set skillLevelFi.
     *
     * @param int $skillLevelFi
     *
     * @return TstSklThresholds
     */
    public function setSkillLevelFi($skillLevelFi)
    {
        $this->skillLevelFi = $skillLevelFi;

        return $this;
    }

    /**
     * Get skillLevelFi.
     *
     * @return int
     */
    public function getSkillLevelFi()
    {
        return $this->skillLevelFi;
    }

    /**
     * Set threshold.
     *
     * @param int $threshold
     *
     * @return TstSklThresholds
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Get threshold.
     *
     * @return int
     */
    public function getThreshold()
    {
        return $this->threshold;
    }
}
