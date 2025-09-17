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

namespace ILIAS\News\Aggregation;

use ILIAS\News\Data\NewsContext;
use SplQueue;

use function RectorPrefix202304\compressJs;

/**
 * News Aggregator aggregates related contexts for a news context using a layer-wise Batching BFS to aggregate context
 * grouped by objects types in a single iteration.
 */
class NewsAggregator
{
    /** @var array<string, NewsAggregationStrategy> */
    protected array $strategies = [];

    public function __construct()
    {
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
        $visited = [];
        $aggregated = [];

        // Prepare queue and visited set
        foreach ($contexts as $context) {
            $frontier->enqueue($context);
            $visited[$context->getRefId()] = true;
        }

        while (!$frontier->isEmpty()) {
            // 1. Aggregate current layer and group by type
            $batches = [];
            $layer_size = $frontier->count();

            for ($i = 0; $i < $layer_size; $i++) {
                $current = $frontier->dequeue();
                $aggregated[] = $current;
                $batches[$current->getObjType() ?? 'default'][] = $current;
            }

            // 2. Collect children for each type using the appropriate strategy
            foreach ($batches as $type => $batch) {
                $strategy = $this->getStrategy($type);
                if (!$strategy) {
                    continue;
                }

                foreach ($strategy->aggregate($batch) as $child) {
                    // Ensure each context is only visited once
                    if (isset($visited[$child->getRefId()])) {
                        continue;
                    }
                    $visited[$child->getRefId()] = true;

                    // 3. Enqueue new children for the next layer (iterative) or store directly (recursive strategy)
                    if (!$strategy->isRecursive()) {
                        $frontier->enqueue($child);
                    } else {
                        $aggregated[] = $child;
                    }
                }
            }
        }

        return $aggregated;
    }

    protected function getStrategy(string $object_type): ?NewsAggregationStrategy
    {
        return $this->strategies[$object_type] ?? null;
    }

    protected function initializeStrategies(): void
    {
        //TODO: use constructor injection instead
        global $DIC;

        $subtree_strategy = new SubtreeAggregationStrategy($DIC->repositoryTree());

        $this->strategies['cat'] = new CategoryAggregationStrategy($DIC->repositoryTree());
        $this->strategies['crs'] = $subtree_strategy;
        $this->strategies['grp'] = $subtree_strategy;
    }
}
