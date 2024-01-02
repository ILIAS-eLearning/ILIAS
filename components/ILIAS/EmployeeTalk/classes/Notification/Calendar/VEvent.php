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
        string $location
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
    }

    protected function renderStartAndEndDates(): string
    {
        // creating DateTimes from Unix timestamps automatically sets the initial timezone to UTC
        $start = new \DateTimeImmutable('@' . $this->start_time);
        $start = $start->setTimezone(new \DateTimeZone('Europe/Paris'));
        $end = new \DateTimeImmutable('@' . $this->end_time);
        $end = $end->setTimezone(new \DateTimeZone('Europe/Paris'));

        if ($this->all_day) {
            return  'DTSTART;TZID=Europe/Paris;VALUE=DATE:' . $start->format('Ymd') . "\r\n" .
                    'DTEND;TZID=Europe/Paris;VALUE=DATE:' . $end->format('Ymd') . "\r\n" .
                    "X-MICROSOFT-CDO-ALLDAYEVENT: TRUE\r\n";
        } else {
            return  'DTSTART;TZID=Europe/Paris:' . $start->format('Ymd\THis') . "\r\n" .
                    'DTEND;TZID=Europe/Paris:' . $end->format('Ymd\THis') . "\r\n";
        }
    }

    public function render(): string
    {
        return 'BEGIN:VEVENT' . "\r\n" .
        'UID: ' . $this->uid . "\r\n" .
        'DESCRIPTION:' . $this->description . "\r\n" .
        $this->renderStartAndEndDates() .
        'DTSTAMP:' . date("Ymd\THis") . "\r\n" .
        'LAST-MODIFIED:' . date("Ymd\THis") . "\r\n" .
        'ORGANIZER;CN="' . $this->organiser_name . '":MAILTO:' . $this->organiser_email . "\r\n" .
        'ATTENDEE;CN="' . $this->attendee_name . '";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:' . $this->attendee_email . "\r\n" .
        'SUMMARY:' . $this->summary . "\r\n" .
        'LOCATION:' . $this->location . "\r\n" .
        'SEQUENCE:' . $this->sequence . "\r\n" .
        "PRIORITY:5\r\n" .
        'STATUS:' . $this->status->value . "\r\n" .
        "TRANSP:OPAQUE\r\n" .
        "X-MICROSOFT-CDO-BUSYSTATUS:BUSY\r\n" .
        'CLASS:PUBLIC' . "\r\n" .
        "X-MICROSOFT-DISALLOW-COUNTER:TRUE\r\n" .
        //'URL:'. $this->url . "\r\n" .

        'BEGIN:VALARM' . "\r\n" .
        'DESCRIPTION:' . $this->summary . "\r\n" .
        'TRIGGER:-PT15M' . "\r\n" .
        'ACTION:DISPLAY' . "\r\n" .
        'END:VALARM' . "\r\n" .

        'END:VEVENT' . "\r\n";
    }
}
