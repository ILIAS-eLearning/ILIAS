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

namespace ILIAS\EmployeeTalk\Service;

final class VEvent
{
    /**
     * Unique id of the event
     * @var string $uid
     */
    private string $uid;
    private string $description;
    private string $summary;
    /**
     * Must be higher then the previous one, or the cal client will ignore the change
     * @var int $sequence
     */
    private int $sequence;
    /**
     * @var string $status
     *
     * @see VEventStatus
     */
    private string $status;
    private string $organiserName;
    private string $organiserEmail;
    private string $attendeeName;
    private string $attendeeEmail;
    private int $startTime;
    private int $endTime;
    private bool $allDay;
    private string $url;
    private string $location;

    /**
     * VEvent constructor.
     * @param string $uid
     * @param string $description
     * @param string $summary
     * @param int    $sequence
     * @param string $status
     * @param string $organiserName
     * @param string $organiserEmail
     * @param string $attendeeName
     * @param string $attendeeEmail
     * @param int    $startTime
     * @param int    $endTime
     * @param bool   $allDay
     * @param string $url
     * @param string $location
     */
    public function __construct(
        string $uid,
        string $description,
        string $summary,
        int $sequence,
        string $status,
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
        $this->organiserName = $organiserName;
        $this->organiserEmail = $organiserEmail;
        $this->attendeeName = $attendeeName;
        $this->attendeeEmail = $attendeeEmail;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->allDay = $allDay;
        $this->url = $url;
        $this->location = $location;
    }

    private function getStartAndEnd(): string
    {
        if ($this->allDay) {
            return  'DTSTART;TZID=Europe/Paris;VALUE=DATE:' . date("Ymd", $this->startTime) . "\r\n" .
                    'DTEND;TZID=Europe/Paris;VALUE=DATE:' . date("Ymd", $this->endTime) . "\r\n" .
                    "X-MICROSOFT-CDO-ALLDAYEVENT: TRUE\r\n";
        } else {
            return  'DTSTART;TZID=Europe/Paris:' . date("Ymd\THis", $this->startTime) . "\r\n" .
                    'DTEND;TZID=Europe/Paris:' . date("Ymd\THis", $this->endTime) . "\r\n";
        }
    }

    public function render(): string
    {
        return 'BEGIN:VEVENT' . "\r\n" .
        'UID: ' . $this->uid . "\r\n" .
        'DESCRIPTION:' . $this->description . "\r\n" .
        $this->getStartAndEnd() .
        'DTSTAMP:' . date("Ymd\THis") . "\r\n" .
        'LAST-MODIFIED:' . date("Ymd\THis") . "\r\n" .
        'ORGANIZER;CN="' . $this->organiserName . '":MAILTO:' . $this->organiserEmail . "\r\n" .
        'ATTENDEE;CN="' . $this->attendeeName . '";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:' . $this->attendeeEmail . "\r\n" .
        'SUMMARY:' . $this->summary . "\r\n" .
        'LOCATION:' . $this->location . "\r\n" .
        'SEQUENCE:' . $this->sequence . "\r\n" .
        "PRIORITY:5\r\n" .
        'STATUS:' . $this->status . "\r\n" .
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
