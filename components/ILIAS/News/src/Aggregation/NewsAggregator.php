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

namespace ILIAS\News\Aggregation;

use ILIAS\News\Data\NewsContext;
use SplQueue;

/**
 * News Aggregator aggregates related contexts for a news context using a layer-wise Batching BFS to aggregate context
 * grouped by objects types in a single iteration.
 */
class NewsAggregator
{
    /** @var array<string, NewsAggregationStrategy> */
    protected array $strategies = [];
    protected \ilTree $tree;

    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->initializeStrategies();
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[] aggregated contexts
     */
    public function aggregate(array $contexts): array
    {
        /** @var SplQueue<NewsContext> $frontier */
        $frontier = new SplQueue();
        $result = [];

        // Prepare queue
        foreach ($contexts as $context) {
            $strategy = $this->getStrategy($context->getObjType());
            if ($strategy === null || $strategy->shouldSkip($context)) {
                continue;
            }
            $frontier->enqueue($context);
        }

        while (!$frontier->isEmpty()) {
            $current = $frontier->dequeue();

            // Ensure each context is only visited once
            if (array_key_exists($current->getObjId(), $result)) {
                continue;
            }
            $result[$current->getObjId()] = $current;

            // Skip if no processing necessary
            $strategy = $this->getStrategy($current->getObjType());
            if ($strategy === null || $strategy->shouldSkip($current)) {
                continue;
            }

            $children = $strategy->aggregate($current);
            foreach ($children as $child) {
                if ($strategy->isRecursive()) {
                    // Recursive items will be added directly
                    $result[$child->getObjId()] = $child;
                } else {
                    // Iterative items will be queued for further processing
                    $frontier->enqueue($child);
                }
            }
        }

        return array_values($result);
    }

    protected function getStrategy(string $object_type): ?NewsAggregationStrategy
    {
        return $this->strategies[$object_type] ?? null;
    }

    protected function initializeStrategies(): void
    {
        $subtree_strategy = new SubtreeAggregationStrategy($this->tree);

        $this->strategies['cat'] = new CategoryAggregationStrategy($this->tree);
        $this->strategies['crs'] = $subtree_strategy;
        $this->strategies['grp'] = $subtree_strategy;
        $this->strategies['fold'] = $subtree_strategy;
    }
}
