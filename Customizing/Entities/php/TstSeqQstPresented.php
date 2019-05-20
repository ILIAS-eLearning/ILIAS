<?php



/**
 * TstSeqQstPresented
 */
class TstSeqQstPresented
{
    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var int
     */
    private $questionFi = '0';


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstSeqQstPresented
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
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstSeqQstPresented
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
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return TstSeqQstPresented
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
}
