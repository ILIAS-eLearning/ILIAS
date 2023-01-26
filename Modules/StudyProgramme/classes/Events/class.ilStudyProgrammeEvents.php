<?php

declare(strict_types=1);

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

class ilStudyProgrammeEvents implements StudyProgrammeEvents
{
    protected ilAppEventHandler $app_event_handler;

    public function __construct(
        ilAppEventHandler $app_event_handler,
        PRGEventHandler $prg_event_handler
    ) {
        $this->app_event_handler = $app_event_handler;
        $this->prg_event_handler = $prg_event_handler;
    }

    public function raise(string $event, array $parameter): void
    {
        switch ($event) {
            case self::EVENT_USER_LP_STATUS_CHANGE:
                $this->prg_event_handler->onUpdateLPStatus($parameter['prg_id'], $parameter['usr_id']);
                break;
            default:
                $this->app_event_handler->raise(self::COMPONENT, $event, $parameter);
        }
    }


    public function userAssigned(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_ASSIGNED,
            [
                "root_prg_id" => $assignment->getRootId(),
                "usr_id" => $assignment->getUserId(),
                "ass_id" => $assignment->getId()
            ]
        );
    }

    public function userReAssigned(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_REASSIGNED,
            [
                "ass_id" => $assignment->getId()
            ]
        );
    }

    public function userDeassigned(ilPRGAssignment $a_assignment): void
    {
        $this->raise(
            self::EVENT_USER_DEASSIGNED,
            [
                "root_prg_id" => $a_assignment->getRootId(),
                "usr_id" => $a_assignment->getUserId(),
                "ass_id" => $a_assignment->getId()
            ]
        );
    }

    public function userSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(
            self::EVENT_USER_SUCCESSFUL,
            [
                "root_prg_id" => $assignment->getRootId(),
                "prg_id" => $pgs_node_id,
                "usr_id" => $assignment->getUserId(),
                "ass_id" => $assignment->getId()
            ]
        );
    }

    public function informUserByMailToRestart(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_TO_RESTART,
            [
                "ass_id" => (int) $assignment->getId()
            ]
        );
    }

    public function userRiskyToFail(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_ABOUT_TO_FAIL,
            [
                "ass_id" => (int) $assignment->getId()
            ]
        );
    }

    public function userLPStatusChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(
            self::EVENT_USER_LP_STATUS_CHANGE,
            [
                "usr_id" => $assignment->getUserId(),
                "prg_id" => $pgs_node_id
            ]
        );
    }
}
