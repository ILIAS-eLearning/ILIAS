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

namespace ILIAS\LearningSequence\LearningMap;

use ILIAS\LearningSequence\Player\AdaptiveNavigator;
use ILIAS\LearningSequence\Player\LSNavigator;
use ILIAS\LearningSequence\Content\Adaptive\LSOItemPath;
use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;
use ilDBInterface;

/**
 * Tracks a user's position and visit history in a learning map.
 */
class LSOLearningMapPosition
{
    public const SIT_END = 'end';
    public const SIT_BLOCKED = 'blocked';
    public const SIT_DEADEND = 'deadend';
    public const SIT_BRANCH = 'branch';
    public const SIT_STRAIGHT = 'straight';
    public const VISITS_TABLE = 'lso_item_visits';
    /** @var array<int, array{ref_id: int, visited_ts: int}>|null Cached visit log. */
    protected ?array $raw_visit_log = null;
    /** @var array<int, array{count: int, last_ts: int}>|null Cached visit statistics. */
    protected ?array $visit_stats = null;
    /** @var array<int, int> Object IDs indexed by reference ID. */
    protected array $obj_id_by_ref_id = [];

    /**
     * Creates a learning map position.
     */
    public function __construct(
        /** @var LSNavigator The learning sequence navigator. */
        protected LSNavigator $navigator,
        /** @var LSOItemPath The user's item path. */
        protected LSOItemPath $item_path,
        /** @var LSOAdaptiveBoundaries The adaptive boundaries. */
        protected LSOAdaptiveBoundaries $boundaries,
        /** @var int The learning sequence object ID. */
        protected int $lso_obj_id,
        /** @var int The user ID. */
        protected int $usr_id,
        /** @var ilDBInterface|null The database connection. */
        protected ?ilDBInterface $db = null
    ) {
    }
    /**
     * Prepares the position for the given items.
     *
     * @param \LSLearnerItem[] $items
     */
    public function prepareForItems(array $items): void
    {
    }
    /**
     * Records a visit to an item.
     */
    public function recordVisit(int $ref_id): void
    {
        if ($this->db === null) {
            return;
        }
        $query = "SELECT MAX(position) AS max_position FROM " . self::VISITS_TABLE
            . " WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($this->lso_obj_id, 'integer');
        $row = $this->db->fetchAssoc($this->db->query($query));
        $next_position = (!$row || $row['max_position'] === null) ? 0 : ((int) $row['max_position']) + 1;

        $this->db->insert(self::VISITS_TABLE, [
            'usr_id' => ['integer', $this->usr_id],
            'lso_obj_id' => ['integer', $this->lso_obj_id],
            'position' => ['integer', $next_position],
            'ref_id' => ['integer', $ref_id],
            'visited_ts' => ['integer', time()]
        ]);

        $this->raw_visit_log = null;
        $this->visit_stats = null;
    }
    /**
     * Returns the object ID for a reference ID.
     */
    protected function lookupObjId(int $ref_id): int
    {
        return $this->obj_id_by_ref_id[$ref_id] ??= \ilObject::_lookupObjId($ref_id);
    }
    /**
     * Returns the configured start reference ID.
     */
    protected function getStartRefId(): int
    {
        $boundaries = $this->boundaries->getBoundariesFor($this->lso_obj_id);
        return $boundaries['start_ref_id'];
    }
    /**
     * Returns the configured end reference ID.
     */
    protected function getEndRefId(): int
    {
        $boundaries = $this->boundaries->getBoundariesFor($this->lso_obj_id);
        return $boundaries['end_ref_id'];
    }
    /**
     * Returns the configured start object ID.
     */
    public function getStartObjId(): int
    {
        $start_ref_id = $this->getStartRefId();
        if ($start_ref_id === 0) {
            return 0;
        }
        return $this->lookupObjId($start_ref_id);
    }
    /**
     * Returns the configured end object ID.
     */
    public function getEndObjId(): int
    {
        $end_ref_id = $this->getEndRefId();
        if ($end_ref_id === 0) {
            return 0;
        }
        return $this->lookupObjId($end_ref_id);
    }
    /**
     * Returns the current item and initializes it when necessary.
     *
     * @param \LSLearnerItem[] $items
     */
    public function getCurrentItem(array $items): ?\LSLearnerItem
    {
        if (count($items) === 0) {
            return null;
        }
        $valid_ref_ids = array_map(fn($i) => $i->getRefId(), array_values($items));

        $current_ref_id = $this->item_path->getCurrent($this->usr_id, $this->lso_obj_id);
        while ($current_ref_id !== null && !in_array($current_ref_id, $valid_ref_ids, true)) {
            $this->item_path->pop($this->usr_id, $this->lso_obj_id);
            $current_ref_id = $this->item_path->getCurrent($this->usr_id, $this->lso_obj_id);
        }

        if ($current_ref_id === null) {
            $start_ref_id = $this->getStartRefId();
            if ($start_ref_id === 0 || !in_array($start_ref_id, $valid_ref_ids, true)) {
                $start_ref_id = $items[0]->getRefId();
            }
            $this->item_path->push($this->usr_id, $this->lso_obj_id, $start_ref_id);
            $this->recordVisit($start_ref_id);
            $current_ref_id = $start_ref_id;
        }

        return $this->findItemByRefId($items, $current_ref_id);
    }
    /**
     * Returns the navigable situation of an item.
     *
     * @param \LSLearnerItem[] $items
     */
    public function getSituation(array $items, \LSLearnerItem $item): string
    {
        if ($this->getEndRefId() !== 0 && $item->getRefId() === $this->getEndRefId()) {
            return self::SIT_END;
        }
        if (!$this->navigator->canLeave($item)) {
            return self::SIT_BLOCKED;
        }
        if ($this->getStructuralSuccessors($items, $item) === []) {
            return self::SIT_DEADEND;
        }
        $count = count($this->getSuccessors($items, $item));
        if ($count === 0) {
            // successors exist, but none of them may be entered yet
            return $this->hasOpenStructuralSuccessor($items, $item)
                ? self::SIT_BLOCKED
                : self::SIT_DEADEND;
        }
        if ($count === 1) {
            return self::SIT_STRAIGHT;
        }
        return self::SIT_BRANCH;
    }

    /**
     * Whether at least one structural successor of the item has not been
     * completed yet, i.e. the path goes on once its input conditions are met.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function hasOpenStructuralSuccessor(array $items, \LSLearnerItem $item): bool
    {
        foreach ($this->getStructuralSuccessors($items, $item) as $successor) {
            if (!$this->isCompletedSuccessor($successor)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Returns the structural situation of an item.
     *
     * @param \LSLearnerItem[] $items
     */
    public function getStructuralSituation(array $items, \LSLearnerItem $item): string
    {
        if ($this->getEndRefId() !== 0 && $item->getRefId() === $this->getEndRefId()) {
            return self::SIT_END;
        }
        if (!$this->navigator->canLeave($item)) {
            return self::SIT_BLOCKED;
        }
        $count = count($this->getStructuralSuccessors($items, $item));
        if ($count === 0) {
            return self::SIT_DEADEND;
        }
        if ($count === 1) {
            return self::SIT_STRAIGHT;
        }
        return self::SIT_BRANCH;
    }
    /**
     * Returns the navigable successors of an item.
     *
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[]
     */
    public function getSuccessors(array $items, \LSLearnerItem $item): array
    {
        $successors = $this->navigator->getSuccessors($items, $item);
        if ($successors === []) {
            $successors = $this->getFallbackSuccessorsFromPath($items, $item);
        }

        return array_values(array_filter(
            $successors,
            fn(\LSLearnerItem $successor): bool => !$this->isCompletedSuccessor($successor)
        ));
    }
    /**
     * Returns the structural successors of an item.
     *
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[]
     */
    public function getStructuralSuccessors(array $items, \LSLearnerItem $item): array
    {
        /** @var \LSLearnerItem[] $successors */
        $successors = $this->navigator->getStructuralSuccessors($items, $item);
        return $successors;
    }
    /**
     * Advances from the current item in a direction.
     *
     * @param \LSLearnerItem[] $items
     */
    public function advance(array $items, \LSLearnerItem $current_item, ?int $direction): \LSLearnerItem
    {
        if ($direction !== null && $direction < 0) {
            if ($this->getPathLength() > 1) {
                $this->item_path->pop($this->usr_id, $this->lso_obj_id);
            }
            return $this->getCurrentItem($items);
        }

        if (!$this->navigator->canLeave($current_item)) {
            return $current_item;
        }
        $successors = $this->getSuccessors($items, $current_item);
        if (count($successors) === 1) {
            $successor = array_values($successors)[0];
            $this->item_path->push($this->usr_id, $this->lso_obj_id, $successor->getRefId());
            $this->recordVisit($successor->getRefId());
            return $successor;
        }
        return $current_item;
    }
    /**
     * Jumps to an accessible item.
     *
     * @param \LSLearnerItem[] $items
     */
    public function jumpTo(array $items, \LSLearnerItem $current_item, ?int $obj_id): \LSLearnerItem
    {
        if ($obj_id === null || $obj_id === 0) {
            return $current_item;
        }
        foreach ($items as $item) {
            if ($this->lookupObjId($item->getRefId()) !== $obj_id) {
                continue;
            }
            if ($item->getRefId() === $current_item->getRefId()) {
                return $current_item;
            }
            if (!$this->mayAccess($items, $item)) {
                return $current_item;
            }
            $this->item_path->push($this->usr_id, $this->lso_obj_id, $item->getRefId());
            $this->recordVisit($item->getRefId());
            return $item;
        }
        return $current_item;
    }
    /**
     * Determines whether an item may be accessed.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function mayAccess(array $items, \LSLearnerItem $item): bool
    {
        if ($this->getStartRefId() !== 0 && $item->getRefId() === $this->getStartRefId()) {
            return true;
        }
        if (!$this->navigator->canEnterIgnoringEdges($item)) {
            return false;
        }
        $predecessors = $this->navigator->getPredecessors($items, $item);
        if ($predecessors === []) {
            return true;
        }
        foreach ($predecessors as $predecessor) {
            $predecessor_obj_id = $this->lookupObjId($predecessor->getRefId());
            if (
                $this->navigator->canLeave($predecessor)
                && $this->hasCompleted($items, $predecessor_obj_id)
                && $this->navigator->canEnterFrom($predecessor, $item)
            ) {
                return true;
            }
        }
        return false;
    }
    /**
     * Moves to a successor item.
     *
     * @param \LSLearnerItem[] $items
     */
    public function goTo(array $items, \LSLearnerItem $current_item, ?int $obj_id): \LSLearnerItem
    {
        if ($obj_id === null || $obj_id === 0 || !$this->navigator->canLeave($current_item)) {
            return $current_item;
        }
        foreach ($this->getSuccessors($items, $current_item) as $successor) {
            if ($this->lookupObjId($successor->getRefId()) === $obj_id) {
                $this->item_path->push($this->usr_id, $this->lso_obj_id, $successor->getRefId());
                $this->recordVisit($successor->getRefId());
                return $successor;
            }
        }
        return $current_item;
    }

    /**
     * Returns the reference ID path.
     *
     * @return int[]
     */
    protected function getPath(): array
    {
        return $this->item_path->getPath($this->usr_id, $this->lso_obj_id);
    }

    /**
     * Returns the number of items in the path.
     */
    public function getPathLength(): int
    {
        return count($this->getPath());
    }

    /**
     * Returns the current reference ID.
     */
    protected function getCurrentRefId(): int
    {
        $current_ref_id = $this->item_path->getCurrent($this->usr_id, $this->lso_obj_id);
        return $current_ref_id ?? 0;
    }

    /**
     * Returns the current object ID.
     */
    public function getCurrentObjId(): int
    {
        $current_ref_id = $this->getCurrentRefId();
        if ($current_ref_id === 0) {
            return 0;
        }
        return $this->lookupObjId($current_ref_id);
    }

    /**
     * Returns the reference IDs on the walked path.
     *
     * @return int[]
     */
    protected function getWalkedRefIds(): array
    {
        return $this->getPath();
    }

    /**
     * Returns the object IDs on the walked path.
     *
     * @return int[]
     */
    public function getWalkedObjIds(): array
    {
        return array_map(
            fn(int $ref_id): int => $this->lookupObjId($ref_id),
            $this->getWalkedRefIds()
        );
    }

    /**
     * Returns the visit log with object IDs.
     *
     * @return array<int, array{obj_id: int, visited_ts: int}>
     */
    public function getVisitLog(): array
    {
        return array_map(
            fn(array $entry): array => [
                'obj_id' => $this->lookupObjId($entry['ref_id']),
                'visited_ts' => $entry['visited_ts']
            ],
            $this->getRawVisitLog()
        );
    }

    /**
     * Returns the cached or persisted visit log.
     *
     * @return array<int, array{ref_id: int, visited_ts: int}>
     */
    protected function getRawVisitLog(): array
    {
        if ($this->raw_visit_log !== null) {
            return $this->raw_visit_log;
        }
        if ($this->db === null) {
            return $this->raw_visit_log = [];
        }
        $query = "SELECT ref_id, visited_ts FROM " . self::VISITS_TABLE
            . " WHERE usr_id = " . $this->db->quote($this->usr_id, 'integer')
            . " AND lso_obj_id = " . $this->db->quote($this->lso_obj_id, 'integer')
            . " ORDER BY position ASC";
        $res = $this->db->query($query);

        $log = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $log[] = [
                'ref_id' => (int) $row['ref_id'],
                'visited_ts' => (int) $row['visited_ts']
            ];
        }

        $this->raw_visit_log = $log;
        return $log;
    }

    /**
     * Returns visit statistics indexed by object ID.
     *
     * @return array<int, array{count: int, last_ts: int}>
     */
    protected function getVisitStats(): array
    {
        if ($this->visit_stats !== null) {
            return $this->visit_stats;
        }

        $stats = [];
        foreach ($this->getRawVisitLog() as $entry) {
            $obj_id = $this->lookupObjId($entry['ref_id']);
            if (!isset($stats[$obj_id])) {
                $stats[$obj_id] = ['count' => 0, 'last_ts' => (int) $entry['visited_ts']];
            }
            $stats[$obj_id]['count']++;
            $stats[$obj_id]['last_ts'] = (int) $entry['visited_ts'];
        }

        $this->visit_stats = $stats;
        return $stats;
    }

    /**
     * Returns all reference IDs that have been visited.
     *
     * @return int[]
     */
    protected function getEverVisitedRefIds(): array
    {
        $ref_ids = [];
        foreach ($this->getRawVisitLog() as $entry) {
            if (!in_array($entry['ref_id'], $ref_ids, true)) {
                $ref_ids[] = $entry['ref_id'];
            }
        }
        return $ref_ids;
    }

    /**
     * Returns the number of visits to an object.
     */
    public function getVisitCount(int $obj_id): int
    {
        return $this->getVisitStats()[$obj_id]['count'] ?? 0;
    }

    /**
     * Returns the timestamp of the last visit to an object.
     */
    public function getLastVisitTs(int $obj_id): ?int
    {
        return $this->getVisitStats()[$obj_id]['last_ts'] ?? null;
    }

    /**
     * Returns all object IDs that have been visited.
     *
     * @return int[]
     */
    public function getEverVisitedObjIds(): array
    {
        return array_map(
            fn(int $ref_id): int => $this->lookupObjId($ref_id),
            $this->getEverVisitedRefIds()
        );
    }

    /**
     * Determines whether an object has been visited.
     */
    public function hasVisited(int $obj_id): bool
    {
        return isset($this->getVisitStats()[$obj_id]);
    }

    /**
     * Determines whether an item has been completed.
     *
     * @param \LSLearnerItem[] $items
     */
    public function hasCompleted(array $items, int $obj_id): bool
    {
        if ($obj_id === 0) {
            return false;
        }
        foreach ($items as $item) {
            if ($this->lookupObjId($item->getRefId()) !== $obj_id) {
                continue;
            }
            if ($this->navigator instanceof LSOLearningMapSequentialNavigator
                && $item->getPostCondition()->getConditionOperator() === \ilLSPostCondition::OPERATOR_ALWAYS
            ) {
                return $this->hasVisited($obj_id);
            }
            // "according to learning progress": the learning progress decides,
            // a visit alone does not complete the item
            if ($this->navigator instanceof LSOLearningMapSequentialNavigator
                && $item->getPostCondition()->getConditionOperator() === \ilLSPostCondition::OPERATOR_LP
            ) {
                return $this->hasLearningProgressCompleted($item, $obj_id);
            }
            if ($this->navigator->getOutputConditionIds($item) === []) {
                return $this->hasVisited($obj_id);
            }
            if ($this->navigator instanceof AdaptiveNavigator
                && $this->navigator->requiresVisitForCompletion($item)
            ) {
                return $this->hasVisited($obj_id);
            }
            return $this->navigator->canLeave($item);
        }
        return false;
    }

    /**
     * Determines completion using learning progress.
     */
    protected function hasLearningProgressCompleted(\LSLearnerItem $item, int $obj_id): bool
    {
        $status = $item->getLearningProgressStatus();
        if ($status !== 0) {
            return $status === \ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }

        try {
            return \ilLPStatus::_hasUserCompleted($obj_id, $this->usr_id);
        } catch (\Throwable $t) {
            return false;
        }
    }

    /**
     * Determines whether a successor should be suppressed because all of its
     * output conditions are already fulfilled.
     */
    protected function isCompletedSuccessor(\LSLearnerItem $successor): bool
    {
        if ($this->navigator->getOutputConditionIds($successor) === []) {
            return false;
        }

        return $this->navigator->canLeave($successor);
    }

    /**
     * Finds an item by its reference ID.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function findItemByRefId(array $items, int $ref_id): ?\LSLearnerItem
    {
        foreach ($items as $item) {
            if ($item->getRefId() === $ref_id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Returns successors from the nearest previous path node when the current
     * node has no direct successor.
     *
     * This keeps adaptive navigation moving in branch-topologies where
     * subsequent options are reachable from an earlier branch node.
     *
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[]
     */
    protected function getFallbackSuccessorsFromPath(array $items, \LSLearnerItem $current_item): array
    {
        $current_ref_id = $current_item->getRefId();
        $walked_ref_ids = $this->getWalkedRefIds();
        if ($walked_ref_ids === []) {
            return [];
        }

        $fallback = [];
        foreach (array_reverse($walked_ref_ids) as $path_ref_id) {
            if ($path_ref_id === $current_ref_id) {
                continue;
            }

            $path_item = $this->findItemByRefId($items, $path_ref_id);
            if ($path_item === null) {
                continue;
            }
            if (!$this->navigator->canLeave($path_item)) {
                continue;
            }

            foreach ($this->navigator->getSuccessors($items, $path_item) as $candidate) {
                $candidate_ref_id = $candidate->getRefId();
                if ($candidate_ref_id === $current_ref_id) {
                    continue;
                }
                $fallback[$candidate_ref_id] = $candidate;
            }

            if ($fallback !== []) {
                break;
            }
        }

        return array_values($fallback);
    }
}
