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

namespace ILIAS\Exercise;

use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\DI\Container;
use ILIAS\Exercise\Object\ObjectManager;
use ILIAS\Exercise\Notification\NotificationManager;
use ILIAS\Exercise\Team\TeamManager;
use ILIAS\Exercise\IndividualDeadline\IndividualDeadlineManager;
use ILIAS\Exercise\Submission\SubmissionManager;
use ILIAS\Exercise\PeerReview\DomainService;
use ILIAS\Exercise\Settings\SettingsManager;

class InternalDomainService
{
    use GlobalDICDomainServices;

    protected InternalDataService $data;
    protected InternalRepoService $repo;
    protected array $instance = [];
    protected Assignment\DomainService $assignment_service;

    public function __construct(
        Container $dic,
        InternalDataService $data,
        InternalRepoService $repo
    ) {
        $this->data = $data;
        $this->repo = $repo;
        $this->initDomainServices($dic);
        $this->assignment_service = new Assignment\DomainService(
            $this,
            $repo
        );
    }

    public function log(): \ilLogger
    {
        return $this->logger()->exc();
    }

    public function object(int $ref_id): ObjectManager
    {
        return new ObjectManager($ref_id);
    }

    public function assignment(): Assignment\DomainService
    {
        return $this->assignment_service;
    }

    public function submission(int $ass_id): SubmissionManager
    {
        return $this->instance["subm"][$ass_id] ??= new SubmissionManager(
            $this->repo,
            $this,
            new \ilExcSubmissionStakeholder(),
            $ass_id
        );
    }

    public function peerReview(): DomainService
    {
        return $this->instance["peer_review"] ??= new DomainService(
            $this->repo,
            $this
        );
    }

    public function notification(int $ref_id): NotificationManager
    {
        return $this->instance["notification"][$ref_id] ??= new NotificationManager(
            $this,
            $ref_id
        );
    }

    public function team(): TeamManager
    {
        return $this->instance["team"] ??= new TeamManager(
            $this->repo,
            $this,
            new \ilExcTutorTeamFeedbackFileStakeholder()
        );
    }

    public function individualDeadline(): IndividualDeadlineManager
    {
        return $this->instance["idl"] ??= new IndividualDeadlineManager();
    }

    public function exercise(
        int $obj_id
    ): ExerciseManager {
        return $this->instance["exercise"][$obj_id] ??= new ExerciseManager(
            $this->repo,
            $this,
            $obj_id
        );
    }

    public function exerciseSettings(
    ): SettingsManager {
        return $this->instance["settings"] ??= new SettingsManager(
            $this->data,
            $this->repo,
            $this
        );
    }

}
