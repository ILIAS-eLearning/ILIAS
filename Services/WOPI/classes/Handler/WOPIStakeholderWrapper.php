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

namespace ILIAS\Services\WOPI\Handler;

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class WOPIStakeholderWrapper extends AbstractResourceStakeholder
{
    private ?int $user_id = null;
    private ?ResourceStakeholder $stakeholder = null;

    public function __construct()
    {
    }

    public function init(ResourceStakeholder $stakeholder, int $user_id): void
    {
        $this->user_id = $user_id;
        $this->stakeholder = $stakeholder;
    }

    public function getId(): string
    {
        return $this->stakeholder->getId();
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->user_id ?? $this->stakeholder->getOwnerOfNewResources();
    }

    public function getFullyQualifiedClassName(): string
    {
        return $this->stakeholder->getFullyQualifiedClassName();
    }

    public function isResourceInUse(ResourceIdentification $identification): bool
    {
        return $this->stakeholder->isResourceInUse($identification);
    }

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool
    {
        return $this->stakeholder->canBeAccessedByCurrentUser($identification);
    }

    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        return $this->stakeholder->resourceHasBeenDeleted($identification);
    }

    public function getOwnerOfResource(ResourceIdentification $identification): int
    {
        return $this->stakeholder->getOwnerOfResource($identification);
    }

    public function getConsumerNameForPresentation(): string
    {
        return $this->stakeholder->getConsumerNameForPresentation();
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        return $this->stakeholder->getLocationURIForResourceUsage($identification);
    }

}
