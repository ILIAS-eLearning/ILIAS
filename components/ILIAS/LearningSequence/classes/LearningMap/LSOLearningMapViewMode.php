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

/**
 * Defines the available learning map view modes.
 */
final class LSOLearningMapViewMode
{
    public const MODE_FULL_ROUTE = 1;
    public const MODE_REACHABLE_ONLY = 2;
    public const MODE_PROGRESS = 3;

    /**
     * Returns all valid view modes.
     *
     * @return int[]
     */
    public static function all(): array
    {
        return [
            self::MODE_FULL_ROUTE,
            self::MODE_REACHABLE_ONLY,
            self::MODE_PROGRESS
        ];
    }

    /**
     * Determines whether a view mode is valid.
     */
    public static function isValid(int $mode): bool
    {
        return in_array($mode, self::all(), true);
    }
}
