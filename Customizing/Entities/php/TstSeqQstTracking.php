<?php



/**
 * TstSeqQstTracking
 */
class TstSeqQstTracking
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
     * @var string|null
     */
    private $status;

    /**
     * @var int
     */
    private $orderindex = '0';


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstSeqQstTracking
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
     * @return TstSeqQstTracking
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
     * @return TstSeqQstTracking
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
     * Set status.
     *
     * @param string|null $status
     *
     * @return TstSeqQstTracking
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set orderindex.
     *
     * @param int $orderindex
     *
     * @return TstSeqQstTracking
     */
    public function setOrderindex($orderindex)
    {
        $this->orderindex = $orderindex;

        return $this;
    }

    /**
     * Get orderindex.
     *
     * @return int
     */
    public function getOrderindex()
    {
        return $this->orderindex;
    }
}
