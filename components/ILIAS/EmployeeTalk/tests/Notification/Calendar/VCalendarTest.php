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

namespace ILIAS\EmployeeTalk\Notification\Calendar;

require_once __DIR__ . '/CalendarIcsTestCase.php';

class VCalendarTest extends CalendarIcsTestCase
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

    public function testRendersCalendarHeaderInOrder(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            'calendar name',
            'unique identifier'
        ))->render();

        $lines = [];
        foreach ($this->contentLines($ics) as $line) {
            if ($line === 'BEGIN:VTIMEZONE') {
                break;
            }
            $lines[] = $line;
        }

        foreach ($lines as $index => $line) {
            if (str_starts_with($line, 'LAST-MODIFIED:')) {
                $lines[$index] = 'LAST-MODIFIED:STAMP';
            }
        }
        self::assertSame(
            [
                'BEGIN:VCALENDAR',
                'PRODID:-//ILIAS',
                'VERSION:2.0',
                'UID:unique identifier',
                'X-WR-RELCALID:unique identifier',
                'NAME:calendar name',
                'X-WR-CALNAME:calendar name',
                'LAST-MODIFIED:STAMP',
                'METHOD:PUBLISH',
            ],
            $lines
        );
    }

    public function testCalendarLastModifiedIsCurrentUtc(): void
    {
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $ics = (new VCalendar(Method::PUBLISH, 'calendar name', 'id'))->render();
        $after = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $stamp = \DateTimeImmutable::createFromFormat(
            'Ymd\THis\Z',
            substr($this->propertyLine($ics, 'LAST-MODIFIED'), strlen('LAST-MODIFIED:')),
            new \DateTimeZone('UTC')
        );
        self::assertInstanceOf(\DateTimeImmutable::class, $stamp);
        self::assertGreaterThanOrEqual($before->modify('-1 second'), $stamp);
        self::assertLessThanOrEqual($after->modify('+1 second'), $stamp);
    }

    public function testRendersConfiguredMethod(): void
    {
        $ics = (new VCalendar(
            Method::CANCEL,
            'calendar name',
            'id'
        ))->render();

        self::assertSame('METHOD:CANCEL', $this->propertyLine($ics, 'METHOD'));
    }

    public function testEscapesCalendarNameAndUid(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            "Talks; Staff, \"A\"\\B\r\nC\nD",
            'id;x'
        ))->render();

        self::assertSame(
            'NAME:Talks\\; Staff\\, "A"\\\\B\\nC\\nD',
            $this->propertyLine($ics, 'NAME')
        );
        self::assertSame(
            'X-WR-CALNAME:Talks\\; Staff\\, "A"\\\\B\\nC\\nD',
            $this->propertyLine($ics, 'X-WR-CALNAME')
        );
        self::assertSame('UID:id\\;x', $this->propertyLine($ics, 'UID'));
        self::assertSame('X-WR-RELCALID:id\\;x', $this->propertyLine($ics, 'X-WR-RELCALID'));
    }

    public function testEmbedsRenderedEventsBetweenTimezoneAndEnd(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            'calendar name',
            'id',
            $this->event(['uid' => 'talk-42@ilias.example'])
        ))->render();
        $lines = $this->contentLines($ics);
        $event_at = array_search('BEGIN:VEVENT', $lines, true);
        $calendar_end = array_search('END:VCALENDAR', $lines, true);

        self::assertSame(
            ['END:VTIMEZONE', 'BEGIN:VEVENT'],
            array_slice($lines, (int) $event_at - 1, 2)
        );
        self::assertSame('END:VEVENT', $lines[(int) $calendar_end - 1]);
        self::assertSame('UID:id', $this->propertyLine($ics, 'UID'));
        self::assertSame(
            'UID:talk-42@ilias.example',
            $this->propertyLine(substr($ics, (int) strpos($ics, 'BEGIN:VEVENT')), 'UID')
        );
    }

    public function testDoesNotFoldLineOfExactly75Octets(): void
    {
        $prefix = 'X-WR-CALNAME:';
        $name = str_repeat('n', 75 - strlen($prefix));

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();

        self::assertContains($prefix . $name, $this->contentLines($ics));
    }

    public function testFoldsFirstLineAfter75Octets(): void
    {
        $prefix = 'X-WR-CALNAME:';
        $head = str_repeat('n', 75 - strlen($prefix));
        $name = $head . 'x';

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();

        $this->assertFoldedLines($ics, $prefix . $head, ' x');
    }

    public function testFoldsContinuationLinesAfter74Octets(): void
    {
        $prefix = 'X-WR-CALNAME:';
        $head = str_repeat('n', 75 - strlen($prefix));
        $middle = str_repeat('n', 74);
        $name = $head . $middle . 'x';

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();

        $this->assertFoldedLines($ics, $prefix . $head, ' ' . $middle, ' x');
    }

    public function testDoesNotSplitUtf8CharacterWhenFolding(): void
    {
        $prefix = 'X-WR-CALNAME:';
        $head = str_repeat('n', 74 - strlen($prefix));

        $ics = (new VCalendar(Method::PUBLISH, $head . 'ä', 'id'))->render();

        $this->assertFoldedLines($ics, $prefix . $head, ' ä');
    }

    public function testKeepsUtf8CharacterThatFitsExactlyAtTheFold(): void
    {
        $prefix = 'X-WR-CALNAME:';
        $head = str_repeat('n', 75 - strlen($prefix) - strlen('ä'));

        $ics = (new VCalendar(Method::PUBLISH, $head . 'äx', 'id'))->render();

        $this->assertFoldedLines($ics, $prefix . $head . 'ä', ' x');
    }

    private function assertFoldedLines(string $ics, string ...$expected): void
    {
        $lines = $this->contentLines($ics);
        $index = array_search($expected[0], $lines, true);

        self::assertSame($expected, array_slice($lines, (int) $index, count($expected)));
    }
}
