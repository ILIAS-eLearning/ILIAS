<?php



/**
 * AddressbookMlistAss
 */
class AddressbookMlistAss
{
    /**
     * @var int
     */
    private $aId = '0';

    /**
     * @var int
     */
    private $mlId = '0';

    /**
     * @var int
     */
    private $usrId = '0';


    /**
     * Get aId.
     *
     * @return int
     */
    public function getAId()
    {
        return $this->aId;
    }

    /**
     * Set mlId.
     *
     * @param int $mlId
     *
     * @return AddressbookMlistAss
     */
    public function setMlId($mlId)
    {
        $this->mlId = $mlId;

        return $this;
    }

    /**
     * Get mlId.
     *
     * @return int
     */
    public function getMlId()
    {
        return $this->mlId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return AddressbookMlistAss
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
}
