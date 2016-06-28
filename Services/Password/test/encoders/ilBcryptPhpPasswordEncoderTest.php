<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPhpPasswordEncoder.php';

/**
 * Class ilBcryptPhpPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoderTest extends PHPUnit_Framework_TestCase
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
	 * Setup
	 */
	protected function setUp()
	{
	}

	/**
	 *
	 */
	private function skipIfPhpVersionIsNotSupported()
	{
		if(version_compare(phpversion(), '5.5.0', '<'))
		{
			$this->markTestSkipped('Requires PHP >= 5.5.0');
		}
	}

	/**
	 * @return ilBcryptPasswordEncoder
	 */
	public function testInstanceCanBeCreated()
	{
		$encoder = new ilBcryptPhpPasswordEncoder(array(
			'cost' => self::VALID_COSTS
		));
		$this->assertInstanceOf('ilBcryptPhpPasswordEncoder', $encoder);
		$this->assertEquals(self::VALID_COSTS, $encoder->getCosts());
		return $encoder;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPhpPasswordEncoder $encoder)
	{
		$encoder->setCosts(4);
		$this->assertEquals(4, $encoder->getCosts());
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testCostsCannotBeSetAboveRange(ilBcryptPhpPasswordEncoder $encoder)
	{
		$encoder->setCosts(32);
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testCostsCannotBeSetBelowRange(ilBcryptPhpPasswordEncoder $encoder)
	{
		$encoder->setCosts(3);
	}

	/**
	 * @depends      testInstanceCanBeCreated
	 * @dataProvider costsProvider
	 */
	public function testCostsCanBeSetInRange($costs, ilBcryptPhpPasswordEncoder $encoder)
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
	public function testPasswordShouldBeCorrectlyEncodedAndVerified(ilBcryptPhpPasswordEncoder $encoder)
	{
		$this->skipIfPhpVersionIsNotSupported();

		$encoder->setCosts(self::VALID_COSTS);
		$encoded_password = $encoder->encodePassword(self::PASSWORD, '');
		$this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, ''));
		$this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, ''));
		return $encoder;
	}

	/**
	 * @depends testInstanceCanBeCreated
	 * @expectedException ilPasswordException
	 */
	public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(ilBcryptPhpPasswordEncoder $encoder)
	{
		$this->skipIfPhpVersionIsNotSupported();

		$encoder->setCosts(self::VALID_COSTS);
		$encoder->encodePassword(str_repeat('a', 5000), '');
	}

	/**
	 * @depends testInstanceCanBeCreated
	 */
	public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(ilBcryptPhpPasswordEncoder $encoder)
	{
		$this->skipIfPhpVersionIsNotSupported();

		$encoder->setCosts(self::VALID_COSTS);
		$this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
	}

	/**
	 *
	 */
	public function testNameShouldBeBcryptPhp()
	{
		$encoder = new ilBcryptPhpPasswordEncoder();
		$this->assertEquals('bcryptphp', $encoder->getName());
	}
}