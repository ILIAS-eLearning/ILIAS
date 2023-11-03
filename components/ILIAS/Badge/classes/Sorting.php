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

namespace ILIAS\Badge;

use ILIAS\DI\Container;
use Closure;
use ilBadge;
use ilBadgeAssignment;

class Sorting
{
    private readonly string $method;
    /** @var Closure(array, array): int */
    private readonly Closure $compare;
    private readonly string $direction;
    private readonly string $key;
    private readonly string $badgeOrAssignment;

    public function __construct(string $sort_by = '')
    {
        $parts = explode('_', $sort_by);
        $what = $parts[0] ?? '';
        $direction = $parts[1] ?? '';

        $map = [
            'title' => ['badge', 'getTitle', 'strcasecmp'],
            'date' => ['assignment', 'getTimestamp', $this->minus()],
        ];
        $directions = ['asc' => 'asc', 'desc' => 'desc'];

        $key = isset($map[$what]) ? $what : key($map);
        $direction = $directions[$direction] ?? 'asc';

        $this->badgeOrAssignment = $map[$key][0];
        $this->method = $map[$key][1];
        $this->compare = Closure::fromCallable($map[$key][2]);
        $this->direction = $direction;
        $this->key = $key . '_' . $direction;
    }

    /**
     * @param array{badge: ilBadge, assignment: ilBadgeAssignment} $badge_and_assignment
     * @param array{badge: ilBadge, assignment: ilBadgeAssignment} $other
     * @return int
     */
    public function compare(array $badge_and_assignment, array $other): int
    {
        $method = $this->method;
        $value = $badge_and_assignment[$this->badgeOrAssignment]->$method();
        $other = $other[$this->badgeOrAssignment]->$method();

        return $this->reverse() * ($this->compare)($value, $other);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->options()[$this->key];
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return [
            'title_asc' => 'sort_by_title_asc',
            'title_desc' => 'sort_by_title_desc',
            'date_asc' => 'sort_by_date_asc',
            'date_desc' => 'sort_by_date_desc',
        ];
    }

    private function reverse(): int
    {
        return $this->direction === 'asc' ? 1 : -1;
    }

    private function minus(): Closure
    {
        return static fn (int $x, int $y): int => $x - $y;
    }
}
