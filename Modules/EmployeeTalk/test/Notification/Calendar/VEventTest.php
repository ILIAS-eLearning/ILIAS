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

namespace ILIAS\EmployeeTalk\Notification\Calendar;

use PHPUnit\Framework\TestCase;

class VEventTest extends TestCase
{
    public function testVEventRenderingWithValidDataWhichShouldSucceed(): void
    {
        $expected_start = "BEGIN:VEVENT\r\n";
        $expected_start .= "UID: unique-id-of-some-sort\r\n";
        $expected_start .= "DESCRIPTION:test description\r\n";
        $expected_start .= "DTSTART;TZID=Europe/Paris:19700101T000010\r\n";
        $expected_start .= "DTEND;TZID=Europe/Paris:19700101T000020\r\n";
        // Timestamps in between which breaks the test because they are changing
        $expected_end = "ORGANIZER;CN=\"organiser-name\":MAILTO:org@anizer.local\r\n";
        $expected_end .= "ATTENDEE;CN=\"attendee-name\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:at@tendee.local\r\n";
        $expected_end .= "SUMMARY:event summery\r\n";
        $expected_end .= "LOCATION:Bern\r\n";
        $expected_end .= "SEQUENCE:1\r\n";
        $expected_end .= "PRIORITY:5\r\n";
        $expected_end .= "STATUS:CONFIRMED\r\n";
        $expected_end .= "TRANSP:OPAQUE\r\n";
        $expected_end .= "X-MICROSOFT-CDO-BUSYSTATUS:BUSY\r\n";
        $expected_end .= "CLASS:PUBLIC\r\n";
        $expected_end .= "X-MICROSOFT-DISALLOW-COUNTER:TRUE\r\n";
        $expected_end .= "BEGIN:VALARM\r\n";
        $expected_end .= "DESCRIPTION:event summery\r\n";
        $expected_end .= "TRIGGER:-PT15M\r\n";
        $expected_end .= "ACTION:DISPLAY\r\n";
        $expected_end .= "END:VALARM\r\n";
        $expected_end .= "END:VEVENT\r\n";

        $subject = new VEvent(
            "unique-id-of-some-sort",
            "test description",
            "event summery",
            1,
            EventStatus::CONFIRMED,
            "organiser-name",
            "org@anizer.local",
            "attendee-name",
            "at@tendee.local",
            10,
            20,
            false,
            'https://ilias.de',
            'Bern'
        );

        $result = $subject->render();

        $this->assertStringStartsWith($expected_start, $result);
        $this->assertStringEndsWith($expected_end, $result);
    }
}
