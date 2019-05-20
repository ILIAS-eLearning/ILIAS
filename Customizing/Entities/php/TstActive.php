<?php



/**
 * TstActive
 */
class TstActive
{
    /**
     * @var int
     */
    private $activeId = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var string|null
     */
    private $anonymousId;

    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var int
     */
    private $tries = '0';

    /**
     * @var bool
     */
    private $submitted = '0';

    /**
     * @var \DateTime|null
     */
    private $submittimestamp;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $importname;

    /**
     * @var string|null
     */
    private $taxfilter;

    /**
     * @var int
     */
    private $lastindex = '0';

    /**
     * @var int|null
     */
    private $lastFinishedPass;

    /**
     * @var string|null
     */
    private $answerstatusfilter;

    /**
     * @var int|null
     */
    private $objectiveContainer;

    /**
     * @var string|null
     */
    private $startLock;

    /**
     * @var string|null
     */
    private $lastPmode;

    /**
     * @var int|null
     */
    private $lastStartedPass;


    /**
     * Get activeId.
     *
     * @return int
     */
    public function getActiveId()
    {
        return $this->activeId;
    }

    /**
     * Set userFi.
     *
     * @param int $userFi
     *
     * @return TstActive
     */
    public function setUserFi($userFi)
    {
        $this->userFi = $userFi;

        return $this;
    }

    /**
     * Get userFi.
     *
     * @return int
     */
    public function getUserFi()
    {
        return $this->userFi;
    }

    /**
     * Set anonymousId.
     *
     * @param string|null $anonymousId
     *
     * @return TstActive
     */
    public function setAnonymousId($anonymousId = null)
    {
        $this->anonymousId = $anonymousId;

        return $this;
    }

    /**
     * Get anonymousId.
     *
     * @return string|null
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstActive
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
     * Set tries.
     *
     * @param int $tries
     *
     * @return TstActive
     */
    public function setTries($tries)
    {
        $this->tries = $tries;

        return $this;
    }

    /**
     * Get tries.
     *
     * @return int
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Set submitted.
     *
     * @param bool $submitted
     *
     * @return TstActive
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * Get submitted.
     *
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Set submittimestamp.
     *
     * @param \DateTime|null $submittimestamp
     *
     * @return TstActive
     */
    public function setSubmittimestamp($submittimestamp = null)
    {
        $this->submittimestamp = $submittimestamp;

        return $this;
    }

    /**
     * Get submittimestamp.
     *
     * @return \DateTime|null
     */
    public function getSubmittimestamp()
    {
        return $this->submittimestamp;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstActive
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
     * Set importname.
     *
     * @param string|null $importname
     *
     * @return TstActive
     */
    public function setImportname($importname = null)
    {
        $this->importname = $importname;

        return $this;
    }

    /**
     * Get importname.
     *
     * @return string|null
     */
    public function getImportname()
    {
        return $this->importname;
    }

    /**
     * Set taxfilter.
     *
     * @param string|null $taxfilter
     *
     * @return TstActive
     */
    public function setTaxfilter($taxfilter = null)
    {
        $this->taxfilter = $taxfilter;

        return $this;
    }

    /**
     * Get taxfilter.
     *
     * @return string|null
     */
    public function getTaxfilter()
    {
        return $this->taxfilter;
    }

    /**
     * Set lastindex.
     *
     * @param int $lastindex
     *
     * @return TstActive
     */
    public function setLastindex($lastindex)
    {
        $this->lastindex = $lastindex;

        return $this;
    }

    /**
     * Get lastindex.
     *
     * @return int
     */
    public function getLastindex()
    {
        return $this->lastindex;
    }

    /**
     * Set lastFinishedPass.
     *
     * @param int|null $lastFinishedPass
     *
     * @return TstActive
     */
    public function setLastFinishedPass($lastFinishedPass = null)
    {
        $this->lastFinishedPass = $lastFinishedPass;

        return $this;
    }

    /**
     * Get lastFinishedPass.
     *
     * @return int|null
     */
    public function getLastFinishedPass()
    {
        return $this->lastFinishedPass;
    }

    /**
     * Set answerstatusfilter.
     *
     * @param string|null $answerstatusfilter
     *
     * @return TstActive
     */
    public function setAnswerstatusfilter($answerstatusfilter = null)
    {
        $this->answerstatusfilter = $answerstatusfilter;

        return $this;
    }

    /**
     * Get answerstatusfilter.
     *
     * @return string|null
     */
    public function getAnswerstatusfilter()
    {
        return $this->answerstatusfilter;
    }

    /**
     * Set objectiveContainer.
     *
     * @param int|null $objectiveContainer
     *
     * @return TstActive
     */
    public function setObjectiveContainer($objectiveContainer = null)
    {
        $this->objectiveContainer = $objectiveContainer;

        return $this;
    }

    /**
     * Get objectiveContainer.
     *
     * @return int|null
     */
    public function getObjectiveContainer()
    {
        return $this->objectiveContainer;
    }

    /**
     * Set startLock.
     *
     * @param string|null $startLock
     *
     * @return TstActive
     */
    public function setStartLock($startLock = null)
    {
        $this->startLock = $startLock;

        return $this;
    }

    /**
     * Get startLock.
     *
     * @return string|null
     */
    public function getStartLock()
    {
        return $this->startLock;
    }

    /**
     * Set lastPmode.
     *
     * @param string|null $lastPmode
     *
     * @return TstActive
     */
    public function setLastPmode($lastPmode = null)
    {
        $this->lastPmode = $lastPmode;

        return $this;
    }

    /**
     * Get lastPmode.
     *
     * @return string|null
     */
    public function getLastPmode()
    {
        return $this->lastPmode;
    }

    /**
     * Set lastStartedPass.
     *
     * @param int|null $lastStartedPass
     *
     * @return TstActive
     */
    public function setLastStartedPass($lastStartedPass = null)
    {
        $this->lastStartedPass = $lastStartedPass;

        return $this;
    }

    /**
     * Get lastStartedPass.
     *
     * @return int|null
     */
    public function getLastStartedPass()
    {
        return $this->lastStartedPass;
    }
}
