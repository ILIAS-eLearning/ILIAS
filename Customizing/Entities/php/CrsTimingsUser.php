<?php



/**
 * CrsTimingsUser
 */
class CrsTimingsUser
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $sstart = '0';

    /**
     * @var int
     */
    private $ssend = '0';


    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return CrsTimingsUser
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CrsTimingsUser
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
     * Set sstart.
     *
     * @param int $sstart
     *
     * @return CrsTimingsUser
     */
    public function setSstart($sstart)
    {
        $this->sstart = $sstart;

        return $this;
    }

    /**
     * Get sstart.
     *
     * @return int
     */
    public function getSstart()
    {
        return $this->sstart;
    }

    /**
     * Set ssend.
     *
     * @param int $ssend
     *
     * @return CrsTimingsUser
     */
    public function setSsend($ssend)
    {
        $this->ssend = $ssend;

        return $this;
    }

    /**
     * Get ssend.
     *
     * @return int
     */
    public function getSsend()
    {
        return $this->ssend;
    }
}
