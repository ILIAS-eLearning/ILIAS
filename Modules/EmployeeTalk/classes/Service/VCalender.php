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

final class VCalender
{
    private string $name;

    /**
     * Calendar UID
     *
     * @var string $uid
     */
    private string $uid;

    /**
     * @var VEvent[] $events
     */
    private array $events;

    /**
     * @var string $method
     *
     * @see VCalenderMethod
     */
    private string $method;

    /**
     * VCalender constructor.
     * @param string   $name
     * @param string   $uid
     * @param VEvent[] $events
     * @param string   $method
     */
    public function __construct(string $name, string $uid, array $events, string $method)
    {
        $this->name = $name;
        $this->uid = $uid;
        $this->events = $events;
        $this->method = $method;
    }

    public function render() : string
    {
        return 'BEGIN:VCALENDAR' . "\r\n" .
            'PRODID:-//ILIAS' . "\r\n" .
            'VERSION:2.0' . "\r\n" .
            'UID:' . $this->uid . "\r\n" .
            'X-WR-RELCALID:' . $this->uid . "\r\n" .
            'NAME:' . $this->name . "\r\n" .
            'X-WR-CALNAME:' . $this->name . "\r\n" .
            'LAST-MODIFIED:' . date("Ymd\THis") . "\r\n" .
            'METHOD:' .$this->method. "\r\n" .
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

            $this->renderVEvents() .

            'END:VCALENDAR'. "\r\n";
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUid() : string
    {
        return $this->uid;
    }

    /**
     * @return VEvent[]
     */
    public function getEvents() : array
    {
        return $this->events;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    private function renderVEvents() : string
    {
        $eventString = "";
        foreach ($this->events as $event) {
            $eventString .= $event->render();
        }

        return $eventString;
    }
}
