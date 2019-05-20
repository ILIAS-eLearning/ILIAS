<?php



/**
 * MemberNoti
 */
class MemberNoti
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var bool
     */
    private $nmode = '0';


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
     * Set nmode.
     *
     * @param bool $nmode
     *
     * @return MemberNoti
     */
    public function setNmode($nmode)
    {
        $this->nmode = $nmode;

        return $this;
    }

    /**
     * Get nmode.
     *
     * @return bool
     */
    public function getNmode()
    {
        return $this->nmode;
    }
}
