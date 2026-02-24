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

/**
 * Subtree Aggregation Strategy aggregates related contexts for groups and courses.
 */
class SubtreeAggregationStrategy implements NewsAggregationStrategy
{
    public function __construct(protected readonly \ilTree $tree)
    {
    }

    /**
     * @ineritDoc
     */
    public function aggregate(NewsContext $base_context): array
    {
        $aggregated = [];

        foreach ($this->tree->getChilds($base_context->getRefId()) as $node) {
            $aggregated[] = new NewsContext(
                $node['child'],
                $node['obj_id'],
                $node['type'],
                $base_context->getRefId(),
                $base_context->getLevel() + 1
            );
        }

        return $aggregated;
    }

    public function isRecursive(): bool
    {
        return false;
    }

    public function shouldSkip(NewsContext $context): bool
    {
        if (in_array($context->getObjType(), ['crs', 'grp', 'cat'])) {
            // see #31471, #30687, and ilMembershipNotification
            return !\ilContainer::_lookupContainerSetting($context->getObjId(), 'cont_use_news', '1')
                || (!\ilContainer::_lookupContainerSetting($context->getObjId(), 'cont_show_news', '1')
                    && !\ilContainer::_lookupContainerSetting($context->getObjId(), 'news_timeline'));
        }

        return false;
    }
}
