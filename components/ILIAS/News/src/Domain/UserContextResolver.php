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

namespace ILIAS\News\Domain;

use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Persistence\NewsCache;

/**
 * User Context Resolver resolves which contexts a user can access for news operations.
 * Handles user permissions, favorites, memberships, and access control.
 */
class UserContextResolver
{
    public function __construct(
        protected readonly \ilFavouritesDBRepository $favourites_repository,
        protected readonly \ilAccessHandler $access,
        protected readonly \ilTree $tree,
        protected readonly NewsCache $cache
    ) {
    }

    /**
     * @return NewsContext[]
     */
    public function getAccessibleContexts(\ilObjUser $user, NewsCriteria $criteria): array
    {
        // 1. Try cache layer
        $cached_contexts = $this->cache->getUserContextAccess($user->getId(), $criteria);
        if ($cached_contexts !== null) {
            return $cached_contexts;
        }

        // 2. Resolve contexts
        $contexts = $this->resolveUserContexts($user, $criteria);

        // 3. Cache the result
        $this->cache->storeUserContextAccess($user->getId(), $criteria, $contexts);

        return $contexts;
    }

    /**
     * @return NewsContext[]
     */
    private function resolveUserContexts(\ilObjUser $user, NewsCriteria $criteria): array
    {
        $contexts = [];

        // Get user's personal desktop items
        if ($this->shouldIncludePersonalDesktop($user)) {
            $contexts = array_merge($contexts, $this->getPersonalDesktopContexts($user));
        }

        // Get user's memberships
        $contexts = array_merge($contexts, $this->getMembershipContexts($user));

        // Remove duplicates and filter by access
        return $this->filterContexts($user, $contexts, $criteria);
    }

    private function shouldIncludePersonalDesktop(\ilObjUser $user): bool
    {
        return $user->getPref('pd_items_news') !== 'n';
    }

    /**
     * @return NewsContext[]
     */
    private function getPersonalDesktopContexts(\ilObjUser $user): array
    {
        $contexts = [];

        foreach ($this->favourites_repository->getFavouritesOfUser($user->getId()) as $item) {
            $contexts[] = new NewsContext($item['ref_id'], $item['obj_id'], $item['type']);
        }
        return $contexts;
    }

    /**
     * @return NewsContext[]
     */
    private function getMembershipContexts(\ilObjUser $user): array
    {
        $contexts = [];
        $memberships = \ilParticipants::_getMembershipByType($user->getId(), ['crs', 'grp']);

        foreach ($memberships as $obj_id) {
            $contexts = array_merge(
                $contexts,
                array_map(fn($ref_id) => new NewsContext($ref_id, $obj_id), \ilObject::_getAllReferences($obj_id)),
            );
        }
        return $contexts;
    }

    /**
     * Deduplicate and filter contexts by access
     *
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    private function filterContexts(\ilObjUser $user, array $contexts, NewsCriteria $criteria): array
    {
        $unique_contexts = [];

        foreach ($contexts as $context) {
            if (isset($unique_contexts[$context->getRefId()])) {
                continue;
            }

            if (
                !$criteria->isOnlyPublic() &&
                !$this->access->checkAccessOfUser($user->getId(), 'read', '', $context->getRefId())
            ) {
                continue;
            }

            $unique_contexts[$context->getRefId()] = $context;
        }

        return array_values($unique_contexts);
    }
}
