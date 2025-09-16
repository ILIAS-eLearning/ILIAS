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

/**
 * News Aggregator aggregates related contexts for a news context using a layer-wise Batching BFS to aggregate context
 * grouped by objects types in a single iteration.
 */
class NewsAggregator
{
    public function __construct(
        protected readonly StrategyRegistry $registry,
    ) {
    }

    public function aggregate(NewsContext $news_context): array
    {
        /** @var SplQueue<NewsContext> $frontier */
        $frontier = new SplQueue();
        $visited = [];
        $aggregated = [];

        $frontier->enqueue($news_context);
        $visited[$news_context->getRefId()] = true;

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
            $children = [];
            foreach ($batches as $type => $batch) {
                $strategy = $this->registry->getStrategy($type);
                $children = array_merge($children, $strategy->aggregate($batch));
            }

            // 3. Enqueue new children for the next layer
            foreach ($children as $child) {
                if (!isset($visited[$child->getRefId()])) {
                    $frontier->enqueue($child);
                    $visited[$child->getRefId()] = true;
                }
            }
        }

        return $aggregated;
    }
}
