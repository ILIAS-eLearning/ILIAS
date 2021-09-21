<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilStudyProgrammeEvents
{
    const COMPONENT = "Modules/StudyProgramme";

    /**
     * @var ilAppEventHandler
     */
    public $app_event_handler;

    /**
     * @var ilStudyProgrammeAssignmentRepository
     */
    protected $assignment_repo;

    public function __construct(
        \ilAppEventHandler $app_event_handler,
        \ilStudyProgrammeAssignmentRepository $assignment_repo
    ) {
        $this->app_event_handler = $app_event_handler;
        $this->assignment_repo = $assignment_repo;
    }

    public function raise($a_event, $a_parameter) : void
    {
        $this->app_event_handler->raise(self::COMPONENT, $a_event, $a_parameter);
    }

    /**
     * @throws ilException
     */
    public function userAssigned(ilStudyProgrammeAssignment $a_assignment) : void
    {
        $this->raise(
            "userAssigned",
            [
                "root_prg_id" => $a_assignment->getRootId(),
                "usr_id" => $a_assignment->getUserId(),
                "ass_id" => $a_assignment->getId()
            ]
        );
    }

    /**
     * @throws ilException
     */
    public function userReAssigned(ilStudyProgrammeAssignment $a_assignment) : void
    {
        $this->raise(
            "userReAssigned",
            [
                "root_prg_ref_id" => (int) ilObjStudyProgramme::getRefIdFor($a_assignment->getRootId()),
                "usr_id" => (int) $a_assignment->getUserId()
            ]
        );
    }

    /**
     * @throws ilException
     */
    public function userDeassigned(ilStudyProgrammeAssignment $a_assignment) : void
    {
        $this->raise(
            "userDeassigned",
            [
                "root_prg_id" => $a_assignment->getRootId(),
                "usr_id" => $a_assignment->getUserId(),
                "ass_id" => $a_assignment->getId()
            ]
        );
    }

    /**
     * @throws ilException
     */
    public function userSuccessful(ilStudyProgrammeProgress $a_progress) : void
    {
        $ass = $this->assignment_repo->get($a_progress->getAssignmentId());
        $this->raise(
            "userSuccessful",
            [
                "root_prg_id" => $ass->getRootId(),
                "prg_id" => $a_progress->getNodeId(),
                "usr_id" => $ass->getUserId(),
                "ass_id" => $ass->getId()
            ]
        );
    }

    public function informUserByMailToRestart(ilStudyProgrammeProgress $progress) : void
    {
        $this->raise(
            'informUserToRestart',
            [
                "usr_id" => (int) $progress->getUserId(),
                "progress_id" => (int) $progress->getId(),
                "ass_id" => (int) $progress->getAssignmentId()
            ]
        );
    }

    public function userRiskyToFail(ilStudyProgrammeProgress $progress) : void
    {
        $this->raise(
            "userRiskyToFail",
            [
                "progress_id" => $progress->getId(),
                "usr_id" => $progress->getUserId()
            ]
        );
    }
}
