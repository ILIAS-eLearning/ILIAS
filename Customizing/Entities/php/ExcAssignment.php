<?php



/**
 * ExcAssignment
 */
class ExcAssignment
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $excId = '0';

    /**
     * @var int|null
     */
    private $timeStamp;

    /**
     * @var string|null
     */
    private $instruction;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int|null
     */
    private $startTime;

    /**
     * @var bool|null
     */
    private $mandatory = '1';

    /**
     * @var int
     */
    private $orderNr = '0';

    /**
     * @var bool
     */
    private $type = '1';

    /**
     * @var bool
     */
    private $peer = '0';

    /**
     * @var int
     */
    private $peerMin = '0';

    /**
     * @var string|null
     */
    private $fbFile;

    /**
     * @var bool
     */
    private $fbCron = '0';

    /**
     * @var bool
     */
    private $fbCronDone = '0';

    /**
     * @var int|null
     */
    private $peerDl = '0';

    /**
     * @var bool|null
     */
    private $peerFile = '0';

    /**
     * @var bool|null
     */
    private $peerPrsl = '0';

    /**
     * @var bool
     */
    private $fbDate = '1';

    /**
     * @var int|null
     */
    private $peerChar;

    /**
     * @var bool
     */
    private $peerUnlock = '0';

    /**
     * @var bool
     */
    private $peerValid = '1';

    /**
     * @var bool
     */
    private $teamTutor = '0';

    /**
     * @var bool|null
     */
    private $maxFile;

    /**
     * @var int|null
     */
    private $deadline2;

    /**
     * @var bool
     */
    private $peerText = '1';

    /**
     * @var bool
     */
    private $peerRating = '1';

    /**
     * @var int|null
     */
    private $peerCritCat;

    /**
     * @var int|null
     */
    private $portfolioTemplate;

    /**
     * @var int|null
     */
    private $minCharLimit;

    /**
     * @var int|null
     */
    private $maxCharLimit;

    /**
     * @var int|null
     */
    private $fbDateCustom;

    /**
     * @var bool|null
     */
    private $rmdSubmitStatus;

    /**
     * @var int|null
     */
    private $rmdSubmitStart;

    /**
     * @var int|null
     */
    private $rmdSubmitEnd;

    /**
     * @var int|null
     */
    private $rmdSubmitFreq;

    /**
     * @var bool|null
     */
    private $rmdGradeStatus;

    /**
     * @var int|null
     */
    private $rmdGradeStart;

    /**
     * @var int|null
     */
    private $rmdGradeEnd;

    /**
     * @var int|null
     */
    private $rmdGradeFreq;

    /**
     * @var bool|null
     */
    private $peerRmdStatus;

    /**
     * @var int|null
     */
    private $peerRmdStart;

    /**
     * @var int|null
     */
    private $peerRmdEnd;

    /**
     * @var int|null
     */
    private $peerRmdFreq;

    /**
     * @var bool|null
     */
    private $deadlineMode = '0';

    /**
     * @var int|null
     */
    private $relativeDeadline = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set excId.
     *
     * @param int $excId
     *
     * @return ExcAssignment
     */
    public function setExcId($excId)
    {
        $this->excId = $excId;

        return $this;
    }

    /**
     * Get excId.
     *
     * @return int
     */
    public function getExcId()
    {
        return $this->excId;
    }

    /**
     * Set timeStamp.
     *
     * @param int|null $timeStamp
     *
     * @return ExcAssignment
     */
    public function setTimeStamp($timeStamp = null)
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }

    /**
     * Get timeStamp.
     *
     * @return int|null
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Set instruction.
     *
     * @param string|null $instruction
     *
     * @return ExcAssignment
     */
    public function setInstruction($instruction = null)
    {
        $this->instruction = $instruction;

        return $this;
    }

    /**
     * Get instruction.
     *
     * @return string|null
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return ExcAssignment
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
     * Set startTime.
     *
     * @param int|null $startTime
     *
     * @return ExcAssignment
     */
    public function setStartTime($startTime = null)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return int|null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set mandatory.
     *
     * @param bool|null $mandatory
     *
     * @return ExcAssignment
     */
    public function setMandatory($mandatory = null)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get mandatory.
     *
     * @return bool|null
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return ExcAssignment
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }

    /**
     * Set type.
     *
     * @param bool $type
     *
     * @return ExcAssignment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set peer.
     *
     * @param bool $peer
     *
     * @return ExcAssignment
     */
    public function setPeer($peer)
    {
        $this->peer = $peer;

        return $this;
    }

    /**
     * Get peer.
     *
     * @return bool
     */
    public function getPeer()
    {
        return $this->peer;
    }

    /**
     * Set peerMin.
     *
     * @param int $peerMin
     *
     * @return ExcAssignment
     */
    public function setPeerMin($peerMin)
    {
        $this->peerMin = $peerMin;

        return $this;
    }

    /**
     * Get peerMin.
     *
     * @return int
     */
    public function getPeerMin()
    {
        return $this->peerMin;
    }

    /**
     * Set fbFile.
     *
     * @param string|null $fbFile
     *
     * @return ExcAssignment
     */
    public function setFbFile($fbFile = null)
    {
        $this->fbFile = $fbFile;

        return $this;
    }

    /**
     * Get fbFile.
     *
     * @return string|null
     */
    public function getFbFile()
    {
        return $this->fbFile;
    }

    /**
     * Set fbCron.
     *
     * @param bool $fbCron
     *
     * @return ExcAssignment
     */
    public function setFbCron($fbCron)
    {
        $this->fbCron = $fbCron;

        return $this;
    }

    /**
     * Get fbCron.
     *
     * @return bool
     */
    public function getFbCron()
    {
        return $this->fbCron;
    }

    /**
     * Set fbCronDone.
     *
     * @param bool $fbCronDone
     *
     * @return ExcAssignment
     */
    public function setFbCronDone($fbCronDone)
    {
        $this->fbCronDone = $fbCronDone;

        return $this;
    }

    /**
     * Get fbCronDone.
     *
     * @return bool
     */
    public function getFbCronDone()
    {
        return $this->fbCronDone;
    }

    /**
     * Set peerDl.
     *
     * @param int|null $peerDl
     *
     * @return ExcAssignment
     */
    public function setPeerDl($peerDl = null)
    {
        $this->peerDl = $peerDl;

        return $this;
    }

    /**
     * Get peerDl.
     *
     * @return int|null
     */
    public function getPeerDl()
    {
        return $this->peerDl;
    }

    /**
     * Set peerFile.
     *
     * @param bool|null $peerFile
     *
     * @return ExcAssignment
     */
    public function setPeerFile($peerFile = null)
    {
        $this->peerFile = $peerFile;

        return $this;
    }

    /**
     * Get peerFile.
     *
     * @return bool|null
     */
    public function getPeerFile()
    {
        return $this->peerFile;
    }

    /**
     * Set peerPrsl.
     *
     * @param bool|null $peerPrsl
     *
     * @return ExcAssignment
     */
    public function setPeerPrsl($peerPrsl = null)
    {
        $this->peerPrsl = $peerPrsl;

        return $this;
    }

    /**
     * Get peerPrsl.
     *
     * @return bool|null
     */
    public function getPeerPrsl()
    {
        return $this->peerPrsl;
    }

    /**
     * Set fbDate.
     *
     * @param bool $fbDate
     *
     * @return ExcAssignment
     */
    public function setFbDate($fbDate)
    {
        $this->fbDate = $fbDate;

        return $this;
    }

    /**
     * Get fbDate.
     *
     * @return bool
     */
    public function getFbDate()
    {
        return $this->fbDate;
    }

    /**
     * Set peerChar.
     *
     * @param int|null $peerChar
     *
     * @return ExcAssignment
     */
    public function setPeerChar($peerChar = null)
    {
        $this->peerChar = $peerChar;

        return $this;
    }

    /**
     * Get peerChar.
     *
     * @return int|null
     */
    public function getPeerChar()
    {
        return $this->peerChar;
    }

    /**
     * Set peerUnlock.
     *
     * @param bool $peerUnlock
     *
     * @return ExcAssignment
     */
    public function setPeerUnlock($peerUnlock)
    {
        $this->peerUnlock = $peerUnlock;

        return $this;
    }

    /**
     * Get peerUnlock.
     *
     * @return bool
     */
    public function getPeerUnlock()
    {
        return $this->peerUnlock;
    }

    /**
     * Set peerValid.
     *
     * @param bool $peerValid
     *
     * @return ExcAssignment
     */
    public function setPeerValid($peerValid)
    {
        $this->peerValid = $peerValid;

        return $this;
    }

    /**
     * Get peerValid.
     *
     * @return bool
     */
    public function getPeerValid()
    {
        return $this->peerValid;
    }

    /**
     * Set teamTutor.
     *
     * @param bool $teamTutor
     *
     * @return ExcAssignment
     */
    public function setTeamTutor($teamTutor)
    {
        $this->teamTutor = $teamTutor;

        return $this;
    }

    /**
     * Get teamTutor.
     *
     * @return bool
     */
    public function getTeamTutor()
    {
        return $this->teamTutor;
    }

    /**
     * Set maxFile.
     *
     * @param bool|null $maxFile
     *
     * @return ExcAssignment
     */
    public function setMaxFile($maxFile = null)
    {
        $this->maxFile = $maxFile;

        return $this;
    }

    /**
     * Get maxFile.
     *
     * @return bool|null
     */
    public function getMaxFile()
    {
        return $this->maxFile;
    }

    /**
     * Set deadline2.
     *
     * @param int|null $deadline2
     *
     * @return ExcAssignment
     */
    public function setDeadline2($deadline2 = null)
    {
        $this->deadline2 = $deadline2;

        return $this;
    }

    /**
     * Get deadline2.
     *
     * @return int|null
     */
    public function getDeadline2()
    {
        return $this->deadline2;
    }

    /**
     * Set peerText.
     *
     * @param bool $peerText
     *
     * @return ExcAssignment
     */
    public function setPeerText($peerText)
    {
        $this->peerText = $peerText;

        return $this;
    }

    /**
     * Get peerText.
     *
     * @return bool
     */
    public function getPeerText()
    {
        return $this->peerText;
    }

    /**
     * Set peerRating.
     *
     * @param bool $peerRating
     *
     * @return ExcAssignment
     */
    public function setPeerRating($peerRating)
    {
        $this->peerRating = $peerRating;

        return $this;
    }

    /**
     * Get peerRating.
     *
     * @return bool
     */
    public function getPeerRating()
    {
        return $this->peerRating;
    }

    /**
     * Set peerCritCat.
     *
     * @param int|null $peerCritCat
     *
     * @return ExcAssignment
     */
    public function setPeerCritCat($peerCritCat = null)
    {
        $this->peerCritCat = $peerCritCat;

        return $this;
    }

    /**
     * Get peerCritCat.
     *
     * @return int|null
     */
    public function getPeerCritCat()
    {
        return $this->peerCritCat;
    }

    /**
     * Set portfolioTemplate.
     *
     * @param int|null $portfolioTemplate
     *
     * @return ExcAssignment
     */
    public function setPortfolioTemplate($portfolioTemplate = null)
    {
        $this->portfolioTemplate = $portfolioTemplate;

        return $this;
    }

    /**
     * Get portfolioTemplate.
     *
     * @return int|null
     */
    public function getPortfolioTemplate()
    {
        return $this->portfolioTemplate;
    }

    /**
     * Set minCharLimit.
     *
     * @param int|null $minCharLimit
     *
     * @return ExcAssignment
     */
    public function setMinCharLimit($minCharLimit = null)
    {
        $this->minCharLimit = $minCharLimit;

        return $this;
    }

    /**
     * Get minCharLimit.
     *
     * @return int|null
     */
    public function getMinCharLimit()
    {
        return $this->minCharLimit;
    }

    /**
     * Set maxCharLimit.
     *
     * @param int|null $maxCharLimit
     *
     * @return ExcAssignment
     */
    public function setMaxCharLimit($maxCharLimit = null)
    {
        $this->maxCharLimit = $maxCharLimit;

        return $this;
    }

    /**
     * Get maxCharLimit.
     *
     * @return int|null
     */
    public function getMaxCharLimit()
    {
        return $this->maxCharLimit;
    }

    /**
     * Set fbDateCustom.
     *
     * @param int|null $fbDateCustom
     *
     * @return ExcAssignment
     */
    public function setFbDateCustom($fbDateCustom = null)
    {
        $this->fbDateCustom = $fbDateCustom;

        return $this;
    }

    /**
     * Get fbDateCustom.
     *
     * @return int|null
     */
    public function getFbDateCustom()
    {
        return $this->fbDateCustom;
    }

    /**
     * Set rmdSubmitStatus.
     *
     * @param bool|null $rmdSubmitStatus
     *
     * @return ExcAssignment
     */
    public function setRmdSubmitStatus($rmdSubmitStatus = null)
    {
        $this->rmdSubmitStatus = $rmdSubmitStatus;

        return $this;
    }

    /**
     * Get rmdSubmitStatus.
     *
     * @return bool|null
     */
    public function getRmdSubmitStatus()
    {
        return $this->rmdSubmitStatus;
    }

    /**
     * Set rmdSubmitStart.
     *
     * @param int|null $rmdSubmitStart
     *
     * @return ExcAssignment
     */
    public function setRmdSubmitStart($rmdSubmitStart = null)
    {
        $this->rmdSubmitStart = $rmdSubmitStart;

        return $this;
    }

    /**
     * Get rmdSubmitStart.
     *
     * @return int|null
     */
    public function getRmdSubmitStart()
    {
        return $this->rmdSubmitStart;
    }

    /**
     * Set rmdSubmitEnd.
     *
     * @param int|null $rmdSubmitEnd
     *
     * @return ExcAssignment
     */
    public function setRmdSubmitEnd($rmdSubmitEnd = null)
    {
        $this->rmdSubmitEnd = $rmdSubmitEnd;

        return $this;
    }

    /**
     * Get rmdSubmitEnd.
     *
     * @return int|null
     */
    public function getRmdSubmitEnd()
    {
        return $this->rmdSubmitEnd;
    }

    /**
     * Set rmdSubmitFreq.
     *
     * @param int|null $rmdSubmitFreq
     *
     * @return ExcAssignment
     */
    public function setRmdSubmitFreq($rmdSubmitFreq = null)
    {
        $this->rmdSubmitFreq = $rmdSubmitFreq;

        return $this;
    }

    /**
     * Get rmdSubmitFreq.
     *
     * @return int|null
     */
    public function getRmdSubmitFreq()
    {
        return $this->rmdSubmitFreq;
    }

    /**
     * Set rmdGradeStatus.
     *
     * @param bool|null $rmdGradeStatus
     *
     * @return ExcAssignment
     */
    public function setRmdGradeStatus($rmdGradeStatus = null)
    {
        $this->rmdGradeStatus = $rmdGradeStatus;

        return $this;
    }

    /**
     * Get rmdGradeStatus.
     *
     * @return bool|null
     */
    public function getRmdGradeStatus()
    {
        return $this->rmdGradeStatus;
    }

    /**
     * Set rmdGradeStart.
     *
     * @param int|null $rmdGradeStart
     *
     * @return ExcAssignment
     */
    public function setRmdGradeStart($rmdGradeStart = null)
    {
        $this->rmdGradeStart = $rmdGradeStart;

        return $this;
    }

    /**
     * Get rmdGradeStart.
     *
     * @return int|null
     */
    public function getRmdGradeStart()
    {
        return $this->rmdGradeStart;
    }

    /**
     * Set rmdGradeEnd.
     *
     * @param int|null $rmdGradeEnd
     *
     * @return ExcAssignment
     */
    public function setRmdGradeEnd($rmdGradeEnd = null)
    {
        $this->rmdGradeEnd = $rmdGradeEnd;

        return $this;
    }

    /**
     * Get rmdGradeEnd.
     *
     * @return int|null
     */
    public function getRmdGradeEnd()
    {
        return $this->rmdGradeEnd;
    }

    /**
     * Set rmdGradeFreq.
     *
     * @param int|null $rmdGradeFreq
     *
     * @return ExcAssignment
     */
    public function setRmdGradeFreq($rmdGradeFreq = null)
    {
        $this->rmdGradeFreq = $rmdGradeFreq;

        return $this;
    }

    /**
     * Get rmdGradeFreq.
     *
     * @return int|null
     */
    public function getRmdGradeFreq()
    {
        return $this->rmdGradeFreq;
    }

    /**
     * Set peerRmdStatus.
     *
     * @param bool|null $peerRmdStatus
     *
     * @return ExcAssignment
     */
    public function setPeerRmdStatus($peerRmdStatus = null)
    {
        $this->peerRmdStatus = $peerRmdStatus;

        return $this;
    }

    /**
     * Get peerRmdStatus.
     *
     * @return bool|null
     */
    public function getPeerRmdStatus()
    {
        return $this->peerRmdStatus;
    }

    /**
     * Set peerRmdStart.
     *
     * @param int|null $peerRmdStart
     *
     * @return ExcAssignment
     */
    public function setPeerRmdStart($peerRmdStart = null)
    {
        $this->peerRmdStart = $peerRmdStart;

        return $this;
    }

    /**
     * Get peerRmdStart.
     *
     * @return int|null
     */
    public function getPeerRmdStart()
    {
        return $this->peerRmdStart;
    }

    /**
     * Set peerRmdEnd.
     *
     * @param int|null $peerRmdEnd
     *
     * @return ExcAssignment
     */
    public function setPeerRmdEnd($peerRmdEnd = null)
    {
        $this->peerRmdEnd = $peerRmdEnd;

        return $this;
    }

    /**
     * Get peerRmdEnd.
     *
     * @return int|null
     */
    public function getPeerRmdEnd()
    {
        return $this->peerRmdEnd;
    }

    /**
     * Set peerRmdFreq.
     *
     * @param int|null $peerRmdFreq
     *
     * @return ExcAssignment
     */
    public function setPeerRmdFreq($peerRmdFreq = null)
    {
        $this->peerRmdFreq = $peerRmdFreq;

        return $this;
    }

    /**
     * Get peerRmdFreq.
     *
     * @return int|null
     */
    public function getPeerRmdFreq()
    {
        return $this->peerRmdFreq;
    }

    /**
     * Set deadlineMode.
     *
     * @param bool|null $deadlineMode
     *
     * @return ExcAssignment
     */
    public function setDeadlineMode($deadlineMode = null)
    {
        $this->deadlineMode = $deadlineMode;

        return $this;
    }

    /**
     * Get deadlineMode.
     *
     * @return bool|null
     */
    public function getDeadlineMode()
    {
        return $this->deadlineMode;
    }

    /**
     * Set relativeDeadline.
     *
     * @param int|null $relativeDeadline
     *
     * @return ExcAssignment
     */
    public function setRelativeDeadline($relativeDeadline = null)
    {
        $this->relativeDeadline = $relativeDeadline;

        return $this;
    }

    /**
     * Get relativeDeadline.
     *
     * @return int|null
     */
    public function getRelativeDeadline()
    {
        return $this->relativeDeadline;
    }
}
