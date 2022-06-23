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

/**
 * Class ilMailOptionsTest
 * @author Niels Theen <ntheen@databay.de>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailOptionsTest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testConstructor(): void
    {
        $userId = 1;

        $database = $this->getMockBuilder(ilDBInterface::class)
            ->getMock();
        $queryMock = $this->getMockBuilder(ilDBStatement::class)
            ->getMock();

        $object = new stdClass();
        $object->cronjob_notification = false;
        $object->signature = 'smth';
        $object->linebreak = 0;
        $object->incoming_type = 1;
        $object->mail_address_option = 0;
        $object->email = 'test@test.com';
        $object->second_email = 'ilias@ilias.com';
        $object->absence_status = false;
        $object->absent_from = time();
        $object->absent_until = time();
        $object->absence_ar_subject = 'subject';
        $object->absence_ar_body = 'body';

        $database->expects($this->once())->method('fetchObject')->willReturn($object);
        $database->expects($this->once())->method('queryF')->willReturn($queryMock);

        $this->setGlobalVariable('ilDB', $database);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mailOptions = new ilMailOptions($userId);
        $this->assertSame($object->signature, $mailOptions->getSignature());
        $this->assertSame($object->incoming_type, $mailOptions->getIncomingType());
        $this->assertSame($object->linebreak, $mailOptions->getLinebreak());
        $this->assertSame($object->cronjob_notification, $mailOptions->isCronJobNotificationEnabled());
        $this->assertSame($object->absence_status, $mailOptions->getAbsenceStatus());
        $this->assertSame($object->absent_from, $mailOptions->getAbsentFrom());
        $this->assertSame($object->absent_until, $mailOptions->getAbsentUntil());
        $this->assertSame($object->absence_ar_subject, $mailOptions->getAbsenceAutoresponderSubject());
        $this->assertSame($object->absence_ar_body, $mailOptions->getAbsenceAutoresponderBody());
    }

    /**
     * @dataProvider provideMailOptionsData
     */
    public function testIsAbsent(bool $absence_status, int $absent_from, int $absent_until, bool $result): void
    {
        $userId = 1;
        $object = new stdClass();
        $object->cronjob_notification = false;
        $object->signature = 'smth';
        $object->linebreak = 0;
        $object->incoming_type = 1;
        $object->mail_address_option = 0;
        $object->email = 'test@test.com';
        $object->second_email = 'ilias@ilias.com';
        $object->absence_status = $absence_status;
        $object->absent_from = $absent_from;
        $object->absent_until = $absent_until;
        $object->absence_ar_subject = 'subject';
        $object->absence_ar_body = 'body';
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $queryMock = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $clockService = $this->getMockBuilder(ClockInterface::class)->getMock();
        $clockService->method('now')->willReturn((new DateTimeImmutable())->setTimestamp(100));

        $database->expects($this->once())->method('fetchObject')->willReturn($object);
        $database->expects($this->once())->method('queryF')->willReturn($queryMock);

        $this->setGlobalVariable('ilDB', $database);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mailOptions = new ilMailOptions(1, null, $clockService);
        $this->assertEquals($mailOptions->isAbsent(), $result);
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
