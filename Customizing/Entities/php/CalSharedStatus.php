<?php



/**
 * CalSharedStatus
 */
class CalSharedStatus
{
    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $status = '0';


    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalSharedStatus
     */
    public function setCalId($calId)
    {
        $this->calId = $calId;

        return $this;
    }

    /**
     * Get calId.
     *
     * @return int
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CalSharedStatus
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
     * Set status.
     *
     * @param int $status
     *
     * @return CalSharedStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
