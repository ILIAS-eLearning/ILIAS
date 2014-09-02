<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPasswordEncoder.php';

/**
 * Class ilBcryptPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPasswordEncoderTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var string
	 */
	const VALID_COSTS = '08';

	/**
	 * @var string
	 */
	const PASSWORD = 'password';

	/**
	 * @var string
	 */
	const WRONG_PASSWORD = 'wrong_password';

	/**
	 * @var string
	 */
	const CLIENT_SALT = 'homer!12345_/';

	/**
	 * @var string
	 */
	const PASSWORD_SALT = 'salt';

	/**
	 * @var vfsStreamDirectory
	 */
	protected $test_directory;

	/**
	 * @return vfsStreamDirectory
	 */
	public function getTestDirectory()
	{
		return $this->test_directory;
	}

	/**
	 * @param vfsStreamDirectory $test_directory
	 */
	public function setTestDirectory($test_directory)
	{
		$this->test_directory = $test_directory;
	}

	/**
	 * @return bool
	 */
	private function isVsfStreamInstalled()
	{
		return file_exists('vfsStream.php');
	}

	/**
	 * Setup
	 */
	protected function setUp()
	{
		if($this->isVsfStreamInstalled())
		{
			require_once 'vfsStream.php';
			vfsStream::setup();
			$this->setTestDirectory(vfsStream::newDirectory('tests')->at(vfsStreamWrapper::getRoot()));
			define('CLIENT_DATA_DIR', vfsStream::url('root/tests'));
		}
		parent::setUp();
	}

	/**
	 * @return ilBcryptPasswordEncoder
	 */
	public function testInstanceCanBeCreated()
	{
		$security_flaw_ignoring_encoder = new ilBcryptPasswordEncoder(array(
			'ignore_security_flaw' => true
		));
		$this->assertTrue($security_flaw_ignoring_encoder->isSecurityFlawIgnored());

		$security_flaw_respecting_encoder = new ilBcryptPasswordEncoder(array(
			'ignore_security_flaw' => false
		));
		$this->assertFalse($security_flaw_respecting_encoder->isSecurityFlawIgnored());

		$encoder = new ilBcryptPasswordEncoder(array(
			'cost' => self::VALID_COSTS
		));
		$this->assertInstanceOf('ilBcryptPasswordEncoder', $encoder);
		$this->assertEquals(self::VALID_COSTS, $encoder->getCosts());
		$this->assertFalse($encoder->isSecurityFlawIgnored());
		$encoder->setClientSalt(self::CLIENT_SALT);
		return $encoder;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(4);
		$this->assertEquals(4, $encoder->getCosts());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testCostsCannotBeSetAboveRange(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(32);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testCostsCannotBeSetBelowRange(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(3);
	}

	/**
	 * @depends      testInstanceCanBeCreated
	 * @dataProvider costsProvider
	 */
	public function testCostsCanBeSetInRange($costs, ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts($costs);
	}

	/**
	 * @return array
	 */
	public function costsProvider()
	{
		$data = array();
		for($i = 4; $i <= 31; $i++)
		{
			$data[] = array($i);
		}
		return $data;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testPasswordShouldBeCorrectlyEncodedAndVerified(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(self::VALID_COSTS);
		$encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
		$this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
		$this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, self::PASSWORD_SALT));
		return $encoder;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(self::VALID_COSTS);
		$encoder->encodePassword(str_repeat('a', 5000), self::PASSWORD_SALT);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(ilBcryptPasswordEncoder $encoder)
	{
		$encoder->setCosts(self::VALID_COSTS);
		$this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), self::PASSWORD_SALT));
	}

	/**
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfSaltIsMissingIsOnEncoding()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setClientSalt(null);
		$encoder->setCosts(self::VALID_COSTS);
		$encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
	}

	/**
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfSaltIsMissingIsOnVerification()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setClientSalt(null);
		$encoder->setCosts(self::VALID_COSTS);
		$encoder->isPasswordValid('12121212', self::PASSWORD, self::PASSWORD_SALT);
	}

	/**
	 *
	 */
	public function testExceptionIsRaisedIfAGeneratedClientSaltCouldNotBeStoredOnEncoderSelection()
	{
		$this->skipIfvfsStreamNotSupported();

		$encoder = new ilBcryptPasswordEncoder();
		$this->assertNull($encoder->getClientSalt());

		try
		{
			$this->getTestDirectory()->chmod(0000);
			$encoder->onSelection();
			$this->fail('An expected exception has not been raised.');
		}
		catch(Exception $e)
		{
			$this->assertNull($encoder->getClientSalt());
			$this->assertFileNotExists(vfsStream::url('root/tests/' . ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME));
		}
	}

	/**
	 *
	 */
	public function testClientSaltIsGeneratedAndStoredOnEncoderSelection()
	{
		$this->skipIfvfsStreamNotSupported();

		$this->getTestDirectory()->chmod(0777);

		$encoder = new ilBcryptPasswordEncoder();
		$this->assertNull($encoder->getClientSalt());

		$encoder->onSelection();

		$this->assertNotNull($encoder->getClientSalt());
		$this->assertFileExists(vfsStream::url('root/tests/' . ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME));
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreatedAndInitializedWithClientSalt()
	{
		$this->skipIfvfsStreamNotSupported();

		$this->getTestDirectory()->chmod(0777);
		vfsStream::newFile(ilBcryptPasswordEncoder::SALT_STORAGE_FILENAME)->withContent(self::CLIENT_SALT)->at($this->getTestDirectory());

		$encoder = new ilBcryptPasswordEncoder();
		$this->assertEquals(self::CLIENT_SALT, $encoder->getClientSalt());
	}

	/**
	 *
	 */
	public function testBackwardCompatibilityCanBeRetrievedWhenBackwardCompatibilityIsSet()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setBackwardCompatibility(true);
		$this->assertTrue($encoder->isBackwardCompatibilityEnabled());
		$encoder->setBackwardCompatibility(false);
		$this->assertFalse($encoder->isBackwardCompatibilityEnabled());
	}

	/**
	 *
	 */
	public function testBackwardCompatibility()
	{
		$this->skipIfPhpVersionIsNotSupported();

		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setClientSalt(self::CLIENT_SALT);
		$encoder->setBackwardCompatibility(true);
		$encoded_password = $encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
		$this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
		$this->assertEquals('$2a$', substr($encoded_password, 0, 4));

		$another_encoder = new ilBcryptPasswordEncoder();
		$another_encoder->setClientSalt(self::CLIENT_SALT);
		$another_encoder->setBackwardCompatibility(false);
		$another_encoded_password = $another_encoder->encodePassword(self::PASSWORD, self::PASSWORD_SALT);
		$this->assertEquals('$2y$', substr($another_encoded_password, 0, 4));
		$this->assertTrue($another_encoder->isPasswordValid($encoded_password, self::PASSWORD, self::PASSWORD_SALT));
	}

	/**
	 * 
	 */
	private function skipIfPhpVersionIsNotSupported()
	{
		if(version_compare(phpversion(), '5.3.7', '<'))
		{
			$this->markTestSkipped('Requires PHP >= 5.3.7');
		}
	}

	/**
	 *
	 */
	private function skipIfvfsStreamNotSupported()
	{
		if(!$this->isVsfStreamInstalled())
		{
			$this->markTestSkipped('Requires vfsStream (http://vfs.bovigo.org)');
		}
	}

	/**
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfTheRawPasswordContainsA8BitCharacterAndBackwardCompatibilityIsEnabled()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setClientSalt(self::CLIENT_SALT);
		$encoder->setBackwardCompatibility(true);
		$encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
	}

	/**
	 *
	 */
	public function testExceptionIsNotRaisedIfTheRawPasswordContainsA8BitCharacterAndBackwardCompatibilityIsEnabledWithIgnoredSecurityFlaw()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$encoder->setClientSalt(self::CLIENT_SALT);
		$encoder->setBackwardCompatibility(true);
		$encoder->setIsSecurityFlawIgnored(true);
		$encoder->encodePassword(self::PASSWORD . chr(195), self::PASSWORD_SALT);
	}

	/**
	 *
	 */
	public function testNameShouldBeBcrypt()
	{
		$encoder = new ilBcryptPasswordEncoder();
		$this->assertEquals('bcrypt', $encoder->getName());
	}
}