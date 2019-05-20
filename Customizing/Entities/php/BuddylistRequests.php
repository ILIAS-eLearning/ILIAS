<?php



/**
 * BuddylistRequests
 */
class BuddylistRequests
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $buddyUsrId = '0';

    /**
     * @var bool
     */
    private $ignored = '0';

    /**
     * @var int
     */
    private $ts = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return BuddylistRequests
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
     * Set buddyUsrId.
     *
     * @param int $buddyUsrId
     *
     * @return BuddylistRequests
     */
    public function setBuddyUsrId($buddyUsrId)
    {
        $this->buddyUsrId = $buddyUsrId;

        return $this;
    }

    /**
     * Get buddyUsrId.
     *
     * @return int
     */
    public function getBuddyUsrId()
    {
        return $this->buddyUsrId;
    }

    /**
     * Set ignored.
     *
     * @param bool $ignored
     *
     * @return BuddylistRequests
     */
    public function setIgnored($ignored)
    {
        $this->ignored = $ignored;

        return $this;
    }

    /**
     * Get ignored.
     *
     * @return bool
     */
    public function getIgnored()
    {
        return $this->ignored;
    }

    /**
     * Set ts.
     *
     * @param int $ts
     *
     * @return BuddylistRequests
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return int
     */
    public function getTs()
    {
        return $this->ts;
    }
}
