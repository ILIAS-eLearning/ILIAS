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

namespace ILIAS\Help\GuidedTour\UserFinished;

use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\InternalDomainService;
use ILIAS\Help\GuidedTour\InternalRepoService;

class UserFinishedManager
{
    protected UserFinishedDBRepository $repo;

    public function __construct(
        protected InternalDataService $data,
        InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
        $this->repo = $repo->userFinished();
    }

    public function setFinished(int $tour_id, int $user_id): void
    {
        $this->repo->setFinished($tour_id, $user_id);
    }

    public function hasFinished(int $tour_id, int $user_id): bool
    {
        return $this->repo->hasFinished($tour_id, $user_id);
    }

    public function resetTour(int $tour_id): void
    {
        $this->repo->resetTour($tour_id);
    }

    public function deleteByUser(int $user_id): void
    {
        $this->repo->deleteByUser($user_id);
    }
}
