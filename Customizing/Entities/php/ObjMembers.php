<?php



/**
 * ObjMembers
 */
class ObjMembers
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var bool
     */
    private $blocked = '0';

    /**
     * @var bool
     */
    private $notification = '0';

    /**
     * @var bool|null
     */
    private $passed;

    /**
     * @var int|null
     */
    private $origin = '0';

    /**
     * @var int|null
     */
    private $originTs = '0';

    /**
     * @var bool|null
     */
    private $contact = '0';

    /**
     * @var bool|null
     */
    private $admin = '0';

    /**
     * @var bool|null
     */
    private $tutor = '0';

    /**
     * @var int|null
     */
    private $member = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjMembers
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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return ObjMembers
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
     * Set blocked.
     *
     * @param bool $blocked
     *
     * @return ObjMembers
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked.
     *
     * @return bool
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Set notification.
     *
     * @param bool $notification
     *
     * @return ObjMembers
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification.
     *
     * @return bool
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set passed.
     *
     * @param bool|null $passed
     *
     * @return ObjMembers
     */
    public function setPassed($passed = null)
    {
        $this->passed = $passed;

        return $this;
    }

    /**
     * Get passed.
     *
     * @return bool|null
     */
    public function getPassed()
    {
        return $this->passed;
    }

    /**
     * Set origin.
     *
     * @param int|null $origin
     *
     * @return ObjMembers
     */
    public function setOrigin($origin = null)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin.
     *
     * @return int|null
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set originTs.
     *
     * @param int|null $originTs
     *
     * @return ObjMembers
     */
    public function setOriginTs($originTs = null)
    {
        $this->originTs = $originTs;

        return $this;
    }

    /**
     * Get originTs.
     *
     * @return int|null
     */
    public function getOriginTs()
    {
        return $this->originTs;
    }

    /**
     * Set contact.
     *
     * @param bool|null $contact
     *
     * @return ObjMembers
     */
    public function setContact($contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact.
     *
     * @return bool|null
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set admin.
     *
     * @param bool|null $admin
     *
     * @return ObjMembers
     */
    public function setAdmin($admin = null)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return bool|null
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set tutor.
     *
     * @param bool|null $tutor
     *
     * @return ObjMembers
     */
    public function setTutor($tutor = null)
    {
        $this->tutor = $tutor;

        return $this;
    }

    /**
     * Get tutor.
     *
     * @return bool|null
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
     * @return ObjMembers
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
