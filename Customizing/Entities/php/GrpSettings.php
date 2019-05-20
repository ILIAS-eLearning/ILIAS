<?php



/**
 * GrpSettings
 */
class GrpSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $information;

    /**
     * @var bool
     */
    private $grpType = '0';

    /**
     * @var bool
     */
    private $registrationType = '0';

    /**
     * @var bool
     */
    private $registrationEnabled = '0';

    /**
     * @var bool
     */
    private $registrationUnlimited = '0';

    /**
     * @var \DateTime|null
     */
    private $registrationStart;

    /**
     * @var \DateTime|null
     */
    private $registrationEnd;

    /**
     * @var string|null
     */
    private $registrationPassword;

    /**
     * @var bool
     */
    private $registrationMemLimit = '0';

    /**
     * @var int
     */
    private $registrationMaxMembers = '0';

    /**
     * @var bool
     */
    private $waitingList = '0';

    /**
     * @var string|null
     */
    private $latitude;

    /**
     * @var string|null
     */
    private $longitude;

    /**
     * @var int
     */
    private $locationZoom = '0';

    /**
     * @var bool
     */
    private $enablemap = '0';

    /**
     * @var bool
     */
    private $regAcEnabled = '0';

    /**
     * @var string|null
     */
    private $regAc;

    /**
     * @var bool
     */
    private $viewMode = '6';

    /**
     * @var bool|null
     */
    private $mailMembersType = '1';

    /**
     * @var int|null
     */
    private $registrationMinMembers;

    /**
     * @var int|null
     */
    private $leaveEnd;

    /**
     * @var bool
     */
    private $autoWait = '0';

    /**
     * @var bool
     */
    private $showMembers = '1';

    /**
     * @var int|null
     */
    private $grpStart;

    /**
     * @var int|null
     */
    private $grpEnd;


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
     * Set information.
     *
     * @param string|null $information
     *
     * @return GrpSettings
     */
    public function setInformation($information = null)
    {
        $this->information = $information;

        return $this;
    }

    /**
     * Get information.
     *
     * @return string|null
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Set grpType.
     *
     * @param bool $grpType
     *
     * @return GrpSettings
     */
    public function setGrpType($grpType)
    {
        $this->grpType = $grpType;

        return $this;
    }

    /**
     * Get grpType.
     *
     * @return bool
     */
    public function getGrpType()
    {
        return $this->grpType;
    }

    /**
     * Set registrationType.
     *
     * @param bool $registrationType
     *
     * @return GrpSettings
     */
    public function setRegistrationType($registrationType)
    {
        $this->registrationType = $registrationType;

        return $this;
    }

    /**
     * Get registrationType.
     *
     * @return bool
     */
    public function getRegistrationType()
    {
        return $this->registrationType;
    }

    /**
     * Set registrationEnabled.
     *
     * @param bool $registrationEnabled
     *
     * @return GrpSettings
     */
    public function setRegistrationEnabled($registrationEnabled)
    {
        $this->registrationEnabled = $registrationEnabled;

        return $this;
    }

    /**
     * Get registrationEnabled.
     *
     * @return bool
     */
    public function getRegistrationEnabled()
    {
        return $this->registrationEnabled;
    }

    /**
     * Set registrationUnlimited.
     *
     * @param bool $registrationUnlimited
     *
     * @return GrpSettings
     */
    public function setRegistrationUnlimited($registrationUnlimited)
    {
        $this->registrationUnlimited = $registrationUnlimited;

        return $this;
    }

    /**
     * Get registrationUnlimited.
     *
     * @return bool
     */
    public function getRegistrationUnlimited()
    {
        return $this->registrationUnlimited;
    }

    /**
     * Set registrationStart.
     *
     * @param \DateTime|null $registrationStart
     *
     * @return GrpSettings
     */
    public function setRegistrationStart($registrationStart = null)
    {
        $this->registrationStart = $registrationStart;

        return $this;
    }

    /**
     * Get registrationStart.
     *
     * @return \DateTime|null
     */
    public function getRegistrationStart()
    {
        return $this->registrationStart;
    }

    /**
     * Set registrationEnd.
     *
     * @param \DateTime|null $registrationEnd
     *
     * @return GrpSettings
     */
    public function setRegistrationEnd($registrationEnd = null)
    {
        $this->registrationEnd = $registrationEnd;

        return $this;
    }

    /**
     * Get registrationEnd.
     *
     * @return \DateTime|null
     */
    public function getRegistrationEnd()
    {
        return $this->registrationEnd;
    }

    /**
     * Set registrationPassword.
     *
     * @param string|null $registrationPassword
     *
     * @return GrpSettings
     */
    public function setRegistrationPassword($registrationPassword = null)
    {
        $this->registrationPassword = $registrationPassword;

        return $this;
    }

    /**
     * Get registrationPassword.
     *
     * @return string|null
     */
    public function getRegistrationPassword()
    {
        return $this->registrationPassword;
    }

    /**
     * Set registrationMemLimit.
     *
     * @param bool $registrationMemLimit
     *
     * @return GrpSettings
     */
    public function setRegistrationMemLimit($registrationMemLimit)
    {
        $this->registrationMemLimit = $registrationMemLimit;

        return $this;
    }

    /**
     * Get registrationMemLimit.
     *
     * @return bool
     */
    public function getRegistrationMemLimit()
    {
        return $this->registrationMemLimit;
    }

    /**
     * Set registrationMaxMembers.
     *
     * @param int $registrationMaxMembers
     *
     * @return GrpSettings
     */
    public function setRegistrationMaxMembers($registrationMaxMembers)
    {
        $this->registrationMaxMembers = $registrationMaxMembers;

        return $this;
    }

    /**
     * Get registrationMaxMembers.
     *
     * @return int
     */
    public function getRegistrationMaxMembers()
    {
        return $this->registrationMaxMembers;
    }

    /**
     * Set waitingList.
     *
     * @param bool $waitingList
     *
     * @return GrpSettings
     */
    public function setWaitingList($waitingList)
    {
        $this->waitingList = $waitingList;

        return $this;
    }

    /**
     * Get waitingList.
     *
     * @return bool
     */
    public function getWaitingList()
    {
        return $this->waitingList;
    }

    /**
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return GrpSettings
     */
    public function setLatitude($latitude = null)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude.
     *
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude.
     *
     * @param string|null $longitude
     *
     * @return GrpSettings
     */
    public function setLongitude($longitude = null)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude.
     *
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set locationZoom.
     *
     * @param int $locationZoom
     *
     * @return GrpSettings
     */
    public function setLocationZoom($locationZoom)
    {
        $this->locationZoom = $locationZoom;

        return $this;
    }

    /**
     * Get locationZoom.
     *
     * @return int
     */
    public function getLocationZoom()
    {
        return $this->locationZoom;
    }

    /**
     * Set enablemap.
     *
     * @param bool $enablemap
     *
     * @return GrpSettings
     */
    public function setEnablemap($enablemap)
    {
        $this->enablemap = $enablemap;

        return $this;
    }

    /**
     * Get enablemap.
     *
     * @return bool
     */
    public function getEnablemap()
    {
        return $this->enablemap;
    }

    /**
     * Set regAcEnabled.
     *
     * @param bool $regAcEnabled
     *
     * @return GrpSettings
     */
    public function setRegAcEnabled($regAcEnabled)
    {
        $this->regAcEnabled = $regAcEnabled;

        return $this;
    }

    /**
     * Get regAcEnabled.
     *
     * @return bool
     */
    public function getRegAcEnabled()
    {
        return $this->regAcEnabled;
    }

    /**
     * Set regAc.
     *
     * @param string|null $regAc
     *
     * @return GrpSettings
     */
    public function setRegAc($regAc = null)
    {
        $this->regAc = $regAc;

        return $this;
    }

    /**
     * Get regAc.
     *
     * @return string|null
     */
    public function getRegAc()
    {
        return $this->regAc;
    }

    /**
     * Set viewMode.
     *
     * @param bool $viewMode
     *
     * @return GrpSettings
     */
    public function setViewMode($viewMode)
    {
        $this->viewMode = $viewMode;

        return $this;
    }

    /**
     * Get viewMode.
     *
     * @return bool
     */
    public function getViewMode()
    {
        return $this->viewMode;
    }

    /**
     * Set mailMembersType.
     *
     * @param bool|null $mailMembersType
     *
     * @return GrpSettings
     */
    public function setMailMembersType($mailMembersType = null)
    {
        $this->mailMembersType = $mailMembersType;

        return $this;
    }

    /**
     * Get mailMembersType.
     *
     * @return bool|null
     */
    public function getMailMembersType()
    {
        return $this->mailMembersType;
    }

    /**
     * Set registrationMinMembers.
     *
     * @param int|null $registrationMinMembers
     *
     * @return GrpSettings
     */
    public function setRegistrationMinMembers($registrationMinMembers = null)
    {
        $this->registrationMinMembers = $registrationMinMembers;

        return $this;
    }

    /**
     * Get registrationMinMembers.
     *
     * @return int|null
     */
    public function getRegistrationMinMembers()
    {
        return $this->registrationMinMembers;
    }

    /**
     * Set leaveEnd.
     *
     * @param int|null $leaveEnd
     *
     * @return GrpSettings
     */
    public function setLeaveEnd($leaveEnd = null)
    {
        $this->leaveEnd = $leaveEnd;

        return $this;
    }

    /**
     * Get leaveEnd.
     *
     * @return int|null
     */
    public function getLeaveEnd()
    {
        return $this->leaveEnd;
    }

    /**
     * Set autoWait.
     *
     * @param bool $autoWait
     *
     * @return GrpSettings
     */
    public function setAutoWait($autoWait)
    {
        $this->autoWait = $autoWait;

        return $this;
    }

    /**
     * Get autoWait.
     *
     * @return bool
     */
    public function getAutoWait()
    {
        return $this->autoWait;
    }

    /**
     * Set showMembers.
     *
     * @param bool $showMembers
     *
     * @return GrpSettings
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
     * Set grpStart.
     *
     * @param int|null $grpStart
     *
     * @return GrpSettings
     */
    public function setGrpStart($grpStart = null)
    {
        $this->grpStart = $grpStart;

        return $this;
    }

    /**
     * Get grpStart.
     *
     * @return int|null
     */
    public function getGrpStart()
    {
        return $this->grpStart;
    }

    /**
     * Set grpEnd.
     *
     * @param int|null $grpEnd
     *
     * @return GrpSettings
     */
    public function setGrpEnd($grpEnd = null)
    {
        $this->grpEnd = $grpEnd;

        return $this;
    }

    /**
     * Get grpEnd.
     *
     * @return int|null
     */
    public function getGrpEnd()
    {
        return $this->grpEnd;
    }
}
