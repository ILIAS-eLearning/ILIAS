<?php



/**
 * MemberAgreement
 */
class MemberAgreement
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool
     */
    private $accepted = '0';

    /**
     * @var int
     */
    private $acceptanceTime = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return MemberAgreement
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return MemberAgreement
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
     * Set accepted.
     *
     * @param bool $accepted
     *
     * @return MemberAgreement
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted.
     *
     * @return bool
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set acceptanceTime.
     *
     * @param int $acceptanceTime
     *
     * @return MemberAgreement
     */
    public function setAcceptanceTime($acceptanceTime)
    {
        $this->acceptanceTime = $acceptanceTime;

        return $this;
    }

    /**
     * Get acceptanceTime.
     *
     * @return int
     */
    public function getAcceptanceTime()
    {
        return $this->acceptanceTime;
    }
}
