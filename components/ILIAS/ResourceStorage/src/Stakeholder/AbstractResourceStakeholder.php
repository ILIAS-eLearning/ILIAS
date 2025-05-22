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
use ILIAS\DI\Container;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
abstract class AbstractResourceStakeholder implements ResourceStakeholder
{
    private string $provider_name_cache = '';

    protected int $default_owner;
    protected int $current_user;

    public function __construct(?int $user_id_of_owner = null)
    {
        global $DIC;

        if ($user_id_of_owner === null) {
            $user_id_of_owner = $DIC instanceof Container && $DIC->isDependencyAvailable('user')
                ? $DIC->user()->getId()
                : (defined('SYSTEM_USER_ID') ? (int) SYSTEM_USER_ID : 6);
        }

        $this->default_owner = $this->current_user = $user_id_of_owner;
    }

    public function setOwner(int $user_id_of_owner): void
    {
        $this->default_owner = $user_id_of_owner;
    }

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
        if ($this->provider_name_cache !== "" && is_string($this->provider_name_cache)) {
            return $this->provider_name_cache;
        }

        $reflector = new \ReflectionClass($this);

        $dirname = dirname($reflector->getFileName());
        $after_components = substr($dirname, strpos($dirname, '/components/') + strlen('/components/'));
        $parts = explode(
            DIRECTORY_SEPARATOR,
            $after_components
        );

        $parts = array_filter($parts, static function ($part): bool {
            $ignore = ['IRSS', 'Storage', 'ResourceStorage', 'classes'];
            return $part !== '' && !in_array($part, $ignore, true);
        });

        return $this->provider_name_cache = implode('/', $parts);
    }

    public function getLocationURIForResourceUsage(ResourceIdentification $identification): ?string
    {
        return null;
    }
}
