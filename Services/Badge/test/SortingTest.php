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

namespace ILIAS\Badge\test;

use ILIAS\Badge\Sorting;
use ILIAS\DI\Container;
use Closure;
use ilBadge;
use ilBadgeAssignment;
use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    public function testConstruct(): void
    {
        $sort = new Sorting();
        $this->assertInstanceOf(Sorting::class, $sort);
    }

    /**
     * @depends testConstruct
     * @dataProvider sortProvider
     */
    public function testSorting(array $input, string $key, string $label, string $what, string $method, array $equal, array $less, array $greater): void
    {
        $sort = new Sorting(...$input);
        $this->assertEquals($key, $sort->key());
        $this->assertEquals($label, $sort->label());
        $this->assertEquals(0, $sort->compare($this->pair($what, $method, $equal[0]), $this->pair($what, $method, $equal[1])));
        $this->assertEquals(-1, $this->sign($sort->compare($this->pair($what, $method, $less[0]), $this->pair($what, $method, $less[1]))));
        $this->assertEquals(1, $this->sign($sort->compare($this->pair($what, $method, $greater[0]), $this->pair($what, $method, $greater[1]))));
    }

    /**
     * @depends testConstruct
     */
    public function testOptions(): void
    {
        $this->assertEquals([
            'title_asc',
            'title_desc',
            'date_asc',
            'date_desc',
        ], array_keys((new Sorting())->options()));
    }

    public function sortProvider(): array
    {
        return [
            'Default sort is title_asc' => [[], 'title_asc', 'sort_by_title_asc', 'badge', 'getTitle', ['A', 'a'], ['f', 'G'], ['d', 'c']],
            'Descending title' => [['title_desc'], 'title_desc', 'sort_by_title_desc', 'badge', 'getTitle', ['A', 'a'], ['d', 'c'], ['f', 'G']],
            'Ascending date' => [['date_asc'], 'date_asc', 'sort_by_date_asc', 'assignment', 'getTimestamp', ['7', '7'], [8, 30], [20, 6]],
            'Ascending date' => [['date_desc'], 'date_desc', 'sort_by_date_desc', 'assignment', 'getTimestamp', [7, 7], [20, 6], [8, 30]],
            'Random input results in title_asc' => [['Lorem ipsum'], 'title_asc', 'sort_by_title_asc', 'badge', 'getTitle', ['A', 'a'], ['f', 'G'], ['d', 'c']]
        ];
    }

    private function pair(string $what, string $method, $value): array
    {
        $badge = $this->getMockBuilder(ilBadge::class)->disableOriginalConstructor()->getMock();
        $assignment = $this->getMockBuilder(ilBadgeAssignment::class)->disableOriginalConstructor()->getMock();

        $pair = [
            'badge' => $badge,
            'assignment' => $assignment,
        ];

        $pair[$what]->method($method)->willReturn($value);

        return $pair;
    }

    private function sign(int $x): int
    {
        return !$x ? 0 : $x / abs($x);
    }
}
