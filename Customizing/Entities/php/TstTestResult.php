<?php



/**
 * TstTestResult
 */
class TstTestResult
{
    /**
     * @var int
     */
    private $testResultId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var float
     */
    private $points = '0';

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var bool
     */
    private $manual = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int|null
     */
    private $hintCount = '0';

    /**
     * @var float|null
     */
    private $hintPoints = '0';

    /**
     * @var bool
     */
    private $answered = '1';

    /**
     * @var int|null
     */
    private $step;


    /**
     * Get testResultId.
     *
     * @return int
     */
    public function getTestResultId()
    {
        return $this->testResultId;
    }

    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstTestResult
     */
    public function setActiveFi($activeFi)
    {
        $this->activeFi = $activeFi;

        return $this;
    }

    /**
     * Get activeFi.
     *
     * @return int
     */
    public function getActiveFi()
    {
        return $this->activeFi;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return TstTestResult
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set points.
     *
     * @param float $points
     *
     * @return TstTestResult
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstTestResult
     */
    public function setPass($pass)
    {
        $this->pass = $pass;

        return $this;
    }

    /**
     * Get pass.
     *
     * @return int
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * Set manual.
     *
     * @param bool $manual
     *
     * @return TstTestResult
     */
    public function setManual($manual)
    {
        $this->manual = $manual;

        return $this;
    }

    /**
     * Get manual.
     *
     * @return bool
     */
    public function getManual()
    {
        return $this->manual;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstTestResult
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

    /**
     * Set hintCount.
     *
     * @param int|null $hintCount
     *
     * @return TstTestResult
     */
    public function setHintCount($hintCount = null)
    {
        $this->hintCount = $hintCount;

        return $this;
    }

    /**
     * Get hintCount.
     *
     * @return int|null
     */
    public function getHintCount()
    {
        return $this->hintCount;
    }

    /**
     * Set hintPoints.
     *
     * @param float|null $hintPoints
     *
     * @return TstTestResult
     */
    public function setHintPoints($hintPoints = null)
    {
        $this->hintPoints = $hintPoints;

        return $this;
    }

    /**
     * Get hintPoints.
     *
     * @return float|null
     */
    public function getHintPoints()
    {
        return $this->hintPoints;
    }

    /**
     * Set answered.
     *
     * @param bool $answered
     *
     * @return TstTestResult
     */
    public function setAnswered($answered)
    {
        $this->answered = $answered;

        return $this;
    }

    /**
     * Get answered.
     *
     * @return bool
     */
    public function getAnswered()
    {
        return $this->answered;
    }

    /**
     * Set step.
     *
     * @param int|null $step
     *
     * @return TstTestResult
     */
    public function setStep($step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step.
     *
     * @return int|null
     */
    public function getStep()
    {
        return $this->step;
    }
}
