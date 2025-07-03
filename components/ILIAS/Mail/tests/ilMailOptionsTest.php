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

use ILIAS\Data\Clock\ClockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;

class ilMailOptionsTest extends ilMailBaseTestCase
{
    protected stdClass $object;
    protected MockObject&ilDBInterface $database;
    protected MockObject&ilSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database = $this->getMockBuilder(ilDBInterface::class)
                               ->getMock();
        $query_mock = $this->getMockBuilder(ilDBStatement::class)
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


        $this->database->expects($this->once())->method('queryF')->willReturn($query_mock);
        $this->database->expects($this->once())->method('fetchObject')->willReturn($this->object);
        $this->database->method('replace')->willReturn(0);
        $this->setGlobalVariable('ilDB', $this->database);
    }

    public function testConstructor(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'mail_incoming_mail' || $key === 'mail_address_option') {
                return $default;
            }

            if ($key === 'show_mail_settings') {
                return '0';
            }

            return $default;
        });

        $mail_options = new ilMailOptions(
            1,
            null,
            $this->createMock(ClockInterface::class),
            $settings
        );

        $this->assertSame('', $mail_options->getSignature());
        $this->assertSame(ilMailOptions::INCOMING_LOCAL, $mail_options->getIncomingType());
        $this->assertFalse($mail_options->isCronJobNotificationEnabled());
    }

    public function testConstructorWithUserSettings(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'mail_incoming_mail' || $key === 'mail_address_option') {
                return $default;
            }

            if ($key === 'show_mail_settings') {
                return '1';
            }

            if ($key === 'usr_settings_disable_mail_incoming_mail') {
                return '0';
            }

            return $default;
        });

        $mail_options = new ilMailOptions(
            1,
            null,
            $this->createMock(ClockInterface::class),
            $settings
        );

        $this->assertSame($this->object->signature, $mail_options->getSignature());
        $this->assertSame($this->object->incoming_type, $mail_options->getIncomingType());
        $this->assertSame($this->object->cronjob_notification, $mail_options->isCronJobNotificationEnabled());
        $this->assertSame($this->object->absence_status, $mail_options->getAbsenceStatus());
        $this->assertSame($this->object->absent_from, $mail_options->getAbsentFrom());
        $this->assertSame($this->object->absent_until, $mail_options->getAbsentUntil());
        $this->assertSame($this->object->absence_ar_subject, $mail_options->getAbsenceAutoresponderSubject());
        $this->assertSame($this->object->absence_ar_body, $mail_options->getAbsenceAutoresponderBody());
    }

    #[DataProvider('provideMailOptionsData')]
    public function testIsAbsent(bool $absence_status, int $absent_from, int $absent_until, bool $result): void
    {
        $usr_id = 1;
        $this->object->absence_status = $absence_status;
        $this->object->absent_from = $absent_from;
        $this->object->absent_until = $absent_until;
        $this->object->absence_ar_subject = 'subject';
        $this->object->absence_ar_body = 'body';

        $cloc_service = $this->createMock(ClockInterface::class);
        $cloc_service->method('now')->willReturn((new DateTimeImmutable())->setTimestamp(100));

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $settings->method('get')->willReturnCallback(static function (string $key, ?string $default = null) {
            if ($key === 'mail_incoming_mail' || $key === 'mail_address_option') {
                return $default;
            }

            if ($key === 'show_mail_settings') {
                return '1';
            }

            if ($key === 'usr_settings_disable_mail_incoming_mail') {
                return '0';
            }

            return $default;
        });

        $mail_options = new ilMailOptions(
            $usr_id,
            null,
            $cloc_service,
            $settings
        );

        $this->assertEquals($result, $mail_options->isAbsent());
    }

    public static function provideMailOptionsData(): Generator
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
