<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMimeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeTest extends ilMailBaseTest
{
    const USER_ID = 6;

    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        ilMimeMail::setDefaultTransport(null);
        ilMailMimeSenderUserById::addUserToCache(self::USER_ID, $this->getUserById(self::USER_ID));

        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function testMimMailDelegatesEmailDeliveryToThePassedTransporter() : void
    {
        $defaultTransport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $defaultTransport->expects($this->never())->method('send');

        $transport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $transport->expects($this->once())->method('send');

        $transportFactory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
        $this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

        $settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mail = new ilMimeMail();
        $mail->send($transport);
    }

    /**
     * @throws ReflectionException
     */
    public function testMimMailDelegatesEmailDeliveryToDefaultTransport() : void
    {
        $defaultTransport = $this->getMockBuilder(ilMailMimeTransport::class)->getMock();
        $defaultTransport->expects($this->once())->method('send');

        $transportFactory = $this->getMockBuilder(ilMailMimeTransportFactory::class)->disableOriginalConstructor()->getMock();
        $transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
        $this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

        $settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();
        $this->setGlobalVariable('ilSetting', $settings);

        $mail = new ilMimeMail();
        $mail->send();
    }

    /**
     * @throws ReflectionException
     */
    public function testTransportFactoryWillReturnNullTransportIfExternalEmailDeliveryIsDisabled() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();
        $settings->expects($this->any())->method('get')->will($this->returnCallback(function ($key) {
            if ('mail_allow_external' == $key) {
                return false;
            }

            return true;
        }));
        $this->setGlobalVariable('ilSetting', $settings);

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->setMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf('\ilMailMimeTransportNull', $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
    public function testTransportFactoryWillReturnSmtpTransportIfEnabled() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();
        $settings->expects($this->any())->method('get')->will($this->returnCallback(function ($key) {
            if ('mail_allow_external' == $key) {
                return true;
            }


            if ('mail_smtp_status' == $key) {
                return true;
            }

            return true;
        }));
        $this->setGlobalVariable('ilSetting', $settings);

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->setMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf('\ilMailMimeTransportSmtp', $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
    public function testTransportFactoryWillReturnSendmailTransportIfSmtpTransportIsDisabled() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();

        $settings->expects($this->any())->method('get')->will($this->returnCallback(function ($key) {
            if ('mail_allow_external' == $key) {
                return true;
            }


            if ('mail_smtp_status' == $key) {
                return false;
            }

            return true;
        }));
        $this->setGlobalVariable('ilSetting', $settings);

        $eventHandler = $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->setMethods([
            'raise',
        ])->getMock();

        $factory = new ilMailMimeTransportFactory($settings, $eventHandler);
        $this->assertInstanceOf('\ilMailMimeTransportSendMail', $factory->getTransport());
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnSystemSenderForAnonymousUserId() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf('\ilMailMimeSenderSystem', $factory->getSenderByUsrId(ANONYMOUS_USER_ID));
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnSystemSenderWhenExplicitlyRequested() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf('\ilMailMimeSenderSystem', $factory->system());
    }

    /**
     * @param int $usrId
     * @return ilObjUser
     * @throws ReflectionException
     */
    protected function getUserById(int $usrId) : ilObjUser
    {
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $user->expects($this->any())->method('getId')->will($this->returnValue($usrId));

        return $user;
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnUserSenderForExistingUserId() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf('\ilMailMimeSenderUser', $factory->getSenderByUsrId(self::USER_ID));
    }

    /**
     * @throws ReflectionException
     */
    public function testFactoryWillReturnUserSenderWhenExplicitlyRequested() : void
    {
        $settings = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->setMethods([
            'set',
            'get'
        ])->getMock();

        $factory = new ilMailMimeSenderFactory($settings);
        $this->assertInstanceOf('\ilMailMimeSenderUser', $factory->user(self::USER_ID));
    }
}