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

use DateTimeImmutable;
use ilDBConstants;
use ILIAS\News\Data\Factory;
use ILIAS\News\Data\LazyNewsCollection;
use ILIAS\News\Data\NewsCollection;
use ILIAS\News\Data\NewsContext;
use ILIAS\News\Data\NewsCriteria;
use ILIAS\News\Data\NewsItem;

/**
 * News Repository provides basic CRUD operations and optimized database access for news operations
 * with batch loading and optimized queries
 */
class NewsRepository
{
    public function __construct(
        protected readonly \ilDBInterface $db,
        protected readonly Factory $factory
    ) {
    }

    public function findById(int $news_id): ?NewsItem
    {
        $query = "SELECT * FROM il_news_item WHERE id = %s";
        $result = $this->db->queryF($query, [\ilDBConstants::T_INTEGER], [$news_id]);

        return $result->numRows()
            ? $this->factory->newsItem($this->db->fetchAssoc($result))
            : null;
    }

    /**
     * @param int[] $news_ids
     * @return NewsItem[]
     */
    public function findByIds(array $news_ids): array
    {
        if (empty($news_ids)) {
            return [];
        }

        $result = $this->db->query($this->buildFindQuery($news_ids));
        return array_map(fn($row) => $this->factory->newsItem($row), $this->db->fetchAll($result));
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    public function filterContext(array $contexts, NewsCriteria $criteria): array
    {
        $obj_ids = array_map(fn($context) => $context->getObjId(), $contexts);

        $values = [];
        $types = [];
        $query = "SELECT DISTINCT (context_obj_id) AS obj_id FROM il_news_item WHERE ";
        $query .= $this->db->in('context_obj_id', $obj_ids, false, \ilDBConstants::T_INTEGER);

        if ($criteria->getPeriod() > 0) {
            $query .= " AND creation_date >= %s";
            $values[] = self::parseTimePeriod($criteria->getPeriod());
            $types[] = ilDBConstants::T_TIMESTAMP;
        }

        if ($criteria->getStartDates() !== []) {
            $query .= " AND id NOT IN ({$this->buildExcludeByDateQuery($criteria->getStartDates())})";
        }

        $result = $this->db->queryF($query, $types, $values);
        $needed_obj_ids = array_column($this->db->fetchAll($result), 'obj_id', 'obj_id');

        return array_filter($contexts, fn($context) => isset($needed_obj_ids[$context->getObjId()]));
    }


    /**
     * @param int[] $news_ids
     * @param string[] $group_context_types
     * @return NewsItem[]
     */
    public function loadLazyItems(array $news_ids, array $group_context_types): array
    {
        if (empty($news_ids)) {
            return [];
        }

        $result = $this->db->query($this->buildFindQuery($news_ids));
        $news_items = [];
        $additional_obj_ids = [];

        foreach ($this->db->fetchAll($result) as $row) {
            $news_item = $this->factory->newsItem($row);

            if (in_array($news_item->getContextObjType(), $group_context_types)) {
                $additional_obj_ids[] = $news_item->getContextObjId();
            }

            $news_items[] = $news_item;
        }

        if (empty($additional_obj_ids)) {
            return $news_items;
        }

        // Fetch all additional items with same context_obj_id for grouping
        $query = $this->buildFindQuery()
            . " WHERE " . $this->db->in('context_obj_id', $additional_obj_ids, false, \ilDBConstants::T_INTEGER)
            . " AND " . $this->db->in('id', $news_ids, true, \ilDBConstants::T_INTEGER);
        $result = $this->db->query($query);

        return array_merge(
            $news_items,
            array_map(fn($row) => $this->factory->newsItem($row), $this->db->fetchAll($result))
        );
    }

    private function buildFindQuery(?array $news_ids = null): string
    {
        $query = "
            SELECT il_news_item.*, 
               COALESCE(
                    (SELECT ref_id FROM object_reference WHERE object_reference.obj_id = il_news_item.context_obj_id LIMIT 1), 
                    0
                ) AS ref_id 
            FROM il_news_item ";

        if ($news_ids !== null) {
            $query .= "WHERE " . $this->db->in('id', $news_ids, false, \ilDBConstants::T_INTEGER);
        }

        return $query;
    }

    /**
     * @param NewsContext[] $contexts
     */
    public function findByContextsBatch(array $contexts, NewsCriteria $criteria): NewsCollection
    {
        if (empty($contexts)) {
            return new NewsCollection();
        }

        $obj_ids = array_map(fn($context) => $context->getObjId(), $contexts);
        $result = $this->db->queryF(...$this->buildBatchQuery($obj_ids, $criteria));

        $items = [];
        $user_read = [];

        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = $this->factory->newsItem($row);
            $user_read[$row['id']] = isset($row['user_read']) && $row['user_read'] !== 0;
        }

        $collection = new NewsCollection($items);
        if ($criteria->isIncludeReadStatus()) {
            $collection->setUserReadStatus($criteria->getReadUserId(), $user_read);
        }

        return $collection;
    }

    /**
     * @param NewsContext[] $contexts
     */
    public function findByContextsBatchLazy(array $contexts, NewsCriteria $criteria): NewsCollection
    {
        if (empty($contexts)) {
            return new NewsCollection();
        }

        $obj_ids = array_map(fn($context) => $context->getObjId(), $contexts);
        $result = $this->db->queryF(...$this->buildBatchQuery($obj_ids, $criteria, true));

        $items = [];
        $user_read = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = $row['id'];
            $user_read[$row['id']] = isset($row['user_read']) && $row['user_read'] !== 0;
        }

        $collection = new LazyNewsCollection($items, fn(...$args) => $this->loadLazyItems(...$args));
        if ($criteria->isIncludeReadStatus()) {
            $collection->setUserReadStatus($criteria->getReadUserId(), $user_read);
        }

        return $collection;
    }

    /**
     * @param NewsContext[] $contexts
     * @return array{0: NewsContext, 1: int}[]
     */
    public function countByContextsBatch(array $contexts): array
    {
        $context_map = [];
        foreach ($contexts as $context) {
            $context_map[$context->getObjId()] = $context;
        }

        $in_clause = $this->db->in('context_obj_id', array_keys($context_map), false, ilDBConstants::T_INTEGER);
        $query = "SELECT context_obj_id, count(context_obj_id) as count FROM il_news_item WHERE {$in_clause} GROUP BY context_obj_id";
        $result = $this->db->query($query);

        $count = [];
        foreach ($this->db->fetchAll($result) as $row) {
            $count[] = [
                $context_map[$row['context_obj_id']],
                $row['count']
            ];
        }

        return $count;
    }

    private function buildBatchQuery(array $obj_ids, NewsCriteria $criteria, bool $only_id = false): array
    {
        $values = [];
        $types = [];
        $joins = '';

        if ($only_id) {
            $columns = ['il_news_item.id'];
        } else {
            $columns = [
                'il_news_item.*',
                'COALESCE((SELECT ref_id FROM object_reference WHERE object_reference.obj_id = il_news_item.context_obj_id LIMIT 1), 0) AS ref_id'
            ];
        }

        if ($criteria->isIncludeReadStatus()) {
            if ($criteria->getReadUserId() === null) {
                throw new \InvalidArgumentException("Read user id is required for read status");
            }

            $columns[] = 'il_news_read.user_id AS user_read';
            $joins .= 'LEFT JOIN il_news_read ON il_news_item.id = il_news_read.news_id AND il_news_read.user_id = %s ';

            $values[] = $criteria->getReadUserId();
            $types[] = ilDBConstants::T_INTEGER;
        }

        $query = "SELECT " . join(', ', $columns) . " FROM il_news_item {$joins} WHERE "
            . $this->db->in('context_obj_id', $obj_ids, false, ilDBConstants::T_INTEGER);

        if ($criteria->getPeriod() > 0) {
            $query .= " AND creation_date >= %s";
            $values[] = self::parseTimePeriod($criteria->getPeriod());
            $types[] = ilDBConstants::T_TIMESTAMP;
        }

        if ($criteria->isNoAutoGenerated()) {
            $query .= " AND priority = 1 AND content_type = 'text'";
        }

        if ($criteria->getMinPriority() !== null || $criteria->getMaxPriority() !== null) {
            $operator = $criteria->getMinPriority() !== null ? '>=' : '<=';
            $query .= " AND n.priority {$operator} %s";
            $values[] = $criteria->getMinPriority();
            $types[] = ilDBConstants::T_INTEGER;
        }

        if ($criteria->isOnlyPublic()) {
            $query .= " AND visibility = '" . NEWS_PUBLIC . "'";
        }

        if ($criteria->getStartDates() !== []) {
            $query .= " AND id NOT IN ({$this->buildExcludeByDateQuery($criteria->getStartDates())})";
        }

        $query .= " ORDER BY creation_date DESC";

        return [$query, $types, $values];
    }

    /**
     * @param array<int, DateTimeImmutable> $start_dates
     */
    private function buildExcludeByDateQuery(array $start_dates): string
    {
        $conditions = [];
        foreach ($start_dates as $obj_id => $date) {
            $conditions[] = " (context_obj_id = {$obj_id} AND creation_date < '{$date->format('Y-m-d H:i:s')}') ";
        }

        return "SELECT id FROM il_news_item WHERE" . implode('OR', $conditions);
    }

    private static function parseTimePeriod(string|int $time_period): string
    {
        // time period is a number of days
        if (is_numeric($time_period) && $time_period > 0) {
            return date('Y-m-d H:i:s', time() - ($time_period * 24 * 60 * 60));
        }

        // time period is datetime (string)
        if (preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $time_period)) {
            return $time_period;
        }

        return '';
    }
}
