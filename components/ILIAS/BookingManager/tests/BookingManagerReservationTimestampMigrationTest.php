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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BookingManagerReservationTimestampMigrationTest extends TestCase
{
    #[DataProvider('normalizeTimestampProvider')]
    public function testNormalizeTimestampRoundsUpToNextMinuteBoundary(int $input, int $expected): void
    {
        $migration = new ilBookingManagerReservationTimestampMigration();
        $method = new ReflectionMethod($migration, 'normalizeTimestamp');

        parent::assertSame($expected, $method->invoke($migration, $input));
    }

    public static function normalizeTimestampProvider(): Generator
    {
        yield 'zero stays zero' => [0, 0];
        yield 'negative value stays unchanged' => [-100, -100];

        $minute_aligned = mktime(12, 0, 0, 7, 15, 2026);
        yield 'already minute aligned stays unchanged' => [$minute_aligned, $minute_aligned];

        $legacy_end_minus_one = mktime(11, 59, 59, 7, 15, 2026);
        yield 'legacy end minus one second rounds up' => [$legacy_end_minus_one, $minute_aligned];

        $one_second_past_minute = mktime(12, 0, 1, 7, 15, 2026);
        $next_minute = mktime(12, 1, 0, 7, 15, 2026);
        yield 'one second past minute rounds up' => [$one_second_past_minute, $next_minute];
    }
}
