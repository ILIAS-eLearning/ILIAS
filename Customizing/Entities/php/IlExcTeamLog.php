<?php



/**
 * IlExcTeamLog
 */
class IlExcTeamLog
{
    /**
     * @var int
     */
    private $logId = '0';

    /**
     * @var int
     */
    private $teamId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $details;

    /**
     * @var bool
     */
    private $action = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get logId.
     *
     * @return int
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * Set teamId.
     *
     * @param int $teamId
     *
     * @return IlExcTeamLog
     */
    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;

        return $this;
    }

    /**
     * Get teamId.
     *
     * @return int
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return IlExcTeamLog
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set details.
     *
     * @param string|null $details
     *
     * @return IlExcTeamLog
     */
    public function setDetails($details = null)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set action.
     *
     * @param bool $action
     *
     * @return IlExcTeamLog
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return bool
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return IlExcTeamLog
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
