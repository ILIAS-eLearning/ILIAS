<?php



/**
 * ExcIdl
 */
class ExcIdl
{
    /**
     * @var int
     */
    private $assId = '0';

    /**
     * @var int
     */
    private $memberId = '0';

    /**
     * @var bool
     */
    private $isTeam = '0';

    /**
     * @var int|null
     */
    private $tstamp = '0';

    /**
     * @var int|null
     */
    private $startingTs = '0';


    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return ExcIdl
     */
    public function setAssId($assId)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set memberId.
     *
     * @param int $memberId
     *
     * @return ExcIdl
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;

        return $this;
    }

    /**
     * Get memberId.
     *
     * @return int
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set isTeam.
     *
     * @param bool $isTeam
     *
     * @return ExcIdl
     */
    public function setIsTeam($isTeam)
    {
        $this->isTeam = $isTeam;

        return $this;
    }

    /**
     * Get isTeam.
     *
     * @return bool
     */
    public function getIsTeam()
    {
        return $this->isTeam;
    }

    /**
     * Set tstamp.
     *
     * @param int|null $tstamp
     *
     * @return ExcIdl
     */
    public function setTstamp($tstamp = null)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int|null
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set startingTs.
     *
     * @param int|null $startingTs
     *
     * @return ExcIdl
     */
    public function setStartingTs($startingTs = null)
    {
        $this->startingTs = $startingTs;

        return $this;
    }

    /**
     * Get startingTs.
     *
     * @return int|null
     */
    public function getStartingTs()
    {
        return $this->startingTs;
    }
}
