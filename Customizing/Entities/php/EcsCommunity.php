<?php



/**
 * EcsCommunity
 */
class EcsCommunity
{
    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $cid = '0';

    /**
     * @var int
     */
    private $ownId = '0';

    /**
     * @var string|null
     */
    private $cname;

    /**
     * @var string|null
     */
    private $mids;


    /**
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsCommunity
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
     * Set cid.
     *
     * @param int $cid
     *
     * @return EcsCommunity
     */
    public function setCid($cid)
    {
        $this->cid = $cid;

        return $this;
    }

    /**
     * Get cid.
     *
     * @return int
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * Set ownId.
     *
     * @param int $ownId
     *
     * @return EcsCommunity
     */
    public function setOwnId($ownId)
    {
        $this->ownId = $ownId;

        return $this;
    }

    /**
     * Get ownId.
     *
     * @return int
     */
    public function getOwnId()
    {
        return $this->ownId;
    }

    /**
     * Set cname.
     *
     * @param string|null $cname
     *
     * @return EcsCommunity
     */
    public function setCname($cname = null)
    {
        $this->cname = $cname;

        return $this;
    }

    /**
     * Get cname.
     *
     * @return string|null
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * Set mids.
     *
     * @param string|null $mids
     *
     * @return EcsCommunity
     */
    public function setMids($mids = null)
    {
        $this->mids = $mids;

        return $this;
    }

    /**
     * Get mids.
     *
     * @return string|null
     */
    public function getMids()
    {
        return $this->mids;
    }
}
