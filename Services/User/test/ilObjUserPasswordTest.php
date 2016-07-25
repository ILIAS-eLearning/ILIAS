<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilUserPasswordManager.php';
require_once 'Services/User/classes/class.ilUserPasswordEncoderFactory.php';
require_once 'Services/Password/classes/class.ilBasePasswordEncoder.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/User/exceptions/class.ilUserException.php';

/**
 * Class ilObjUserPasswordTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesUser
 */
class ilObjUserPasswordTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var string
	 */
	const PASSWORD = 'password';

	/**
	 * @var string
	 */
	const ENCODED_PASSWORD = 'encoded';

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		if(!defined('CLIENT_DATA_DIR'))
		{
			define('CLIENT_DATA_DIR', '/tmp');
		}

		parent::setUp();
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedIfPasswordManagerIsCreatedWithoutEncoderInformation()
	{
		$this->expectException(ilUserException::class);
		 new ilUserPasswordManager();
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedIfPasswordManagerIsCreatedWithoutFactory()
	{
		$this->expectException(ilUserException::class);
		new ilUserPasswordManager(
			array(
				'password_encoder' => 'md5'
			)
		);
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedIfPasswordManagerIsCreatedWithoutValidFactory()
	{
		$this->expectException(PHPUnit_Framework_Error::class);
		try
		{
			new ilUserPasswordManager(
				array(
					'password_encoder' => 'md5',
					'encoder_factory'  => 'test'
				)
			);
		}
		catch(TypeError $e)
		{
			throw new PHPUnit_Framework_Error($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine());
		}
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreated()
	{
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();
		$factory_mock->expects($this->exactly(2))->method('getSupportedEncoderNames')->will($this->onConsecutiveCalls(
			array(
				'mockencoder', 'second_mockencoder'
			),
			array(
				'mockencoder'
			)
		));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'md5',
				'encoder_factory'  => $factory_mock
			)
		);
		$this->assertInstanceOf('ilUserPasswordManager', $password_manager);
		$this->assertEquals('md5', $password_manager->getEncoderName());
		$this->assertEquals($factory_mock, $password_manager->getEncoderFactory());

		$this->assertTrue($password_manager->isEncodingTypeSupported('second_mockencoder'));
		$this->assertFalse($password_manager->isEncodingTypeSupported('second_mockencoder'));
	}

	/**
	 *
	 */
	public function testPasswordManagerEncodesRawPasswordWithSalt()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->once())->method('setPasswordSalt')->with($this->isType('string'));
		$user_mock->expects($this->once())->method('getPasswordSalt')->will($this->returnValue('asuperrandomsalt'));
		$user_mock->expects($this->once())->method('setPasswordEncodingType')->with($this->equalTo('mockencoder'));
		$user_mock->expects($this->once())->method('setPasswd')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(IL_PASSWD_CRYPTED));

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('mockencoder'));
		$encoder->expects($this->once())->method('requiresSalt')->will($this->returnValue(true));
		$encoder->expects($this->once())->method('encodePassword')->with($this->equalTo(self::PASSWORD), $this->isType('string'))->will($this->returnValue(self::ENCODED_PASSWORD));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$password_manager->encodePassword($user_mock, self::PASSWORD);
	}

	/**
	 *
	 */
	public function testPasswordManagerEncodesRawPasswordWithoutSalt()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->once())->method('setPasswordSalt')->with($this->equalTo(null));
		$user_mock->expects($this->once())->method('getPasswordSalt')->will($this->returnValue(null));
		$user_mock->expects($this->once())->method('setPasswordEncodingType')->with($this->equalTo('mockencoder'));
		$user_mock->expects($this->once())->method('setPasswd')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(IL_PASSWD_CRYPTED));

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('mockencoder'));
		$encoder->expects($this->once())->method('requiresSalt')->will($this->returnValue(false));
		$encoder->expects($this->once())->method('encodePassword')->with($this->equalTo(self::PASSWORD), $this->equalTo(null))->will($this->returnValue(self::ENCODED_PASSWORD));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$password_manager->encodePassword($user_mock, self::PASSWORD);
	}

	/**
	 *
	 */
	public function testPasswordManagerVerifiesPassword()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->atLeast(1))->method('getPasswordSalt')->will($this->returnValue('asuperrandomsalt'));
		$user_mock->expects($this->atLeast(1))->method('getPasswordEncodingType')->will($this->returnValue('mockencoder'));
		$user_mock->expects($this->atLeast(1))->method('getPasswd')->will($this->returnValue(self::ENCODED_PASSWORD));
		$user_mock->expects($this->never())->method('resetPassword');

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('mockencoder'));
		$encoder->expects($this->once())->method('isPasswordValid')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(self::PASSWORD), $this->isType('string'))->will($this->returnValue(true));
		$encoder->expects($this->once())->method('requiresReencoding')->with($this->equalTo(self::ENCODED_PASSWORD))->will($this->returnValue(false));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$this->assertTrue($password_manager->verifyPassword($user_mock, self::PASSWORD));
	}

	/**
	 *
	 */
	public function testPasswordManagerMigratesPasswordOnVerificationWithVariantEncoders()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->once())->method('getPasswordSalt')->will($this->returnValue('asuperrandomsalt'));
		$user_mock->expects($this->once())->method('getPasswordEncodingType')->will($this->returnValue('second_mockencoder'));
		$user_mock->expects($this->once())->method('getPasswd')->will($this->returnValue(self::ENCODED_PASSWORD));
		$user_mock->expects($this->once())->method('resetPassword')->with($this->equalTo(self::PASSWORD), $this->equalTo(self::PASSWORD));

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('second_mockencoder'));
		$encoder->expects($this->once())->method('isPasswordValid')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(self::PASSWORD), $this->isType('string'))->will($this->returnValue(true));
		$encoder->expects($this->never())->method('requiresReencoding')->with($this->equalTo(self::ENCODED_PASSWORD))->will($this->returnValue(false));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$this->assertTrue($password_manager->verifyPassword($user_mock, self::PASSWORD));
	}

	/**
	 *
	 */
	public function testPasswordManagerReencodesPasswordIfReencodingIsNecessary()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->once())->method('getPasswordSalt')->will($this->returnValue('asuperrandomsalt'));
		$user_mock->expects($this->once())->method('getPasswordEncodingType')->will($this->returnValue('mockencoder'));
		$user_mock->expects($this->exactly(2))->method('getPasswd')->will($this->returnValue(self::ENCODED_PASSWORD));
		$user_mock->expects($this->once())->method('resetPassword')->with($this->equalTo(self::PASSWORD), $this->equalTo(self::PASSWORD));

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('mockencoder'));
		$encoder->expects($this->once())->method('isPasswordValid')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(self::PASSWORD), $this->isType('string'))->will($this->returnValue(true));
		$encoder->expects($this->once())->method('requiresReencoding')->with($this->equalTo(self::ENCODED_PASSWORD))->will($this->returnValue(true));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$this->assertTrue($password_manager->verifyPassword($user_mock, self::PASSWORD));
	}

	/**
	 *
	 */
	public function testPasswordManagerNeverMigratesPasswordOnFailedVerificationWithVariantEncoders()
	{
		$user_mock    = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->getMock();
		$encoder      = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$factory_mock = $this->getMockBuilder('ilUserPasswordEncoderFactory')->disableOriginalConstructor()->getMock();

		$user_mock->expects($this->once())->method('getPasswordSalt')->will($this->returnValue('asuperrandomsalt'));
		$user_mock->expects($this->once())->method('getPasswordEncodingType')->will($this->returnValue('second_mockencoder'));
		$user_mock->expects($this->once())->method('getPasswd')->will($this->returnValue(self::ENCODED_PASSWORD));
		$user_mock->expects($this->never())->method('resetPassword');

		$encoder->expects($this->once())->method('getName')->will($this->returnValue('second_mockencoder'));
		$encoder->expects($this->never())->method('requiresReencoding');
		$encoder->expects($this->once())->method('isPasswordValid')->with($this->equalTo(self::ENCODED_PASSWORD), $this->equalTo(self::PASSWORD), $this->isType('string'))->will($this->returnValue(false));

		$factory_mock->expects($this->once())->method('getEncoderByName')->will($this->returnValue($encoder));

		$password_manager = new ilUserPasswordManager(
			array(
				'password_encoder' => 'mockencoder',
				'encoder_factory'  => $factory_mock
			)
		);

		$this->assertFalse($password_manager->verifyPassword($user_mock, self::PASSWORD));
	}

	/**
	 *
	 */
	public function testFactoryCanBeCreated()
	{
		$factory = new ilUserPasswordEncoderFactory();
		$this->assertInstanceOf('ilUserPasswordEncoderFactory', $factory);
	}

	/**
	 *
	 */
	public function testGettersOfFactoryShouldReturnWhatWasSetBySetters()
	{
		$factory = new ilUserPasswordEncoderFactory(array('default_password_encoder' => 'md5'));
		$this->assertEquals('md5', $factory->getDefaultEncoder());

		$encoder = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$encoder->expects($this->atLeastOnce())->method('getName')->will($this->returnValue('mockencoder'));

		$second_mockencoder = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$second_mockencoder->expects($this->atLeastOnce())->method('getName')->will($this->returnValue('second_mockencoder'));

		$factory->setEncoders(array($encoder, $second_mockencoder));
		$this->assertCount(2, $factory->getEncoders());
		$this->assertCount(2, $factory->getSupportedEncoderNames());
		$this->assertCount(0, array_diff(array('mockencoder', 'second_mockencoder'), $factory->getSupportedEncoderNames()));
		$this->assertCount(0, array_diff($factory->getSupportedEncoderNames(), array('mockencoder', 'second_mockencoder')));
	}

	/**
	 * 
	 */
	public function testFactoryRaisesAnExceptionIfAnUnsupportedEncoderWasInjected()
	{
		$this->expectException(ilUserException::class);
		$factory = new ilUserPasswordEncoderFactory();
		$factory->setEncoders(array('phpunit'));
	}

	/**
	 * 
	 */
	public function testExceptionIsRaisedIfAnUnsupportedEncoderIsRequestedFromFactory()
	{
		$this->expectException(ilUserException::class);
		$factory = new ilUserPasswordEncoderFactory(array('default_password_encoder' => 'md5'));
		$factory->getEncoderByName('phpunit');
	}

	/**
	 * 
	 */
	public function testFactoryRaisesAnExceptionIfAnUnsupportedEncoderIsRequestedAndNoDefaultEncoderWasSpecifiedInFallbackMode()
	{
		$this->expectException(ilUserException::class);
		$factory = new ilUserPasswordEncoderFactory();
		$factory->getEncoderByName('phpunit', true);
	}

	/**
	 */
	public function testFactoryRaisesAnExceptionIfAnUnsupportedEncoderIsRequestedAndTheDefaultEncoderDoesNotMatchOneOfTheSupportedEncodersInFallbackMode()
	{
		$this->expectException(ilUserException::class);
		$factory = new ilUserPasswordEncoderFactory(array('default_password_encoder' => 'phpunit'));
		$factory->getEncoderByName('phpunit', true);
	}

	/**
	 *
	 */
	public function testFactoryReturnsTheDefaultEncoderIfAnUnsupportedEncoderIsRequestedAndASupportedDefaultEncoderWasSpecifiedInFallbackMode()
	{
		$encoder = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$encoder->expects($this->atLeastOnce())->method('getName')->will($this->returnValue('mockencoder'));

		$factory = new ilUserPasswordEncoderFactory(array('default_password_encoder' => $encoder->getName()));
		$factory->setEncoders(array($encoder));
		$this->assertEquals($encoder, $factory->getEncoderByName('phpunit', true));
	}

	/**
	 *
	 */
	public function testFactoryReturnsCorrectEncoderIfAMatchingEncoderWasFound()
	{
		$encoder = $this->getMockBuilder('ilBasePasswordEncoder')->disableOriginalConstructor()->getMock();
		$encoder->expects($this->atLeastOnce())->method('getName')->will($this->returnValue('mockencoder'));

		$factory = new ilUserPasswordEncoderFactory(array('default_password_encoder' => $encoder->getName()));
		$factory->setEncoders(array($encoder));
		$this->assertEquals($encoder, $factory->getEncoderByName('mockencoder', true));
	}
}