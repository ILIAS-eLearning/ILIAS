<?php



/**
 * UsrSessionLog
 */
class UsrSessionLog
{
    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $maxval = '0';

    /**
     * @var int
     */
    private $userId = '0';


    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return UsrSessionLog
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
     * Set maxval.
     *
     * @param int $maxval
     *
     * @return UsrSessionLog
     */
    public function setMaxval($maxval)
    {
        $this->maxval = $maxval;

        return $this;
    }

    /**
     * Get maxval.
     *
     * @return int
     */
    public function getMaxval()
    {
        return $this->maxval;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrSessionLog
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
