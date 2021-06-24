<?php

declare(strict_types=1);

/**
 * Edit the record of a user, set LP.
 * @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 * @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilIndividualAssessmentMember
{
    /**
     * @var ilObjIndividualAssessment
     */
    protected $iass;

    /**
     * @var ilObjUser
     */
    protected $usr;

    /**
     * @var ilIndividualAssessmentUserGrading
     */
    protected $grading;

    /**
     * @var int|null
     */
    protected $examiner_id;

    /**
     * @var int
     */
    protected $notification_ts;

    /**
     * @var int|null
     */
    protected $changer_id;

    /**
     * @var DateTime|null
     */
    protected $change_time;

    public function __construct(
        ilObjIndividualAssessment $iass,
        ilObjUser $usr,
        ?ilIndividualAssessmentUserGrading $grading,
        ?int $examiner_id,
        int $notification_ts,
        ?int $changer_id,
        ?DateTime $change_time
    ) {
        $this->iass = $iass;
        $this->usr = $usr;
        $this->grading = $grading;
        $this->examiner_id = $examiner_id;
        $this->notification_ts = $notification_ts;
        $this->changer_id = $changer_id;
        $this->change_time = $change_time;
    }

    public function record() : string
    {
        return $this->grading->getRecord();
    }

    public function internalNote() : string
    {
        return $this->grading->getInternalNote();
    }

    public function examinerId() : ?int
    {
        return $this->examiner_id;
    }

    public function changerId() : ?int
    {
        return $this->changer_id;
    }

    public function changeTime() : ?DateTime
    {
        return $this->change_time;
    }

    public function notify() : bool
    {
        return $this->grading->isNotify();
    }

    public function maybeSendNotification(
        ilIndividualAssessmentNotificator $notificator
    ) : ilIndividualAssessmentMember {
        if (!$this->finalized()) {
            throw new ilIndividualAssessmentException('must finalize before notification');
        }
        if ($this->notify()) {
            $notificator = (string) $this->LPStatus() === (string) ilIndividualAssessmentMembers::LP_COMPLETED ?
                $notificator->withOccasionCompleted() :
                $notificator->withOccasionFailed();
            $notificator->withReciever($this)->send();
            $this->notification_ts = time();
        }
        return $this;
    }

    public function id() : int
    {
        return (int) $this->usr->getId();
    }

    public function assessmentId() : int
    {
        return (int) $this->iass->getId();
    }

    public function assessment() : ilObjIndividualAssessment
    {
        return $this->iass;
    }

    public function finalized() : bool
    {
        return $this->grading->isFinalized();
    }

    public function mayBeFinalized() : bool
    {
        if ($this->iass->getSettings()->isFileRequired() && (string) $this->fileName() === '') {
            return false;
        }
        return in_array(
            $this->LPStatus(),
            [
                ilIndividualAssessmentMembers::LP_COMPLETED,
                ilIndividualAssessmentMembers::LP_FAILED
            ]
        ) &&
            !$this->finalized();
    }

    public function withExaminerId(int $examiner_id) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->examiner_id = $examiner_id;
        return $clone;
    }

    public function withChangerId(int $changer_id) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->changer_id = $changer_id;
        return $clone;
    }

    public function withChangeTime(DateTime $change_time = null) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->change_time = $change_time;
        return $clone;
    }

    protected function LPStatusValid($lp_status)
    {
        return (string) $lp_status === (string) ilIndividualAssessmentMembers::LP_NOT_ATTEMPTED
                || (string) $lp_status === (string) ilIndividualAssessmentMembers::LP_IN_PROGRESS
                || (string) $lp_status === (string) ilIndividualAssessmentMembers::LP_COMPLETED
                || (string) $lp_status === (string) ilIndividualAssessmentMembers::LP_FAILED;
    }

    public function lastname() : string
    {
        return $this->usr->getLastname();
    }

    public function firstname() : string
    {
        return $this->usr->getFirstname();
    }

    public function login() : string
    {
        return $this->usr->getLogin();
    }

    public function name() : string
    {
        return $this->usr->getFullname();
    }

    public function LPStatus() : int
    {
        return $this->grading->getLearningProgress();
    }

    public function notificationTS() : int
    {
        return $this->notification_ts;
    }

    public function place() : string
    {
        return $this->grading->getPlace();
    }

    public function eventTime() : ?DateTimeImmutable
    {
        return $this->grading->getEventTime();
    }

    public function fileName() : ?string
    {
        return $this->grading->getFile();
    }

    public function viewFile() : bool
    {
        return $this->grading->isFileVisible();
    }

    public function getGrading() : ilIndividualAssessmentUserGrading
    {
        return $this->grading;
    }

    public function withGrading($grading) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->grading = $grading;
        return $clone;
    }
}
