<?php



/**
 * UsrSessionStats
 */
class UsrSessionStats
{
    /**
     * @var int
     */
    private $slotBegin = '0';

    /**
     * @var int
     */
    private $slotEnd = '0';

    /**
     * @var int|null
     */
    private $activeMin;

    /**
     * @var int|null
     */
    private $activeMax;

    /**
     * @var int|null
     */
    private $activeAvg;

    /**
     * @var int|null
     */
    private $activeEnd;

    /**
     * @var int|null
     */
    private $opened;

    /**
     * @var int|null
     */
    private $closedManual;

    /**
     * @var int|null
     */
    private $closedExpire;

    /**
     * @var int|null
     */
    private $closedIdle;

    /**
     * @var int|null
     */
    private $closedIdleFirst;

    /**
     * @var int|null
     */
    private $closedLimit;

    /**
     * @var int|null
     */
    private $closedLogin;

    /**
     * @var int|null
     */
    private $maxSessions;

    /**
     * @var int|null
     */
    private $closedMisc = '0';


    /**
     * Get slotBegin.
     *
     * @return int
     */
    public function getSlotBegin()
    {
        return $this->slotBegin;
    }

    /**
     * Set slotEnd.
     *
     * @param int $slotEnd
     *
     * @return UsrSessionStats
     */
    public function setSlotEnd($slotEnd)
    {
        $this->slotEnd = $slotEnd;

        return $this;
    }

    /**
     * Get slotEnd.
     *
     * @return int
     */
    public function getSlotEnd()
    {
        return $this->slotEnd;
    }

    /**
     * Set activeMin.
     *
     * @param int|null $activeMin
     *
     * @return UsrSessionStats
     */
    public function setActiveMin($activeMin = null)
    {
        $this->activeMin = $activeMin;

        return $this;
    }

    /**
     * Get activeMin.
     *
     * @return int|null
     */
    public function getActiveMin()
    {
        return $this->activeMin;
    }

    /**
     * Set activeMax.
     *
     * @param int|null $activeMax
     *
     * @return UsrSessionStats
     */
    public function setActiveMax($activeMax = null)
    {
        $this->activeMax = $activeMax;

        return $this;
    }

    /**
     * Get activeMax.
     *
     * @return int|null
     */
    public function getActiveMax()
    {
        return $this->activeMax;
    }

    /**
     * Set activeAvg.
     *
     * @param int|null $activeAvg
     *
     * @return UsrSessionStats
     */
    public function setActiveAvg($activeAvg = null)
    {
        $this->activeAvg = $activeAvg;

        return $this;
    }

    /**
     * Get activeAvg.
     *
     * @return int|null
     */
    public function getActiveAvg()
    {
        return $this->activeAvg;
    }

    /**
     * Set activeEnd.
     *
     * @param int|null $activeEnd
     *
     * @return UsrSessionStats
     */
    public function setActiveEnd($activeEnd = null)
    {
        $this->activeEnd = $activeEnd;

        return $this;
    }

    /**
     * Get activeEnd.
     *
     * @return int|null
     */
    public function getActiveEnd()
    {
        return $this->activeEnd;
    }

    /**
     * Set opened.
     *
     * @param int|null $opened
     *
     * @return UsrSessionStats
     */
    public function setOpened($opened = null)
    {
        $this->opened = $opened;

        return $this;
    }

    /**
     * Get opened.
     *
     * @return int|null
     */
    public function getOpened()
    {
        return $this->opened;
    }

    /**
     * Set closedManual.
     *
     * @param int|null $closedManual
     *
     * @return UsrSessionStats
     */
    public function setClosedManual($closedManual = null)
    {
        $this->closedManual = $closedManual;

        return $this;
    }

    /**
     * Get closedManual.
     *
     * @return int|null
     */
    public function getClosedManual()
    {
        return $this->closedManual;
    }

    /**
     * Set closedExpire.
     *
     * @param int|null $closedExpire
     *
     * @return UsrSessionStats
     */
    public function setClosedExpire($closedExpire = null)
    {
        $this->closedExpire = $closedExpire;

        return $this;
    }

    /**
     * Get closedExpire.
     *
     * @return int|null
     */
    public function getClosedExpire()
    {
        return $this->closedExpire;
    }

    /**
     * Set closedIdle.
     *
     * @param int|null $closedIdle
     *
     * @return UsrSessionStats
     */
    public function setClosedIdle($closedIdle = null)
    {
        $this->closedIdle = $closedIdle;

        return $this;
    }

    /**
     * Get closedIdle.
     *
     * @return int|null
     */
    public function getClosedIdle()
    {
        return $this->closedIdle;
    }

    /**
     * Set closedIdleFirst.
     *
     * @param int|null $closedIdleFirst
     *
     * @return UsrSessionStats
     */
    public function setClosedIdleFirst($closedIdleFirst = null)
    {
        $this->closedIdleFirst = $closedIdleFirst;

        return $this;
    }

    /**
     * Get closedIdleFirst.
     *
     * @return int|null
     */
    public function getClosedIdleFirst()
    {
        return $this->closedIdleFirst;
    }

    /**
     * Set closedLimit.
     *
     * @param int|null $closedLimit
     *
     * @return UsrSessionStats
     */
    public function setClosedLimit($closedLimit = null)
    {
        $this->closedLimit = $closedLimit;

        return $this;
    }

    /**
     * Get closedLimit.
     *
     * @return int|null
     */
    public function getClosedLimit()
    {
        return $this->closedLimit;
    }

    /**
     * Set closedLogin.
     *
     * @param int|null $closedLogin
     *
     * @return UsrSessionStats
     */
    public function setClosedLogin($closedLogin = null)
    {
        $this->closedLogin = $closedLogin;

        return $this;
    }

    /**
     * Get closedLogin.
     *
     * @return int|null
     */
    public function getClosedLogin()
    {
        return $this->closedLogin;
    }

    /**
     * Set maxSessions.
     *
     * @param int|null $maxSessions
     *
     * @return UsrSessionStats
     */
    public function setMaxSessions($maxSessions = null)
    {
        $this->maxSessions = $maxSessions;

        return $this;
    }

    /**
     * Get maxSessions.
     *
     * @return int|null
     */
    public function getMaxSessions()
    {
        return $this->maxSessions;
    }

    /**
     * Set closedMisc.
     *
     * @param int|null $closedMisc
     *
     * @return UsrSessionStats
     */
    public function setClosedMisc($closedMisc = null)
    {
        $this->closedMisc = $closedMisc;

        return $this;
    }

    /**
     * Get closedMisc.
     *
     * @return int|null
     */
    public function getClosedMisc()
    {
        return $this->closedMisc;
    }
}
