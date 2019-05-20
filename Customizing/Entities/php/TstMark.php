<?php



/**
 * TstMark
 */
class TstMark
{
    /**
     * @var int
     */
    private $markId = '0';

    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var string|null
     */
    private $shortName;

    /**
     * @var string|null
     */
    private $officialName;

    /**
     * @var float
     */
    private $minimumLevel = '0';

    /**
     * @var string|null
     */
    private $passed = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get markId.
     *
     * @return int
     */
    public function getMarkId()
    {
        return $this->markId;
    }

    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstMark
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
     * Set shortName.
     *
     * @param string|null $shortName
     *
     * @return TstMark
     */
    public function setShortName($shortName = null)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName.
     *
     * @return string|null
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Set officialName.
     *
     * @param string|null $officialName
     *
     * @return TstMark
     */
    public function setOfficialName($officialName = null)
    {
        $this->officialName = $officialName;

        return $this;
    }

    /**
     * Get officialName.
     *
     * @return string|null
     */
    public function getOfficialName()
    {
        return $this->officialName;
    }

    /**
     * Set minimumLevel.
     *
     * @param float $minimumLevel
     *
     * @return TstMark
     */
    public function setMinimumLevel($minimumLevel)
    {
        $this->minimumLevel = $minimumLevel;

        return $this;
    }

    /**
     * Get minimumLevel.
     *
     * @return float
     */
    public function getMinimumLevel()
    {
        return $this->minimumLevel;
    }

    /**
     * Set passed.
     *
     * @param string|null $passed
     *
     * @return TstMark
     */
    public function setPassed($passed = null)
    {
        $this->passed = $passed;

        return $this;
    }

    /**
     * Get passed.
     *
     * @return string|null
     */
    public function getPassed()
    {
        return $this->passed;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstMark
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
