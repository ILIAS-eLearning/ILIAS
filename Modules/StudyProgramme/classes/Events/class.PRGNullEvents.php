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


class PRGNullEvents implements StudyProgrammeEvents
{
    public function userAssigned(ilPRGAssignment $assignment): void
    {
    }
    public function userReAssigned(ilPRGAssignment $assignment): void
    {
    }
    public function userDeassigned(ilPRGAssignment $a_assignment): void
    {
    }
    public function userSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
    }
    public function userRevertSuccessful(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
    }
    public function validityChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
    }
    public function deadlineChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
    }
    public function scoreChange(ilPRGAssignment $assignment, int $pgs_node_id): void
    {
    }
    public function informUserByMailToRestart(ilPRGAssignment $assignment): void
    {
    }
    public function userRiskyToFail(ilPRGAssignment $assignment): void
    {
    }
}
