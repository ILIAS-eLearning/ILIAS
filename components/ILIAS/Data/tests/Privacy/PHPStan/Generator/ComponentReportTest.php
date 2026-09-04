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

use PHPUnit\Framework\TestCase;

class ComponentReportTest extends TestCase
{
    private function entry(EntryCategory $category, string $type = 'PostalAddress'): ResolveEntry
    {
        return new ResolveEntry($type, 'SomePurpose', [], $category, 'f.php', 1);
    }

    public function testStartsEmpty(): void
    {
        $report = new ComponentReport();

        $this->assertTrue($report->isEmpty());
        $this->assertSame([], $report->get(EntryCategory::Store));
        $this->assertSame([], $report->all());
    }

    public function testGroupsByCategory(): void
    {
        $report = new ComponentReport();
        $store = $this->entry(EntryCategory::Store);
        $display_one = $this->entry(EntryCategory::Display);
        $display_two = $this->entry(EntryCategory::Display, 'OtherType');
        $legacy = $this->entry(EntryCategory::Legacy);

        $report->add($store);
        $report->add($display_one);
        $report->add($display_two);
        $report->add($legacy);

        $this->assertFalse($report->isEmpty());
        $this->assertSame([$store], $report->get(EntryCategory::Store));
        $this->assertSame([$display_one, $display_two], $report->get(EntryCategory::Display));
        $this->assertSame([$legacy], $report->get(EntryCategory::Legacy));
        $this->assertSame([], $report->get(EntryCategory::Pass));
        $this->assertSame([], $report->get(EntryCategory::Technical));
        $this->assertCount(4, $report->all());
    }
}
