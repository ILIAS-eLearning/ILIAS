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

namespace ILIAS\ResourceStorage\Stakeholder;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ResourceStakeholder
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
interface ResourceStakeholder
{
    /**
     * Constructor must not have any parameters. The Stakeholder must be able to be instantiated without any parameters.
     * Additionally, Stakeholder may get instantiated without dependencies available. Therefore, the Stakeholder must
     * must check for dependencies and implement fallbacks.
     */
    public function __construct();

    /**
     * @return string not longer than 64 characters
     */
    public function getId(): string;

    public function getConsumerNameForPresentation(): string;

    /**
     * @return string not longer than 250 characters
     */
    public function getFullyQualifiedClassName(): string;

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool;

    public function isResourceInUse(ResourceIdentification $identification): bool;

    /**
     * @return bool true: if the Stakeholder could handle the deletion; false: if the Stakeholder could not handle
     * the deletion of the resource.
     */
    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool;

    public function getOwnerOfResource(ResourceIdentification $identification): int;

    public function getOwnerOfNewResources(): int;

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string;
}
