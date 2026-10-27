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

namespace ILIAS\LearningSequence\Content\Condition;

final readonly class StaticInputConfigurationIssueDetail
{
    /**
     * @var array<string, string|array<int, int|string>>
     */
    public array $properties_by_language_var;

    /**
     * @param array<string, string|array<int, int|string>> $properties_by_language_var
     */
    public function __construct(
        public int $affected_ref_id,
        public string $title_language_var,
        public ?string $description_language_var = null,
        array $properties_by_language_var = []
    ) {
        $normalized_properties = [];

        foreach ($properties_by_language_var as $language_var => $value) {
            if (is_array($value)) {
                $normalized_properties[$language_var] = array_values(array_unique(array_filter(
                    array_map(
                        static fn(int|string $entry): int|string => is_int($entry) ? $entry : trim($entry),
                        $value
                    ),
                    static fn(int|string $entry): bool => $entry !== '' && $entry !== 0
                ), SORT_REGULAR));
                continue;
            }

            $normalized_properties[$language_var] = trim($value);
        }

        $this->properties_by_language_var = $normalized_properties;
    }
}
