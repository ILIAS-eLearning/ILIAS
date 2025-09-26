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

    /** @var array<int, string[]> Inverted index for lookup of aggregated contexts */
    protected array $inverted_index = [];

    public function __construct(
    ) {
        $settings = new \ilSetting('news');

        $this->cache_ttl = (int) $settings->get('acc_cache_mins');
        $this->enabled = $this->cache_ttl !== 0;

        $this->il_cache = new \ilCache('ServicesNews', 'NewsMultiLevel', true);
        $this->il_cache->setExpiresAfter($this->cache_ttl * 60);

        $this->loadIndex();
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
            return [
                'hit' => [],
                'missing' => $contexts,
            ];
        }

        // Check for exact matches
        if ($hits = $this->getAggregatedContextsStrict($contexts)) {
            return [
                'hit' => $hits,
                'missing' => [],
            ];
        }

        $hits = [];
        $uncovered = [];
        foreach ($contexts as $context) {
            $uncovered[$context->getRefId()] = $context;
        }

        // Use greedy algorithm to solve set-cover-problem and find stored subsets
        while (!empty($uncovered)) {
            $best_candidate_key = '';
            $best_candidate_items = [];

            // Use inverted index to find potential candidates
            foreach ($this->findPotentialCandidates(array_keys($uncovered)) as $candidate_key) {
                // Check if the candidate is a subset of the remaining uncovered ids
                $candidate_items = explode(',', $candidate_key);
                $is_subset = true;
                foreach ($candidate_items as $k) {
                    if (!isset($uncovered[$k])) {
                        $is_subset = false;
                        break;
                    }
                }

                // The best candidate is the one that covers the most items
                if ($is_subset && count($candidate_items) > count($best_candidate_items)) {
                    $best_candidate_key = $candidate_key;
                    $best_candidate_items = $candidate_items;
                }
            }

            // If a candidate was found, fetch the stored elements
            if (
                $best_candidate_key !== '' &&
                $entry = $this->il_cache->getEntry($this->generateL1Key($best_candidate_key))
            ) {
                array_push($hits, ...unserialize($entry));

                // Remove the covered items from the map
                foreach ($best_candidate_items as $k) {
                    unset($uncovered[$k]);
                }
            } else {
                // Break if no more hits can be found
                break;
            }
        }

        return [
            'hit' => $hits,
            'missing' => array_values($uncovered),
        ];
    }

    /**
     * @param int[] $elements
     * @return string[]
     */
    protected function findPotentialCandidates(array $elements): array
    {
        $keys = [];
        foreach ($elements as $element) {
            if (isset($this->inverted_index[$element])) {
                array_push($keys, ...$this->inverted_index[$element]);
            }
        }
        return array_unique($keys);
    }

    protected function generateL1Key(string|array $contexts): string
    {
        return 'agg:' . md5(is_array($contexts) ? join(',', $contexts) : $contexts);
    }

    /**
     * Level-1 Cache stores a collection of the aggregated contexts for the provided base context.
     * It returns a list of the NewsContexts (complete) or null on cache miss.
     *
     * @param NewsContext[] $contexts
     * @return NewsContext[]|null
     */
    public function getAggregatedContextsStrict(array $contexts): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        if (empty($contexts)) {
            return [];
        }

        $context_ids = array_map(fn($context) => $context->getRefId(), $contexts);
        sort($context_ids, SORT_NUMERIC);

        if ($entry = $this->il_cache->getEntry($this->generateL1Key($context_ids))) {
            return unserialize($entry);
        }
        return null;
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

        $key = join(',', $context_ids);
        $this->il_cache->storeEntry($this->generateL1Key($key), serialize($aggregated));

        foreach ($context_ids as $context_id) {
            if (!isset($this->inverted_index[$context_id])) {
                $this->inverted_index[$context_id] = [];
            }
            $this->inverted_index[$context_id][] = $key;
        }

        $this->saveIndex();
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
        $key = join(',', $context_ids);

        // Delete cache entry
        $this->il_cache->deleteEntry($this->generateL1Key($key));

        // Delete reference from inverted index
        foreach ($context_ids as $context_id) {
            if (isset($this->inverted_index[$context_id])) {
                $this->inverted_index[$context_id] = array_diff($this->inverted_index[$context_id], [$key]);
            }
        }

        $this->saveIndex();
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

        return new LazyNewsCollection(unserialize($entry));
    }

    public function storeNewsForUser(int $user_id, NewsCriteria $criteria, NewsCollection $news): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->il_cache->storeEntry(
            $this->generateL3Key($user_id, $criteria),
            serialize($news->pluck('id'))
        );
    }

    public function invalidateNewsForUser(int $user_id, NewsCriteria $criteria): void
    {
        $this->il_cache->deleteEntry($this->generateL3Key($user_id, $criteria));
    }

    protected function generateL3Key(int $user_id, NewsCriteria $criteria): string
    {
        $payload = [
            'start_date' => $criteria->getStartDate(),
            'min_priority' => $criteria->getMinPriority(),
            'max_priority' => $criteria->getMaxPriority(),
            'no_auto_generated' => $criteria->isNoAutoGenerated(),
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
        $this->inverted_index = [];
        $this->saveIndex();
    }


    protected function loadIndex(): void
    {
        if (apcu_enabled() && apcu_exists('news:cache:idx')) {
            $this->inverted_index = apcu_fetch('news:cache:idx');
        } elseif ($entry = $this->il_cache->getEntry('idx')) {
            $this->inverted_index = unserialize($entry);
        } else {
            $this->inverted_index = [];
        }
    }

    protected function saveIndex(): void
    {
        $this->il_cache->storeEntry('idx', serialize($this->inverted_index));
        if (apcu_enabled()) {
            apcu_store('news:cache:idx', $this->inverted_index);
        }
    }
}
