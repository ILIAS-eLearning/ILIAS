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

class VCalendar
{
    protected string $name;
    protected string $uid;

    /**
     * @var VEvent[]
     */
    protected array $events;
    protected Method $method;

    public function __construct(Method $method, string $name, string $uid, VEvent ...$events)
    {
        $this->name = $name;
        $this->uid = $uid;
        $this->events = $events;
        $this->method = $method;
    }

    public function render(): string
    {
        return $this->foldContent(
            'BEGIN:VCALENDAR' . "\r\n" .
            'PRODID:-//ILIAS' . "\r\n" .
            'VERSION:2.0' . "\r\n" .
            'UID:' . $this->escapeText($this->uid) . "\r\n" .
            'X-WR-RELCALID:' . $this->escapeText($this->uid) . "\r\n" .
            'NAME:' . $this->escapeText($this->name) . "\r\n" .
            'X-WR-CALNAME:' . $this->escapeText($this->name) . "\r\n" .
            'LAST-MODIFIED:' . gmdate('Ymd\THis\Z') . "\r\n" .
            'METHOD:' . $this->method->value . "\r\n" .
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

            'END:VCALENDAR' . "\r\n"
        );
    }

    private function renderVEvents(): string
    {
        $eventString = '';
        foreach ($this->events as $event) {
            $eventString .= $event->render();
        }

        return $eventString;
    }

    private function escapeText(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n'],
            $text
        );
    }

    private function foldContent(string $content): string
    {
        $folded = '';
        foreach (preg_split("/\r\n/", rtrim($content, "\r\n")) as $line) {
            $folded .= $this->foldLine($line);
        }

        return $folded;
    }

    private function foldLine(string $line): string
    {
        $result = '';
        $first = true;
        while ($line !== '') {
            $max_octets = $first ? 75 : 74;
            $chunk = $this->takeUtf8Octets($line, $max_octets);
            $result .= ($first ? '' : ' ') . $chunk . "\r\n";
            $line = substr($line, \strlen($chunk));
            $first = false;
        }

        return $result;
    }

    private function takeUtf8Octets(string $value, int $max_octets): string
    {
        if (\strlen($value) <= $max_octets) {
            return $value;
        }

        $chunk = substr($value, 0, $max_octets);
        while ($chunk !== '' && (\ord($chunk[\strlen($chunk) - 1]) & 0xC0) === 0x80) {
            $chunk = substr($chunk, 0, -1);
        }
        if ($chunk !== '' && \ord($chunk[\strlen($chunk) - 1]) >= 0xC0) {
            $chunk = substr($chunk, 0, -1);
        }

        return $chunk !== '' ? $chunk : $value[0];
    }
}
