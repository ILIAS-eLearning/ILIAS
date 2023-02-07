<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ilMailOptionsTest extends ilMailBaseTest
{
    /** @var MockObject */
    protected $setting;
    /** @var sdtClass */
    protected $object;

    protected function setUp() : void
    {
        parent::setUp();

        $this->database = $this->getMockBuilder(ilDBInterface::class)
                         ->getMock();
        $queryMock = $this->getMockBuilder(ilDBStatement::class)
                          ->getMock();

        $this->object = new stdClass();
        $this->object->cronjob_notification = false;
        $this->object->signature = 'smth';
        $this->object->linebreak = 0;
        $this->object->incoming_type = 1;
        $this->object->mail_address_option = 0;
        $this->object->email = 'test@test.com';
        $this->object->second_email = 'ilias@ilias.com';

        $this->database->expects($this->once())->method('fetchObject')->willReturn($this->object);
        $this->database->expects($this->once())->method('queryF')->willReturn($queryMock);
        $this->database->method('replace')->willReturn(0);
        $this->setGlobalVariable('ilDB', $this->database);

        $this->settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
    }

    public function testConstructor() : void
    {
        $this->settings->expects($this->exactly(3))->method('get')->willReturnMap(
            [
                ['mail_incoming_mail', '', ''],
                ['mail_address_option', '', ''],
                ['show_mail_settings', false, '0']
            ]
        );
        $this->setGlobalVariable('ilSetting', $this->settings);

        $mailOptions = new ilMailOptions(1);

        $this->assertSame('', $mailOptions->getSignature());
        $this->assertSame(ilMailOptions::INCOMING_LOCAL, $mailOptions->getIncomingType());
        $this->assertSame(ilMailOptions::DEFAULT_LINE_BREAK, $mailOptions->getLinebreak());
        $this->assertFalse($mailOptions->isCronJobNotificationEnabled());
    }

    public function testConstructorWithUserSettings() : void
    {
        $this->settings->expects($this->exactly(3))->method('get')->willReturnMap(
                    [
                        ['mail_incoming_mail', '', ''],
                        ['mail_address_option', '', ''],
                        ['show_mail_settings', false, '1']
                    ]
                );
        $this->setGlobalVariable('ilSetting', $this->settings);

        $mailOptions = new ilMailOptions(1);

        $this->assertSame($this->object->signature, $mailOptions->getSignature());
        $this->assertSame($this->object->incoming_type, $mailOptions->getIncomingType());
        $this->assertSame($this->object->linebreak, $mailOptions->getLinebreak());
        $this->assertSame($this->object->cronjob_notification, $mailOptions->isCronJobNotificationEnabled());
    }
}
