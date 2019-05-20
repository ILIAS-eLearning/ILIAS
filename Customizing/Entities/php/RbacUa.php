<?php



/**
 * RbacUa
 */
class RbacUa
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $rolId = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return RbacUa
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
     * Set rolId.
     *
     * @param int $rolId
     *
     * @return RbacUa
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
}
