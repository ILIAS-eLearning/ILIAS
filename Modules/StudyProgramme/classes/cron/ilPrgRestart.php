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


class ilPrgRestart implements ilPrgCronJobAdapter
{
    protected ilStudyProgrammeSettingsDBRepository $settings_repo;
    protected ilStudyProgrammeEvents $events;

    public function __construct(
        ilStudyProgrammeSettingsDBRepository $settings_repo,
        ilStudyProgrammeEvents $events
    ) {
        $this->settings_repo = $settings_repo;
        $this->events = $events;
    }

    public function getRelevantProgrammeIds(): array
    {
        return $this->settings_repo
            ->getProgrammeIdsWithReassignmentForExpiringValidity();
    }

    public function actOnSingleAssignment(ilPRGAssignment $ass): void
    {
        $this->events->userReAssigned($ass);
    }
}
