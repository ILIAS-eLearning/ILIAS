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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

/**
 * Collects the resolve entries of one component, grouped by category.
 */
final class ComponentReport
{
    /**
     * @var array<string, list<ResolveEntry>>
     */
    private array $by_category = [];

    public function add(ResolveEntry $entry): void
    {
        $this->by_category[$entry->category->name][] = $entry;
    }

    public function isEmpty(): bool
    {
        return $this->by_category === [];
    }

    /**
     * @return list<ResolveEntry>
     */
    public function get(EntryCategory $category): array
    {
        return $this->by_category[$category->name] ?? [];
    }

    /**
     * @return list<ResolveEntry>
     */
    public function all(): array
    {
        return array_merge(...array_values($this->by_category) ?: [[]]);
    }
}
