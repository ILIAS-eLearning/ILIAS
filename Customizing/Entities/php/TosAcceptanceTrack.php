<?php



/**
 * TosAcceptanceTrack
 */
class TosAcceptanceTrack
{
    /**
     * @var int
     */
    private $tosvId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $ts = '0';

    /**
     * @var string|null
     */
    private $criteria;


    /**
     * Set tosvId.
     *
     * @param int $tosvId
     *
     * @return TosAcceptanceTrack
     */
    public function setTosvId($tosvId)
    {
        $this->tosvId = $tosvId;

        return $this;
    }

    /**
     * Get tosvId.
     *
     * @return int
     */
    public function getTosvId()
    {
        return $this->tosvId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return TosAcceptanceTrack
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
     * Set ts.
     *
     * @param int $ts
     *
     * @return TosAcceptanceTrack
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

    /**
     * Set criteria.
     *
     * @param string|null $criteria
     *
     * @return TosAcceptanceTrack
     */
    public function setCriteria($criteria = null)
    {
        $this->criteria = $criteria;

        return $this;
    }

    /**
     * Get criteria.
     *
     * @return string|null
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
