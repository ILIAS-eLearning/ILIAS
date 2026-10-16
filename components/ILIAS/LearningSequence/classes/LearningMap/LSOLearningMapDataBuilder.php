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

use ILIAS\LearningSequence\Player\LSNavigator;

/**
 * Builds learning map data for a user and view mode.
 */
class LSOLearningMapDataBuilder
{
    /**
     * @var array<int, int>
     */
    protected array $obj_id_by_ref_id = [];

    /**
     * Creates a learning map data builder.
     */
    public function __construct(
        /** @var LSNavigator The learning sequence navigator. */
        protected LSNavigator $navigator,
        /** @var \LSUrlBuilder The URL builder. */
        protected \LSUrlBuilder $url_builder,
        /** @var string The player command. */
        protected string $goto_command,
        /** @var int The learning sequence object ID. */
        protected int $lso_obj_id,
        /** @var int The default user ID. */
        protected int $default_usr_id,
        /** @var \Closure The position factory. */
        protected \Closure $position_factory,
        /** @var \Closure The item factory. */
        protected \Closure $items_factory,
        /** @var bool Whether links use reference IDs. */
        protected bool $link_by_ref_id = false
    ) {
    }

    /**
     * Builds a learning map.
     */
    public function build(int $mode, ?int $usr_id = null): LSOLearningMap
    {
        if (!LSOLearningMapViewMode::isValid($mode)) {
            $mode = LSOLearningMapViewMode::MODE_FULL_ROUTE;
        }

        $usr_id = $usr_id ?? $this->default_usr_id;
        $position = ($this->position_factory)($usr_id);
        $items = ($this->items_factory)($usr_id);

        $this->prepareCaches($position, $items);

        $start_obj_id = $position->getStartObjId();
        $end_obj_id = $position->getEndObjId();
        $current_obj_id = $position->getCurrentObjId();
        $walked_obj_ids = $position->getWalkedObjIds();

        $start_item = $this->resolveStartItem($items, $start_obj_id);
        if ($start_item === null) {
            return new LSOLearningMap($this->lso_obj_id, $usr_id, $mode, $start_obj_id, $end_obj_id, []);
        }
        $start_obj_id = $this->lookupObjId($start_item->getRefId());

        $nodes = $this->traverse($position, $items, $start_item, $start_obj_id, $end_obj_id, $current_obj_id, $walked_obj_ids);
        $nodes = $this->applyViewMode($nodes, $mode);

        return new LSOLearningMap($this->lso_obj_id, $usr_id, $mode, $start_obj_id, $end_obj_id, $nodes);
    }

    /**
     * Prepares object ID and navigation caches.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function prepareCaches(LSOLearningMapPosition $position, array $items): void
    {
        $this->obj_id_by_ref_id = [];
        foreach ($items as $item) {
            $ref_id = $item->getRefId();
            $this->obj_id_by_ref_id[$ref_id] = \ilObject::_lookupObjId($ref_id);
        }

        $position->prepareForItems($items);
        $this->navigator->preload($items);
    }

    /**
     * Returns the object ID for a reference ID.
     */
    protected function lookupObjId(int $ref_id): int
    {
        return $this->obj_id_by_ref_id[$ref_id] ??= \ilObject::_lookupObjId($ref_id);
    }

    /**
     * Traverses the learning sequence and builds its nodes.
     *
     * @param \LSLearnerItem[] $items
     * @param int[] $walked_obj_ids
     * @return array<int, LSOLearningMapNode>
     */
    protected function traverse(
        LSOLearningMapPosition $position,
        array $items,
        \LSLearnerItem $start_item,
        int $start_obj_id,
        int $end_obj_id,
        int $current_obj_id,
        array $walked_obj_ids
    ): array {
        $nodes = [];
        $depth = [$start_obj_id => 0];
        $queue = $start_obj_id === 0 ? [] : [[$start_item, $start_obj_id]];
        $visited = [$start_obj_id => true];
        $roots = $this->collectAdditionalRoots($items, $visited);

        while (count($queue) > 0 || count($roots) > 0) {
            if (count($queue) === 0) {
                [$root_item, $root_obj_id] = array_shift($roots);
                if (isset($visited[$root_obj_id])) {
                    continue;
                }
                $visited[$root_obj_id] = true;
                $depth[$root_obj_id] = 0;
                $queue[] = [$root_item, $root_obj_id];
            }
            [$item, $obj_id] = array_shift($queue);

            $successor_items = $position->getStructuralSuccessors($items, $item);
            $successor_obj_ids = [];
            $passable_successor_obj_ids = [];
            $can_leave = $position->hasCompleted($items, $obj_id) && $this->navigator->canLeave($item);
            foreach ($successor_items as $successor) {
                $successor_obj_id = $this->lookupObjId($successor->getRefId());
                $successor_obj_ids[] = $successor_obj_id;
                if ($can_leave && $this->navigator->canEnterFrom($item, $successor)) {
                    $passable_successor_obj_ids[] = $successor_obj_id;
                }
                if (!isset($visited[$successor_obj_id])) {
                    $visited[$successor_obj_id] = true;
                    $depth[$successor_obj_id] = ($depth[$obj_id] ?? 0) + 1;
                    $queue[] = [$successor, $successor_obj_id];
                }
            }

            $nodes[$obj_id] = $this->buildNode(
                $position,
                $items,
                $item,
                $obj_id,
                $successor_obj_ids,
                $passable_successor_obj_ids,
                $start_obj_id,
                $end_obj_id,
                $current_obj_id,
                $walked_obj_ids,
                $depth[$obj_id] ?? 0
            );
        }

        return $nodes;
    }

    /**
     * Collects unvisited items that start disconnected graph segments.
     *
     * @param \LSLearnerItem[] $items
     * @param array<int, bool> $visited
     * @return array<int, array{0: \LSLearnerItem, 1: int}>
     */
    protected function collectAdditionalRoots(array $items, array $visited): array
    {
        $roots = [];
        foreach ($items as $item) {
            $obj_id = $this->lookupObjId($item->getRefId());
            if (isset($visited[$obj_id])) {
                continue;
            }
            $roots[] = [$item, $obj_id];
        }
        return $roots;
    }

    /**
     * Creates a node for an item.
     *
     * @param \LSLearnerItem[] $items
     * @param int[] $successor_obj_ids
     * @param int[] $passable_successor_obj_ids
     * @param int[] $walked_obj_ids
     */
    protected function buildNode(
        LSOLearningMapPosition $position,
        array $items,
        \LSLearnerItem $item,
        int $obj_id,
        array $successor_obj_ids,
        array $passable_successor_obj_ids,
        int $start_obj_id,
        int $end_obj_id,
        int $current_obj_id,
        array $walked_obj_ids,
        int $depth
    ): LSOLearningMapNode {
        $can_access = $this->canAccess($position, $items, $item, $obj_id, $start_obj_id);
        if (!$can_access) {
            $passable_successor_obj_ids = [];
        }

        return new LSOLearningMapNode(
            obj_id: $obj_id,
            title: $item->getTitle(),
            description: $item->getDescription(),
            icon: $item->getIconPath(),
            player_link: $can_access
                ? $this->url_builder->getHref(
                    $this->goto_command,
                    $this->link_by_ref_id ? $item->getRefId() : $obj_id
                )
                : null,
            can_access: $can_access,
            has_visited: $position->hasVisited($obj_id),
            has_completed: $can_access && $position->hasCompleted($items, $obj_id),
            can_leave: $this->navigator->canLeave($item),
            situation: $obj_id === $start_obj_id
                ? 'start'
                : $position->getStructuralSituation($items, $item),
            successors: $successor_obj_ids,
            passable_successors: $passable_successor_obj_ids,
            input_condition_ids: $this->navigator->getInputConditionIds($item),
            output_condition_ids: $this->navigator->getOutputConditionIds($item),
            visit_count: $position->getVisitCount($obj_id),
            last_visited_ts: $position->getLastVisitTs($obj_id),
            is_current: $obj_id === $current_obj_id,
            is_on_walked_path: in_array($obj_id, $walked_obj_ids, true),
            depth: $depth
        );
    }

    /**
     * Determines whether an item can be accessed.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function canAccess(
        LSOLearningMapPosition $position,
        array $items,
        \LSLearnerItem $item,
        int $obj_id,
        int $start_obj_id
    ): bool {
        if ($obj_id === $start_obj_id) {
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
            if ($this->navigator->canLeave($predecessor)
                && $position->hasCompleted($items, $predecessor_obj_id)
                && $this->navigator->canEnterFrom($predecessor, $item)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Applies a view mode to learning map nodes.
     *
     * @param array<int, LSOLearningMapNode> $nodes
     * @return array<int, LSOLearningMapNode>
     */
    protected function applyViewMode(array $nodes, int $mode): array
    {
        if ($mode !== LSOLearningMapViewMode::MODE_REACHABLE_ONLY) {
            return $nodes;
        }

        $kept = [];
        foreach ($nodes as $obj_id => $node) {
            if ($node->can_access || $node->has_visited || $node->is_current) {
                $kept[$obj_id] = $node;
            }
        }

        return $this->pruneDanglingEdges($kept);
    }

    /**
     * Removes edges pointing to omitted nodes.
     *
     * @param array<int, LSOLearningMapNode> $nodes
     * @return array<int, LSOLearningMapNode>
     */
    protected function pruneDanglingEdges(array $nodes): array
    {
        $pruned = [];
        foreach ($nodes as $obj_id => $node) {
            $successors = array_values(array_filter(
                $node->successors,
                static fn(int $successor_obj_id): bool => isset($nodes[$successor_obj_id])
            ));
            $passable_successors = array_values(array_filter(
                $node->passable_successors,
                static fn(int $successor_obj_id): bool => isset($nodes[$successor_obj_id])
            ));
            $pruned[$obj_id] = new LSOLearningMapNode(
                obj_id: $node->obj_id,
                title: $node->title,
                description: $node->description,
                icon: $node->icon,
                player_link: $node->player_link,
                can_access: $node->can_access,
                has_visited: $node->has_visited,
                has_completed: $node->has_completed,
                can_leave: $node->can_leave,
                situation: $node->situation,
                successors: $successors,
                passable_successors: $passable_successors,
                input_condition_ids: $node->input_condition_ids,
                output_condition_ids: $node->output_condition_ids,
                visit_count: $node->visit_count,
                last_visited_ts: $node->last_visited_ts,
                is_current: $node->is_current,
                is_on_walked_path: $node->is_on_walked_path,
                depth: $node->depth
            );
        }
        return $pruned;
    }

    /**
     * Resolves the first item from the configured start object.
     *
     * @param \LSLearnerItem[] $items
     */
    protected function resolveStartItem(array $items, int $start_obj_id): ?\LSLearnerItem
    {
        if ($items === []) {
            return null;
        }
        if ($start_obj_id !== 0) {
            foreach ($items as $item) {
                if ($this->lookupObjId($item->getRefId()) === $start_obj_id) {
                    return $item;
                }
            }
        }
        return array_values($items)[0];
    }
}
