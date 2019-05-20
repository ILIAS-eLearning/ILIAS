<?php



/**
 * Event
 */
class Event
{
    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $tutorName;

    /**
     * @var string|null
     */
    private $tutorEmail;

    /**
     * @var string|null
     */
    private $tutorPhone;

    /**
     * @var string|null
     */
    private $details;

    /**
     * @var bool
     */
    private $registration = '0';

    /**
     * @var bool
     */
    private $participation = '0';

    /**
     * @var int|null
     */
    private $regType = '0';

    /**
     * @var int|null
     */
    private $regLimitUsers = '0';

    /**
     * @var bool|null
     */
    private $regWaitingList = '0';

    /**
     * @var bool|null
     */
    private $regLimited = '0';

    /**
     * @var int|null
     */
    private $regMinUsers;

    /**
     * @var bool
     */
    private $regAutoWait = '0';

    /**
     * @var bool
     */
    private $showMembers = '0';

    /**
     * @var bool
     */
    private $mailMembers = '0';


    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return Event
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
     * Set title.
     *
     * @param string|null $title
     *
     * @return Event
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return Event
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return Event
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set tutorName.
     *
     * @param string|null $tutorName
     *
     * @return Event
     */
    public function setTutorName($tutorName = null)
    {
        $this->tutorName = $tutorName;

        return $this;
    }

    /**
     * Get tutorName.
     *
     * @return string|null
     */
    public function getTutorName()
    {
        return $this->tutorName;
    }

    /**
     * Set tutorEmail.
     *
     * @param string|null $tutorEmail
     *
     * @return Event
     */
    public function setTutorEmail($tutorEmail = null)
    {
        $this->tutorEmail = $tutorEmail;

        return $this;
    }

    /**
     * Get tutorEmail.
     *
     * @return string|null
     */
    public function getTutorEmail()
    {
        return $this->tutorEmail;
    }

    /**
     * Set tutorPhone.
     *
     * @param string|null $tutorPhone
     *
     * @return Event
     */
    public function setTutorPhone($tutorPhone = null)
    {
        $this->tutorPhone = $tutorPhone;

        return $this;
    }

    /**
     * Get tutorPhone.
     *
     * @return string|null
     */
    public function getTutorPhone()
    {
        return $this->tutorPhone;
    }

    /**
     * Set details.
     *
     * @param string|null $details
     *
     * @return Event
     */
    public function setDetails($details = null)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details.
     *
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set registration.
     *
     * @param bool $registration
     *
     * @return Event
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration.
     *
     * @return bool
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set participation.
     *
     * @param bool $participation
     *
     * @return Event
     */
    public function setParticipation($participation)
    {
        $this->participation = $participation;

        return $this;
    }

    /**
     * Get participation.
     *
     * @return bool
     */
    public function getParticipation()
    {
        return $this->participation;
    }

    /**
     * Set regType.
     *
     * @param int|null $regType
     *
     * @return Event
     */
    public function setRegType($regType = null)
    {
        $this->regType = $regType;

        return $this;
    }

    /**
     * Get regType.
     *
     * @return int|null
     */
    public function getRegType()
    {
        return $this->regType;
    }

    /**
     * Set regLimitUsers.
     *
     * @param int|null $regLimitUsers
     *
     * @return Event
     */
    public function setRegLimitUsers($regLimitUsers = null)
    {
        $this->regLimitUsers = $regLimitUsers;

        return $this;
    }

    /**
     * Get regLimitUsers.
     *
     * @return int|null
     */
    public function getRegLimitUsers()
    {
        return $this->regLimitUsers;
    }

    /**
     * Set regWaitingList.
     *
     * @param bool|null $regWaitingList
     *
     * @return Event
     */
    public function setRegWaitingList($regWaitingList = null)
    {
        $this->regWaitingList = $regWaitingList;

        return $this;
    }

    /**
     * Get regWaitingList.
     *
     * @return bool|null
     */
    public function getRegWaitingList()
    {
        return $this->regWaitingList;
    }

    /**
     * Set regLimited.
     *
     * @param bool|null $regLimited
     *
     * @return Event
     */
    public function setRegLimited($regLimited = null)
    {
        $this->regLimited = $regLimited;

        return $this;
    }

    /**
     * Get regLimited.
     *
     * @return bool|null
     */
    public function getRegLimited()
    {
        return $this->regLimited;
    }

    /**
     * Set regMinUsers.
     *
     * @param int|null $regMinUsers
     *
     * @return Event
     */
    public function setRegMinUsers($regMinUsers = null)
    {
        $this->regMinUsers = $regMinUsers;

        return $this;
    }

    /**
     * Get regMinUsers.
     *
     * @return int|null
     */
    public function getRegMinUsers()
    {
        return $this->regMinUsers;
    }

    /**
     * Set regAutoWait.
     *
     * @param bool $regAutoWait
     *
     * @return Event
     */
    public function setRegAutoWait($regAutoWait)
    {
        $this->regAutoWait = $regAutoWait;

        return $this;
    }

    /**
     * Get regAutoWait.
     *
     * @return bool
     */
    public function getRegAutoWait()
    {
        return $this->regAutoWait;
    }

    /**
     * Set showMembers.
     *
     * @param bool $showMembers
     *
     * @return Event
     */
    public function setShowMembers($showMembers)
    {
        $this->showMembers = $showMembers;

        return $this;
    }

    /**
     * Get showMembers.
     *
     * @return bool
     */
    public function getShowMembers()
    {
        return $this->showMembers;
    }

    /**
     * Set mailMembers.
     *
     * @param bool $mailMembers
     *
     * @return Event
     */
    public function setMailMembers($mailMembers)
    {
        $this->mailMembers = $mailMembers;

        return $this;
    }

    /**
     * Get mailMembers.
     *
     * @return bool
     */
    public function getMailMembers()
    {
        return $this->mailMembers;
    }
}
