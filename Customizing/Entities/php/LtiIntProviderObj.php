<?php



/**
 * LtiIntProviderObj
 */
class LtiIntProviderObj
{
    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $extConsumerId = '0';

    /**
     * @var int|null
     */
    private $admin;

    /**
     * @var int|null
     */
    private $tutor;

    /**
     * @var int|null
     */
    private $member;


    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return LtiIntProviderObj
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
     * Set extConsumerId.
     *
     * @param int $extConsumerId
     *
     * @return LtiIntProviderObj
     */
    public function setExtConsumerId($extConsumerId)
    {
        $this->extConsumerId = $extConsumerId;

        return $this;
    }

    /**
     * Get extConsumerId.
     *
     * @return int
     */
    public function getExtConsumerId()
    {
        return $this->extConsumerId;
    }

    /**
     * Set admin.
     *
     * @param int|null $admin
     *
     * @return LtiIntProviderObj
     */
    public function setAdmin($admin = null)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return int|null
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set tutor.
     *
     * @param int|null $tutor
     *
     * @return LtiIntProviderObj
     */
    public function setTutor($tutor = null)
    {
        $this->tutor = $tutor;

        return $this;
    }

    /**
     * Get tutor.
     *
     * @return int|null
     */
    public function getTutor()
    {
        return $this->tutor;
    }

    /**
     * Set member.
     *
     * @param int|null $member
     *
     * @return LtiIntProviderObj
     */
    public function setMember($member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return int|null
     */
    public function getMember()
    {
        return $this->member;
    }
}
