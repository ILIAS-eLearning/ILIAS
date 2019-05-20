<?php



/**
 * TstSequence
 */
class TstSequence
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
     * @var string|null
     */
    private $sequence;

    /**
     * @var string|null
     */
    private $postponed;

    /**
     * @var string|null
     */
    private $hidden;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var bool
     */
    private $ansOptConfirmed = '0';


    /**
     * Set activeFi.
     *
     * @param int $activeFi
     *
     * @return TstSequence
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
     * @return TstSequence
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
     * Set sequence.
     *
     * @param string|null $sequence
     *
     * @return TstSequence
     */
    public function setSequence($sequence = null)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return string|null
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set postponed.
     *
     * @param string|null $postponed
     *
     * @return TstSequence
     */
    public function setPostponed($postponed = null)
    {
        $this->postponed = $postponed;

        return $this;
    }

    /**
     * Get postponed.
     *
     * @return string|null
     */
    public function getPostponed()
    {
        return $this->postponed;
    }

    /**
     * Set hidden.
     *
     * @param string|null $hidden
     *
     * @return TstSequence
     */
    public function setHidden($hidden = null)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden.
     *
     * @return string|null
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstSequence
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
     * Set ansOptConfirmed.
     *
     * @param bool $ansOptConfirmed
     *
     * @return TstSequence
     */
    public function setAnsOptConfirmed($ansOptConfirmed)
    {
        $this->ansOptConfirmed = $ansOptConfirmed;

        return $this;
    }

    /**
     * Get ansOptConfirmed.
     *
     * @return bool
     */
    public function getAnsOptConfirmed()
    {
        return $this->ansOptConfirmed;
    }
}
