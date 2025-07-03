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

namespace ILIAS\Component\Activities;

class StaticRepository implements Repository
{
    protected array $activities = [];

    /**
     * @param array<Activity> $ativities
     */
    public function __construct(
        array $activities
    ) {
        foreach ($activities as $activity) {
            if (!($activity instanceof Activity)) {
                throw new \InvalidArgumentException(
                    "Expected `Activity`, got: " . get_class($activity)
                );
            }
            $this->activities[(string) $activity->getName()] = $activity;
        }
    }

    public function getActivitiesByName(string $name_matcher, ?ActivityType $type = null, ?Range $range = null): \Iterator
    {
        foreach ($this->activities as $name => $activity) {
            if (preg_match($name_matcher, $name)) {
                yield $name => $activity;
            }
        }
    }
}
