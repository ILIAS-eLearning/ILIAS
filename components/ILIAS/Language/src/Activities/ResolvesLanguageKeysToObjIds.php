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

namespace ILIAS\Language\Activities;

trait ResolvesLanguageKeysToObjIds
{
    // Requires the using class to declare and initialize its own
    // `private readonly \Closure $lng_objects` ((): list<array{obj_id: int, title: string}>)
    // - left to each using class (rather than declared here) since its default value differs
    // per Activity.

    /**
     * Language keys (titles) shared by more than one "lng" object are excluded from the first
     * array and returned as keys of the second instead.
     *
     * @return array{0: array<string, int>, 1: array<string, true>}
     */
    private function resolveObjIdsByLanguageKey(): array
    {
        $lng_objects = ($this->lng_objects)();

        $title_occurrences = [];
        foreach ($lng_objects as $lng_object) {
            $title_occurrences[$lng_object['title']] = ($title_occurrences[$lng_object['title']] ?? 0) + 1;
        }

        $obj_id_by_language_key = [];
        $ambiguous_language_keys = [];
        foreach ($lng_objects as $lng_object) {
            $title = $lng_object['title'];
            if ($title_occurrences[$title] > 1) {
                $ambiguous_language_keys[$title] = true;
                continue;
            }

            $obj_id_by_language_key[$title] = (int) $lng_object['obj_id'];
        }

        return [$obj_id_by_language_key, $ambiguous_language_keys];
    }
}
