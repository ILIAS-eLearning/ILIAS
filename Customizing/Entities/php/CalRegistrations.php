<?php



/**
 * CalRegistrations
 */
class CalRegistrations
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
    private $dstart = '0';

    /**
     * @var int
     */
    private $dend = '0';


    /**
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalRegistrations
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
     * @return CalRegistrations
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
     * Set dstart.
     *
     * @param int $dstart
     *
     * @return CalRegistrations
     */
    public function setDstart($dstart)
    {
        $this->dstart = $dstart;

        return $this;
    }

    /**
     * Get dstart.
     *
     * @return int
     */
    public function getDstart()
    {
        return $this->dstart;
    }

    /**
     * Set dend.
     *
     * @param int $dend
     *
     * @return CalRegistrations
     */
    public function setDend($dend)
    {
        $this->dend = $dend;

        return $this;
    }

    /**
     * Get dend.
     *
     * @return int
     */
    public function getDend()
    {
        return $this->dend;
    }
}
