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

interface StudyProgrammeEvents
{
    public const COMPONENT = "Modules/StudyProgramme";
    public const EVENT_USER_ASSIGNED = 'userAssigned';
    public const EVENT_USER_REASSIGNED = 'userReAssigned';
    public const EVENT_USER_DEASSIGNED = 'userDeassigned';
    public const EVENT_USER_SUCCESSFUL = 'userSuccessful';
    public const EVENT_USER_TO_RESTART = 'informUserToRestart';
    public const EVENT_USER_ABOUT_TO_FAIL = 'userRiskyToFail';
    public const EVENT_VALIDITY_CHANGE = 'vqChange';
    public const EVENT_DEADLINE_CHANGE = 'deadlineChange';
    public const EVENT_SCORE_CHANGE = 'currentPointsChange';
    public const EVENT_USER_NOT_SUCCESSFUL = 'userNotSuccessful';


    public function userAssigned(ilPRGAssignment $assignment): void;
    public function userReAssigned(ilPRGAssignment $assignment): void;
    public function userDeassigned(ilPRGAssignment $a_assignment): void;

    public function userSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void;
    public function userRevertSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void;
    public function validityChange(ilPRGAssignment $assignment, int $pgs_node_id): void;
    public function deadlineChange(ilPRGAssignment $assignment, int $pgs_node_id): void;
    public function scoreChange(ilPRGAssignment $assignment, int $pgs_node_id): void;

    public function userRiskyToFail(ilPRGAssignment $assignment): void;
    public function informUserByMailToRestart(ilPRGAssignment $assignment): void;
}
