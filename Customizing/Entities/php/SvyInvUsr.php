<?php



/**
 * SvyInvUsr
 */
class SvyInvUsr
{
    /**
     * @var int
     */
    private $invitedUserId = '0';

    /**
     * @var int
     */
    private $surveyFi = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get invitedUserId.
     *
     * @return int
     */
    public function getInvitedUserId()
    {
        return $this->invitedUserId;
    }

    /**
     * Set surveyFi.
     *
     * @param int $surveyFi
     *
     * @return SvyInvUsr
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
     * @return SvyInvUsr
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyInvUsr
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
