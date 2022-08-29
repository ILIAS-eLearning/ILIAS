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

use ILIAS\Refinery\Factory;

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeTest extends ilMailBaseTest
{
    private const USER_ID = 6;

    protected function setUp(): void
    {
        ilMimeMail::setDefaultTransport(null);
        ilMailMimeSenderUserById::addUserToCache(self::USER_ID, $this->getUserById(self::USER_ID));

        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function testMimMailDelegatesEmailDeliveryToThePassedTransporter(): void
    {
        $defaultTransport = $this->getMockBuilder(ilMailMimeTransport::class)->disableOriginalConstructor()->getMock();
        $defaultTransport->expects($this->never())->method('send');

        $transport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $transport->expects($this->once())->method('send');

        $transportFactory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transportFactory->method('getTransport')->willReturn($defaultTransport);
        $this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

        $refineryMock = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refineryMock);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mail = new ilMimeMail();
        $mail->Send($transport);
    }

    /**
     * @throws ReflectionException
     */
    public function testMimMailDelegatesEmailDeliveryToDefaultTransport(): void
    {
        $defaultTransport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $defaultTransport->expects($this->once())->method('send');

        $transportFactory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transportFactory->method('getTransport')->willReturn($defaultTransport);
        $this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

        $refineryMock = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('refinery', $refineryMock);

        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mail = new ilMimeMail();
        $mail->Send();
    }

    /**
     * @throws ReflectionException
     */
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

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf(ilMailMimeTransportNull::class, $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
    public function testTransportFactoryWillReturnSmtpTransportIfEnabled(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();
        $settings->method('get')->willReturnCallback(static function ($key): ?string {
            return '1';
        });
        $this->setGlobalVariable('ilSetting', $settings);

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf(ilMailMimeTransportSmtp::class, $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
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

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf(ilMailMimeTransportSendmail::class, $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnSystemSenderForAnonymousUserId(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf(ilMailMimeSenderSystem::class, $factory->getSenderByUsrId(ANONYMOUS_USER_ID));
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnSystemSenderWhenExplicitlyRequested(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf(ilMailMimeSenderSystem::class, $factory->system());
    }

    /**
     * @throws ReflectionException
     */
    protected function getUserById(int $usrId): ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $user->method('getId')->willReturn($usrId);

        return $user;
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnUserSenderForExistingUserId(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf(ilMailMimeSenderUser::class, $factory->getSenderByUsrId(self::USER_ID));
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnUserSenderWhenExplicitlyRequested(): void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods([
            'set',
            'get',
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf(ilMailMimeSenderUser::class, $factory->user(self::USER_ID));
    }
}
