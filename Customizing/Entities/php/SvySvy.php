<?php



/**
 * SvySvy
 */
class SvySvy
{
    /**
     * @var int
     */
    private $surveyId = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $author;

    /**
     * @var string|null
     */
    private $introduction;

    /**
     * @var string|null
     */
    private $outro;

    /**
     * @var string|null
     */
    private $status = '1';

    /**
     * @var string|null
     */
    private $evaluationAccess = '0';

    /**
     * @var string|null
     */
    private $invitation = '0';

    /**
     * @var string|null
     */
    private $invitationMode = '1';

    /**
     * @var string|null
     */
    private $complete = '0';

    /**
     * @var string|null
     */
    private $anonymize = '0';

    /**
     * @var string|null
     */
    private $showQuestionTitles = '1';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var bool|null
     */
    private $mailnotification;

    /**
     * @var string|null
     */
    private $startdate;

    /**
     * @var string|null
     */
    private $enddate;

    /**
     * @var string|null
     */
    private $mailaddresses;

    /**
     * @var string|null
     */
    private $mailparticipantdata;

    /**
     * @var int|null
     */
    private $templateId;

    /**
     * @var bool|null
     */
    private $poolUsage;

    /**
     * @var bool
     */
    private $mode = '0';

    /**
     * @var bool
     */
    private $mode360SelfEval = '0';

    /**
     * @var bool
     */
    private $mode360SelfRate = '0';

    /**
     * @var bool
     */
    private $mode360SelfAppr = '0';

    /**
     * @var bool
     */
    private $mode360Results = '0';

    /**
     * @var bool
     */
    private $modeSkillService = '0';

    /**
     * @var bool
     */
    private $reminderStatus = '0';

    /**
     * @var \DateTime|null
     */
    private $reminderStart;

    /**
     * @var \DateTime|null
     */
    private $reminderEnd;

    /**
     * @var int
     */
    private $reminderFrequency = '0';

    /**
     * @var bool
     */
    private $reminderTarget = '0';

    /**
     * @var bool
     */
    private $tutorNtfStatus = '0';

    /**
     * @var string|null
     */
    private $tutorNtfReci;

    /**
     * @var bool
     */
    private $tutorNtfTarget = '0';

    /**
     * @var \DateTime|null
     */
    private $reminderLastSent;

    /**
     * @var bool|null
     */
    private $ownResultsView = '0';

    /**
     * @var bool|null
     */
    private $ownResultsMail = '0';

    /**
     * @var bool|null
     */
    private $confirmationMail;

    /**
     * @var bool|null
     */
    private $anonUserList = '0';

    /**
     * @var int|null
     */
    private $reminderTmpl;

    /**
     * @var bool|null
     */
    private $modeSelfEvalResults = '0';


    /**
     * Get surveyId.
     *
     * @return int
     */
    public function getSurveyId()
    {
        return $this->surveyId;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return SvySvy
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set author.
     *
     * @param string|null $author
     *
     * @return SvySvy
     */
    public function setAuthor($author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set introduction.
     *
     * @param string|null $introduction
     *
     * @return SvySvy
     */
    public function setIntroduction($introduction = null)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction.
     *
     * @return string|null
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set outro.
     *
     * @param string|null $outro
     *
     * @return SvySvy
     */
    public function setOutro($outro = null)
    {
        $this->outro = $outro;

        return $this;
    }

    /**
     * Get outro.
     *
     * @return string|null
     */
    public function getOutro()
    {
        return $this->outro;
    }

    /**
     * Set status.
     *
     * @param string|null $status
     *
     * @return SvySvy
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set evaluationAccess.
     *
     * @param string|null $evaluationAccess
     *
     * @return SvySvy
     */
    public function setEvaluationAccess($evaluationAccess = null)
    {
        $this->evaluationAccess = $evaluationAccess;

        return $this;
    }

    /**
     * Get evaluationAccess.
     *
     * @return string|null
     */
    public function getEvaluationAccess()
    {
        return $this->evaluationAccess;
    }

    /**
     * Set invitation.
     *
     * @param string|null $invitation
     *
     * @return SvySvy
     */
    public function setInvitation($invitation = null)
    {
        $this->invitation = $invitation;

        return $this;
    }

    /**
     * Get invitation.
     *
     * @return string|null
     */
    public function getInvitation()
    {
        return $this->invitation;
    }

    /**
     * Set invitationMode.
     *
     * @param string|null $invitationMode
     *
     * @return SvySvy
     */
    public function setInvitationMode($invitationMode = null)
    {
        $this->invitationMode = $invitationMode;

        return $this;
    }

    /**
     * Get invitationMode.
     *
     * @return string|null
     */
    public function getInvitationMode()
    {
        return $this->invitationMode;
    }

    /**
     * Set complete.
     *
     * @param string|null $complete
     *
     * @return SvySvy
     */
    public function setComplete($complete = null)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete.
     *
     * @return string|null
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set anonymize.
     *
     * @param string|null $anonymize
     *
     * @return SvySvy
     */
    public function setAnonymize($anonymize = null)
    {
        $this->anonymize = $anonymize;

        return $this;
    }

    /**
     * Get anonymize.
     *
     * @return string|null
     */
    public function getAnonymize()
    {
        return $this->anonymize;
    }

    /**
     * Set showQuestionTitles.
     *
     * @param string|null $showQuestionTitles
     *
     * @return SvySvy
     */
    public function setShowQuestionTitles($showQuestionTitles = null)
    {
        $this->showQuestionTitles = $showQuestionTitles;

        return $this;
    }

    /**
     * Get showQuestionTitles.
     *
     * @return string|null
     */
    public function getShowQuestionTitles()
    {
        return $this->showQuestionTitles;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvySvy
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return SvySvy
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set mailnotification.
     *
     * @param bool|null $mailnotification
     *
     * @return SvySvy
     */
    public function setMailnotification($mailnotification = null)
    {
        $this->mailnotification = $mailnotification;

        return $this;
    }

    /**
     * Get mailnotification.
     *
     * @return bool|null
     */
    public function getMailnotification()
    {
        return $this->mailnotification;
    }

    /**
     * Set startdate.
     *
     * @param string|null $startdate
     *
     * @return SvySvy
     */
    public function setStartdate($startdate = null)
    {
        $this->startdate = $startdate;

        return $this;
    }

    /**
     * Get startdate.
     *
     * @return string|null
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * Set enddate.
     *
     * @param string|null $enddate
     *
     * @return SvySvy
     */
    public function setEnddate($enddate = null)
    {
        $this->enddate = $enddate;

        return $this;
    }

    /**
     * Get enddate.
     *
     * @return string|null
     */
    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * Set mailaddresses.
     *
     * @param string|null $mailaddresses
     *
     * @return SvySvy
     */
    public function setMailaddresses($mailaddresses = null)
    {
        $this->mailaddresses = $mailaddresses;

        return $this;
    }

    /**
     * Get mailaddresses.
     *
     * @return string|null
     */
    public function getMailaddresses()
    {
        return $this->mailaddresses;
    }

    /**
     * Set mailparticipantdata.
     *
     * @param string|null $mailparticipantdata
     *
     * @return SvySvy
     */
    public function setMailparticipantdata($mailparticipantdata = null)
    {
        $this->mailparticipantdata = $mailparticipantdata;

        return $this;
    }

    /**
     * Get mailparticipantdata.
     *
     * @return string|null
     */
    public function getMailparticipantdata()
    {
        return $this->mailparticipantdata;
    }

    /**
     * Set templateId.
     *
     * @param int|null $templateId
     *
     * @return SvySvy
     */
    public function setTemplateId($templateId = null)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Set poolUsage.
     *
     * @param bool|null $poolUsage
     *
     * @return SvySvy
     */
    public function setPoolUsage($poolUsage = null)
    {
        $this->poolUsage = $poolUsage;

        return $this;
    }

    /**
     * Get poolUsage.
     *
     * @return bool|null
     */
    public function getPoolUsage()
    {
        return $this->poolUsage;
    }

    /**
     * Set mode.
     *
     * @param bool $mode
     *
     * @return SvySvy
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Get mode.
     *
     * @return bool
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set mode360SelfEval.
     *
     * @param bool $mode360SelfEval
     *
     * @return SvySvy
     */
    public function setMode360SelfEval($mode360SelfEval)
    {
        $this->mode360SelfEval = $mode360SelfEval;

        return $this;
    }

    /**
     * Get mode360SelfEval.
     *
     * @return bool
     */
    public function getMode360SelfEval()
    {
        return $this->mode360SelfEval;
    }

    /**
     * Set mode360SelfRate.
     *
     * @param bool $mode360SelfRate
     *
     * @return SvySvy
     */
    public function setMode360SelfRate($mode360SelfRate)
    {
        $this->mode360SelfRate = $mode360SelfRate;

        return $this;
    }

    /**
     * Get mode360SelfRate.
     *
     * @return bool
     */
    public function getMode360SelfRate()
    {
        return $this->mode360SelfRate;
    }

    /**
     * Set mode360SelfAppr.
     *
     * @param bool $mode360SelfAppr
     *
     * @return SvySvy
     */
    public function setMode360SelfAppr($mode360SelfAppr)
    {
        $this->mode360SelfAppr = $mode360SelfAppr;

        return $this;
    }

    /**
     * Get mode360SelfAppr.
     *
     * @return bool
     */
    public function getMode360SelfAppr()
    {
        return $this->mode360SelfAppr;
    }

    /**
     * Set mode360Results.
     *
     * @param bool $mode360Results
     *
     * @return SvySvy
     */
    public function setMode360Results($mode360Results)
    {
        $this->mode360Results = $mode360Results;

        return $this;
    }

    /**
     * Get mode360Results.
     *
     * @return bool
     */
    public function getMode360Results()
    {
        return $this->mode360Results;
    }

    /**
     * Set modeSkillService.
     *
     * @param bool $modeSkillService
     *
     * @return SvySvy
     */
    public function setModeSkillService($modeSkillService)
    {
        $this->modeSkillService = $modeSkillService;

        return $this;
    }

    /**
     * Get modeSkillService.
     *
     * @return bool
     */
    public function getModeSkillService()
    {
        return $this->modeSkillService;
    }

    /**
     * Set reminderStatus.
     *
     * @param bool $reminderStatus
     *
     * @return SvySvy
     */
    public function setReminderStatus($reminderStatus)
    {
        $this->reminderStatus = $reminderStatus;

        return $this;
    }

    /**
     * Get reminderStatus.
     *
     * @return bool
     */
    public function getReminderStatus()
    {
        return $this->reminderStatus;
    }

    /**
     * Set reminderStart.
     *
     * @param \DateTime|null $reminderStart
     *
     * @return SvySvy
     */
    public function setReminderStart($reminderStart = null)
    {
        $this->reminderStart = $reminderStart;

        return $this;
    }

    /**
     * Get reminderStart.
     *
     * @return \DateTime|null
     */
    public function getReminderStart()
    {
        return $this->reminderStart;
    }

    /**
     * Set reminderEnd.
     *
     * @param \DateTime|null $reminderEnd
     *
     * @return SvySvy
     */
    public function setReminderEnd($reminderEnd = null)
    {
        $this->reminderEnd = $reminderEnd;

        return $this;
    }

    /**
     * Get reminderEnd.
     *
     * @return \DateTime|null
     */
    public function getReminderEnd()
    {
        return $this->reminderEnd;
    }

    /**
     * Set reminderFrequency.
     *
     * @param int $reminderFrequency
     *
     * @return SvySvy
     */
    public function setReminderFrequency($reminderFrequency)
    {
        $this->reminderFrequency = $reminderFrequency;

        return $this;
    }

    /**
     * Get reminderFrequency.
     *
     * @return int
     */
    public function getReminderFrequency()
    {
        return $this->reminderFrequency;
    }

    /**
     * Set reminderTarget.
     *
     * @param bool $reminderTarget
     *
     * @return SvySvy
     */
    public function setReminderTarget($reminderTarget)
    {
        $this->reminderTarget = $reminderTarget;

        return $this;
    }

    /**
     * Get reminderTarget.
     *
     * @return bool
     */
    public function getReminderTarget()
    {
        return $this->reminderTarget;
    }

    /**
     * Set tutorNtfStatus.
     *
     * @param bool $tutorNtfStatus
     *
     * @return SvySvy
     */
    public function setTutorNtfStatus($tutorNtfStatus)
    {
        $this->tutorNtfStatus = $tutorNtfStatus;

        return $this;
    }

    /**
     * Get tutorNtfStatus.
     *
     * @return bool
     */
    public function getTutorNtfStatus()
    {
        return $this->tutorNtfStatus;
    }

    /**
     * Set tutorNtfReci.
     *
     * @param string|null $tutorNtfReci
     *
     * @return SvySvy
     */
    public function setTutorNtfReci($tutorNtfReci = null)
    {
        $this->tutorNtfReci = $tutorNtfReci;

        return $this;
    }

    /**
     * Get tutorNtfReci.
     *
     * @return string|null
     */
    public function getTutorNtfReci()
    {
        return $this->tutorNtfReci;
    }

    /**
     * Set tutorNtfTarget.
     *
     * @param bool $tutorNtfTarget
     *
     * @return SvySvy
     */
    public function setTutorNtfTarget($tutorNtfTarget)
    {
        $this->tutorNtfTarget = $tutorNtfTarget;

        return $this;
    }

    /**
     * Get tutorNtfTarget.
     *
     * @return bool
     */
    public function getTutorNtfTarget()
    {
        return $this->tutorNtfTarget;
    }

    /**
     * Set reminderLastSent.
     *
     * @param \DateTime|null $reminderLastSent
     *
     * @return SvySvy
     */
    public function setReminderLastSent($reminderLastSent = null)
    {
        $this->reminderLastSent = $reminderLastSent;

        return $this;
    }

    /**
     * Get reminderLastSent.
     *
     * @return \DateTime|null
     */
    public function getReminderLastSent()
    {
        return $this->reminderLastSent;
    }

    /**
     * Set ownResultsView.
     *
     * @param bool|null $ownResultsView
     *
     * @return SvySvy
     */
    public function setOwnResultsView($ownResultsView = null)
    {
        $this->ownResultsView = $ownResultsView;

        return $this;
    }

    /**
     * Get ownResultsView.
     *
     * @return bool|null
     */
    public function getOwnResultsView()
    {
        return $this->ownResultsView;
    }

    /**
     * Set ownResultsMail.
     *
     * @param bool|null $ownResultsMail
     *
     * @return SvySvy
     */
    public function setOwnResultsMail($ownResultsMail = null)
    {
        $this->ownResultsMail = $ownResultsMail;

        return $this;
    }

    /**
     * Get ownResultsMail.
     *
     * @return bool|null
     */
    public function getOwnResultsMail()
    {
        return $this->ownResultsMail;
    }

    /**
     * Set confirmationMail.
     *
     * @param bool|null $confirmationMail
     *
     * @return SvySvy
     */
    public function setConfirmationMail($confirmationMail = null)
    {
        $this->confirmationMail = $confirmationMail;

        return $this;
    }

    /**
     * Get confirmationMail.
     *
     * @return bool|null
     */
    public function getConfirmationMail()
    {
        return $this->confirmationMail;
    }

    /**
     * Set anonUserList.
     *
     * @param bool|null $anonUserList
     *
     * @return SvySvy
     */
    public function setAnonUserList($anonUserList = null)
    {
        $this->anonUserList = $anonUserList;

        return $this;
    }

    /**
     * Get anonUserList.
     *
     * @return bool|null
     */
    public function getAnonUserList()
    {
        return $this->anonUserList;
    }

    /**
     * Set reminderTmpl.
     *
     * @param int|null $reminderTmpl
     *
     * @return SvySvy
     */
    public function setReminderTmpl($reminderTmpl = null)
    {
        $this->reminderTmpl = $reminderTmpl;

        return $this;
    }

    /**
     * Get reminderTmpl.
     *
     * @return int|null
     */
    public function getReminderTmpl()
    {
        return $this->reminderTmpl;
    }

    /**
     * Set modeSelfEvalResults.
     *
     * @param bool|null $modeSelfEvalResults
     *
     * @return SvySvy
     */
    public function setModeSelfEvalResults($modeSelfEvalResults = null)
    {
        $this->modeSelfEvalResults = $modeSelfEvalResults;

        return $this;
    }

    /**
     * Get modeSelfEvalResults.
     *
     * @return bool|null
     */
    public function getModeSelfEvalResults()
    {
        return $this->modeSelfEvalResults;
    }
}
