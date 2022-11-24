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

class ilStudyProgrammeEvents
{
    private const COMPONENT = "Modules/StudyProgramme";

    protected ilAppEventHandler $app_event_handler;

    public function __construct(
        ilAppEventHandler $app_event_handler
    ) {
        $this->app_event_handler = $app_event_handler;
    }

    public function raise(string $event, array $parameter): void
    {
        $this->app_event_handler->raise(self::COMPONENT, $event, $parameter);
    }

    public function userAssigned(ilPRGAssignment $assignment): void
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

    public function userReAssigned(ilPRGAssignment $assignment): void
    {
        $this->raise(
            "userReAssigned",
            [
                "root_prg_ref_id" => $this->getRefIdFor($assignment->getRootId()),
                "usr_id" => $assignment->getUserId()
            ]
        );
    }
    protected function getRefIdFor(int $obj_id): int
    {
        return ilObjStudyProgramme::getRefIdFor($obj_id);
    }


    public function userDeassigned(ilPRGAssignment $a_assignment): void
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
    public function userSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
        $this->raise(
            "userSuccessful",
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
            'informUserToRestart',
            [
                "usr_id" => (int) $assignment->getUserId(),
                "progress_id" => (int) $assignment->getRootId(),
                "ass_id" => (int) $assignment->getId()
            ]
        );
    }

    public function userRiskyToFail(ilPRGAssignment $assignment): void
    {
        $this->raise(
            "userRiskyToFail",
            [
                "progress_id" => $assignment->getRootId(),
                "usr_id" => $assignment->getUserId()
            ]
        );
    }
}
