<?php



/**
 * RbacPa
 */
class RbacPa
{
    /**
     * @var int
     */
    private $rolId = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var string|null
     */
    private $opsId;


    /**
     * Set rolId.
     *
     * @param int $rolId
     *
     * @return RbacPa
     */
    public function setRolId($rolId)
    {
        $this->rolId = $rolId;

        return $this;
    }

    /**
     * Get rolId.
     *
     * @return int
     */
    public function getRolId()
    {
        return $this->rolId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return RbacPa
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
     * Set opsId.
     *
     * @param string|null $opsId
     *
     * @return RbacPa
     */
    public function setOpsId($opsId = null)
    {
        $this->opsId = $opsId;

        return $this;
    }

    /**
     * Get opsId.
     *
     * @return string|null
     */
    public function getOpsId()
    {
        return $this->opsId;
    }
}
