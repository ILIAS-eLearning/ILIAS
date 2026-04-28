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

use ILIAS\HTTP\Services;
use ILIAS\DI\Container;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Builder\URIBuilder;

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

    public function lng(): \ilLanguage
    {
        return $this->container->language();
    }

    public function mainTemplate(): \ilGlobalTemplateInterface
    {
        return $this->container->ui()->mainTemplate();
    }

    public function http(): Services
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

    public function findFirstAccessibleParentRefId(int $ref_id, string $permission = 'read'): ?int
    {
        $tree = $this->container->repositoryTree();
        if ($ref_id <= 0 || !$tree->isInTree($ref_id)) {
            return null;
        }

        $root_id = $tree->getRootId();
        $current = $ref_id;
        $visited = [];
        while (($parent = (int) $tree->getParentId($current)) > 0) {
            if (isset($visited[$parent])) {
                return null;
            }
            $visited[$parent] = true;
            if ($this->checkPermission($permission, $parent)) {
                return $parent;
            }
            if ($parent === $root_id) {
                return null;
            }
            $current = $parent;
        }

        return null;
    }

    public function getUserId(): int
    {
        return $this->container->user()->getId();
    }

    public function isUserLoggedIn(): bool
    {
        return !$this->container->user()->isAnonymous() && $this->container->user()->getId() !== 0;
    }

    public function isPublicSectionActive(): bool
    {
        return (bool) ($this->container->settings()->get('pub_section') ?? false);
    }

    public function builder(): URIBuilder
    {
        return $this->container['static_url']->builder();
    }
}
