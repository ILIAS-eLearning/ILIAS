<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2015 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 * Represents one assignment of a user to a study programme.
 *
 * A user could have multiple assignments per programme.
 */
class ilStudyProgrammeUserAssignment
{
    /**
     * @var ilStudyProgrammeAssignment
     */
    protected $assignment;

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    private $sp_user_progress_db;

    /**
     * @var ilStudyProgrammeAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * @var ilStudyProgrammeEvents
     */
    protected $sp_events;

    public function __construct(
        ilStudyProgrammeAssignment $assignment,
        ilStudyProgrammeUserProgressDB $sp_user_progress_db,
        ilStudyProgrammeAssignmentRepository $assignment_repository,
        ilStudyProgrammeEvents $sp_events
    ) {
        $this->assignment = $assignment;
        $this->sp_user_progress_db = $sp_user_progress_db;
        $this->assignment_repository = $assignment_repository;
        $this->sp_events = $sp_events;
    }

    /**
     * Get the id of the assignment.
     */
    public function getId() : int
    {
        return $this->assignment->getId();
    }

    /**
     * Get the program node where this assignment was made.
     *
     * Throws when program this assignment is about has no ref id.
     *
     * @throws ilException
     */
    public function getStudyProgramme() : ilObjStudyProgramme
    {
        $refs = ilObject::_getAllReferences((int) $this->assignment->getRootId());
        if (!count($refs)) {
            throw new ilException("ilStudyProgrammeUserAssignment::getStudyProgramme: "
                                 . "could not find ref_id for program '"
                                 . $this->assignment->getRootId() . "'.");
        }
        return ilObjStudyProgramme::getInstanceByRefId((int) array_shift($refs));
    }

    /**
     * Get the possible restart date of this assignment.
     * @return DateTime | null
     */
    public function getRestartDate()
    {
        return $this->assignment->getRestartDate();
    }

    /**
     * Get restarted assignment id.
     */
    public function getRestartedAssignmentId() : int
    {
        return $this->assignment->getRestartedAssignmentId();
    }

    /**
     * Get the progress on the root node of the programme.
     *
     * @throws ilException
     */
    public function getRootProgress() : ilStudyProgrammeUserProgress
    {
        return $this->getStudyProgramme()->getProgressForAssignment($this->getId());
    }

    /**
     * Assign the user belonging to this assignment to the prg
     * belonging to this assignment again.
     *
     * @throws ilException
     */
    public function restartAssignment() : ilStudyProgrammeUserAssignment
    {
        $restarted = $this->getStudyProgramme()->assignUser($this->getUserId(), $this->getUserId());
        $this->assignment_repository->update(
            $this->assignment->setRestartedAssignmentId($restarted->getId())
        );

        $this->sp_events->userReAssigned($this);

        return $restarted;
    }

    public function informUserByMailToRestart() : void
    {
        $this->sp_events->informUserByMailToRestart($this);
    }

    public function getUserId() : int
    {
        return $this->assignment->getUserId();
    }

    /**
     * Remove this assignment.
     *
     * @throws ilException
     */
    public function deassign() : void
    {
        $this->getStudyProgramme()->removeAssignment($this);
    }

    /**
     * Delete the assignment from database.
     */
    public function delete() : void
    {
        $progresses = $this->sp_user_progress_db->getInstancesForAssignment($this->getId());
        foreach ($progresses as $progress) {
            $progress->delete();
        }
        $this->assignment_repository->delete(
            $this->assignment
        );
    }

    /**
     * Update all unmodified nodes in this assignment to the current state
     * of the program.
     */
    public function updateFromProgram() : ilStudyProgrammeUserAssignment
    {
        $prg = $this->getStudyProgramme();
        $id = $this->getId();

        $prg->applyToSubTreeNodes(
            function (ilObjStudyProgramme $node) use ($id) {
                /**@var ilStudyProgrammeUserProgress $progress*/
                $progress = $node->getProgressForAssignment($id);
                return $progress->updateFromProgramNode();
            },
            true
        );

        return $this;
    }

    public function updateValidityFromProgram() : void
    {
        $prg = $this->getStudyProgramme();
        $progress = $this->getRootProgress();
        if (!$progress->hasSuccessStatus()) {
            return;
        }

        $validity_settings = $prg->getValidityOfQualificationSettings();
        $period = $validity_settings->getQualificationPeriod();
        $date = $validity_settings->getQualificationDate();

        if ($period) {
            $date = $progress->getCompletionDate();
            $date->add(new DateInterval('P' . $period . 'D'));
        }
        $progress->setValidityOfQualification($date);
        $progress->storeProgress();
    }

    public function updateDeadlineFromProgram() : void
    {
        $prg = $this->getStudyProgramme();
        $progress = $this->getRootProgress();
        if ($progress->hasSuccessStatus()) {
            return;
        }

        $deadline_settings = $prg->getDeadlineSettings();
        $period = $deadline_settings->getDeadlinePeriod();
        $date = $deadline_settings->getDeadlineDate();
        if ($period) {
            $date = $progress->getAssignmentDate();
            $date->add(new DateInterval('P' . $period . 'D'));
        }
        $progress->setDeadline($date);
        $progress->storeProgress();
    }

    public function getSPAssignment() : ilStudyProgrammeAssignment
    {
        return $this->assignment;
    }

    /**
     * @throws ilException
     */
    public static function sendInformToReAssignMail(int $assignment_id, int $usr_id) : void
    {
        global $DIC;
        $lng = $DIC['lng'];
        $log = $DIC['ilLog'];
        $lng->loadLanguageModule("prg");
        $lng->loadLanguageModule("mail");

        /** @var ilStudyProgrammeUserAssignmentDB $assignment_db */
        $assignment_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        /** @var ilStudyProgrammeUserAssignment $assignment */
        $assignment = $assignment_db->getInstanceById($assignment_id);
        /** @var ilObjStudyProgramme $prg */
        $prg = $assignment->getStudyProgramme();

        if (!$prg->shouldSendInfoToReAssignMail()) {
            $log->write("Send info to re assign mail is deactivated in study programme settings");
            return;
        }

        $subject = $lng->txt("info_to_re_assign_mail_subject");
        $gender = ilObjUser::_lookupGender($usr_id);
        $name = ilObjUser::_lookupFullname($usr_id);
        $body = sprintf(
            $lng->txt("info_to_re_assign_mail_body"),
            $lng->txt("mail_salutation_" . $gender),
            $name,
            $prg->getTitle()
        );

        $send = true;
        $mail = new ilMail(ANONYMOUS_USER_ID);
        try {
            $mail->enqueue(
                ilObjUser::_lookupLogin($usr_id),
                '',
                '',
                $subject,
                $body,
                null
            );
        } catch (Exception $e) {
            $send = false;
        }

        if ($send) {
            $assignment_db->reminderSendFor($assignment->getId());
        }
    }
}
