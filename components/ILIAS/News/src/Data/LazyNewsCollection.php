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

namespace ILIAS\News\Data;

/**
 * This class is a special implementation of a NewsCollection that is designed to load the complete NewsItems only
 * when needed. This lazy loading has the advantage that less main memory is required, since only the IDs of the
 * NewsItems are initially stored in the collection. It also prevents large database queries from being made when
 * only some of the data records are needed.
 *
 * This class should be used with caution and should not be used across the board. If used incorrectly, lazy loading
 * can lead to a deterioration in performance due to the N+1 problem.
 */
class LazyNewsCollection extends NewsCollection
{
    private bool $group_files = false;
    private int $group_forums = 0;

    public function __construct(
        array $news_ids = [],
        protected ?\Closure $fetch_callback = null
    ) {
        parent::__construct();

        foreach ($news_ids as $news_id) {
            $this->news_items[$news_id] = null;
        }
    }

    /**
     * @param null|\Closure(int[], string[]): NewsItem[] $callback
     */
    public function withFetchCallback(?\Closure $callback): static
    {
        $clone = clone $this;
        $clone->fetch_callback = $callback;
        return $clone;
    }

    public function addNewsItems(array $news_items): static
    {
        foreach ($news_items as $id => $item) {
            if ($item !== null) {
                $this->addNewsItem($item);
            } else {
                $this->news_items[$id] = null;
            }

        }
        return $this;
    }

    /**
     * This method loads the provided NewsItems from the database. If an empty array is provided, it will load
     * all missing NewsItems. It will also load the dependencies of the NewsItems if grouping is enabled.
     *
     * @param int[] $news_ids
     */
    public function load(array $news_ids = []): static
    {
        if ($this->fetch_callback === null) {
            throw new \RuntimeException('No fetch callback provided');
        }

        if (empty($news_ids)) {
            $news_ids = array_keys(array_filter($this->news_items, fn($item) => $item === null));
        } else {
            $news_ids = array_intersect($news_ids, array_keys($this->news_items));
        }

        if (empty($news_ids)) {
            return $this;
        }

        // Check if grouping was requested
        $context_types = [];
        if ($this->group_files === true) {
            $context_types[] = 'file';
        }
        if ($this->group_forums > 0) {
            $context_types[] = 'frm';
        }

        // Load items from a repository
        $items = call_user_func($this->fetch_callback, $news_ids, $context_types);
        $this->addNewsItems($items);

        // Perform re-grouping if necessary
        $this->regroup($items);

        return $this;
    }

    /*
        Grouping
     */

    public function groupFiles(): static
    {
        $this->group_files = true;
        return $this;
    }

    public function groupForums(bool $group_posting_sequence): static
    {
        $this->group_forums = $group_posting_sequence ? 2 : 1;
        return $this;
    }

    private function regroup(array $items): void
    {
        // Check if re-grouping is required
        $needs_file_grouping = false;
        $needs_forum_grouping = false;
        foreach ($items as $item) {
            if ($this->group_files === true && $item->getContextObjType() === 'file') {
                $needs_file_grouping = true;
            }
            if ($this->group_forums > 0 && $item->getContextObjType() === 'frm') {
                $needs_forum_grouping = true;
            }
        }

        // Perform re-grouping
        if ($needs_file_grouping) {
            parent::groupFiles();
        }
        if ($needs_forum_grouping) {
            parent::groupForums($this->group_forums === 2);
        }
    }

    /*
        Access & Lazy Loading
     */

    /**
     * This method returns `true` if the NewsItem exists in this collection. It does not provide any
     * information about whether the NewsItem has been loaded or not.
     */
    public function contains(int $news_id): bool
    {
        return array_key_exists($news_id, $this->news_items);
    }

    /**
     * This method returns `true` if the NewsItem exists in this collection and has been loaded.
     */
    public function has(int $news_id): bool
    {
        return isset($this->news_items[$news_id]);
    }

    /**
     * This method returns the NewsItem with the given id or null if it does not exist. If the
     * NewsItem was not loaded, it will be loaded from the database.
     *
     * WARNING: this may cause N+1 queries when using this method in a loop. Use `load` instead,
     * to batch load NewsItems.
     */
    public function getById(int $news_id): ?NewsItem
    {
        if (!$this->has($news_id)) {
            $this->load([$news_id]);
        }

        return $this->news_items[$news_id] ?? null;
    }

    /**
     * This method returns the NewsItem of the given offset of the collection. If the NewsItem
     * was not loaded, it will be loaded from the database.
     *
     * WARNING: this may cause N+1 queries when using this method in a loop. Use `load` instead,
     * to batch load NewsItems.
     */
    public function pick(int $offset): ?NewsItem
    {
        $index = max(0, $offset);
        $news_id = array_keys($this->news_items)[$index] ?? null;

        return $news_id !== null ? $this->getById($news_id) : null;
    }

    /**
     * INFO: This method will load all NewsItems into the collection.
     */
    public function pluck(string $key, bool $wrap = false): array
    {
        if ($key === 'id') {
            return $wrap ? array_map(fn($item) => [$item], array_keys($this->news_items)) : array_keys($this->news_items);
        }

        $this->load();
        return parent::pluck($key, $wrap);
    }

    /**
     * INFO: This method will load all NewsItems into the collection.
     */
    public function toArray(): array
    {
        $this->load();
        return parent::toArray();
    }

    /**
     * INFO: This method will load all NewsItems into the collection.
     */
    public function getAggregatedNews(
        bool $aggregate_files = false,
        bool $aggregate_forums = false,
        bool $group_posting_sequence = false
    ): array {
        $this->load();
        return parent::getAggregatedNews($aggregate_files, $aggregate_forums, $group_posting_sequence);
    }

    public function merge(NewsCollection $other): static
    {
        return parent::merge($other)->withFetchCallback($this->fetch_callback);
    }

    public function exclude(array $news_ids): static
    {
        return parent::exclude($news_ids)->withFetchCallback($this->fetch_callback);
    }

    public function limit(?int $limit): static
    {
        return parent::limit($limit)->withFetchCallback($this->fetch_callback);
    }

    public function orderByDate(): static
    {
        return parent::orderByDate()->withFetchCallback($this->fetch_callback);
    }
}
