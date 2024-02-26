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
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
abstract class AbstractResourceStakeholder implements ResourceStakeholder
{
    private string $provider_name_cache = '';

    public function getFullyQualifiedClassName(): string
    {
        return static::class;
    }

    public function isResourceInUse(ResourceIdentification $identification): bool
    {
        return false;
    }

    public function canBeAccessedByCurrentUser(ResourceIdentification $identification): bool
    {
        return true;
    }

    public function resourceHasBeenDeleted(ResourceIdentification $identification): bool
    {
        return true;
    }

    public function getOwnerOfResource(ResourceIdentification $identification): int
    {
        return 6;
    }

    public function getConsumerNameForPresentation(): string
    {
        if ($this->provider_name_cache !== '' && is_string($this->provider_name_cache)) {
            return $this->provider_name_cache;
        }
        $reflector = new \ReflectionClass($this);

        $parts = explode(DIRECTORY_SEPARATOR, str_replace(ILIAS_ABSOLUTE_PATH, '', dirname($reflector->getFileName())));
        $parts = array_filter($parts, static function ($part) {
            return $part !== '' && $part !== 'classes';
        });

        return $this->provider_name_cache = implode('/', $parts);
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        return null;
    }
}
