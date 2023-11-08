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

namespace ILIAS\Exercise;

use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\DI\Container;
use ILIAS\Exercise\Object\ObjectManager;
use ILIAS\Exercise\Notification\NotificationManager;
use ILIAS\Refinery\Logical\Not;
use ILIAS\Exercise\InstructionFile\InstructionFileManager;
use ILIAS\Exercise\Team\TeamManager;

class InternalDomainService
{
    use GlobalDICDomainServices;

    protected InternalDataService $data;
    protected InternalRepoService $repo;
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

    public function peerReview(\ilExAssignment $ass): ?\ilExPeerReview
    {
        if ($ass->getPeerReview()) {
            return new \ilExPeerReview($ass);
        }
        return null;
    }

    public function notification(int $ref_id): NotificationManager
    {
        return new NotificationManager(
            $this,
            $ref_id
        );
    }

    public function team(): TeamManager
    {
        return new TeamManager(
            $this->repo,
            $this
        );
    }

}
