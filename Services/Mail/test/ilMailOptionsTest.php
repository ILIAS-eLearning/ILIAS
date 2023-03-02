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

use ILIAS\Data\Clock\ClockInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ilMailOptionsTest extends ilMailBaseTest
{
    protected MockObject $setting;
    protected stdClass $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->getMockBuilder(ilDBInterface::class)
                               ->getMock();
        $queryMock = $this->getMockBuilder(ilDBStatement::class)
                          ->getMock();

        $this->object = new stdClass();
        $this->object->cronjob_notification = false;
        $this->object->signature = 'smth';
        $this->object->incoming_type = 1;
        $this->object->mail_address_option = 0;
        $this->object->email = 'test@test.com';
        $this->object->second_email = 'ilias@ilias.com';
        $this->object->absence_status = false;
        $this->object->absent_from = time();
        $this->object->absent_until = time();
        $this->object->absence_ar_subject = 'subject';
        $this->object->absence_ar_body = 'body';


        $this->database->expects($this->once())->method('fetchObject')->willReturn($this->object);
        $this->database->expects($this->once())->method('queryF')->willReturn($queryMock);
        $this->database->method('replace')->willReturn(0);
        $this->setGlobalVariable('ilDB', $this->database);

        $this->settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
    }

    public function testConstructor(): void
    {
        $this->settings->expects($this->exactly(3))->method('get')->willReturnMap(
            [
                ['mail_incoming_mail', '', ''],
                ['mail_address_option', '', ''],
                ['show_mail_settings', null, '0']
            ]
        );
        $this->setGlobalVariable('ilSetting', $this->settings);

        $mailOptions = new ilMailOptions(1);

        $this->assertSame('', $mailOptions->getSignature());
        $this->assertSame(ilMailOptions::INCOMING_LOCAL, $mailOptions->getIncomingType());
        $this->assertFalse($mailOptions->isCronJobNotificationEnabled());
    }

    public function testConstructorWithUserSettings(): void
    {
        $this->settings->expects($this->exactly(3))->method('get')->willReturnMap(
            [
                ['mail_incoming_mail', '', ''],
                ['mail_address_option', '', ''],
                ['show_mail_settings', null, '1']
            ]
        );
        $this->setGlobalVariable('ilSetting', $this->settings);

        $mailOptions = new ilMailOptions(1);

        $this->assertSame($this->object->signature, $mailOptions->getSignature());
        $this->assertSame($this->object->incoming_type, $mailOptions->getIncomingType());
        $this->assertSame($this->object->cronjob_notification, $mailOptions->isCronJobNotificationEnabled());
        $this->assertSame($this->object->absence_status, $mailOptions->getAbsenceStatus());
        $this->assertSame($this->object->absent_from, $mailOptions->getAbsentFrom());
        $this->assertSame($this->object->absent_until, $mailOptions->getAbsentUntil());
        $this->assertSame($this->object->absence_ar_subject, $mailOptions->getAbsenceAutoresponderSubject());
        $this->assertSame($this->object->absence_ar_body, $mailOptions->getAbsenceAutoresponderBody());
    }

    /**
     * @dataProvider provideMailOptionsData
     */
    public function testIsAbsent(bool $absence_status, int $absent_from, int $absent_until, bool $result): void
    {
        $userId = 1;
        $this->object->absence_status = $absence_status;
        $this->object->absent_from = $absent_from;
        $this->object->absent_until = $absent_until;
        $this->object->absence_ar_subject = 'subject';
        $this->object->absence_ar_body = 'body';

        $clockService = $this->getMockBuilder(ClockInterface::class)->getMock();
        $clockService->method('now')->willReturn((new DateTimeImmutable())->setTimestamp(100));

        $this->settings->expects($this->exactly(3))->method('get')->willReturnMap(
            [
                ['mail_incoming_mail', '', ''],
                ['mail_address_option', '', ''],
                ['show_mail_settings', null, '1']
            ]
        );
        $this->setGlobalVariable('ilSetting', $this->settings);

        $mailOptions = new ilMailOptions(1, null, $clockService);
        $this->assertEquals($result, $mailOptions->isAbsent());
    }

    public function provideMailOptionsData(): Generator
    {
        yield 'correct configuration' => [
            'absence_status' => true,
            'absent_from' => 100,
            'absent_until' => 100,
            'result' => true,
        ];

        yield 'not absent' => [
            'absence_status' => false,
            'absent_from' => 100,
            'absent_until' => 100,
            'result' => false,
        ];

        yield 'absent, absent_from is in the future' => [
            'absence_status' => true,
            'absent_from' => 100 + 1,
            'absent_until' => 100,
            'result' => false,
        ];

        yield 'absent, absent_until is in the past' => [
            'absence_status' => true,
            'absent_from' => 100,
            'absent_until' => 100 - 1,
            'result' => false,
        ];

        yield 'absent, absent_from is in the past, absent_until is in the future' => [
            'absence_status' => true,
            'absent_from' => 100 - 1,
            'absent_until' => 100 + 1,
            'result' => true,
        ];
    }
}
