<?php

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

declare(strict_types=1);

namespace ILIAS\Exercise\Team;

use ILIAS\Exercise\InternalDataService;
use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\TutorFeedbackFile\TutorFeedbackFileTeamRepository;

class TeamManager
{
    protected TutorFeedbackFileTeamRepository $feedback_repo;
    protected TeamDBRepository $repo;
    protected InternalDomainService $domain;

    public function __construct(
        InternalRepoService $repo,
        InternalDomainService $domain,
        protected \ilExcTutorTeamFeedbackFileStakeholder $feedback_stakeholder
    ) {
        $this->repo = $repo->team();
        $this->domain = $domain;
        $this->feedback_repo = $repo->tutorFeedbackFileTeam();
    }

    public function create(
        int $ass_id,
        int $first_user
    ): int {
        $id = $this->repo->create();
        $this->repo->addUser($id, $ass_id, $first_user);
        $this->domain->assignment()->tutorFeedbackFile($ass_id)->createCollection($first_user);
        return $id;
    }

    public function getTeamForMember(int $ass_id, int $user_id): ?int
    {
        return $this->repo->getTeamForMember($ass_id, $user_id);
    }

    public function getStatusForTeam(int $team_id): string
    {
        $members = iterator_to_array($this->repo->getMembers($team_id));
        $ass_id = $this->getAssignmentForTeam($team_id);
        $mem_status = new \ilExAssignmentMemberStatus(
            $ass_id,
            current($members)->getUserId()
        );
        return $mem_status->getStatus();
    }

    public function getAssignmentForTeam(int $team_id): int
    {
        return $this->repo->getAssignmentForTeam($team_id);
    }

    public function deleteTeamsOfAssignment(int $ass_id): void
    {
        foreach ($this->repo->getTeamIdsOfAssignment($ass_id) as $team_id) {
            $this->repo->deleteTeamLog($team_id);
            $this->feedback_repo->deleteTeamCollection(
                $team_id,
                $this->feedback_stakeholder
            );
            $this->repo->deleteTeam($team_id);
        }
    }

}
