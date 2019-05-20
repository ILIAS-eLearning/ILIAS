<?php



/**
 * TstTimes
 */
class TstTimes
{
    /**
     * @var int
     */
    private $timesId = '0';

    /**
     * @var int
     */
    private $activeFi = '0';

    /**
     * @var \DateTime|null
     */
    private $started;

    /**
     * @var \DateTime|null
     */
    private $finished;

    /**
     * @var int
     */
    private $pass = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get timesId.
     *
     * @return int
     */
    public function getTimesId()
    {
        return $this->timesId;
    }

    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstTimes
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
     * Set started.
     *
     * @param \DateTime|null $started
     *
     * @return TstTimes
     */
    public function setStarted($started = null)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started.
     *
     * @return \DateTime|null
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set finished.
     *
     * @param \DateTime|null $finished
     *
     * @return TstTimes
     */
    public function setFinished($finished = null)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished.
     *
     * @return \DateTime|null
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * Set pass.
     *
     * @param int $pass
     *
     * @return TstTimes
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
     * @return TstTimes
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
