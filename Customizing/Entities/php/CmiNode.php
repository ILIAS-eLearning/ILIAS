<?php



/**
 * CmiNode
 */
class CmiNode
{
    /**
     * @var int
     */
    private $cmiNodeId = '0';

    /**
     * @var int|null
     */
    private $accesscount;

    /**
     * @var string|null
     */
    private $accessduration;

    /**
     * @var string|null
     */
    private $accessed;

    /**
     * @var string|null
     */
    private $activityabsduration;

    /**
     * @var int|null
     */
    private $activityattemptcount;

    /**
     * @var string|null
     */
    private $activityexpduration;

    /**
     * @var bool|null
     */
    private $activityprogstatus;

    /**
     * @var string|null
     */
    private $attemptabsduration;

    /**
     * @var float|null
     */
    private $attemptcomplamount;

    /**
     * @var bool|null
     */
    private $attemptcomplstatus;

    /**
     * @var string|null
     */
    private $attemptexpduration;

    /**
     * @var bool|null
     */
    private $attemptprogstatus;

    /**
     * @var int|null
     */
    private $audioCaptioning;

    /**
     * @var float|null
     */
    private $audioLevel;

    /**
     * @var string|null
     */
    private $availablechildren;

    /**
     * @var float|null
     */
    private $completion;

    /**
     * @var string|null
     */
    private $completionStatus;

    /**
     * @var string|null
     */
    private $completionThreshold;

    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $created;

    /**
     * @var string|null
     */
    private $credit;

    /**
     * @var float|null
     */
    private $deliverySpeed;

    /**
     * @var string|null
     */
    private $cEntry;

    /**
     * @var string|null
     */
    private $cExit;

    /**
     * @var string|null
     */
    private $cLanguage;

    /**
     * @var string|null
     */
    private $launchData;

    /**
     * @var string|null
     */
    private $learnerName;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var float|null
     */
    private $cMax;

    /**
     * @var float|null
     */
    private $cMin;

    /**
     * @var string|null
     */
    private $cMode;

    /**
     * @var string|null
     */
    private $modified;

    /**
     * @var float|null
     */
    private $progressMeasure;

    /**
     * @var float|null
     */
    private $cRaw;

    /**
     * @var float|null
     */
    private $scaled;

    /**
     * @var float|null
     */
    private $scaledPassingScore;

    /**
     * @var string|null
     */
    private $sessionTime;

    /**
     * @var string|null
     */
    private $successStatus;

    /**
     * @var string|null
     */
    private $suspendData;

    /**
     * @var string|null
     */
    private $totalTime;

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var \DateTime|null
     */
    private $cTimestamp;

    /**
     * @var bool
     */
    private $additionalTables = '0';


    /**
     * Get cmiNodeId.
     *
     * @return int
     */
    public function getCmiNodeId()
    {
        return $this->cmiNodeId;
    }

    /**
     * Set accesscount.
     *
     * @param int|null $accesscount
     *
     * @return CmiNode
     */
    public function setAccesscount($accesscount = null)
    {
        $this->accesscount = $accesscount;

        return $this;
    }

    /**
     * Get accesscount.
     *
     * @return int|null
     */
    public function getAccesscount()
    {
        return $this->accesscount;
    }

    /**
     * Set accessduration.
     *
     * @param string|null $accessduration
     *
     * @return CmiNode
     */
    public function setAccessduration($accessduration = null)
    {
        $this->accessduration = $accessduration;

        return $this;
    }

    /**
     * Get accessduration.
     *
     * @return string|null
     */
    public function getAccessduration()
    {
        return $this->accessduration;
    }

    /**
     * Set accessed.
     *
     * @param string|null $accessed
     *
     * @return CmiNode
     */
    public function setAccessed($accessed = null)
    {
        $this->accessed = $accessed;

        return $this;
    }

    /**
     * Get accessed.
     *
     * @return string|null
     */
    public function getAccessed()
    {
        return $this->accessed;
    }

    /**
     * Set activityabsduration.
     *
     * @param string|null $activityabsduration
     *
     * @return CmiNode
     */
    public function setActivityabsduration($activityabsduration = null)
    {
        $this->activityabsduration = $activityabsduration;

        return $this;
    }

    /**
     * Get activityabsduration.
     *
     * @return string|null
     */
    public function getActivityabsduration()
    {
        return $this->activityabsduration;
    }

    /**
     * Set activityattemptcount.
     *
     * @param int|null $activityattemptcount
     *
     * @return CmiNode
     */
    public function setActivityattemptcount($activityattemptcount = null)
    {
        $this->activityattemptcount = $activityattemptcount;

        return $this;
    }

    /**
     * Get activityattemptcount.
     *
     * @return int|null
     */
    public function getActivityattemptcount()
    {
        return $this->activityattemptcount;
    }

    /**
     * Set activityexpduration.
     *
     * @param string|null $activityexpduration
     *
     * @return CmiNode
     */
    public function setActivityexpduration($activityexpduration = null)
    {
        $this->activityexpduration = $activityexpduration;

        return $this;
    }

    /**
     * Get activityexpduration.
     *
     * @return string|null
     */
    public function getActivityexpduration()
    {
        return $this->activityexpduration;
    }

    /**
     * Set activityprogstatus.
     *
     * @param bool|null $activityprogstatus
     *
     * @return CmiNode
     */
    public function setActivityprogstatus($activityprogstatus = null)
    {
        $this->activityprogstatus = $activityprogstatus;

        return $this;
    }

    /**
     * Get activityprogstatus.
     *
     * @return bool|null
     */
    public function getActivityprogstatus()
    {
        return $this->activityprogstatus;
    }

    /**
     * Set attemptabsduration.
     *
     * @param string|null $attemptabsduration
     *
     * @return CmiNode
     */
    public function setAttemptabsduration($attemptabsduration = null)
    {
        $this->attemptabsduration = $attemptabsduration;

        return $this;
    }

    /**
     * Get attemptabsduration.
     *
     * @return string|null
     */
    public function getAttemptabsduration()
    {
        return $this->attemptabsduration;
    }

    /**
     * Set attemptcomplamount.
     *
     * @param float|null $attemptcomplamount
     *
     * @return CmiNode
     */
    public function setAttemptcomplamount($attemptcomplamount = null)
    {
        $this->attemptcomplamount = $attemptcomplamount;

        return $this;
    }

    /**
     * Get attemptcomplamount.
     *
     * @return float|null
     */
    public function getAttemptcomplamount()
    {
        return $this->attemptcomplamount;
    }

    /**
     * Set attemptcomplstatus.
     *
     * @param bool|null $attemptcomplstatus
     *
     * @return CmiNode
     */
    public function setAttemptcomplstatus($attemptcomplstatus = null)
    {
        $this->attemptcomplstatus = $attemptcomplstatus;

        return $this;
    }

    /**
     * Get attemptcomplstatus.
     *
     * @return bool|null
     */
    public function getAttemptcomplstatus()
    {
        return $this->attemptcomplstatus;
    }

    /**
     * Set attemptexpduration.
     *
     * @param string|null $attemptexpduration
     *
     * @return CmiNode
     */
    public function setAttemptexpduration($attemptexpduration = null)
    {
        $this->attemptexpduration = $attemptexpduration;

        return $this;
    }

    /**
     * Get attemptexpduration.
     *
     * @return string|null
     */
    public function getAttemptexpduration()
    {
        return $this->attemptexpduration;
    }

    /**
     * Set attemptprogstatus.
     *
     * @param bool|null $attemptprogstatus
     *
     * @return CmiNode
     */
    public function setAttemptprogstatus($attemptprogstatus = null)
    {
        $this->attemptprogstatus = $attemptprogstatus;

        return $this;
    }

    /**
     * Get attemptprogstatus.
     *
     * @return bool|null
     */
    public function getAttemptprogstatus()
    {
        return $this->attemptprogstatus;
    }

    /**
     * Set audioCaptioning.
     *
     * @param int|null $audioCaptioning
     *
     * @return CmiNode
     */
    public function setAudioCaptioning($audioCaptioning = null)
    {
        $this->audioCaptioning = $audioCaptioning;

        return $this;
    }

    /**
     * Get audioCaptioning.
     *
     * @return int|null
     */
    public function getAudioCaptioning()
    {
        return $this->audioCaptioning;
    }

    /**
     * Set audioLevel.
     *
     * @param float|null $audioLevel
     *
     * @return CmiNode
     */
    public function setAudioLevel($audioLevel = null)
    {
        $this->audioLevel = $audioLevel;

        return $this;
    }

    /**
     * Get audioLevel.
     *
     * @return float|null
     */
    public function getAudioLevel()
    {
        return $this->audioLevel;
    }

    /**
     * Set availablechildren.
     *
     * @param string|null $availablechildren
     *
     * @return CmiNode
     */
    public function setAvailablechildren($availablechildren = null)
    {
        $this->availablechildren = $availablechildren;

        return $this;
    }

    /**
     * Get availablechildren.
     *
     * @return string|null
     */
    public function getAvailablechildren()
    {
        return $this->availablechildren;
    }

    /**
     * Set completion.
     *
     * @param float|null $completion
     *
     * @return CmiNode
     */
    public function setCompletion($completion = null)
    {
        $this->completion = $completion;

        return $this;
    }

    /**
     * Get completion.
     *
     * @return float|null
     */
    public function getCompletion()
    {
        return $this->completion;
    }

    /**
     * Set completionStatus.
     *
     * @param string|null $completionStatus
     *
     * @return CmiNode
     */
    public function setCompletionStatus($completionStatus = null)
    {
        $this->completionStatus = $completionStatus;

        return $this;
    }

    /**
     * Get completionStatus.
     *
     * @return string|null
     */
    public function getCompletionStatus()
    {
        return $this->completionStatus;
    }

    /**
     * Set completionThreshold.
     *
     * @param string|null $completionThreshold
     *
     * @return CmiNode
     */
    public function setCompletionThreshold($completionThreshold = null)
    {
        $this->completionThreshold = $completionThreshold;

        return $this;
    }

    /**
     * Get completionThreshold.
     *
     * @return string|null
     */
    public function getCompletionThreshold()
    {
        return $this->completionThreshold;
    }

    /**
     * Set cpNodeId.
     *
     * @param int $cpNodeId
     *
     * @return CmiNode
     */
    public function setCpNodeId($cpNodeId)
    {
        $this->cpNodeId = $cpNodeId;

        return $this;
    }

    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set created.
     *
     * @param string|null $created
     *
     * @return CmiNode
     */
    public function setCreated($created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return string|null
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set credit.
     *
     * @param string|null $credit
     *
     * @return CmiNode
     */
    public function setCredit($credit = null)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit.
     *
     * @return string|null
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set deliverySpeed.
     *
     * @param float|null $deliverySpeed
     *
     * @return CmiNode
     */
    public function setDeliverySpeed($deliverySpeed = null)
    {
        $this->deliverySpeed = $deliverySpeed;

        return $this;
    }

    /**
     * Get deliverySpeed.
     *
     * @return float|null
     */
    public function getDeliverySpeed()
    {
        return $this->deliverySpeed;
    }

    /**
     * Set cEntry.
     *
     * @param string|null $cEntry
     *
     * @return CmiNode
     */
    public function setCEntry($cEntry = null)
    {
        $this->cEntry = $cEntry;

        return $this;
    }

    /**
     * Get cEntry.
     *
     * @return string|null
     */
    public function getCEntry()
    {
        return $this->cEntry;
    }

    /**
     * Set cExit.
     *
     * @param string|null $cExit
     *
     * @return CmiNode
     */
    public function setCExit($cExit = null)
    {
        $this->cExit = $cExit;

        return $this;
    }

    /**
     * Get cExit.
     *
     * @return string|null
     */
    public function getCExit()
    {
        return $this->cExit;
    }

    /**
     * Set cLanguage.
     *
     * @param string|null $cLanguage
     *
     * @return CmiNode
     */
    public function setCLanguage($cLanguage = null)
    {
        $this->cLanguage = $cLanguage;

        return $this;
    }

    /**
     * Get cLanguage.
     *
     * @return string|null
     */
    public function getCLanguage()
    {
        return $this->cLanguage;
    }

    /**
     * Set launchData.
     *
     * @param string|null $launchData
     *
     * @return CmiNode
     */
    public function setLaunchData($launchData = null)
    {
        $this->launchData = $launchData;

        return $this;
    }

    /**
     * Get launchData.
     *
     * @return string|null
     */
    public function getLaunchData()
    {
        return $this->launchData;
    }

    /**
     * Set learnerName.
     *
     * @param string|null $learnerName
     *
     * @return CmiNode
     */
    public function setLearnerName($learnerName = null)
    {
        $this->learnerName = $learnerName;

        return $this;
    }

    /**
     * Get learnerName.
     *
     * @return string|null
     */
    public function getLearnerName()
    {
        return $this->learnerName;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return CmiNode
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
     * Set cMax.
     *
     * @param float|null $cMax
     *
     * @return CmiNode
     */
    public function setCMax($cMax = null)
    {
        $this->cMax = $cMax;

        return $this;
    }

    /**
     * Get cMax.
     *
     * @return float|null
     */
    public function getCMax()
    {
        return $this->cMax;
    }

    /**
     * Set cMin.
     *
     * @param float|null $cMin
     *
     * @return CmiNode
     */
    public function setCMin($cMin = null)
    {
        $this->cMin = $cMin;

        return $this;
    }

    /**
     * Get cMin.
     *
     * @return float|null
     */
    public function getCMin()
    {
        return $this->cMin;
    }

    /**
     * Set cMode.
     *
     * @param string|null $cMode
     *
     * @return CmiNode
     */
    public function setCMode($cMode = null)
    {
        $this->cMode = $cMode;

        return $this;
    }

    /**
     * Get cMode.
     *
     * @return string|null
     */
    public function getCMode()
    {
        return $this->cMode;
    }

    /**
     * Set modified.
     *
     * @param string|null $modified
     *
     * @return CmiNode
     */
    public function setModified($modified = null)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return string|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set progressMeasure.
     *
     * @param float|null $progressMeasure
     *
     * @return CmiNode
     */
    public function setProgressMeasure($progressMeasure = null)
    {
        $this->progressMeasure = $progressMeasure;

        return $this;
    }

    /**
     * Get progressMeasure.
     *
     * @return float|null
     */
    public function getProgressMeasure()
    {
        return $this->progressMeasure;
    }

    /**
     * Set cRaw.
     *
     * @param float|null $cRaw
     *
     * @return CmiNode
     */
    public function setCRaw($cRaw = null)
    {
        $this->cRaw = $cRaw;

        return $this;
    }

    /**
     * Get cRaw.
     *
     * @return float|null
     */
    public function getCRaw()
    {
        return $this->cRaw;
    }

    /**
     * Set scaled.
     *
     * @param float|null $scaled
     *
     * @return CmiNode
     */
    public function setScaled($scaled = null)
    {
        $this->scaled = $scaled;

        return $this;
    }

    /**
     * Get scaled.
     *
     * @return float|null
     */
    public function getScaled()
    {
        return $this->scaled;
    }

    /**
     * Set scaledPassingScore.
     *
     * @param float|null $scaledPassingScore
     *
     * @return CmiNode
     */
    public function setScaledPassingScore($scaledPassingScore = null)
    {
        $this->scaledPassingScore = $scaledPassingScore;

        return $this;
    }

    /**
     * Get scaledPassingScore.
     *
     * @return float|null
     */
    public function getScaledPassingScore()
    {
        return $this->scaledPassingScore;
    }

    /**
     * Set sessionTime.
     *
     * @param string|null $sessionTime
     *
     * @return CmiNode
     */
    public function setSessionTime($sessionTime = null)
    {
        $this->sessionTime = $sessionTime;

        return $this;
    }

    /**
     * Get sessionTime.
     *
     * @return string|null
     */
    public function getSessionTime()
    {
        return $this->sessionTime;
    }

    /**
     * Set successStatus.
     *
     * @param string|null $successStatus
     *
     * @return CmiNode
     */
    public function setSuccessStatus($successStatus = null)
    {
        $this->successStatus = $successStatus;

        return $this;
    }

    /**
     * Get successStatus.
     *
     * @return string|null
     */
    public function getSuccessStatus()
    {
        return $this->successStatus;
    }

    /**
     * Set suspendData.
     *
     * @param string|null $suspendData
     *
     * @return CmiNode
     */
    public function setSuspendData($suspendData = null)
    {
        $this->suspendData = $suspendData;

        return $this;
    }

    /**
     * Get suspendData.
     *
     * @return string|null
     */
    public function getSuspendData()
    {
        return $this->suspendData;
    }

    /**
     * Set totalTime.
     *
     * @param string|null $totalTime
     *
     * @return CmiNode
     */
    public function setTotalTime($totalTime = null)
    {
        $this->totalTime = $totalTime;

        return $this;
    }

    /**
     * Get totalTime.
     *
     * @return string|null
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CmiNode
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set cTimestamp.
     *
     * @param \DateTime|null $cTimestamp
     *
     * @return CmiNode
     */
    public function setCTimestamp($cTimestamp = null)
    {
        $this->cTimestamp = $cTimestamp;

        return $this;
    }

    /**
     * Get cTimestamp.
     *
     * @return \DateTime|null
     */
    public function getCTimestamp()
    {
        return $this->cTimestamp;
    }

    /**
     * Set additionalTables.
     *
     * @param bool $additionalTables
     *
     * @return CmiNode
     */
    public function setAdditionalTables($additionalTables)
    {
        $this->additionalTables = $additionalTables;

        return $this;
    }

    /**
     * Get additionalTables.
     *
     * @return bool
     */
    public function getAdditionalTables()
    {
        return $this->additionalTables;
    }
}
