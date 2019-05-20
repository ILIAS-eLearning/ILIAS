<?php



/**
 * IassMembers
 */
class IassMembers
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
     * @var int|null
     */
    private $examinerId = '0';

    /**
     * @var string|null
     */
    private $record;

    /**
     * @var string|null
     */
    private $internalNote;

    /**
     * @var bool
     */
    private $notify = '0';

    /**
     * @var int
     */
    private $notificationTs = '-1';

    /**
     * @var bool|null
     */
    private $learningProgress = '0';

    /**
     * @var bool
     */
    private $finalized = '0';

    /**
     * @var string|null
     */
    private $place;

    /**
     * @var int|null
     */
    private $eventTime;

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * @var bool|null
     */
    private $userViewFile;

    /**
     * @var int|null
     */
    private $changerId;

    /**
     * @var string|null
     */
    private $changeTime;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IassMembers
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
     * @return IassMembers
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
     * Set examinerId.
     *
     * @param int|null $examinerId
     *
     * @return IassMembers
     */
    public function setExaminerId($examinerId = null)
    {
        $this->examinerId = $examinerId;

        return $this;
    }

    /**
     * Get examinerId.
     *
     * @return int|null
     */
    public function getExaminerId()
    {
        return $this->examinerId;
    }

    /**
     * Set record.
     *
     * @param string|null $record
     *
     * @return IassMembers
     */
    public function setRecord($record = null)
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get record.
     *
     * @return string|null
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * Set internalNote.
     *
     * @param string|null $internalNote
     *
     * @return IassMembers
     */
    public function setInternalNote($internalNote = null)
    {
        $this->internalNote = $internalNote;

        return $this;
    }

    /**
     * Get internalNote.
     *
     * @return string|null
     */
    public function getInternalNote()
    {
        return $this->internalNote;
    }

    /**
     * Set notify.
     *
     * @param bool $notify
     *
     * @return IassMembers
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * Get notify.
     *
     * @return bool
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * Set notificationTs.
     *
     * @param int $notificationTs
     *
     * @return IassMembers
     */
    public function setNotificationTs($notificationTs)
    {
        $this->notificationTs = $notificationTs;

        return $this;
    }

    /**
     * Get notificationTs.
     *
     * @return int
     */
    public function getNotificationTs()
    {
        return $this->notificationTs;
    }

    /**
     * Set learningProgress.
     *
     * @param bool|null $learningProgress
     *
     * @return IassMembers
     */
    public function setLearningProgress($learningProgress = null)
    {
        $this->learningProgress = $learningProgress;

        return $this;
    }

    /**
     * Get learningProgress.
     *
     * @return bool|null
     */
    public function getLearningProgress()
    {
        return $this->learningProgress;
    }

    /**
     * Set finalized.
     *
     * @param bool $finalized
     *
     * @return IassMembers
     */
    public function setFinalized($finalized)
    {
        $this->finalized = $finalized;

        return $this;
    }

    /**
     * Get finalized.
     *
     * @return bool
     */
    public function getFinalized()
    {
        return $this->finalized;
    }

    /**
     * Set place.
     *
     * @param string|null $place
     *
     * @return IassMembers
     */
    public function setPlace($place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     *
     * @return string|null
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set eventTime.
     *
     * @param int|null $eventTime
     *
     * @return IassMembers
     */
    public function setEventTime($eventTime = null)
    {
        $this->eventTime = $eventTime;

        return $this;
    }

    /**
     * Get eventTime.
     *
     * @return int|null
     */
    public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return IassMembers
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set userViewFile.
     *
     * @param bool|null $userViewFile
     *
     * @return IassMembers
     */
    public function setUserViewFile($userViewFile = null)
    {
        $this->userViewFile = $userViewFile;

        return $this;
    }

    /**
     * Get userViewFile.
     *
     * @return bool|null
     */
    public function getUserViewFile()
    {
        return $this->userViewFile;
    }

    /**
     * Set changerId.
     *
     * @param int|null $changerId
     *
     * @return IassMembers
     */
    public function setChangerId($changerId = null)
    {
        $this->changerId = $changerId;

        return $this;
    }

    /**
     * Get changerId.
     *
     * @return int|null
     */
    public function getChangerId()
    {
        return $this->changerId;
    }

    /**
     * Set changeTime.
     *
     * @param string|null $changeTime
     *
     * @return IassMembers
     */
    public function setChangeTime($changeTime = null)
    {
        $this->changeTime = $changeTime;

        return $this;
    }

    /**
     * Get changeTime.
     *
     * @return string|null
     */
    public function getChangeTime()
    {
        return $this->changeTime;
    }
}
