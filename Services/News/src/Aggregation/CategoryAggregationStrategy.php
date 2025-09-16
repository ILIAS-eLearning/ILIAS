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

/**
 * Category Aggregation Strategy aggregates related contexts for a category context
 */
class CategoryAggregationStrategy implements NewsAggregationStrategy
{
    public function __construct(
        protected readonly \ilTree $tree
    ) {
    }

    /**
     * @param NewsContext[] $contexts
     * @return NewsContext[]
     */
    public function aggregate(array $contexts): array
    {
        $aggregated = [];

        foreach ($contexts as $context) {
            foreach ($this->tree->getChilds($context->getRefId()) as $node) {
                $aggregated[] = new NewsContext($node['child'], $node['obj_id'], $node['type'], $context->getRefId());
            }
        }
        return $aggregated;
    }
}
