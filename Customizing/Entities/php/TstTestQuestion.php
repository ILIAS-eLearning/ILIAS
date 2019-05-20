<?php



/**
 * TstTestQuestion
 */
class TstTestQuestion
{
    /**
     * @var int
     */
    private $testQuestionId = '0';

    /**
     * @var int
     */
    private $testFi = '0';

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
    private $tstamp = '0';

    /**
     * @var bool
     */
    private $obligatory = '0';


    /**
     * Get testQuestionId.
     *
     * @return int
     */
    public function getTestQuestionId()
    {
        return $this->testQuestionId;
    }

    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstTestQuestion
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
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return TstTestQuestion
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
     * @return TstTestQuestion
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstTestQuestion
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
     * Set obligatory.
     *
     * @param bool $obligatory
     *
     * @return TstTestQuestion
     */
    public function setObligatory($obligatory)
    {
        $this->obligatory = $obligatory;

        return $this;
    }

    /**
     * Get obligatory.
     *
     * @return bool
     */
    public function getObligatory()
    {
        return $this->obligatory;
    }
}
