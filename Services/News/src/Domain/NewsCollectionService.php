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
        private readonly \ilAccessHandler $access,
    ) {
    }

    public function getNewsForUser(\ilObjUser $user, NewsCriteria $criteria): NewsCollection
    {
        // 1. Try user cache first
        $cached_news = $this->cache->getNewsForUser($user->getId(), $criteria);
        if ($cached_news !== null) {
            // Apply request-specific filtering [DPL 5]
            return $this->applyFinalProcessing($cached_news, $criteria);
        }

        // 2. Validate criteria
        $criteria->validate();

        // 3. Get user accessible contexts [DPL 1]
        $user_contexts = $this->user_context_resolver->getAccessibleContexts($user, $criteria);
        if (empty($user_contexts)) {
            return new NewsCollection();
        }

        // 4. Query news for resolved contexts [DPL 2-4]
        $news_collection = $this->getNewsForContexts($user_contexts, $criteria);

        // 5. Store in cache
        $this->cache->storeNewsForUser($user->getId(), $user_contexts, $criteria, $news_collection);

        // 6. Apply request-specific filtering [DPL 5]
        return $this->applyFinalProcessing($news_collection, $criteria);
    }

    public function getNewsForContext(NewsContext $context, NewsCriteria $criteria): NewsCollection
    {
        return $this->applyFinalProcessing($this->getNewsForContexts([$context], $criteria), $criteria);
    }

    /**
     * @param NewsContext[] $contexts
     */
    private function getNewsForContexts(array $contexts, NewsCriteria $criteria): NewsCollection
    {
        // 1. Try context cache first (L1)
        $aggregated = $this->cache->getAggregatedContexts($contexts, $criteria);
        if ($aggregated === null) {
            // 2. Batch load missing context object information [DPL 2]
            $contexts = $this->fetchContextData($contexts);

            // 3. Perform aggregation [DPL 3]
            if (!$criteria->isPreventAggregation()) {
                $aggregated = (new NewsAggregator())->aggregate($contexts);
            } else {
                $aggregated = $contexts;
            }
        }

        // 4. Perform access checks [DPL 3]
        $aggregated = $this->filterByAccess($aggregated, $criteria);

        // 5. Batch load news from the database [DPL 4]
        return $this->repository->findByContextsBatch($aggregated, $criteria);
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    private function fetchContextData(array $contexts): array
    {
        // Batch load object_data and object_references using preloading
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
    private function filterByAccess(array $contexts, NewsCriteria $criteria): array
    {
        if ($criteria->isOnlyPublic()) {
            return $contexts;
        }

        $filtered = [];
        foreach ($contexts as $context) {
            if ($this->access->checkAccess('read', '', $context->getRefId())) {
                $filtered[] = $context;
            }
        }
        return $filtered;
    }

    private function applyFinalProcessing(NewsCollection $collection, NewsCriteria $criteria): NewsCollection
    {
        // Sort by date (default)
        $collection = $collection->sortByDate();

        // Apply limit
        if ($criteria->getLimit()) {
            $collection = $collection->limit($criteria->getLimit());
        }

        return $collection;
    }
}
