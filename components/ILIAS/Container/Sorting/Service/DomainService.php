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

namespace ILIAS\Container\Sorting\Service;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDataService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\Sorting\Settings\Manager as SettingsManager;
use ILIAS\Container\Sorting\Positions\Manager as PositionsManager;

class DomainService
{
    public function __construct(
        protected InternalRepoService $repo_service,
        protected InternalDataService $data_service,
        protected InternalDomainService $domain_service
    ) {
    }

    public function settings(): SettingsManager
    {
        return new SettingsManager($this->data_service, $this->repo_service, $this->domain_service);
    }

    public function positions(): PositionsManager
    {
        return new PositionsManager($this->data_service, $this->repo_service, $this->domain_service);
    }
}
