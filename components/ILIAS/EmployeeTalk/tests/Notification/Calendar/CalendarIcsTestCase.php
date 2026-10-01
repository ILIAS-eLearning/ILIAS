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

use PHPUnit\Framework\TestCase;

abstract class CalendarIcsTestCase extends TestCase
{
    protected function generated_at(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-09-25 12:34:56', new \DateTimeZone('UTC'));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function renderEvent(array $overrides = []): string
    {
        $values = array_merge(
            [
                'uid' => 'talk-42@ilias.example',
                'description' => 'test description',
                'summary' => 'event summary',
                'sequence' => 1,
                'status' => EventStatus::CONFIRMED,
                'organiserName' => 'organiser-name',
                'organiserEmail' => 'org@anizer.local',
                'attendeeName' => 'attendee-name',
                'attendeeEmail' => 'at@tendee.local',
                'startTime' => 10,
                'endTime' => 20,
                'allDay' => false,
                'url' => '',
                'location' => 'Bern',
                'generated_at' => $this->generated_at(),
            ],
            $overrides
        );

        return (new VEvent(
            $values['uid'],
            $values['description'],
            $values['summary'],
            $values['sequence'],
            $values['status'],
            $values['organiserName'],
            $values['organiserEmail'],
            $values['attendeeName'],
            $values['attendeeEmail'],
            $values['startTime'],
            $values['endTime'],
            $values['allDay'],
            $values['url'],
            $values['location'],
            $values['generated_at']
        ))->render();
    }

    /**
     * @return list<string>
     */
    protected function contentLines(string $ics): array
    {
        if (!str_ends_with($ics, "\r\n")) {
            self::fail('RFC 5545 requires every content line to end with CRLF.');
        }

        return explode("\r\n", substr($ics, 0, -2));
    }

    protected function unfolded(string $ics): string
    {
        return str_replace(["\r\n ", "\r\n\t"], '', $ics);
    }

    protected function propertyLine(string $ics, string $name): string
    {
        foreach ($this->contentLines($this->unfolded($ics)) as $line) {
            if (str_starts_with($line, $name . ':') || str_starts_with($line, $name . ';')) {
                return $line;
            }
        }

        self::fail("Missing RFC property {$name}.");
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function event(array $overrides = []): VEvent
    {
        $values = array_merge(
            [
                'uid' => 'talk-42@ilias.example',
                'description' => 'desc',
                'summary' => 'summary',
                'sequence' => 0,
                'status' => EventStatus::CONFIRMED,
                'organiserName' => 'organiser-name',
                'organiserEmail' => 'org@anizer.local',
                'attendeeName' => 'attendee-name',
                'attendeeEmail' => 'at@tendee.local',
                'startTime' => 10,
                'endTime' => 20,
                'allDay' => false,
                'url' => '',
                'location' => 'Bern',
                'generated_at' => $this->generated_at(),
            ],
            $overrides
        );

        return new VEvent(
            $values['uid'],
            $values['description'],
            $values['summary'],
            $values['sequence'],
            $values['status'],
            $values['organiserName'],
            $values['organiserEmail'],
            $values['attendeeName'],
            $values['attendeeEmail'],
            $values['startTime'],
            $values['endTime'],
            $values['allDay'],
            $values['url'],
            $values['location'],
            $values['generated_at']
        );
    }
}
