<?php



/**
 * Buddylist
 */
class Buddylist
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
     * @var int
     */
    private $ts = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return Buddylist
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
     * @return Buddylist
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
     * Set ts.
     *
     * @param int $ts
     *
     * @return Buddylist
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
