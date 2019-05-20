<?php



/**
 * UsrCronMailReminder
 */
class UsrCronMailReminder
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $ts = '0';


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
     * Set ts.
     *
     * @param int $ts
     *
     * @return UsrCronMailReminder
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
