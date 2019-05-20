<?php



/**
 * SvyFinished
 */
class SvyFinished
{
    /**
     * @var int
     */
    private $finishedId = '0';

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var string|null
     */
    private $anonymousId;

    /**
     * @var string|null
     */
    private $state = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $lastpage = '0';

    /**
     * @var int|null
     */
    private $apprId = '0';


    /**
     * Get finishedId.
     *
     * @return int
     */
    public function getFinishedId()
    {
        return $this->finishedId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyFinished
     */
    public function setSurveyFi($surveyFi)
    {
        $this->surveyFi = $surveyFi;

        return $this;
    }

    /**
     * Get surveyFi.
     *
     * @return int
     */
    public function getSurveyFi()
    {
        return $this->surveyFi;
    }

    /**
     * Set userFi.
     *
     * @param int $userFi
     *
     * @return SvyFinished
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
     * @return SvyFinished
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
     * Set state.
     *
     * @param string|null $state
     *
     * @return SvyFinished
     */
    public function setState($state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyFinished
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
     * Set lastpage.
     *
     * @param int $lastpage
     *
     * @return SvyFinished
     */
    public function setLastpage($lastpage)
    {
        $this->lastpage = $lastpage;

        return $this;
    }

    /**
     * Get lastpage.
     *
     * @return int
     */
    public function getLastpage()
    {
        return $this->lastpage;
    }

    /**
     * Set apprId.
     *
     * @param int|null $apprId
     *
     * @return SvyFinished
     */
    public function setApprId($apprId = null)
    {
        $this->apprId = $apprId;

        return $this;
    }

    /**
     * Get apprId.
     *
     * @return int|null
     */
    public function getApprId()
    {
        return $this->apprId;
    }
}
