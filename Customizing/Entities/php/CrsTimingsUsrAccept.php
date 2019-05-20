<?php



/**
 * CrsTimingsUsrAccept
 */
class CrsTimingsUsrAccept
{
    /**
     * @var int
     */
    private $crsId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var bool
     */
    private $accept = '0';

    /**
     * @var string|null
     */
    private $remark;

    /**
     * @var bool
     */
    private $visible = '0';


    /**
     * Set crsId.
     *
     * @param int $crsId
     *
     * @return CrsTimingsUsrAccept
     */
    public function setCrsId($crsId)
    {
        $this->crsId = $crsId;

        return $this;
    }

    /**
     * Get crsId.
     *
     * @return int
     */
    public function getCrsId()
    {
        return $this->crsId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CrsTimingsUsrAccept
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
     * Set accept.
     *
     * @param bool $accept
     *
     * @return CrsTimingsUsrAccept
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;

        return $this;
    }

    /**
     * Get accept.
     *
     * @return bool
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * Set remark.
     *
     * @param string|null $remark
     *
     * @return CrsTimingsUsrAccept
     */
    public function setRemark($remark = null)
    {
        $this->remark = $remark;

        return $this;
    }

    /**
     * Get remark.
     *
     * @return string|null
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     *
     * @return CrsTimingsUsrAccept
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }
}
