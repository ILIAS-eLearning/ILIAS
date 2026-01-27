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

use ILIAS\News\Aggregation\NewsAggregator;
use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Persistence\NewsCache;
use ILIAS\News\Persistence\NewsRepository;

/**
 * News Collection Service orchestrates all news-related operations and provides a
 * high-level API for the news service.
 */
class NewsCollectionService
{
    public function __construct(
        private readonly NewsRepository $repository,
        private readonly NewsCache $cache,
        private readonly UserContextResolver $user_context_resolver,
        private readonly \ilObjectDataCache $object_data,
        private readonly \ilRbacSystem $rbac
    ) {
    }

    public function getNewsForUser(\ilObjUser $user, NewsCriteria $criteria, bool $lazy = false): NewsCollection
    {
        // 1. Try user cache first
        $cached_news = $this->cache->getNewsForUser($user->getId(), $criteria);
        if ($cached_news !== null) {
            // Transform the lazy collection to a normal collection if needed
            if (!$lazy) {
                $news_collection = new NewsCollection($this->repository->findByIds($cached_news->pluck('id')));
            } else {
                $news_collection = $cached_news->withFetchCallback(
                    fn(...$args) => $this->repository->loadLazyItems(...$args)
                );
            }

            // Apply request-specific filtering [DPL 5]
            return $this->applyFinalProcessing($news_collection, $criteria);
        }

        // 2. Add missing criteria and validate it
        if ($criteria->isIncludeReadStatus() && $criteria->getReadUserId() === null) {
            $criteria = $criteria->withReadUserId($user->getId());
        }
        $criteria->validate();

        // 3. Get user accessible contexts [DPL 1]
        $user_contexts = $this->user_context_resolver->getAccessibleContexts($user, $criteria);
        if (empty($user_contexts)) {
            return new NewsCollection();
        }

        // 4. Query news for resolved contexts [DPL 2-4]
        $news_collection = $this->getNewsForContexts($user_contexts, $criteria, $user->getId(), $lazy);

        // 5. Store in cache
        $this->cache->storeNewsForUser($user->getId(), $criteria, $news_collection);

        // 6. Apply request-specific filtering [DPL 5]
        return $this->applyFinalProcessing($news_collection, $criteria);
    }

    public function getNewsForContext(
        NewsContext $context,
        NewsCriteria $criteria,
        int $user_id,
        bool $lazy = false
    ): NewsCollection {
        return $this->applyFinalProcessing($this->getNewsForContexts([$context], $criteria, $user_id, $lazy), $criteria);
    }

    public function invalidateCache(int $user_id): void
    {
        $this->cache->invalidateNewsForUser($user_id, new NewsCriteria());
    }

    /**
     * @param NewsContext[] $contexts
     */
    private function getNewsForContexts(array $contexts, NewsCriteria $criteria, int $user_id, bool $lazy): NewsCollection
    {
        // 1. Try context cache first (L1)
        $cached = $this->cache->getAggregatedContexts($contexts);
        $hits = $cached['hit'];

        if (!empty($cached['missing'])) {
            // 2. Batch load missing context object information [DPL 2]
            $remaining = $this->fetchContextData($cached['missing']);

            // 3. Perform aggregation [DPL 3]
            if (!$criteria->isPreventNesting()) {
                $aggregated = (new NewsAggregator())->aggregate($remaining);
                $this->cache->storeAggregatedContexts($remaining, $aggregated);
                $hits = array_merge($hits, $aggregated);
            } else {
                $hits = array_merge($hits, $remaining);
            }
        }

        // 4. Add start dates to criteria
        $criteria = $this->appendStartDateFilter($hits, $criteria);

        // 5. Perform access checks [DPL 3]
        $aggregated = $this->filterByAccess($hits, $criteria, $user_id);

        // 6. Batch load news from the database [DPL 4]
        return $lazy
            ? $this->repository->findByContextsBatchLazy($aggregated, $criteria)
            : $this->repository->findByContextsBatch($aggregated, $criteria);
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    private function fetchContextData(array $contexts): array
    {
        // Batch loads object_data and object_references using preloading
        $obj_ids = array_filter(array_map(fn($context) => $context->getObjId(), $contexts));
        $this->object_data->preloadObjectCache($obj_ids);

        for ($i = 0; $i < count($contexts); $i++) {
            $context = $contexts[$i];

            if ($context->getObjId() === null) {
                $context->setObjId($this->object_data->lookupObjId($context->getRefId()));
            }

            if ($context->getObjType() === null) {
                $context->setObjType($this->object_data->lookupType($context->getObjId()));
            }

            $contexts[$i] = $context;
        }

        return $contexts;
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    private function filterByAccess(array $contexts, NewsCriteria $criteria, int $user_id): array
    {
        if ($criteria->isOnlyPublic()) {
            return $contexts;
        }

        // Remove contexts without news items or outside the criteria
        $contexts = $this->repository->filterContext($contexts, $criteria);

        // Preload rbac cache
        $this->rbac->preloadRbacPaCache(array_map(fn($context) => $context->getRefId(), $contexts), $user_id);

        // Order contexts by level to keep tree hierarchy
        usort($contexts, fn($a, $b) => $a->getLevel() <=> $b->getLevel());
        $filtered = [];
        $ac_result = [];

        foreach ($contexts as $context) {
            // Filter object and skip access check if the parent object was denied
            if (isset($ac_result[$context->getParentRefId()]) && !$ac_result[$context->getParentRefId()]) {
                continue;
            }

            $ac_result[$context->getRefId()] = $this->rbac->checkAccess(
                'read',
                $context->getRefId(),
                $context->getObjType(),
            );

            if ($ac_result[$context->getRefId()]) {
                $filtered[] = $context;
            }
        }
        return $filtered;
    }

    /**
     * @param NewsContext[] $contexts
     */
    private function appendStartDateFilter(array $contexts, NewsCriteria $criteria): NewsCriteria
    {
        $date_filter = [];

        foreach ($contexts as $context) {
            if (
                !in_array($context->getObjType(), ['grp', 'crs']) ||
                !\ilBlockSetting::_lookup('news', 'hide_news_per_date', 0, $context->getObjId())
            ) {
                continue;
            }

            $hide_date = \ilBlockSetting::_lookup('news', 'hide_news_date', 0, $context->getObjId());
            if (empty($hide_date)) {
                continue;
            }

            $date_filter[$context->getObjId()] = new \DateTimeImmutable($hide_date);
        }

        return $criteria->withStartDates($date_filter);
    }

    /**
     * Apply the last steps of the news collection processing pipeline: Exclude, Limit
     */
    private function applyFinalProcessing(NewsCollection $collection, NewsCriteria $criteria): NewsCollection
    {
        return $collection->exclude($criteria->getExcludedNewsIds())->limit($criteria->getLimit());
    }
}
