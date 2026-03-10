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

use ILIAS\Refinery\Factory;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;

class ilMailMimeTest extends ilMailBaseTestCase
{
    private const int USER_ID = 6;

    protected function setUp(): void
    {
        ilMimeMail::setDefaultTransport(null);
        ilMailMimeSenderUserById::addUserToCache(self::USER_ID, $this->getUserById(self::USER_ID));

        parent::setUp();
    }

    public function testMimMailDelegatesEmailDeliveryToThePassedTransporter(): void
    {
        $default_transport = $this->getMockBuilder(ilMailMimeTransport::class)->disableOriginalConstructor()->getMock();
        $default_transport->expects($this->never())->method('send');

        $transport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $transport->expects($this->once())->method('send');

        $transport_factory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transport_factory->method('getTransport')->willReturn($default_transport);
        $this->setGlobalVariable('mail.mime.transport.factory', $transport_factory);

        $refinery = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $refinery = $this->getMockBuilder(\ILIAS\Refinery\Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $mail = new ilMimeMail();
        $mail->Send($transport);
    }

    public function testMimMailDelegatesEmailDeliveryToDefaultTransport(): void
    {
        $default_transport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $default_transport->expects($this->once())->method('send');

        $transport_factory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transport_factory->method('getTransport')->willReturn($default_transport);
        $this->setGlobalVariable('mail.mime.transport.factory', $transport_factory);

        $refinery = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $refinery = $this->getMockBuilder(\ILIAS\Refinery\Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refinery);

        $mail = new ilMimeMail();
        $mail->Send();
    }

    public function testTransportFactoryWillReturnNullTransportIfExternalEmailDeliveryIsDisabled(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $settings->method('get')->willReturnCallback(static function ($key): ?string {
            return (string) ('mail_allow_external' !== $key);
        });
        $this->setGlobalVariable('ilSetting', $settings);

        $event_handler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $event_handler);
        $this->assertInstanceOf(ilMailMimeTransportNull::class, $factory->getTransport());
    }

    public function testTransportFactoryWillReturnSmtpTransportIfEnabled(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $settings->method('get')->willReturnCallback(static fn($key): ?string => '1');
        $this->setGlobalVariable('ilSetting', $settings);

        $event_handler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $event_handler);
        $this->assertInstanceOf(ilMailMimeTransportSmtp::class, $factory->getTransport());
    }

    public function testTransportFactoryWillReturnSendmailTransportIfSmtpTransportIsDisabled(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();

        $settings->method('get')->willReturnCallback(static function ($key): ?string {
            if ('mail_allow_external' === $key) {
                return '1';
            }


            if ('mail_smtp_status' === $key) {
                return '0';
            }

            return '1';
        });
        $this->setGlobalVariable('ilSetting', $settings);

        $event_handler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $event_handler);
        $this->assertInstanceOf(ilMailMimeTransportSendmail::class, $factory->getTransport());
    }

    public function testFactoryWillReturnSystemSenderForAnonymousUserId(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $template_engine_factory = $this->createMock(TemplateEngineFactoryInterface::class);

        $factory = new ilMailMimeSenderFactory($settings, $template_engine_factory);
        $this->assertInstanceOf(ilMailMimeSenderSystem::class, $factory->getSenderByUsrId(ANONYMOUS_USER_ID));
    }

    public function testFactoryWillReturnSystemSenderWhenExplicitlyRequested(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $template_engine_factory = $this->createMock(TemplateEngineFactoryInterface::class);

        $factory = new ilMailMimeSenderFactory($settings, $template_engine_factory);
        $this->assertInstanceOf(ilMailMimeSenderSystem::class, $factory->system());
    }

    protected function getUserById(int $usr_id): ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn($usr_id);

        return $user;
    }

    public function testFactoryWillReturnUserSenderForExistingUserId(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $template_engine_factory = $this->createMock(TemplateEngineFactoryInterface::class);

        $factory = new ilMailMimeSenderFactory($settings, $template_engine_factory);
        $this->assertInstanceOf(ilMailMimeSenderUser::class, $factory->getSenderByUsrId(self::USER_ID));
    }

    public function testFactoryWillReturnUserSenderWhenExplicitlyRequested(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $template_engine_factory = $this->createMock(TemplateEngineFactoryInterface::class);

        $factory = new ilMailMimeSenderFactory($settings, $template_engine_factory);
        $this->assertInstanceOf(ilMailMimeSenderUser::class, $factory->user(self::USER_ID));
    }
}
