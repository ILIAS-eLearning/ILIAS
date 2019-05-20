<?php



/**
 * TstSolutions
 */
class TstSolutions
{
    /**
     * @var int
     */
    private $solutionId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var float|null
     */
    private $points;

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $value1;

    /**
     * @var string|null
     */
    private $value2;

    /**
     * @var int|null
     */
    private $step;

    /**
     * @var bool|null
     */
    private $authorized = '1';


    /**
     * Get solutionId.
     *
     * @return int
     */
    public function getSolutionId()
    {
        return $this->solutionId;
    }

    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstSolutions
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
     * @return TstSolutions
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
     * @param float|null $points
     *
     * @return TstSolutions
     */
    public function setPoints($points = null)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points.
     *
     * @return float|null
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
     * @return TstSolutions
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstSolutions
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
     * Set value1.
     *
     * @param string|null $value1
     *
     * @return TstSolutions
     */
    public function setValue1($value1 = null)
    {
        $this->value1 = $value1;

        return $this;
    }

    /**
     * Get value1.
     *
     * @return string|null
     */
    public function getValue1()
    {
        return $this->value1;
    }

    /**
     * Set value2.
     *
     * @param string|null $value2
     *
     * @return TstSolutions
     */
    public function setValue2($value2 = null)
    {
        $this->value2 = $value2;

        return $this;
    }

    /**
     * Get value2.
     *
     * @return string|null
     */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
     * Set step.
     *
     * @param int|null $step
     *
     * @return TstSolutions
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

    /**
     * Set authorized.
     *
     * @param bool|null $authorized
     *
     * @return TstSolutions
     */
    public function setAuthorized($authorized = null)
    {
        $this->authorized = $authorized;

        return $this;
    }

    /**
     * Get authorized.
     *
     * @return bool|null
     */
    public function getAuthorized()
    {
        return $this->authorized;
    }
}
