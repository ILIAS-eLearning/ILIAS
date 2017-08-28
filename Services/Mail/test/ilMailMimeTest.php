<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/test/ilMailBaseTest.php';
require_once 'Services/Mail/classes/Mime/Transport/class.ilMailMimeTransportFactory.php';
require_once 'Services/Mail/classes/Mime/Transport/interface.ilMailMimeTransport.php';
require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderFactory.php';
require_once 'Services/Mail/classes/Mime/Sender/class.ilMailMimeSenderUser.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/classes/class.ilMimeMail.php';

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailMimeTest extends \ilMailBaseTest
{
	/**
	 * 
	 */
	const USER_ID = 6;

	/**
	 * 
	 */
	protected function setUp()
	{
		if(!defined('ANONYMOUS_USER_ID'))
		{
			define('ANONYMOUS_USER_ID', 13);
		}

		\ilMimeMail::setDefaultTransport(null);
		\ilMailMimeSenderUser::addUserToCache(self::USER_ID, $this->getUserById(self::USER_ID));

		parent::setUp();
	}

	/**
	 * 
	 */
	public function testMimMailDelegatesEmailDeliveryToThePassedTransporter()
	{
		$defaultTransport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$defaultTransport->expects($this->never())->method('send');

		$transport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$transport->expects($this->once())->method('send');

		$transportFactory = $this->getMockBuilder('\ilMailMimeTransportFactory')->disableOriginalConstructor()->getMock();
		$transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
		$this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mail = new \ilMimeMail();
		$mail->send($transport);
	}

	/**
	 *
	 */
	public function testMimMailDelegatesEmailDeliveryToDefaultTransport()
	{
		$defaultTransport = $this->getMockBuilder('\ilMailMimeTransport')->getMock();
		$defaultTransport->expects($this->once())->method('send');

		$transportFactory = $this->getMockBuilder('\ilMailMimeTransportFactory')->disableOriginalConstructor()->getMock();
		$transportFactory->expects($this->any())->method('getTransport')->will($this->returnValue($defaultTransport));
		$this->setGlobalVariable('mail.mime.transport.factory', $transportFactory);

		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$this->setGlobalVariable('ilSetting', $settings);

		$mail = new \ilMimeMail();
		$mail->send();
	}

	/**
	 * 
	 */
	public function testTransportFactoryWillReturnNullTransportIfExternalEmailDeliveryIsDisabled()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$settings->expects($this->any())->method('get')->will($this->returnCallback(function($key) {
			if('mail_allow_external' == $key)
			{
				return false;
			}
			
			return true;
		}));
		$this->setGlobalVariable('ilSetting', $settings);

		$factory = new \ilMailMimeTransportFactory($settings);
		$this->assertInstanceOf('\ilMailMimeTransportNull', $factory->getTransport());
	}

	/**
	 *
	 */
	public function testTransportFactoryWillReturnSmtpTransportIfEnabled()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$settings->expects($this->any())->method('get')->will($this->returnCallback(function($key) {
			if('mail_allow_external' == $key)
			{
				return true;
			}


			if('mail_smtp_status' == $key)
			{
				return true;
			}

			return true;
		}));
		$this->setGlobalVariable('ilSetting', $settings);

		$factory = new \ilMailMimeTransportFactory($settings);
		$this->assertInstanceOf('\ilMailMimeTransportSmtp', $factory->getTransport());
	}

	/**
	 *
	 */
	public function testTransportFactoryWillReturnSendmailTransportIfSmtpTransportIsDisabled()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();
		$settings->expects($this->any())->method('get')->will($this->returnCallback(function($key) {
			if('mail_allow_external' == $key)
			{
				return true;
			}


			if('mail_smtp_status' == $key)
			{
				return false;
			}

			return true;
		}));
		$this->setGlobalVariable('ilSetting', $settings);

		$factory = new \ilMailMimeTransportFactory($settings);
		$this->assertInstanceOf('\ilMailMimeTransportSendMail', $factory->getTransport());
	}

	/**
	 * 
	 */
	public function testFactoryWillReturnSystemSenderForAnonymousUserId()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();

		$factory = new \ilMailMimeSenderFactory($settings);
		$this->assertInstanceOf('\ilMailMimeSenderSystem', $factory->getSenderByUsrId(\ANONYMOUS_USER_ID));
	}

	/**
	 *
	 */
	public function testFactoryWillReturnSystemSenderWhenExplicitlyRequested()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();

		$factory = new \ilMailMimeSenderFactory($settings);
		$this->assertInstanceOf('\ilMailMimeSenderSystem', $factory->system());
	}

	/**
	 * @param int $usrId
	 * @return \ilObjUser
	 */
	protected function getUserById($usrId)
	{
		$user = $this->getMockBuilder('\ilObjUser')->disableOriginalConstructor()->setMethods(
			array()
		)->getMock();
		$user->expects($this->any())->method('getId')->will($this->returnValue($usrId));

		return $user;
	}

	/**
	 *
	 */
	public function testFactoryWillReturnUserSenderForExistingUserId()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();

		$factory = new \ilMailMimeSenderFactory($settings);
		$this->assertInstanceOf('\ilMailMimeSenderUser', $factory->getSenderByUsrId(self::USER_ID));
	}

	/**
	 *
	 */
	public function testFactoryWillReturnUserSenderWhenExplicitlyRequested()
	{
		$settings = $this->getMockBuilder('\ilSetting')->disableOriginalConstructor()->setMethods(array('set', 'get'))->getMock();

		$factory = new \ilMailMimeSenderFactory($settings);
		$this->assertInstanceOf('\ilMailMimeSenderUser', $factory->user(self::USER_ID));
	}
}