<?php



/**
 * SvyAnonymous
 */
class SvyAnonymous
{
    /**
     * @var int
     */
    private $anonymousId = '0';

    /**
     * @var string|null
     */
    private $surveyKey;

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var string|null
     */
    private $userKey;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $externaldata;

    /**
     * @var int
     */
    private $sent = '0';


    /**
     * Get anonymousId.
     *
     * @return int
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * Set surveyKey.
     *
     * @param string|null $surveyKey
     *
     * @return SvyAnonymous
     */
    public function setSurveyKey($surveyKey = null)
    {
        $this->surveyKey = $surveyKey;

        return $this;
    }

    /**
     * Get surveyKey.
     *
     * @return string|null
     */
    public function getSurveyKey()
    {
        return $this->surveyKey;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyAnonymous
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
     * Set userKey.
     *
     * @param string|null $userKey
     *
     * @return SvyAnonymous
     */
    public function setUserKey($userKey = null)
    {
        $this->userKey = $userKey;

        return $this;
    }

    /**
     * Get userKey.
     *
     * @return string|null
     */
    public function getUserKey()
    {
        return $this->userKey;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyAnonymous
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
     * Set externaldata.
     *
     * @param string|null $externaldata
     *
     * @return SvyAnonymous
     */
    public function setExternaldata($externaldata = null)
    {
        $this->externaldata = $externaldata;

        return $this;
    }

    /**
     * Get externaldata.
     *
     * @return string|null
     */
    public function getExternaldata()
    {
        return $this->externaldata;
    }

    /**
     * Set sent.
     *
     * @param int $sent
     *
     * @return SvyAnonymous
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent.
     *
     * @return int
     */
    public function getSent()
    {
        return $this->sent;
    }
}
