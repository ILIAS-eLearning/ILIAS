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

namespace ILIAS\Survey\Execution;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalRepoService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected InternalRepoService $repo_service;
    protected InternalDomainService $domain_service;

    protected static array $managers = [];

    public function __construct(
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
    }

    public function run(
        \ilObjSurvey $survey,
        int $user_id
    ): RunManager {
        if (!isset(self::$managers[RunManager::class][$survey->getId()][$user_id])) {
            self::$managers[RunManager::class][$survey->getId()][$user_id] =
                new RunManager(
                    $this->repo_service,
                    $this->domain_service,
                    $survey,
                    $user_id
                );
        }
        return self::$managers[RunManager::class][$survey->getId()][$user_id];
    }
}
