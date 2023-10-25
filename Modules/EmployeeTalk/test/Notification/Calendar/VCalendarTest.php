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

namespace ILIAS\EmployeeTalk\Notification\Calendar;

use PHPUnit\Framework\TestCase;

class VCalendarTest extends TestCase
{
    public function testVCalendarRenderingWithoutEvents(): void
    {
        $expected_start = 'BEGIN:VCALENDAR' . "\r\n" .
            'PRODID:-//ILIAS' . "\r\n" .
            'VERSION:2.0' . "\r\n" .
            "UID:unique identifier\r\n" .
            "X-WR-RELCALID:unique identifier\r\n" .
            "NAME:calendar name\r\n" .
            "X-WR-CALNAME:calendar name\r\n";
        // Timestamps in between which breaks the test because they are changing
        $expected_end = 'METHOD:' . Method::PUBLISH->value . "\r\n" .
            'BEGIN:VTIMEZONE' . "\r\n" .
            'TZID:Europe/Paris' . "\r\n" .
            'X-LIC-LOCATION:Europe/Paris' . "\r\n" .
            'BEGIN:DAYLIGHT' . "\r\n" .
            'TZOFFSETFROM:+0100' . "\r\n" .
            'TZOFFSETTO:+0200' . "\r\n" .
            'TZNAME:CEST' . "\r\n" .
            'DTSTART:19700329T020000' . "\r\n" .
            'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU' . "\r\n" .
            'END:DAYLIGHT' . "\r\n" .
            'BEGIN:STANDARD' . "\r\n" .
            'TZOFFSETFROM:+0200' . "\r\n" .
            'TZOFFSETTO:+0100' . "\r\n" .
            'TZNAME:CET' . "\r\n" .
            'DTSTART:19701025T030000' . "\r\n" .
            'RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU' . "\r\n" .
            'END:STANDARD' . "\r\n" .
            'END:VTIMEZONE' . "\r\n" .
            'END:VCALENDAR' . "\r\n";

        $subject = new VCalendar(
            Method::PUBLISH,
            'calendar name',
            'unique identifier'
        );

        $result = $subject->render();

        $this->assertStringStartsWith($expected_start, $result);
        $this->assertStringEndsWith($expected_end, $result);
    }
}
