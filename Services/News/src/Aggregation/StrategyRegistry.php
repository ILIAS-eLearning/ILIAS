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

/**
 * News Aggregation Strategy Registry provides appropriate aggregation strategies based on context object types
 */
class StrategyRegistry
{
    /** @var array<string, NewsAggregationStrategy> */
    protected array $strategies = [];

    public function __construct()
    {
        $this->initializeStrategies();
    }

    public function getStrategy(string $object_type): NewsAggregationStrategy
    {
        return $this->strategies[$object_type] ?? $this->strategies['default'];
    }

    private function initializeStrategies(): void
    {
        //TODO: use constructor injection instead
        global $DIC;

        $this->strategies['default'] = new DefaultAggregationStrategy();
        $this->strategies['cat'] = new CategoryAggregationStrategy($DIC->repositoryTree());
    }
}
