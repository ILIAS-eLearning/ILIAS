<?php



/**
 * TstSeqQstPostponed
 */
class TstSeqQstPostponed
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
     * @var int
     */
    private $cnt = '0';


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstSeqQstPostponed
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
     * @return TstSeqQstPostponed
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
     * @return TstSeqQstPostponed
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
     * Set cnt.
     *
     * @param int $cnt
     *
     * @return TstSeqQstPostponed
     */
    public function setCnt($cnt)
    {
        $this->cnt = $cnt;

        return $this;
    }

    /**
     * Get cnt.
     *
     * @return int
     */
    public function getCnt()
    {
        return $this->cnt;
    }
}
