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

namespace ILIAS\Style\Content\Container;

use ILIAS\Style\Content\InternalRepoService;

/**
 * Manages container related content style behaviour
 * @author Alexander Killing <killing@leifos.de>
 */
class ContainerManager
{
    protected ContainerDBRepository $container_repo;
    protected int $ref_id;
    protected InternalRepoService $repo_service;

    public function __construct(
        InternalRepoService $repo_service,
        int $ref_id
    ) {
        $this->ref_id = $ref_id;
        $this->repo_service = $repo_service;
        $this->container_repo = $repo_service->repositoryContainer();
    }

    public function saveReuse(bool $reuse): void
    {
        $this->container_repo->updateReuse($this->ref_id, $reuse);
    }

    public function getReuse(): bool
    {
        return $this->container_repo->readReuse($this->ref_id);
    }
}
