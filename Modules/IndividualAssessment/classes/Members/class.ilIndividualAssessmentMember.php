<?php declare(strict_types=1);

/* Copyright (c) 2021 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Edit the record of a user, set LP.
 */
class ilIndividualAssessmentMember
{
    protected ilObjIndividualAssessment $iass;
    protected ilObjUser $usr;
    protected ilIndividualAssessmentUserGrading $grading;
    protected int $notification_ts;
    protected ?int $examiner_id;
    protected ?int $changer_id;
    protected ?DateTime $change_time;

    public function __construct(
        ilObjIndividualAssessment $iass,
        ilObjUser $usr,
        ilIndividualAssessmentUserGrading $grading,
        int $notification_ts,
        ?int $examiner_id = null,
        ?int $changer_id = null,
        ?DateTime $change_time = null
    ) {
        $this->iass = $iass;
        $this->usr = $usr;
        $this->grading = $grading;
        $this->notification_ts = $notification_ts;
        $this->examiner_id = $examiner_id;
        $this->changer_id = $changer_id;
        $this->change_time = $change_time;
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
            $notificator->withReceiver($this)->send();
            $this->notification_ts = time();
        }
        return $this;
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

    public function notificationTS() : int
    {
        return $this->notification_ts;
    }

    public function examinerId() : ?int
    {
        return $this->examiner_id;
    }

    public function withExaminerId(int $examiner_id) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->examiner_id = $examiner_id;
        return $clone;
    }

    public function changerId() : ?int
    {
        return $this->changer_id;
    }

    public function withChangerId(int $changer_id) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->changer_id = $changer_id;
        return $clone;
    }

    public function changeTime() : ?DateTime
    {
        return $this->change_time;
    }

    public function withChangeTime(DateTime $change_time = null) : ilIndividualAssessmentMember
    {
        $clone = clone $this;
        $clone->change_time = $change_time;
        return $clone;
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

    public function record() : string
    {
        return $this->grading->getRecord();
    }

    public function internalNote() : string
    {
        return $this->grading->getInternalNote();
    }

    public function fileName() : ?string
    {
        return $this->grading->getFile();
    }

    public function viewFile() : bool
    {
        return $this->grading->isFileVisible();
    }

    public function LPStatus() : int
    {
        return $this->grading->getLearningProgress();
    }

    public function place() : string
    {
        return $this->grading->getPlace();
    }

    public function eventTime() : ?DateTimeImmutable
    {
        return $this->grading->getEventTime();
    }

    public function notify() : bool
    {
        return $this->grading->isNotify();
    }

    public function finalized() : bool
    {
        return $this->grading->isFinalized();
    }

    public function assessment() : ilObjIndividualAssessment
    {
        return $this->iass;
    }

    public function assessmentId() : int
    {
        return $this->iass->getId();
    }

    public function id() : int
    {
        return $this->usr->getId();
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
}
