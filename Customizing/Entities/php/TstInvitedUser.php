<?php



/**
 * TstInvitedUser
 */
class TstInvitedUser
{
    /**
     * @var int
     */
    private $testFi = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var string|null
     */
    private $clientip;

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Set testFi.
     *
     * @param int $testFi
     *
     * @return TstInvitedUser
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
     * Set userFi.
     *
     * @param int $userFi
     *
     * @return TstInvitedUser
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
     * Set clientip.
     *
     * @param string|null $clientip
     *
     * @return TstInvitedUser
     */
    public function setClientip($clientip = null)
    {
        $this->clientip = $clientip;

        return $this;
    }

    /**
     * Get clientip.
     *
     * @return string|null
     */
    public function getClientip()
    {
        return $this->clientip;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstInvitedUser
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
