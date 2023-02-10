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
        ilLogger $logger,
        ilAppEventHandler $app_event_handler,
        PRGEventHandler $prg_event_handler
    ) {
        $this->logger = $logger;
        $this->app_event_handler = $app_event_handler;
        $this->prg_event_handler = $prg_event_handler;
    }

    public function raise(string $event, array $parameter): void
    {
        $this->logger->debug("PRG raised: " . $event . ' (' . print_r($parameter, true) . ')');

        if (in_array($event, [
            self::EVENT_USER_ASSIGNED,
            self::EVENT_USER_DEASSIGNED,
            self::EVENT_USER_SUCCESSFUL,
            self::EVENT_USER_NOT_SUCCESSFUL,
            self::EVENT_VALIDITY_CHANGE
        ])) {
            $this->prg_event_handler->updateLPStatus($parameter['prg_id'], $parameter['usr_id']);
        }

        if (in_array($event, [
                self::EVENT_USER_SUCCESSFUL,
                self::EVENT_USER_NOT_SUCCESSFUL,
                self::EVENT_VALIDITY_CHANGE,
                self::EVENT_SCORE_CHANGE
            ])
            && $parameter["root_prg_id"] === $parameter["prg_id"]
        ) {
            $cert = fn () => $this->app_event_handler->raise(self::COMPONENT, self::EVENT_USER_SUCCESSFUL, $parameter);
            $this->prg_event_handler->triggerCertificateOnce($cert, $parameter["root_prg_id"], $parameter["usr_id"]);
        }

        if ($event === self::EVENT_USER_ABOUT_TO_FAIL) {
            $this->prg_event_handler->sendRiskyToFailMail($parameter['ass_id'], $parameter['root_prg_id']);
        }
        if ($event === self::EVENT_USER_TO_RESTART) {
            $this->prg_event_handler->sendInformToReAssignMail($parameter['ass_id'], $parameter['root_prg_id']);
        }
        if ($event === self::EVENT_USER_REASSIGNED) {
            $this->prg_event_handler->sendReAssignedMail($parameter['ass_id'], $parameter['root_prg_id']);
        }

        if (in_array($event, [
            self::EVENT_USER_ASSIGNED,
            self::EVENT_USER_DEASSIGNED
        ])) {
            $this->app_event_handler->raise(self::COMPONENT, $event, $parameter);
        }

        if ($event === self::EVENT_VALIDITY_CHANGE) {
            $this->prg_event_handler->resetMailFlagValidity($parameter['ass_id'], $parameter['root_prg_id']);
        }
        if ($event === self::EVENT_DEADLINE_CHANGE) {
            $this->prg_event_handler->resetMailFlagDeadline($parameter['ass_id'], $parameter['root_prg_id']);
        }
    }

    public function userAssigned(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_ASSIGNED,
            [
                "root_prg_id" => $assignment->getRootId(),
                "prg_id" => $assignment->getRootId(),
                "usr_id" => $assignment->getUserId(),
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
                "prg_id" => $a_assignment->getRootId(),
                "usr_id" => $a_assignment->getUserId(),
                "ass_id" => $a_assignment->getId()
            ]
        );
    }

    public function userReAssigned(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_REASSIGNED,
            [
                "ass_id" => $assignment->getId(),
                "root_prg_id" => (int) $assignment->getRootId()
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
                "ass_id" => (int) $assignment->getId(),
                "root_prg_id" => (int) $assignment->getRootId()
            ]
        );
    }

    public function userRiskyToFail(ilPRGAssignment $assignment): void
    {
        $this->raise(
            self::EVENT_USER_ABOUT_TO_FAIL,
            [
                "ass_id" => (int) $assignment->getId(),
                "root_prg_id" => (int) $assignment->getRootId()
            ]
        );
    }

    public function validityChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(self::EVENT_VALIDITY_CHANGE, [
            "ass_id" => $assignment->getId(),
            "root_prg_id" => $assignment->getRootId(),
            "prg_id" => $pgs_node_id,
            "usr_id" => $assignment->getUserId()
        ]);
    }

    public function deadlineChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(self::EVENT_DEADLINE_CHANGE, [
            "ass_id" => $assignment->getId(),
            "root_prg_id" => $assignment->getRootId(),
            "prg_id" => $pgs_node_id,
            "usr_id" => $assignment->getUserId()
        ]);
    }

    public function scoreChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(self::EVENT_SCORE_CHANGE, [
            "ass_id" => $assignment->getId(),
            "root_prg_id" => $assignment->getRootId(),
            "prg_id" => $pgs_node_id,
            "usr_id" => $assignment->getUserId()
        ]);
    }

    public function userRevertSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(self::EVENT_USER_NOT_SUCCESSFUL, [
            "ass_id" => $assignment->getId(),
            "root_prg_id" => $assignment->getRootId(),
            "prg_id" => $pgs_node_id,
            "usr_id" => $assignment->getUserId()
        ]);
    }
}
