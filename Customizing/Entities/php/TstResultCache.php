<?php



/**
 * TstResultCache
 */
class TstResultCache
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
     * @var float
     */
    private $maxPoints = '0';

    /**
     * @var float
     */
    private $reachedPoints = '0';

    /**
     * @var string
     */
    private $markShort = '';

    /**
     * @var string
     */
    private $markOfficial = '';

    /**
     * @var int
     */
    private $passed = '0';

    /**
     * @var int
     */
    private $failed = '0';

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
    private $obligationsAnswered = '1';

    /**
     * @var bool|null
     */
    private $passedOnce = '0';


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
     * @return TstResultCache
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
     * Set maxPoints.
     *
     * @param float $maxPoints
     *
     * @return TstResultCache
     */
    public function setMaxPoints($maxPoints)
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    /**
     * Get maxPoints.
     *
     * @return float
     */
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }

    /**
     * Set reachedPoints.
     *
     * @param float $reachedPoints
     *
     * @return TstResultCache
     */
    public function setReachedPoints($reachedPoints)
    {
        $this->reachedPoints = $reachedPoints;

        return $this;
    }

    /**
     * Get reachedPoints.
     *
     * @return float
     */
    public function getReachedPoints()
    {
        return $this->reachedPoints;
    }

    /**
     * Set markShort.
     *
     * @param string $markShort
     *
     * @return TstResultCache
     */
    public function setMarkShort($markShort)
    {
        $this->markShort = $markShort;

        return $this;
    }

    /**
     * Get markShort.
     *
     * @return string
     */
    public function getMarkShort()
    {
        return $this->markShort;
    }

    /**
     * Set markOfficial.
     *
     * @param string $markOfficial
     *
     * @return TstResultCache
     */
    public function setMarkOfficial($markOfficial)
    {
        $this->markOfficial = $markOfficial;

        return $this;
    }

    /**
     * Get markOfficial.
     *
     * @return string
     */
    public function getMarkOfficial()
    {
        return $this->markOfficial;
    }

    /**
     * Set passed.
     *
     * @param int $passed
     *
     * @return TstResultCache
     */
    public function setPassed($passed)
    {
        $this->passed = $passed;

        return $this;
    }

    /**
     * Get passed.
     *
     * @return int
     */
    public function getPassed()
    {
        return $this->passed;
    }

    /**
     * Set failed.
     *
     * @param int $failed
     *
     * @return TstResultCache
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Get failed.
     *
     * @return int
     */
    public function getFailed()
    {
        return $this->failed;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstResultCache
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
     * @return TstResultCache
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
     * @return TstResultCache
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
     * Set obligationsAnswered.
     *
     * @param bool $obligationsAnswered
     *
     * @return TstResultCache
     */
    public function setObligationsAnswered($obligationsAnswered)
    {
        $this->obligationsAnswered = $obligationsAnswered;

        return $this;
    }

    /**
     * Get obligationsAnswered.
     *
     * @return bool
     */
    public function getObligationsAnswered()
    {
        return $this->obligationsAnswered;
    }

    /**
     * Set passedOnce.
     *
     * @param bool|null $passedOnce
     *
     * @return TstResultCache
     */
    public function setPassedOnce($passedOnce = null)
    {
        $this->passedOnce = $passedOnce;

        return $this;
    }

    /**
     * Get passedOnce.
     *
     * @return bool|null
     */
    public function getPassedOnce()
    {
        return $this->passedOnce;
    }
}
