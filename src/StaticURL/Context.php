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

namespace ILIAS\StaticURL;

use ILIAS\DI\Container;
use ILIAS\Refinery\Factory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Context
{
    public function __construct(private Container $container)
    {
    }

    public function getUserLanguage(): string
    {
        return $this->container->user()->getCurrentLanguage();
    }

    public function refinery(): Factory
    {
        return $this->container->refinery();
    }

    public function http(): \ILIAS\HTTP\Services
    {
        return $this->container->http();
    }

    public function ctrl(): \ilCtrlInterface
    {
        return $this->container->ctrl();
    }

    public function checkPermission(string $permission, int $ref_id): bool
    {
        return $this->container->access()->checkAccess($permission, '', $ref_id);
    }

    public function getParentRefId(int $ref_id): ?int
    {
        return $this->container->repositoryTree()->getParentId($ref_id);
    }

    public function exists(int $ref_id): bool
    {
        return $this->container->repositoryTree()->isInTree($ref_id);
    }

    public function getUserId(): int
    {
        return $this->container->user()->getId();
    }

    public function isUserLoggedIn(): bool
    {
        return !$this->container->user()->isAnonymous() && $this->container->user()->getId() !== 0;
    }
}
