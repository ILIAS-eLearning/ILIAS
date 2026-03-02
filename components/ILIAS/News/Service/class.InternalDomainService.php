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

namespace ILIAS\News;

use ILIAS\DI\Container;
use ILIAS\News\Domain\NewsCollectionService;
use ILIAS\News\Domain\UserContextResolver;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\News\Dashboard\DashboardNewsManager;
use ILIAS\News\Timeline\TimelineManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    public function __construct(
        Container $DIC,
        protected InternalRepoService $repo_service,
        protected InternalDataService $data_service
    ) {
        $this->initDomainServices($DIC);
    }

    public function resolver(): UserContextResolver
    {
        return new UserContextResolver(
            new \ilFavouritesDBRepository($this->DIC->database(), $this->repositoryTree()),
            $this->access(),
            $this->repositoryTree(),
            $this->repo_service->cache()
        );
    }

    public function collection(): NewsCollectionService
    {
        return new NewsCollectionService(
            $this->repo_service->news(),
            $this->repo_service->cache(),
            $this->resolver(),
            $this->objectDataCache(),
            $this->DIC->rbac()->system()
        );
    }

    public function dashboard(): DashboardNewsManager
    {
        return new DashboardNewsManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }

    public function timeline(): TimelineManager
    {
        return new TimelineManager(
            $this->data_service,
            $this->repo_service,
            $this
        );
    }
}
