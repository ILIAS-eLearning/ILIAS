<?php



/**
 * CmiCustom
 */
class CmiCustom
{
    /**
     * @var int
     */
    private $scoId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $lvalue = ' ';

    /**
     * @var string|null
     */
    private $rvalue;

    /**
     * @var \DateTime|null
     */
    private $cTimestamp;


    /**
     * Set scoId.
     *
     * @param int $scoId
     *
     * @return CmiCustom
     */
    public function setScoId($scoId)
    {
        $this->scoId = $scoId;

        return $this;
    }

    /**
     * Get scoId.
     *
     * @return int
     */
    public function getScoId()
    {
        return $this->scoId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CmiCustom
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CmiCustom
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set lvalue.
     *
     * @param string $lvalue
     *
     * @return CmiCustom
     */
    public function setLvalue($lvalue)
    {
        $this->lvalue = $lvalue;

        return $this;
    }

    /**
     * Get lvalue.
     *
     * @return string
     */
    public function getLvalue()
    {
        return $this->lvalue;
    }

    /**
     * Set rvalue.
     *
     * @param string|null $rvalue
     *
     * @return CmiCustom
     */
    public function setRvalue($rvalue = null)
    {
        $this->rvalue = $rvalue;

        return $this;
    }

    /**
     * Get rvalue.
     *
     * @return string|null
     */
    public function getRvalue()
    {
        return $this->rvalue;
    }

    /**
     * Set cTimestamp.
     *
     * @param \DateTime|null $cTimestamp
     *
     * @return CmiCustom
     */
    public function setCTimestamp($cTimestamp = null)
    {
        $this->cTimestamp = $cTimestamp;

        return $this;
    }

    /**
     * Get cTimestamp.
     *
     * @return \DateTime|null
     */
    public function getCTimestamp()
    {
        return $this->cTimestamp;
    }
}
