<?php



/**
 * TstTestRndQst
 */
class TstTestRndQst
{
    /**
     * @var int
     */
    private $testRandomQuestionId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $sequence = '0';

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int|null
     */
    private $srcPoolDefFi;


    /**
     * Get testRandomQuestionId.
     *
     * @return int
     */
    public function getTestRandomQuestionId()
    {
        return $this->testRandomQuestionId;
    }

    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstTestRndQst
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
     * @return TstTestRndQst
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
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return TstTestRndQst
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstTestRndQst
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
     * @return TstTestRndQst
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
     * Set srcPoolDefFi.
     *
     * @param int|null $srcPoolDefFi
     *
     * @return TstTestRndQst
     */
    public function setSrcPoolDefFi($srcPoolDefFi = null)
    {
        $this->srcPoolDefFi = $srcPoolDefFi;

        return $this;
    }

    /**
     * Get srcPoolDefFi.
     *
     * @return int|null
     */
    public function getSrcPoolDefFi()
    {
        return $this->srcPoolDefFi;
    }
}
