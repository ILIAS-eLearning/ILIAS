<?php



/**
 * UsrPwassist
 */
class UsrPwassist
{
    /**
     * @var string
     */
    private $pwassistId = '';

    /**
     * @var int
     */
    private $expires = '0';

    /**
     * @var int
     */
    private $ctime = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Get pwassistId.
     *
     * @return string
     */
    public function getPwassistId()
    {
        return $this->pwassistId;
    }

    /**
     * Set expires.
     *
     * @param int $expires
     *
     * @return UsrPwassist
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires.
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set ctime.
     *
     * @param int $ctime
     *
     * @return UsrPwassist
     */
    public function setCtime($ctime)
    {
        $this->ctime = $ctime;

        return $this;
    }

    /**
     * Get ctime.
     *
     * @return int
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrPwassist
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
}
