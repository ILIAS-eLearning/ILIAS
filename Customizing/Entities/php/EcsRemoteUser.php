<?php



/**
 * EcsRemoteUser
 */
class EcsRemoteUser
{
    /**
     * @var int
     */
    private $eruId = '0';

    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string|null
     */
    private $remoteUsrId;


    /**
     * Get eruId.
     *
     * @return int
     */
    public function getEruId()
    {
        return $this->eruId;
    }

    /**
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsRemoteUser
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsRemoteUser
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return EcsRemoteUser
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
     * Set remoteUsrId.
     *
     * @param string|null $remoteUsrId
     *
     * @return EcsRemoteUser
     */
    public function setRemoteUsrId($remoteUsrId = null)
    {
        $this->remoteUsrId = $remoteUsrId;

        return $this;
    }

    /**
     * Get remoteUsrId.
     *
     * @return string|null
     */
    public function getRemoteUsrId()
    {
        return $this->remoteUsrId;
    }
}
