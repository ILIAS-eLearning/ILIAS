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

class VEvent
{
    private const TIMEZONE = 'Europe/Paris';

    protected string $uid;
    protected string $description;
    protected string $summary;
    protected int $sequence;
    protected EventStatus $status;
    protected string $organiser_name;
    protected string $organiser_email;
    protected string $attendee_name;
    protected string $attendee_email;
    protected int $start_time;
    protected int $end_time;
    protected bool $all_day;
    protected string $url;
    protected string $location;
    protected \DateTimeImmutable $generated_at;

    public function __construct(
        string $uid,
        string $description,
        string $summary,
        int $sequence,
        EventStatus $status,
        string $organiserName,
        string $organiserEmail,
        string $attendeeName,
        string $attendeeEmail,
        int $startTime,
        int $endTime,
        bool $allDay,
        string $url,
        string $location,
        ?\DateTimeImmutable $generated_at = null
    ) {
        $this->uid = $uid;
        $this->description = $description;
        $this->summary = $summary;
        $this->sequence = $sequence;
        $this->status = $status;
        $this->organiser_name = $organiserName;
        $this->organiser_email = $organiserEmail;
        $this->attendee_name = $attendeeName;
        $this->attendee_email = $attendeeEmail;
        $this->start_time = $startTime;
        $this->end_time = $endTime;
        $this->all_day = $allDay;
        $this->url = $url;
        $this->location = $location;
        $this->generated_at = ($generated_at ?? new \DateTimeImmutable('now'))
            ->setTimezone(new \DateTimeZone('UTC'));
    }

    protected function renderStartAndEndDates(): string
    {
        $timezone = new \DateTimeZone(self::TIMEZONE);
        $start = (new \DateTimeImmutable('@' . $this->start_time))->setTimezone($timezone);
        $end = (new \DateTimeImmutable('@' . $this->end_time))->setTimezone($timezone);

        if ($this->all_day) {
            $end_exclusive = $end->modify('+1 day');

            return 'DTSTART;VALUE=DATE:' . $start->format('Ymd') . "\r\n" .
                'DTEND;VALUE=DATE:' . $end_exclusive->format('Ymd') . "\r\n" .
                "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n";
        }

        return 'DTSTART;TZID=' . self::TIMEZONE . ':' . $start->format('Ymd\THis') . "\r\n" .
            'DTEND;TZID=' . self::TIMEZONE . ':' . $end->format('Ymd\THis') . "\r\n";
    }

    public function render(): string
    {
        $stamp = $this->generated_at->format('Ymd\THis\Z');

        return 'BEGIN:VEVENT' . "\r\n" .
            'UID:' . $this->escapeText($this->uid) . "\r\n" .
            'DESCRIPTION:' . $this->escapeText($this->description) . "\r\n" .
            $this->renderStartAndEndDates() .
            'DTSTAMP:' . $stamp . "\r\n" .
            'LAST-MODIFIED:' . $stamp . "\r\n" .
            $this->renderOrganizer() .
            'SUMMARY:' . $this->escapeText($this->summary) . "\r\n" .
            'LOCATION:' . $this->escapeText($this->location) . "\r\n" .
            'SEQUENCE:' . $this->sequence . "\r\n" .
            "PRIORITY:5\r\n" .
            'STATUS:' . $this->status->value . "\r\n" .
            "TRANSP:OPAQUE\r\n" .
            "X-MICROSOFT-CDO-BUSYSTATUS:BUSY\r\n" .
            'CLASS:PUBLIC' . "\r\n" .
            "X-MICROSOFT-DISALLOW-COUNTER:TRUE\r\n" .
            'BEGIN:VALARM' . "\r\n" .
            'DESCRIPTION:' . $this->escapeText($this->summary) . "\r\n" .
            'TRIGGER:-PT15M' . "\r\n" .
            'ACTION:DISPLAY' . "\r\n" .
            'END:VALARM' . "\r\n" .
            'END:VEVENT' . "\r\n";
    }

    private function renderOrganizer(): string
    {
        if ($this->organiser_email === '') {
            return '';
        }

        return 'ORGANIZER;CN="' . $this->escapeQuoted($this->organiser_name) .
            '":mailto:' . $this->organiser_email . "\r\n";
    }

    private function escapeText(string $text): string
    {
        $newline = "\x00n\x00";

        return str_replace(
            ['\\n', '\\N', "\r\n", "\n", '\\', ';', ',', $newline],
            [$newline, $newline, $newline, $newline, '\\\\', '\\;', '\\,', '\\n'],
            $text
        );
    }

    private function escapeQuoted(string $text): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $text);
    }
}
