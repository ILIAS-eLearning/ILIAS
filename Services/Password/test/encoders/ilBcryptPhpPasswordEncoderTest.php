<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilBcryptPhpPasswordEncoder.php';
require_once 'Services/Password/test/ilPasswordBaseTest.php';

/**
 * Class ilBcryptPhpPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoderTest extends ilPasswordBaseTest
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
        if (version_compare(phpversion(), '5.5.0', '<')) {
            $this->markTestSkipped('Requires PHP >= 5.5.0');
        }
    }

    /**
     * @return array
     */
    public function costsProvider()
    {
        $data = array();
        for ($i = 4; $i <= 31; $i++) {
            $data[] = array($i);
        }
        return $data;
    }

    /**
     * @return ilBcryptPhpPasswordEncoder
     */
    public function testInstanceCanBeCreated()
    {
        $this->skipIfPhpVersionIsNotSupported();

        $default_costs_encoder = new ilBcryptPhpPasswordEncoder();
        $this->assertTrue((int) $default_costs_encoder->getCosts() > 4 && (int) $default_costs_encoder->getCosts() < 32);

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
        $this->assertException(ilPasswordException::class);
        $encoder->setCosts(32);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @expectedException ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPhpPasswordEncoder $encoder)
    {
        $this->assertException(ilPasswordException::class);
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
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(ilBcryptPhpPasswordEncoder $encoder)
    {
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
        $this->assertException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(ilBcryptPhpPasswordEncoder $encoder)
    {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeBcryptPhp(ilBcryptPhpPasswordEncoder $encoder)
    {
        $this->assertEquals('bcryptphp', $encoder->getName());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testCostsCanBeDeterminedDynamically(ilBcryptPhpPasswordEncoder $encoder)
    {
        $costs_default = $encoder->benchmarkCost();
        $costs_target = $encoder->benchmarkCost(0.5);

        $this->assertTrue($costs_default > 4 && $costs_default < 32);
        $this->assertTrue($costs_target > 4 && $costs_target < 32);
        $this->assertInternalType('int', $costs_default);
        $this->assertInternalType('int', $costs_target);
        $this->assertNotEquals($costs_default, $costs_target);
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilBcryptPhpPasswordEncoder $encoder)
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testReencodingIsDetectedWhenNecessary(ilBcryptPhpPasswordEncoder $encoder)
    {
        $raw = self::PASSWORD;

        $encoder->setCosts(8);
        $encoded = $encoder->encodePassword($raw, '');
        $encoder->setCosts(8);
        $this->assertFalse($encoder->requiresReencoding($encoded));

        $encoder->setCosts(9);
        $this->assertTrue($encoder->requiresReencoding($encoded));
    }
}
