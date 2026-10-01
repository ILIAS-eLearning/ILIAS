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
    public function testRendersOriginalCalendarPropertiesInOrder(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            'calendar name',
            'unique identifier'
        ))->render();

        $names = [];
        foreach ($this->contentLines($ics) as $line) {
            if ($line === 'BEGIN:VTIMEZONE') {
                break;
            }
            $names[] = explode(';', explode(':', $line, 2)[0], 2)[0];
        }

        self::assertSame(
            [
                'BEGIN',
                'PRODID',
                'VERSION',
                'UID',
                'X-WR-RELCALID',
                'NAME',
                'X-WR-CALNAME',
                'LAST-MODIFIED',
                'METHOD',
            ],
            $names
        );
        self::assertSame('BEGIN:VCALENDAR', $this->contentLines($ics)[0]);
        self::assertSame('PRODID:-//ILIAS', $this->propertyLine($ics, 'PRODID'));
        self::assertSame('VERSION:2.0', $this->propertyLine($ics, 'VERSION'));
        self::assertSame('UID:unique identifier', $this->propertyLine($ics, 'UID'));
        self::assertSame('X-WR-RELCALID:unique identifier', $this->propertyLine($ics, 'X-WR-RELCALID'));
        self::assertSame('NAME:calendar name', $this->propertyLine($ics, 'NAME'));
        self::assertSame('X-WR-CALNAME:calendar name', $this->propertyLine($ics, 'X-WR-CALNAME'));
        self::assertSame('METHOD:PUBLISH', $this->propertyLine($ics, 'METHOD'));
        self::assertStringEndsWith('END:VCALENDAR' . "\r\n", $ics);
    }

    public function testCalendarLastModifiedIsUtcZulu(): void
    {
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $ics = (new VCalendar(Method::PUBLISH, 'calendar name', 'id'))->render();
        $after = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $line = $this->propertyLine($ics, 'LAST-MODIFIED');
        self::assertMatchesRegularExpression('/^LAST-MODIFIED:\d{8}T\d{6}Z$/', $line);

        $stamp = \DateTimeImmutable::createFromFormat(
            'Ymd\THis\Z',
            substr($line, strlen('LAST-MODIFIED:')),
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

    public function testEscapesCalendarNameAndUidSpecialCharacters(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            'Talks; Staff, "A"',
            'id;x'
        ))->render();

        self::assertSame('NAME:Talks\\; Staff\\, "A"', $this->propertyLine($ics, 'NAME'));
        self::assertSame('X-WR-CALNAME:Talks\\; Staff\\, "A"', $this->propertyLine($ics, 'X-WR-CALNAME'));
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
        $event_block = strstr($this->unfolded($ics), 'BEGIN:VEVENT');

        self::assertNotFalse($event_block);
        self::assertSame('END:VTIMEZONE', $lines[array_search('BEGIN:VEVENT', $lines, true) - 1]);
        self::assertSame('END:VEVENT', $lines[array_search('END:VCALENDAR', $lines, true) - 1]);
        self::assertSame('UID:talk-42@ilias.example', $this->propertyLine($event_block, 'UID'));
        self::assertSame('UID:id', $this->propertyLine($ics, 'UID'));
    }

    public function testDoesNotFoldLineOfExactly75Octets(): void
    {
        $name = str_repeat('n', 62);

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();
        $lines = $this->contentLines($ics);
        $index = array_search('X-WR-CALNAME:' . $name, $lines, true);

        self::assertNotFalse($index);
        self::assertSame(75, strlen($lines[$index]));
        self::assertStringStartsWith('LAST-MODIFIED:', $lines[$index + 1]);
    }

    public function testFoldsLineOf76OctetsAfter75(): void
    {
        $name = str_repeat('n', 63);

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();
        $lines = $this->contentLines($ics);
        $index = array_search('X-WR-CALNAME:' . str_repeat('n', 62), $lines, true);

        self::assertNotFalse($index);
        self::assertSame(75, strlen($lines[$index]));
        self::assertSame(' n', $lines[$index + 1]);
        self::assertSame(2, strlen($lines[$index + 1]));
    }

    public function testDoesNotSplitUtf8CharacterWhenFolding(): void
    {
        $name = str_repeat('n', 61) . 'ä';

        $ics = (new VCalendar(Method::PUBLISH, $name, 'id'))->render();
        $lines = $this->contentLines($ics);
        $index = array_search('X-WR-CALNAME:' . str_repeat('n', 61), $lines, true);

        self::assertNotFalse($index);
        self::assertTrue(mb_check_encoding($lines[$index], 'UTF-8'));
        self::assertTrue(mb_check_encoding($lines[$index + 1], 'UTF-8'));
        self::assertSame(' ä', $lines[$index + 1]);
        self::assertSame('X-WR-CALNAME:' . $name, $this->propertyLine($ics, 'X-WR-CALNAME'));
    }

    public function testEveryFoldedLineStaysWithin75Octets(): void
    {
        $ics = (new VCalendar(
            Method::PUBLISH,
            'calendar name',
            'id',
            $this->event([
                'description' => str_repeat('Beschreibung mit Umlauten äöüß und Sonderzeichen; Komma. ', 8),
            ])
        ))->render();

        $oversize = [];
        foreach ($this->contentLines($ics) as $line) {
            if (strlen($line) > 75) {
                $oversize[] = strlen($line) . ':' . $line;
            }
        }

        self::assertSame([], $oversize);
    }
}
