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

namespace ILIAS\News\Data;

use ArrayIterator;

/**
 * Optimized News Collection with memory-efficient data structures to support large news feeds. It's designed for
 * context-based filtering and fast_lookups.
 */
final class NewsCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /** @var array<int, NewsItem> */
    private array $news_items = [];

    /** @var array<string, int[]> */
    private array $context_map = [];

    /** @var array<string, int[]> */
    private array $type_map = [];
    private array $user_read_status = [];

    public function __construct(array $news_items = [])
    {
        $this->addNewsItems($news_items);
    }

    /**
     * Add multiple news items efficiently
     */
    public function addNewsItems(array $news_items): self
    {
        foreach ($news_items as $item) {
            $this->addNewsItem($item);
        }
        return $this;
    }

    /**
     * Add a single news item with indexing
     */
    public function addNewsItem(NewsItem $item): self
    {
        $id = $item->getId();
        $this->news_items[$id] = $item;

        // Build context index for fast context-based lookups
        $context_key = $item->getContextObjId() . '_' . $item->getContextObjType();
        $this->context_map[$context_key][] = $id;

        // Build type index for fast type-based filtering
        $this->type_map[$item->getContextObjType()][] = $id;

        return $this;
    }

    public function getNewsItems(): array
    {
        return $this->news_items;
    }

    public function getNewsForContext(int $context_obj_id, string $context_obj_type): array
    {
        $context_key = $context_obj_id . '_' . $context_obj_type;

        if (!isset($this->context_map[$context_key])) {
            return [];
        }

        return array_map(
            fn($id) => $this->news_items[$id],
            $this->context_map[$context_key]
        );
    }

    public function getNewsByType(string $obj_type): array
    {
        if (!isset($this->type_map[$obj_type])) {
            return [];
        }

        return array_map(
            fn($id) => $this->news_items[$id],
            $this->type_map[$obj_type]
        );
    }

    /**
     * Sort news items by creation date and returns it as a new collection
     */
    public function sortByDate(bool $ascending = false): self
    {
        $sorted = new self();
        $items = $this->news_items;

        usort($items, function (NewsItem $a, NewsItem $b) use ($ascending) {
            $comparison = $a->getCreationDate() <=> $b->getCreationDate();
            return $ascending ? $comparison : -$comparison;
        });

        $sorted->addNewsItems($items);
        return $sorted;
    }

    public function setUserReadStatus(int $user_id, array $read_news_ids): self
    {
        $this->user_read_status[$user_id] = array_flip($read_news_ids);
        return $this;
    }

    public function isReadByUser(int $user_id, int $news_id): bool
    {
        return isset($this->user_read_status[$user_id][$news_id]);
    }

    public function getUnreadCountForUser(int $user_id): int
    {
        if (!isset($this->user_read_status[$user_id])) {
            return count($this->news_items);
        }

        $read_ids = array_keys($this->user_read_status[$user_id]);
        return count($this->news_items) - count(array_intersect_key($this->news_items, array_flip($read_ids)));
    }

    /**
     * Get aggregated news (forums, files, etc.) as a flat array which can be used for rendering
     */
    public function getAggregatedNews(): array
    {
        $aggregated = [];

        foreach ($this->news_items as $item) {
            $context_key = $item->getContextObjId() . '_' . $item->getContextObjType();

            if (!isset($aggregated[$context_key])) {
                $aggregated[$context_key] = [
                    'context_obj_id' => $item->getContextObjId(),
                    'context_obj_type' => $item->getContextObjType(),
                    'items' => [],
                    'count' => 0,
                    'latest_date' => $item->getCreationDate()
                ];
            }

            $aggregated[$context_key]['items'][] = $item;
            $aggregated[$context_key]['count']++;

            if ($item->getCreationDate() > $aggregated[$context_key]['latest_date']) {
                $aggregated[$context_key]['latest_date'] = $item->getCreationDate();
            }
        }

        return $aggregated;
    }

    /*
        Interface Methods & Additional Accessors
     */

    public function jsonSerialize(): array
    {
        return array_values($this->news_items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->news_items);
    }

    public function count(): int
    {
        return count($this->news_items);
    }

    public function isEmpty(): bool
    {
        return empty($this->news_items);
    }

    public function first(): ?NewsItem
    {
        return reset($this->news_items) ?: null;
    }

    public function last(): ?NewsItem
    {
        return end($this->news_items) ?: null;
    }

    public function contains(NewsItem $item): bool
    {
        return isset($this->news_items[$item->getId()]);
    }

    public function containsId(int $news_id): bool
    {
        return isset($this->news_items[$news_id]);
    }

    public function getById(int $news_id): ?NewsItem
    {
        return $this->news_items[$news_id] ?? null;
    }

    /**
     * @return NewsItem[]
     */
    public function toArray(): array
    {
        return $this->news_items;
    }

    /**
     * Merge with another collection and returns it as a new collection
     */
    public function merge(NewsCollection $other): self
    {
        $merged = new self();
        $merged->addNewsItems($this->news_items);
        $merged->addNewsItems($other->getNewsItems());

        // Merge user read status
        foreach ($other->user_read_status as $user_id => $read_ids) {
            if (isset($this->user_read_status[$user_id])) {
                $merged->user_read_status[$user_id] = array_merge(
                    $this->user_read_status[$user_id],
                    $read_ids
                );
            } else {
                $merged->user_read_status[$user_id] = $read_ids;
            }
        }

        return $merged;
    }

    /**
     * Limit the number of news items and returns it as a new collection
     */
    public function limit(int $limit): self
    {
        if ($limit >= count($this->news_items)) {
            return $this;
        }

        $limited = new self();
        $items = array_slice($this->news_items, 0, $limit, true);
        $limited->addNewsItems($items);

        return $limited;
    }

    /**
     * Paginate the news items and returns it as a new collection
     */
    public function paginate(int $page, int $page_size): self
    {
        $offset = ($page - 1) * $page_size;
        $items = array_slice($this->news_items, $offset, $page_size, true);

        $paginated = new self();
        $paginated->addNewsItems($items);

        return $paginated;
    }
}
