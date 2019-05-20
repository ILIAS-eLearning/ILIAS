<?php



/**
 * LoginnameHistory
 */
class LoginnameHistory
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $login = '';

    /**
     * @var int
     */
    private $historyDate = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return LoginnameHistory
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set login.
     *
     * @param string $login
     *
     * @return LoginnameHistory
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set historyDate.
     *
     * @param int $historyDate
     *
     * @return LoginnameHistory
     */
    public function setHistoryDate($historyDate)
    {
        $this->historyDate = $historyDate;

        return $this;
    }

    /**
     * Get historyDate.
     *
     * @return int
     */
    public function getHistoryDate()
    {
        return $this->historyDate;
    }
}
