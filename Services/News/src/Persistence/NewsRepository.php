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

        $result = $this->db->query(
            "SELECT il_news_item.*, object_reference.ref_id FROM il_news_item 
                    RIGHT JOIN object_reference ON il_news_item.context_obj_id = object_reference.obj_id WHERE "
                    . $this->db->in('id', $news_ids, false, \ilDBConstants::T_INTEGER)
        );

        return array_map(fn($row) => $this->factory->newsItem($row), $this->db->fetchAll($result));
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

        $in_ids = $this->db->in('id', $news_ids, false, \ilDBConstants::T_INTEGER);
        $in_types = $this->db->in('context_obj_type', $group_context_types, false, \ilDBConstants::T_TEXT);

        $result = $this->db->query(
            "SELECT DISTINCT il_news_item.*, object_reference.ref_id FROM il_news_item 
                    RIGHT JOIN object_reference ON il_news_item.context_obj_id = object_reference.obj_id 
                    WHERE {$in_ids} OR context_obj_id IN 
                        (SELECT il_news_item.context_obj_id FROM il_news_item WHERE {$in_ids} AND {$in_types})"
        );

        return array_map(fn($row) => $this->factory->newsItem($row), $this->db->fetchAll($result));
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
    public function findByContextsBatchLazy(array $contexts, NewsCriteria $criteria): LazyNewsCollection
    {
        if (empty($contexts)) {
            return new LazyNewsCollection();
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

        if ($only_id) {
            $columns = ['il_news_item.id'];
            $joins = '';
        } else {
            $columns = ['il_news_item.*', 'object_reference.ref_id'];
            $joins = 'RIGHT JOIN object_reference ON il_news_item.context_obj_id = object_reference.obj_id ';
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

        if ($criteria->getStartDate()) {
            $query .= " AND creation_date >= %s";
            $values[] = $criteria->getStartDate()->format('Y-m-d H:i:s');
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

        $query .= " ORDER BY creation_date DESC";

        return [$query, $types, $values];
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
