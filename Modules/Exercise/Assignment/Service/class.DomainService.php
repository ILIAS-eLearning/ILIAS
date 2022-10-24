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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalRepoService;
use ILIAS\Exercise\InternalDomainService;

/**
 * Assignments domain service
 * @author Alexander Killing <killing@leifos.de>
 */
class DomainService
{
    protected InternalDomainService $domain_service;
    protected InternalRepoService $repo_service;

    // for managers that need to be created
    // on the fly and should be cached
    protected static array $managers = [];

    public function __construct(
        InternalDomainService $domain_service,
        InternalRepoService $repo_service
    ) {
        $this->domain_service = $domain_service;
        $this->repo_service = $repo_service;
    }

    /**
     * Get random assignment manager.
     * The manager is used if the "Pass Mode" is set to "Random Selection" in the exercise settings.
     */
    public function randomAssignments(\ilObjExercise $exercise, \ilObjUser $user = null): Mandatory\RandomAssignmentsManager
    {
        if (!isset(self::$managers[Mandatory\RandomAssignmentsManager::class][$exercise->getId()])) {
            self::$managers[Mandatory\RandomAssignmentsManager::class][$exercise->getId()] =
                new Mandatory\RandomAssignmentsManager(
                    $exercise,
                    $this->repo_service->assignment()->randomAssignments(),
                    $this->repo_service->submission(),
                    $user
                );
        }
        return self::$managers[Mandatory\RandomAssignmentsManager::class][$exercise->getId()];
    }

    /**
     * Get mandatory assignment manager
     * @throws \ilExcUnknownAssignmentTypeException
     */
    public function mandatoryAssignments(\ilObjExercise $exercise): Mandatory\MandatoryAssignmentsManager
    {
        if (!isset(self::$managers[Mandatory\MandatoryAssignmentsManager::class][$exercise->getId()])) {
            self::$managers[Mandatory\MandatoryAssignmentsManager::class][$exercise->getId()] =
                new Mandatory\MandatoryAssignmentsManager($exercise, $this->randomAssignments($exercise));
        }
        return self::$managers[Mandatory\MandatoryAssignmentsManager::class][$exercise->getId()];
    }
}
