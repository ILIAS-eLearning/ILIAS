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

use ArrayIterator;

/**
 * Optimized News Collection with memory-efficient data structures to support large news feeds. It's designed for
 * context-based filtering and fast_lookups.
 */
class NewsCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /** @var array<int, NewsItem> */
    protected array $news_items = [];

    /** @var array<string, int[]> */
    protected array $context_map = [];

    /** @var array<string, int[]> */
    protected array $type_map = [];

    /** @var array<int, int[]> */
    protected array $user_read_status = [];
    /** @var array<int, null|array{first: int, aggregation: int[]}> */
    protected array $grouped_items_map = [];

    public function __construct(array $news_items = [])
    {
        $this->addNewsItems($news_items);
    }

    /**
     * Add multiple news items efficiently
     */
    public function addNewsItems(array $news_items): static
    {
        foreach ($news_items as $item) {
            $this->addNewsItem($item);
        }
        return $this;
    }

    /**
     * Add a single news item with indexing
     */
    public function addNewsItem(NewsItem $item): static
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
     * @param array<int, bool> $read_news_ids
     */
    public function setUserReadStatus(int $user_id, array $read_news_ids): static
    {
        $this->user_read_status[$user_id] = array_filter($read_news_ids);
        return $this;
    }

    public function isReadByUser(int $user_id, int $news_id): bool
    {
        return isset($this->user_read_status[$user_id][$news_id]);
    }

    /**
     * @return array<int, bool>
     */
    public function getUserReadStatus(int $user_id): array
    {
        $result = [];
        foreach (array_keys($this->news_items) as $news_id) {
            $result[$news_id] = $this->isReadByUser($user_id, $news_id);
        }
        return $result;
    }

    /*
        Grouping
     */

    public function groupFiles(): static
    {
        foreach (array_filter($this->news_items) as $item) {
            if ($item->getContextObjType() === 'file') {
                if (isset($this->grouped_items_map[$item->getContextObjId()])) {
                    $this->grouped_items_map[$item->getContextObjId()]['aggregation'][] = $item->getId();
                } else {
                    $this->grouped_items_map[$item->getContextObjId()] = [
                        'first' => $item->getId(),
                        'aggregation' => []
                    ];
                }
            }
        }
        return $this;
    }

    public function groupForums(bool $group_posting_sequence): static
    {
        $last_forum = 0;

        foreach (array_filter($this->news_items) as $item) {
            // If we are grouping by sequence, we need to reset the entry in the aggregation map when switching
            if ($group_posting_sequence && $last_forum !== $item->getContextObjType() && $last_forum !== 0) {
                $this->grouped_items_map[$last_forum] = null;
            }

            if ($item->getContextObjType() === 'frm') {
                if (isset($this->grouped_items_map[$item->getContextObjId()])) {
                    $this->grouped_items_map[$item->getContextObjId()]['aggregation'][] = $item->getId();
                } else {
                    $this->grouped_items_map[$item->getContextObjId()] = [
                        'first' => $item->getId(),
                        'aggregation' => []
                    ];
                    $last_forum = $item->getContextObjId();
                }
            }
        }

        return $this;
    }

    /**
     * Returns the grouping for a given news item. It will return an array with the grouped items
     * if the provided item is the first in the group.
     *
     * @return array{parent: NewsItem, aggregation: NewsItem[], agg_ref_id: int, no_context_title: bool}|null
     */
    public function getGroupingFor(NewsItem $item): ?array
    {
        if (!isset($this->grouped_items_map[$item->getContextObjId()])) {
            return null;
        }

        $aggregation = $this->grouped_items_map[$item->getContextObjId()];
        if ($aggregation['first'] !== $item->getId()) {
            return null;
        }

        if ($item->getContextObjType() === 'frm') {
            $item = $item->withContent('')->withContentLong('');
        }

        return [
            'parent' => $item,
            'aggregation' => [$item, ...array_map(fn($id) => $this->news_items[$id], $aggregation['aggregation'])],
            'agg_ref_id' => $item->getContextRefId(),
            'no_context_title' => false
        ];
    }

    /*
        Legacy Adapter
     */

    /**
     * Get news items in a format compatible with the legacy rendering implementation.
     * This should never be introduced in new code and will be removed in the future.
     *
     * @deprecated
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAggregatedNews(
        bool $aggregate_files = false,
        bool $aggregate_forums = false,
        bool $group_posting_sequence = false
    ): array {
        $items = [];
        $file_aggregation_map = [];
        $forum_aggregation_map = [];
        $last_forum = 0;

        foreach ($this->news_items as $item) {
            $entry = [
                'id' => $item->getId(),
                'priority' => $item->getPriority(),
                'title' => $item->getTitle(),
                'content' => $item->getContent(),
                'context_obj_id' => $item->getContextObjId(),
                'context_obj_type' => $item->getContextObjType(),
                'context_sub_obj_id' => $item->getContextSubObjId(),
                'context_sub_obj_type' => $item->getContextSubObjType(),
                'content_type' => $item->getContentType(),
                'creation_date' => $item->getCreationDate()->format('Y-m-d H:i:s'),
                'user_id' => $item->getUserId(),
                'visibility' => $item->getVisibility(),
                'content_long' => $item->getContentLong(),
                'content_is_lang_var' => $item->isContentIsLangVar(),
                'mob_id' => $item->getMobId(),
                'playtime' => $item->getPlaytime(),
                'start_date' => null, //it seems like this is not used anymore
                'end_date' => null, //it seems like this is not used anymore
                'content_text_is_lang_var' => $item->isContentTextIsLangVar(),
                'mob_cnt_download' => $item->getMobCntDownload(),
                'mob_cnt_play' => $item->getMobCntPlay(),
                'content_html' => $item->isContentHtml(),
                'update_user_id' => $item->getUpdateUserId(),
                'user_read' => (int) $this->isReadByUser($item->getUserId(), $item->getId()),
                'ref_id' => $item->getContextRefId()
            ];

            if ($aggregate_files && $item->getContextObjType() === 'file') {
                if (isset($file_aggregation_map[$item->getContextObjId()])) {
                    // If this file already has an aggregation entry, add it there and prevent adding it to the main list
                    $idx = $file_aggregation_map[$item->getContextObjId()];
                    $items[$idx]['aggregation'][$item->getId()] = $entry;
                    continue;
                }

                // If this is the first news for this file, set the aggregation array
                $entry['aggregation'] = [];
                $entry['agg_ref_id'] = $item->getContextRefId();
                $file_aggregation_map[$item->getContextObjId()] = $item->getId();

            }

            if ($aggregate_forums) {
                // If we are grouping by sequence, we need to reset the entry in the aggregation map when switching
                if ($group_posting_sequence && $last_forum !== 0 && $last_forum !== $item->getContextObjType()) {
                    $forum_aggregation_map[$last_forum] = null;
                }

                if ($item->getContextObjType() === 'frm') {
                    $entry['no_context_title'] = true;

                    if (isset($forum_aggregation_map[$item->getContextObjId()])) {
                        // If this form already has an aggregation entry, add it there and prevent adding it to the main list
                        $idx = $forum_aggregation_map[$item->getContextObjId()];
                        $items[$idx]['aggregation'][$item->getId()] = $entry;
                        continue;
                    }

                    // If this is the first news for this forum, set the aggregation array
                    $entry['agg_ref_id'] = $item->getContextRefId();
                    $entry['content'] = '';
                    $entry['content_long'] = '';

                    $forum_aggregation_map[$item->getContextObjId()] = $item->getId();
                    $last_forum = $item->getContextObjType();

                }
            }

            $items[$item->getId()] = $entry;
        }
        return $items;
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

    public function contains(int $news_id): bool
    {
        return isset($this->news_items[$news_id]);
    }

    public function getById(int $news_id): ?NewsItem
    {
        return $this->news_items[$news_id] ?? null;
    }

    public function getPageFor(int $news_id): int
    {
        $pages = array_keys($this->news_items);
        return (int) array_search($news_id, $pages);
    }

    public function pick(int $offset): ?NewsItem
    {
        $index = max(0, $offset);
        return array_values($this->news_items)[$index] ?? null;
    }

    public function pluck(string $key, bool $wrap = false): array
    {
        $arr = array_column($this->toArray(), $key);
        return $wrap ? array_map(fn($item) => [$item], $arr) : $arr;
    }

    /**
     * @return array<int, array>
     */
    public function toArray(): array
    {
        return array_map(fn($item) => $item->toArray(), $this->news_items);
    }


    /**
     * Merge with another collection and returns it as a new collection
     */
    public function merge(NewsCollection $other): static
    {
        $merged = new static();
        $merged->addNewsItems($this->news_items);
        $merged->addNewsItems($other->getNewsItems());

        // Merge user read status
        foreach ($other->user_read_status as $user_id => $read_ids) {
            $merged->user_read_status[$user_id] = isset($this->user_read_status[$user_id])
                ? array_merge($this->user_read_status[$user_id], $read_ids)
                : $read_ids;
        }

        return $merged;
    }

    /**
     * Limit the number of news items and returns it as a new collection
     */
    public function limit(?int $limit): static
    {
        if ($limit === null || $limit >= count($this->news_items)) {
            return $this;
        }

        $limited = new static();
        $items = array_slice($this->news_items, 0, $limit, true);
        $limited->addNewsItems($items);

        return $limited;
    }

    /**
     * Returns a new collection with only the news items that are not in the provided list
     *
     * @param int[] $news_ids
     */
    public function exclude(array $news_ids): static
    {
        if (empty($news_ids)) {
            return $this;
        }

        $filtered = new static();
        $filtered->addNewsItems(array_filter(
            $this->news_items,
            fn($item) => !in_array($item->getId(), $news_ids)
        ));
        return $filtered;
    }

    /**
     * Returns a new collection with news items ordered by creation date
     */
    public function orderByDate(): static
    {
        $ordered = new static();
        $ordered->addNewsItems($this->news_items);

        uasort(
            $ordered->news_items,
            fn(NewsItem $a, NewsItem $b): int => $a->getCreationDate() <=> $b->getCreationDate()
        );

        return $ordered;
    }

    public function load(array $news_ids = []): static
    {
        return $this;
    }
}
