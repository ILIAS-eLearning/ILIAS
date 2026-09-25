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

    public function testUidHasNoSpaceAfterColon(): void
    {
        $ics = $this->renderEvent(['uid' => 'talk-42@ilias.example']);

        self::assertSame('UID:talk-42@ilias.example', $this->propertyLine($ics, 'UID'));
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
    }

    public function testTimedEventUsesTzidDateTimeWithoutUtcZ(): void
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
    }

    public function testAllDayEventUsesDateValuesAndExclusiveEnd(): void
    {
        $ics = $this->renderEvent(['startTime' => 0, 'endTime' => 0, 'allDay' => true]);

        self::assertSame('DTSTART;VALUE=DATE:19700101', $this->propertyLine($ics, 'DTSTART'));
        self::assertSame('DTEND;VALUE=DATE:19700102', $this->propertyLine($ics, 'DTEND'));
    }

    public function testAllDayEventMustNotCombineTzidWithDate(): void
    {
        $ics = $this->renderEvent(['allDay' => true, 'startTime' => 0, 'endTime' => 0]);

        self::assertStringNotContainsString('TZID=', $this->propertyLine($ics, 'DTSTART'));
        self::assertStringNotContainsString('TZID=', $this->propertyLine($ics, 'DTEND'));
    }

    public function testAllDayEventEmitsMicrosoftAllDayHintWithoutSpaceAfterColon(): void
    {
        $ics = $this->renderEvent(['allDay' => true, 'startTime' => 0, 'endTime' => 0]);

        self::assertSame(
            'X-MICROSOFT-CDO-ALLDAYEVENT:TRUE',
            $this->propertyLine($ics, 'X-MICROSOFT-CDO-ALLDAYEVENT')
        );
    }

    public function testTimedEventOmitsMicrosoftAllDayHint(): void
    {
        $ics = $this->renderEvent(['allDay' => false]);

        self::assertStringNotContainsString('X-MICROSOFT-CDO-ALLDAYEVENT', $ics);
    }

    public function testPublishEventOmitsAttendee(): void
    {
        $ics = $this->renderEvent(['attendeeEmail' => 'at@tendee.local']);

        self::assertStringNotContainsString('ATTENDEE', $ics);
    }

    public function testOrganizerUsesMailtoWhenEmailIsPresent(): void
    {
        $ics = $this->renderEvent([
            'organiserName' => 'organiser-name',
            'organiserEmail' => 'org@anizer.local',
        ]);

        self::assertSame(
            'ORGANIZER;CN="organiser-name":mailto:org@anizer.local',
            $this->propertyLine($ics, 'ORGANIZER')
        );
    }

    public function testOmitsOrganizerWhenEmailIsEmpty(): void
    {
        $ics = $this->renderEvent(['organiserEmail' => '']);

        self::assertStringNotContainsString('ORGANIZER', $ics);
        self::assertStringNotContainsString('mailto:', $ics);
    }

    public function testEscapesQuotedOrganizerName(): void
    {
        $ics = $this->renderEvent(['organiserName' => 'Ann "The Boss"']);

        self::assertSame(
            'ORGANIZER;CN="Ann \\"The Boss\\"":mailto:org@anizer.local',
            $this->propertyLine($ics, 'ORGANIZER')
        );
    }

    public function testEscapesBackslashInText(): void
    {
        $ics = $this->renderEvent(['location' => 'Room A\\B']);

        self::assertSame('LOCATION:Room A\\\\B', $this->propertyLine($ics, 'LOCATION'));
    }

    public function testEscapesSemicolonInText(): void
    {
        $ics = $this->renderEvent(['location' => 'Room; A']);

        self::assertSame('LOCATION:Room\\; A', $this->propertyLine($ics, 'LOCATION'));
    }

    public function testEscapesCommaInText(): void
    {
        $ics = $this->renderEvent(['summary' => 'Talk, Q3']);

        self::assertSame('SUMMARY:Talk\\, Q3', $this->propertyLine($ics, 'SUMMARY'));
    }

    public function testEscapesNewlineInTextAsLiteralN(): void
    {
        $ics = $this->renderEvent(['description' => "Title: Talk\nLocation: Room"]);

        self::assertSame(
            'DESCRIPTION:Title: Talk\\nLocation: Room',
            $this->propertyLine($ics, 'DESCRIPTION')
        );
    }

    public function testPreservesAlreadyEscapedIcalNewlines(): void
    {
        $ics = $this->renderEvent(['description' => 'Title: Talk' . '\n' . 'Location: Room']);

        self::assertSame(
            'DESCRIPTION:Title: Talk\\nLocation: Room',
            $this->propertyLine($ics, 'DESCRIPTION')
        );
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

    public function testMicrosoftBusyAndCounterHintsUseBooleanTrueWithoutSpaces(): void
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
        $ics = $this->renderEvent(['summary' => 'event summary']);
        $lines = $this->contentLines($ics);
        $alarm_start = array_search('BEGIN:VALARM', $lines, true);
        $alarm_end = array_search('END:VALARM', $lines, true);

        self::assertIsInt($alarm_start);
        self::assertIsInt($alarm_end);
        self::assertSame(
            [
                'BEGIN:VALARM',
                'DESCRIPTION:event summary',
                'TRIGGER:-PT15M',
                'ACTION:DISPLAY',
                'END:VALARM',
            ],
            array_slice($lines, $alarm_start, $alarm_end - $alarm_start + 1)
        );
    }
}
