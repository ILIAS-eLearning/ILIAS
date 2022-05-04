<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilStudyProgrammeEvents
{
    private const COMPONENT = "Modules/StudyProgramme";

    public ilAppEventHandler $app_event_handler;
    protected ilStudyProgrammeAssignmentRepository $assignment_repo;

    public function __construct(
        ilAppEventHandler $app_event_handler,
        ilStudyProgrammeAssignmentRepository $assignment_repo
    ) {
        $this->app_event_handler = $app_event_handler;
        $this->assignment_repo = $assignment_repo;
    }

    public function raise(string $event, array $parameter) : void
    {
        $this->app_event_handler->raise(self::COMPONENT, $event, $parameter);
    }

    public function userAssigned(ilStudyProgrammeAssignment $assignment) : void
    {
        $this->raise(
            "userAssigned",
            [
                "root_prg_id" => $assignment->getRootId(),
                "usr_id" => $assignment->getUserId(),
                "ass_id" => $assignment->getId()
            ]
        );
    }

    public function userReAssigned(ilStudyProgrammeAssignment $a_assignment) : void
    {
        $this->raise(
            "userReAssigned",
            [
                "root_prg_ref_id" => ilObjStudyProgramme::getRefIdFor($a_assignment->getRootId()),
                "usr_id" => $a_assignment->getUserId()
            ]
        );
    }

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
                "usr_id" => $progress->getUserId(),
                "progress_id" => $progress->getId(),
                "ass_id" => $progress->getAssignmentId()
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
