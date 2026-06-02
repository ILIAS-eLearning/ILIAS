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
 * News Aggregation Strategy Interface defines the contract for news aggregation strategies.
 * Each strategy implements specific aggregation logic for different context types.
 */
interface NewsAggregationStrategy
{
    /**
     * @return NewsContext[]
     */
    public function aggregate(NewsContext $base_context): array;

    /**
     * Returns true if the provided context should not be aggregated. This method may check criteria or external
     * conditions.
     */
    public function shouldSkip(NewsContext $context): bool;

    /**
     * Returns true if the strategy already resolves contexts recursively (which is more performant in some cases).
     * If it returns false, the returned contexts will be enqueued by the aggregator to be resolved iteratively.
     */
    public function isRecursive(): bool;
}
