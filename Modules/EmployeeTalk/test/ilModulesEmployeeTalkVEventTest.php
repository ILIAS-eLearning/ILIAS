<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\EmployeeTalk\Service\VEvent;
use ILIAS\EmployeeTalk\Service\VEventStatus;

class ilModulesEmployeeTalkVEventTest extends TestCase
{
    public function testVEventRenderingWithValidDataWhichShouldSucceed() : void
    {
        $expectedStart = "BEGIN:VEVENT\r\n";
        $expectedStart .= "UID: unique-id-of-some-sort\r\n";
        $expectedStart .= "DESCRIPTION:test description\r\n";
        $expectedStart .= "DTSTART;TZID=Europe/Paris:19700101T000010\r\n";
        $expectedStart .= "DTEND;TZID=Europe/Paris:19700101T000020\r\n";
        // Timestamps in between which breaks the test because they are changing
        $expectedEnd = "ORGANIZER;CN=\"organiser-name\":MAILTO:org@anizer.local\r\n";
        $expectedEnd .= "ATTENDEE;CN=\"attendee-name\";ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:at@tendee.local\r\n";
        $expectedEnd .= "SUMMARY:event summery\r\n";
        $expectedEnd .= "LOCATION:Bern\r\n";
        $expectedEnd .= "SEQUENCE:1\r\n";
        $expectedEnd .= "PRIORITY:5\r\n";
        $expectedEnd .= "STATUS:CONFIRMED\r\n";
        $expectedEnd .= "TRANSP:OPAQUE\r\n";
        $expectedEnd .= "X-MICROSOFT-CDO-BUSYSTATUS:BUSY\r\n";
        $expectedEnd .= "CLASS:PUBLIC\r\n";
        $expectedEnd .= "X-MICROSOFT-DISALLOW-COUNTER:TRUE\r\n";
        $expectedEnd .= "BEGIN:VALARM\r\n";
        $expectedEnd .= "DESCRIPTION:event summery\r\n";
        $expectedEnd .= "TRIGGER:-PT15M\r\n";
        $expectedEnd .= "ACTION:DISPLAY\r\n";
        $expectedEnd .= "END:VALARM\r\n";
        $expectedEnd .= "END:VEVENT\r\n";
        
        $subject = new VEvent(
            "unique-id-of-some-sort",
            "test description",
            "event summery",
            1,
            VEventStatus::CONFIRMED,
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

        self::assertStringStartsWith($expectedStart, $result);
        self::assertStringEndsWith($expectedEnd, $result);
    }
}
