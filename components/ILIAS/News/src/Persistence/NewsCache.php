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

namespace ILIAS\News\Persistence;

use ILIAS\News\Data\LazyNewsCollection;
use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;

/**
 * Multi-Level News Cache Implementation:
 *
 * - Level 1: Context Cache - Context-specific data
 * - Level 2: User Context Cache - User-specific data
 * - Level 3: User News Cache - List of news items for a user
 */
class NewsCache
{
    protected readonly bool $enabled;
    /** @var int Number of minutes until an entry expires */
    protected readonly int $cache_ttl;
    protected readonly \ilCache $il_cache;

    public function __construct(
    ) {
        $settings = new \ilSetting('news');

        $this->cache_ttl = (int) $settings->get('acc_cache_mins');
        $this->enabled = $this->cache_ttl !== 0;

        $this->il_cache = new \ilCache('ServicesNews', 'NewsMultiLevel', true);
        $this->il_cache->setExpiresAfter($this->cache_ttl * 60);
    }

    /**
     * Level-1 Cache stores a collection of the aggregated contexts for the provided base context.
     * This method uses a greedy algorithm to collect subset matches in the cache and return both
     * cache hits (as complete NewsContexts objects) and missing contexts.
     *
     * @param NewsContext[] $contexts
     * @return array{hit: NewsContext[], missing: NewsContext[]}
     */
    public function getAggregatedContexts(array $contexts): array
    {
        if (!$this->enabled || empty($contexts)) {
            return ['hit' => [], 'missing' => $contexts];
        }

        $context_ids = array_map(fn($context) => $context->getRefId(), $contexts);
        sort($context_ids, SORT_NUMERIC);

        if ($entry = $this->il_cache->getEntry($this->generateL1Key($context_ids))) {
            $contexts = array_map(fn($raw) => NewsContext::denormalize($raw), unserialize($entry));
            return ['hit' => $contexts, 'missing' => []];
        }

        return ['hit' => [], 'missing' => $contexts];
    }

    protected function generateL1Key(string|array $contexts): string
    {
        return 'agg:' . md5(is_array($contexts) ? implode(',', $contexts) : $contexts);
    }

    /**
     * @param NewsContext[] $contexts
     * @param NewsContext[] $aggregated
     */
    public function storeAggregatedContexts(array $contexts, array $aggregated): void
    {
        if (!$this->enabled || empty($contexts)) {
            return;
        }

        $context_ids = array_map(fn($context) => $context->getRefId(), $contexts);
        sort($context_ids, SORT_NUMERIC);
        $key = implode(',', $context_ids);

        $payload = array_map(fn($context) => $context->normalize(), $aggregated);
        $this->il_cache->storeEntry($this->generateL1Key($key), serialize($payload));
    }

    /**
     * @param NewsContext[] $contexts
     */
    public function invalidateAggregatedContexts(array $contexts): void
    {
        if (!$this->enabled || empty($contexts)) {
            return;
        }

        $context_ids = array_map(fn($context) => $context->getRefId(), $contexts);
        sort($context_ids, SORT_NUMERIC);
        $key = implode(',', $context_ids);

        // Delete cache entry
        $this->il_cache->deleteEntry($this->generateL1Key($key));
    }


    /**
     * Level-2 Cache stores a collection of the base news contexts for a specific user. It returns a list of the
     * NewsContexts (ref_id only) or null on cache miss.
     *
     * @return NewsContext[]|null
     */
    public function getUserContextAccess(int $user_id, NewsCriteria $criteria): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $entry = $this->il_cache->getEntry("access:{$user_id}");
        if (!$entry) {
            return null;
        }

        // Check if the stored payload matches the criteria
        $payload = unserialize($entry);
        if ($payload['only_public'] !== $criteria->isOnlyPublic()) {
            $this->invalidateUserContextAccess($user_id);
            return null;
        }

        return array_map(fn($ref_id) => new NewsContext($ref_id), $payload['contexts']);
    }

    /**
     * @param NewsContext[] $contexts
     */
    public function storeUserContextAccess(int $user_id, NewsCriteria $criteria, array $contexts): void
    {
        if (!$this->enabled) {
            return;
        }

        $contexts = array_map(fn($context) => $context->getRefId(), $contexts);
        $payload = ['contexts' => $contexts, 'only_public' => $criteria->isOnlyPublic()];
        $this->il_cache->storeEntry("access:{$user_id}", serialize($payload));
    }

    public function invalidateUserContextAccess(int $user_id): void
    {
        if ($this->enabled) {
            $this->il_cache->deleteEntry("access:{$user_id}");
        }
    }


    /**
     * Level-3 Cache stores a collection of the news items for a specific user. It returns a
     * LazyNewsCollection or null on cache miss.
     */
    public function getNewsForUser(int $user_id, NewsCriteria $criteria): ?LazyNewsCollection
    {
        if (!$this->enabled) {
            return null;
        }

        $entry = $this->il_cache->getEntry($this->generateL3Key($user_id, $criteria));
        if (!$entry) {
            return null;
        }

        $payload = unserialize($entry);
        return (new LazyNewsCollection(array_keys($payload)))
            ->setUserReadStatus($user_id, $payload);
    }

    public function storeNewsForUser(int $user_id, NewsCriteria $criteria, NewsCollection $news): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->il_cache->storeEntry(
            $this->generateL3Key($user_id, $criteria),
            serialize($news->getUserReadStatus($user_id)),
            $user_id
        );
    }

    public function invalidateNewsForUser(int $user_id): void
    {
        $this->il_cache->deleteByAdditionalKeys($user_id);
    }

    protected function generateL3Key(int $user_id, NewsCriteria $criteria): string
    {
        $payload = [
            'min_priority' => $criteria->getMinPriority(),
            'max_priority' => $criteria->getMaxPriority(),
            'no_auto_generated' => $criteria->isNoAutoGenerated(),
            'only_public' => $criteria->isOnlyPublic(),
            'start_dates' => $criteria->getStartDates(),
        ];

        // The Period of entries only needs to be considered if cache entries are stored for longer periods
        $period_minutes = ($criteria->getPeriod() ?? 0) * 1440;
        if ($period_minutes <= $this->cache_ttl) {
            $payload['period'] = $criteria->getPeriod();
        }

        return "user:{$user_id}:" . md5(serialize($payload));
    }


    public function flush(): void
    {
        $this->il_cache->deleteAllEntries();
    }
}
