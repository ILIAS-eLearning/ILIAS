<?php



/**
 * ExcAssignmentPeer
 */
class ExcAssignmentPeer
{
    /**
     * @var int
     */
    private $assId = '0';

    /**
     * @var int
     */
    private $giverId = '0';

    /**
     * @var int
     */
    private $peerId = '0';

    /**
     * @var \DateTime|null
     */
    private $tstamp;

    /**
     * @var string|null
     */
    private $pcomment;

    /**
     * @var bool
     */
    private $isValid = '0';


    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return ExcAssignmentPeer
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
     * Set giverId.
     *
     * @param int $giverId
     *
     * @return ExcAssignmentPeer
     */
    public function setGiverId($giverId)
    {
        $this->giverId = $giverId;

        return $this;
    }

    /**
     * Get giverId.
     *
     * @return int
     */
    public function getGiverId()
    {
        return $this->giverId;
    }

    /**
     * Set peerId.
     *
     * @param int $peerId
     *
     * @return ExcAssignmentPeer
     */
    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;

        return $this;
    }

    /**
     * Get peerId.
     *
     * @return int
     */
    public function getPeerId()
    {
        return $this->peerId;
    }

    /**
     * Set tstamp.
     *
     * @param \DateTime|null $tstamp
     *
     * @return ExcAssignmentPeer
     */
    public function setTstamp($tstamp = null)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return \DateTime|null
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set pcomment.
     *
     * @param string|null $pcomment
     *
     * @return ExcAssignmentPeer
     */
    public function setPcomment($pcomment = null)
    {
        $this->pcomment = $pcomment;

        return $this;
    }

    /**
     * Get pcomment.
     *
     * @return string|null
     */
    public function getPcomment()
    {
        return $this->pcomment;
    }

    /**
     * Set isValid.
     *
     * @param bool $isValid
     *
     * @return ExcAssignmentPeer
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid.
     *
     * @return bool
     */
    public function getIsValid()
    {
        return $this->isValid;
    }
}
