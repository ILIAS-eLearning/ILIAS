<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilMd5PasswordEncoder.php';

/**
 * Class ilMd5PasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilMd5PasswordEncoderTest  extends PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */
	public function testInstanceCanBeCreated()
	{
		$this->assertInstanceOf('ilMd5PasswordEncoder', new ilMd5PasswordEncoder());
	}

	/**
	 * @throws ilPasswordException
	 */
	public function testPasswordShouldBeCorrectlyEncoded()
	{
		$encoder = new ilMd5PasswordEncoder();
		$this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
	}

	/**
	 * 
	 */
	public function testPasswordCanBeVerified()
	{
		$encoder = new ilMd5PasswordEncoder();
		$this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
	}

	/**
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding()
	{
		$encoder = new ilMd5PasswordEncoder();
		$encoder->encodePassword(str_repeat('a', 5000), '');
	}

	/**
	 * 
	 */
	public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength()
	{
		$encoder = new ilMd5PasswordEncoder();
		$this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
	}

	/**
	 *
	 */
	public function testNameShouldBeMd5()
	{
		$encoder = new ilMd5PasswordEncoder();
		$this->assertEquals('md5', $encoder->getName());
	}
} 