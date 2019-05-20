<?php



/**
 * CrsSettings
 */
class CrsSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $syllabus;

    /**
     * @var string|null
     */
    private $contactName;

    /**
     * @var string|null
     */
    private $contactResponsibility;

    /**
     * @var string|null
     */
    private $contactPhone;

    /**
     * @var string|null
     */
    private $contactEmail;

    /**
     * @var string|null
     */
    private $contactConsultation;

    /**
     * @var bool
     */
    private $activationType = '0';

    /**
     * @var int|null
     */
    private $activationStart;

    /**
     * @var int|null
     */
    private $activationEnd;

    /**
     * @var bool
     */
    private $subLimitationType = '0';

    /**
     * @var int|null
     */
    private $subStart;

    /**
     * @var int|null
     */
    private $subEnd;

    /**
     * @var int|null
     */
    private $subType;

    /**
     * @var string|null
     */
    private $subPassword;

    /**
     * @var bool
     */
    private $subMemLimit = '0';

    /**
     * @var int|null
     */
    private $subMaxMembers;

    /**
     * @var int|null
     */
    private $subNotify;

    /**
     * @var bool
     */
    private $viewMode = '0';

    /**
     * @var int|null
     */
    private $sortorder;

    /**
     * @var int|null
     */
    private $archiveStart;

    /**
     * @var int|null
     */
    private $archiveEnd;

    /**
     * @var int|null
     */
    private $archiveType;

    /**
     * @var bool|null
     */
    private $abo = '1';

    /**
     * @var bool
     */
    private $waitingList = '1';

    /**
     * @var string|null
     */
    private $important;

    /**
     * @var bool
     */
    private $showMembers = '1';

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
    private $enableCourseMap = '0';

    /**
     * @var bool
     */
    private $sessionLimit = '0';

    /**
     * @var int
     */
    private $sessionPrev = '-1';

    /**
     * @var int
     */
    private $sessionNext = '-1';

    /**
     * @var bool
     */
    private $regAcEnabled = '0';

    /**
     * @var string|null
     */
    private $regAc;

    /**
     * @var bool|null
     */
    private $statusDt = '2';

    /**
     * @var bool
     */
    private $autoNotification = '1';

    /**
     * @var bool|null
     */
    private $mailMembersType = '1';

    /**
     * @var int|null
     */
    private $crsStart;

    /**
     * @var int|null
     */
    private $crsEnd;

    /**
     * @var int|null
     */
    private $leaveEnd;

    /**
     * @var bool
     */
    private $autoWait = '0';

    /**
     * @var int|null
     */
    private $minMembers;

    /**
     * @var int|null
     */
    private $showMembersExport;

    /**
     * @var bool|null
     */
    private $timingMode = '0';


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
     * Set syllabus.
     *
     * @param string|null $syllabus
     *
     * @return CrsSettings
     */
    public function setSyllabus($syllabus = null)
    {
        $this->syllabus = $syllabus;

        return $this;
    }

    /**
     * Get syllabus.
     *
     * @return string|null
     */
    public function getSyllabus()
    {
        return $this->syllabus;
    }

    /**
     * Set contactName.
     *
     * @param string|null $contactName
     *
     * @return CrsSettings
     */
    public function setContactName($contactName = null)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName.
     *
     * @return string|null
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set contactResponsibility.
     *
     * @param string|null $contactResponsibility
     *
     * @return CrsSettings
     */
    public function setContactResponsibility($contactResponsibility = null)
    {
        $this->contactResponsibility = $contactResponsibility;

        return $this;
    }

    /**
     * Get contactResponsibility.
     *
     * @return string|null
     */
    public function getContactResponsibility()
    {
        return $this->contactResponsibility;
    }

    /**
     * Set contactPhone.
     *
     * @param string|null $contactPhone
     *
     * @return CrsSettings
     */
    public function setContactPhone($contactPhone = null)
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    /**
     * Get contactPhone.
     *
     * @return string|null
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    /**
     * Set contactEmail.
     *
     * @param string|null $contactEmail
     *
     * @return CrsSettings
     */
    public function setContactEmail($contactEmail = null)
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail.
     *
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Set contactConsultation.
     *
     * @param string|null $contactConsultation
     *
     * @return CrsSettings
     */
    public function setContactConsultation($contactConsultation = null)
    {
        $this->contactConsultation = $contactConsultation;

        return $this;
    }

    /**
     * Get contactConsultation.
     *
     * @return string|null
     */
    public function getContactConsultation()
    {
        return $this->contactConsultation;
    }

    /**
     * Set activationType.
     *
     * @param bool $activationType
     *
     * @return CrsSettings
     */
    public function setActivationType($activationType)
    {
        $this->activationType = $activationType;

        return $this;
    }

    /**
     * Get activationType.
     *
     * @return bool
     */
    public function getActivationType()
    {
        return $this->activationType;
    }

    /**
     * Set activationStart.
     *
     * @param int|null $activationStart
     *
     * @return CrsSettings
     */
    public function setActivationStart($activationStart = null)
    {
        $this->activationStart = $activationStart;

        return $this;
    }

    /**
     * Get activationStart.
     *
     * @return int|null
     */
    public function getActivationStart()
    {
        return $this->activationStart;
    }

    /**
     * Set activationEnd.
     *
     * @param int|null $activationEnd
     *
     * @return CrsSettings
     */
    public function setActivationEnd($activationEnd = null)
    {
        $this->activationEnd = $activationEnd;

        return $this;
    }

    /**
     * Get activationEnd.
     *
     * @return int|null
     */
    public function getActivationEnd()
    {
        return $this->activationEnd;
    }

    /**
     * Set subLimitationType.
     *
     * @param bool $subLimitationType
     *
     * @return CrsSettings
     */
    public function setSubLimitationType($subLimitationType)
    {
        $this->subLimitationType = $subLimitationType;

        return $this;
    }

    /**
     * Get subLimitationType.
     *
     * @return bool
     */
    public function getSubLimitationType()
    {
        return $this->subLimitationType;
    }

    /**
     * Set subStart.
     *
     * @param int|null $subStart
     *
     * @return CrsSettings
     */
    public function setSubStart($subStart = null)
    {
        $this->subStart = $subStart;

        return $this;
    }

    /**
     * Get subStart.
     *
     * @return int|null
     */
    public function getSubStart()
    {
        return $this->subStart;
    }

    /**
     * Set subEnd.
     *
     * @param int|null $subEnd
     *
     * @return CrsSettings
     */
    public function setSubEnd($subEnd = null)
    {
        $this->subEnd = $subEnd;

        return $this;
    }

    /**
     * Get subEnd.
     *
     * @return int|null
     */
    public function getSubEnd()
    {
        return $this->subEnd;
    }

    /**
     * Set subType.
     *
     * @param int|null $subType
     *
     * @return CrsSettings
     */
    public function setSubType($subType = null)
    {
        $this->subType = $subType;

        return $this;
    }

    /**
     * Get subType.
     *
     * @return int|null
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * Set subPassword.
     *
     * @param string|null $subPassword
     *
     * @return CrsSettings
     */
    public function setSubPassword($subPassword = null)
    {
        $this->subPassword = $subPassword;

        return $this;
    }

    /**
     * Get subPassword.
     *
     * @return string|null
     */
    public function getSubPassword()
    {
        return $this->subPassword;
    }

    /**
     * Set subMemLimit.
     *
     * @param bool $subMemLimit
     *
     * @return CrsSettings
     */
    public function setSubMemLimit($subMemLimit)
    {
        $this->subMemLimit = $subMemLimit;

        return $this;
    }

    /**
     * Get subMemLimit.
     *
     * @return bool
     */
    public function getSubMemLimit()
    {
        return $this->subMemLimit;
    }

    /**
     * Set subMaxMembers.
     *
     * @param int|null $subMaxMembers
     *
     * @return CrsSettings
     */
    public function setSubMaxMembers($subMaxMembers = null)
    {
        $this->subMaxMembers = $subMaxMembers;

        return $this;
    }

    /**
     * Get subMaxMembers.
     *
     * @return int|null
     */
    public function getSubMaxMembers()
    {
        return $this->subMaxMembers;
    }

    /**
     * Set subNotify.
     *
     * @param int|null $subNotify
     *
     * @return CrsSettings
     */
    public function setSubNotify($subNotify = null)
    {
        $this->subNotify = $subNotify;

        return $this;
    }

    /**
     * Get subNotify.
     *
     * @return int|null
     */
    public function getSubNotify()
    {
        return $this->subNotify;
    }

    /**
     * Set viewMode.
     *
     * @param bool $viewMode
     *
     * @return CrsSettings
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
     * Set sortorder.
     *
     * @param int|null $sortorder
     *
     * @return CrsSettings
     */
    public function setSortorder($sortorder = null)
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    /**
     * Get sortorder.
     *
     * @return int|null
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * Set archiveStart.
     *
     * @param int|null $archiveStart
     *
     * @return CrsSettings
     */
    public function setArchiveStart($archiveStart = null)
    {
        $this->archiveStart = $archiveStart;

        return $this;
    }

    /**
     * Get archiveStart.
     *
     * @return int|null
     */
    public function getArchiveStart()
    {
        return $this->archiveStart;
    }

    /**
     * Set archiveEnd.
     *
     * @param int|null $archiveEnd
     *
     * @return CrsSettings
     */
    public function setArchiveEnd($archiveEnd = null)
    {
        $this->archiveEnd = $archiveEnd;

        return $this;
    }

    /**
     * Get archiveEnd.
     *
     * @return int|null
     */
    public function getArchiveEnd()
    {
        return $this->archiveEnd;
    }

    /**
     * Set archiveType.
     *
     * @param int|null $archiveType
     *
     * @return CrsSettings
     */
    public function setArchiveType($archiveType = null)
    {
        $this->archiveType = $archiveType;

        return $this;
    }

    /**
     * Get archiveType.
     *
     * @return int|null
     */
    public function getArchiveType()
    {
        return $this->archiveType;
    }

    /**
     * Set abo.
     *
     * @param bool|null $abo
     *
     * @return CrsSettings
     */
    public function setAbo($abo = null)
    {
        $this->abo = $abo;

        return $this;
    }

    /**
     * Get abo.
     *
     * @return bool|null
     */
    public function getAbo()
    {
        return $this->abo;
    }

    /**
     * Set waitingList.
     *
     * @param bool $waitingList
     *
     * @return CrsSettings
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
     * Set important.
     *
     * @param string|null $important
     *
     * @return CrsSettings
     */
    public function setImportant($important = null)
    {
        $this->important = $important;

        return $this;
    }

    /**
     * Get important.
     *
     * @return string|null
     */
    public function getImportant()
    {
        return $this->important;
    }

    /**
     * Set showMembers.
     *
     * @param bool $showMembers
     *
     * @return CrsSettings
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
     * Set latitude.
     *
     * @param string|null $latitude
     *
     * @return CrsSettings
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
     * @return CrsSettings
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
     * @return CrsSettings
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
     * Set enableCourseMap.
     *
     * @param bool $enableCourseMap
     *
     * @return CrsSettings
     */
    public function setEnableCourseMap($enableCourseMap)
    {
        $this->enableCourseMap = $enableCourseMap;

        return $this;
    }

    /**
     * Get enableCourseMap.
     *
     * @return bool
     */
    public function getEnableCourseMap()
    {
        return $this->enableCourseMap;
    }

    /**
     * Set sessionLimit.
     *
     * @param bool $sessionLimit
     *
     * @return CrsSettings
     */
    public function setSessionLimit($sessionLimit)
    {
        $this->sessionLimit = $sessionLimit;

        return $this;
    }

    /**
     * Get sessionLimit.
     *
     * @return bool
     */
    public function getSessionLimit()
    {
        return $this->sessionLimit;
    }

    /**
     * Set sessionPrev.
     *
     * @param int $sessionPrev
     *
     * @return CrsSettings
     */
    public function setSessionPrev($sessionPrev)
    {
        $this->sessionPrev = $sessionPrev;

        return $this;
    }

    /**
     * Get sessionPrev.
     *
     * @return int
     */
    public function getSessionPrev()
    {
        return $this->sessionPrev;
    }

    /**
     * Set sessionNext.
     *
     * @param int $sessionNext
     *
     * @return CrsSettings
     */
    public function setSessionNext($sessionNext)
    {
        $this->sessionNext = $sessionNext;

        return $this;
    }

    /**
     * Get sessionNext.
     *
     * @return int
     */
    public function getSessionNext()
    {
        return $this->sessionNext;
    }

    /**
     * Set regAcEnabled.
     *
     * @param bool $regAcEnabled
     *
     * @return CrsSettings
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
     * @return CrsSettings
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
     * Set statusDt.
     *
     * @param bool|null $statusDt
     *
     * @return CrsSettings
     */
    public function setStatusDt($statusDt = null)
    {
        $this->statusDt = $statusDt;

        return $this;
    }

    /**
     * Get statusDt.
     *
     * @return bool|null
     */
    public function getStatusDt()
    {
        return $this->statusDt;
    }

    /**
     * Set autoNotification.
     *
     * @param bool $autoNotification
     *
     * @return CrsSettings
     */
    public function setAutoNotification($autoNotification)
    {
        $this->autoNotification = $autoNotification;

        return $this;
    }

    /**
     * Get autoNotification.
     *
     * @return bool
     */
    public function getAutoNotification()
    {
        return $this->autoNotification;
    }

    /**
     * Set mailMembersType.
     *
     * @param bool|null $mailMembersType
     *
     * @return CrsSettings
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
     * Set crsStart.
     *
     * @param int|null $crsStart
     *
     * @return CrsSettings
     */
    public function setCrsStart($crsStart = null)
    {
        $this->crsStart = $crsStart;

        return $this;
    }

    /**
     * Get crsStart.
     *
     * @return int|null
     */
    public function getCrsStart()
    {
        return $this->crsStart;
    }

    /**
     * Set crsEnd.
     *
     * @param int|null $crsEnd
     *
     * @return CrsSettings
     */
    public function setCrsEnd($crsEnd = null)
    {
        $this->crsEnd = $crsEnd;

        return $this;
    }

    /**
     * Get crsEnd.
     *
     * @return int|null
     */
    public function getCrsEnd()
    {
        return $this->crsEnd;
    }

    /**
     * Set leaveEnd.
     *
     * @param int|null $leaveEnd
     *
     * @return CrsSettings
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
     * @return CrsSettings
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
     * Set minMembers.
     *
     * @param int|null $minMembers
     *
     * @return CrsSettings
     */
    public function setMinMembers($minMembers = null)
    {
        $this->minMembers = $minMembers;

        return $this;
    }

    /**
     * Get minMembers.
     *
     * @return int|null
     */
    public function getMinMembers()
    {
        return $this->minMembers;
    }

    /**
     * Set showMembersExport.
     *
     * @param int|null $showMembersExport
     *
     * @return CrsSettings
     */
    public function setShowMembersExport($showMembersExport = null)
    {
        $this->showMembersExport = $showMembersExport;

        return $this;
    }

    /**
     * Get showMembersExport.
     *
     * @return int|null
     */
    public function getShowMembersExport()
    {
        return $this->showMembersExport;
    }

    /**
     * Set timingMode.
     *
     * @param bool|null $timingMode
     *
     * @return CrsSettings
     */
    public function setTimingMode($timingMode = null)
    {
        $this->timingMode = $timingMode;

        return $this;
    }

    /**
     * Get timingMode.
     *
     * @return bool|null
     */
    public function getTimingMode()
    {
        return $this->timingMode;
    }


}
