<?php



/**
 * RbacTa
 */
class RbacTa
{
    /**
     * @var int
     */
    private $typId = '0';

    /**
     * @var int
     */
    private $opsId = '0';


    /**
     * Set typId.
     *
     * @param int $typId
     *
     * @return RbacTa
     */
    public function setTypId($typId)
    {
        $this->typId = $typId;

        return $this;
    }

    /**
     * Get typId.
     *
     * @return int
     */
    public function getTypId()
    {
        return $this->typId;
    }

    /**
     * Set opsId.
     *
     * @param int $opsId
     *
     * @return RbacTa
     */
    public function setOpsId($opsId)
    {
        $this->opsId = $opsId;

        return $this;
    }

    /**
     * Get opsId.
     *
     * @return int
     */
    public function getOpsId()
    {
        return $this->opsId;
    }
}
