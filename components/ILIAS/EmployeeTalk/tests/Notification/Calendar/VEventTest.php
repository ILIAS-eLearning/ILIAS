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

class VEventTest extends CalendarIcsTestCase
{
    public function testUsesCrlfLineEndings(): void
    {
        $ics = $this->renderEvent();

        self::assertSame(0, substr_count(str_replace("\r\n", '', $ics), "\n"));
        self::assertStringEndsWith("\r\n", $ics);
    }

    public function testDtstampAndLastModifiedAreFrozenUtcWithZ(): void
    {
        $ics = $this->renderEvent();

        self::assertSame('DTSTAMP:20260925T123456Z', $this->propertyLine($ics, 'DTSTAMP'));
        self::assertSame(
            'LAST-MODIFIED:20260925T123456Z',
            $this->propertyLine($ics, 'LAST-MODIFIED')
        );
    }

    public function testConvertsGeneratedAtToUtc(): void
    {
        $ics = $this->renderEvent([
            'generated_at' => new \DateTimeImmutable('2026-09-25 14:34:56', new \DateTimeZone('Europe/Paris')),
        ]);

        self::assertSame('DTSTAMP:20260925T123456Z', $this->propertyLine($ics, 'DTSTAMP'));
        self::assertSame(
            'LAST-MODIFIED:20260925T123456Z',
            $this->propertyLine($ics, 'LAST-MODIFIED')
        );
    }

    public function testUsesCurrentUtcWhenGeneratedAtIsOmitted(): void
    {
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $ics = $this->renderEvent(['generated_at' => null]);
        $after = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $stamp = \DateTimeImmutable::createFromFormat(
            'Ymd\THis\Z',
            substr($this->propertyLine($ics, 'DTSTAMP'), strlen('DTSTAMP:')),
            new \DateTimeZone('UTC')
        );
        self::assertInstanceOf(\DateTimeImmutable::class, $stamp);
        self::assertGreaterThanOrEqual($before->modify('-1 second'), $stamp);
        self::assertLessThanOrEqual($after->modify('+1 second'), $stamp);
        self::assertSame(
            'LAST-MODIFIED:' . $stamp->format('Ymd\THis\Z'),
            $this->propertyLine($ics, 'LAST-MODIFIED')
        );
    }

    public function testTimedEventUsesTzidDateTime(): void
    {
        $ics = $this->renderEvent(['startTime' => 10, 'endTime' => 20, 'allDay' => false]);

        self::assertSame(
            'DTSTART;TZID=Europe/Paris:19700101T010010',
            $this->propertyLine($ics, 'DTSTART')
        );
        self::assertSame(
            'DTEND;TZID=Europe/Paris:19700101T010020',
            $this->propertyLine($ics, 'DTEND')
        );
        self::assertStringNotContainsString('X-MICROSOFT-CDO-ALLDAYEVENT', $ics);
    }

    public function testAllDayEventUsesExclusiveDateWithoutTzid(): void
    {
        $ics = $this->renderEvent(['startTime' => 0, 'endTime' => 0, 'allDay' => true]);

        self::assertSame('DTSTART;VALUE=DATE:19700101', $this->propertyLine($ics, 'DTSTART'));
        self::assertSame('DTEND;VALUE=DATE:19700102', $this->propertyLine($ics, 'DTEND'));
        self::assertSame(
            'X-MICROSOFT-CDO-ALLDAYEVENT:TRUE',
            $this->propertyLine($ics, 'X-MICROSOFT-CDO-ALLDAYEVENT')
        );
    }

    public function testPublishEventOmitsAttendee(): void
    {
        $ics = $this->renderEvent();

        self::assertStringNotContainsString('ATTENDEE', $ics);
    }

    public function testOrganizerQuotesParameterAndKeepsComma(): void
    {
        $ics = $this->renderEvent([
            'organiserName' => 'Müller, Anna "Anni"\\B',
        ]);

        self::assertSame(
            'ORGANIZER;CN="Müller, Anna \\"Anni\\"\\\\B":mailto:org@anizer.local',
            $this->propertyLine($ics, 'ORGANIZER')
        );
    }

    public function testOmitsOrganizerWhenEmailIsEmpty(): void
    {
        $ics = $this->renderEvent(['organiserEmail' => '']);

        self::assertStringNotContainsString('ORGANIZER', $ics);
    }

    public function testEscapesTextValues(): void
    {
        $ics = $this->renderEvent([
            'uid' => 'id;x',
            'description' => 'A\\B; C, D' . "\r\n" . 'E' . "\n" . 'F' . '\n' . 'G' . '\N' . 'H',
            'location' => 'Room; 1',
        ]);

        self::assertSame('UID:id\\;x', $this->propertyLine($ics, 'UID'));
        self::assertSame(
            'DESCRIPTION:A\\\\B\\; C\\, D\\nE\\nF\\nG\\nH',
            $this->propertyLine($ics, 'DESCRIPTION')
        );
        self::assertSame('LOCATION:Room\\; 1', $this->propertyLine($ics, 'LOCATION'));
    }

    public function testSequenceAndStatusAreRenderedLiterally(): void
    {
        $ics = $this->renderEvent([
            'sequence' => 7,
            'status' => EventStatus::TENTATIVE,
        ]);

        self::assertSame('SEQUENCE:7', $this->propertyLine($ics, 'SEQUENCE'));
        self::assertSame('STATUS:TENTATIVE', $this->propertyLine($ics, 'STATUS'));
    }

    public function testMicrosoftBusyAndCounterHints(): void
    {
        $ics = $this->renderEvent();

        self::assertSame(
            'X-MICROSOFT-CDO-BUSYSTATUS:BUSY',
            $this->propertyLine($ics, 'X-MICROSOFT-CDO-BUSYSTATUS')
        );
        self::assertSame(
            'X-MICROSOFT-DISALLOW-COUNTER:TRUE',
            $this->propertyLine($ics, 'X-MICROSOFT-DISALLOW-COUNTER')
        );
    }

    public function testAlarmDescribesTheSummary(): void
    {
        $ics = $this->renderEvent(['summary' => 'Talk, Q3']);
        $lines = $this->contentLines($ics);
        $alarm_start = array_search('BEGIN:VALARM', $lines, true);

        self::assertSame(
            [
                'BEGIN:VALARM',
                'DESCRIPTION:Talk\\, Q3',
                'TRIGGER:-PT15M',
                'ACTION:DISPLAY',
                'END:VALARM',
            ],
            array_slice($lines, (int) $alarm_start, 5)
        );
        self::assertSame('SUMMARY:Talk\\, Q3', $this->propertyLine($ics, 'SUMMARY'));
    }
}
