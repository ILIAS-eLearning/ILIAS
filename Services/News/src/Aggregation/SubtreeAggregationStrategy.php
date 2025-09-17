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
 * Subtree Aggregation Strategy aggregates related contexts for groups and courses.
 */
class SubtreeAggregationStrategy implements NewsAggregationStrategy
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
            if ($this->shouldSkip($context)) {
                continue;
            }

            $context_node = $this->tree->getNodeData($context->getRefId());
            if (!$context_node) {
                continue;
            }

            $nodes = $this->tree->getSubTree($context_node);
            foreach ($nodes as $node) {
                $aggregated[] = new NewsContext(
                    $node['child'],
                    $node['obj_id'],
                    $node['type'],
                    $context->getRefId(),
                    $context->getLevel() + ($node['depth'] - $context_node['depth'])
                );
            }
        }

        return $aggregated;
    }

    public function isRecursive(): bool
    {
        return true;
    }

    private function shouldSkip(NewsContext $context): bool
    {
        // see #31471, #30687, and ilMembershipNotification
        return !\ilContainer::_lookupContainerSetting($context->getObjId(), 'cont_use_news', '1')
            || (!\ilContainer::_lookupContainerSetting($context->getObjId(), 'cont_show_news', '1')
                && !\ilContainer::_lookupContainerSetting($context->getObjId(), 'news_timeline'));
    }
}
