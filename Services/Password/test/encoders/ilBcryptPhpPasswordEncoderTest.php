<?php declare(strict_types=1);
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
    /** @var string */
    const VALID_COSTS = '08';

    /** @var string */
    const PASSWORD = 'password';

    /** @var string */
    const WRONG_PASSWORD = 'wrong_password';

    /**
     *
     */
    private function skipIfPhpVersionIsNotSupported() : void
    {
        if (version_compare(phpversion(), '5.5.0', '<')) {
            $this->markTestSkipped('Requires PHP >= 5.5.0');
        }
    }

    /**
     * @return array
     */
    public function costsProvider() : array
    {
        $data = [];
        for ($i = 4; $i <= 31; $i++) {
            $data[sprintf("Costs: %s", (string) $i)] = [(string) $i];
        }

        return $data;
    }

    /**
     * @return ilBcryptPhpPasswordEncoder
     * @throws ilPasswordException
     */
    public function testInstanceCanBeCreated() : ilBcryptPhpPasswordEncoder
    {
        $this->skipIfPhpVersionIsNotSupported();

        $default_costs_encoder = new ilBcryptPhpPasswordEncoder();
        $this->assertTrue((int) $default_costs_encoder->getCosts() > 4 && (int) $default_costs_encoder->getCosts() < 32);

        $encoder = new ilBcryptPhpPasswordEncoder([
            'cost' => self::VALID_COSTS
        ]);
        $this->assertInstanceOf('ilBcryptPhpPasswordEncoder', $encoder);
        $this->assertEquals(self::VALID_COSTS, $encoder->getCosts());
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $expected = '04';

        $encoder->setCosts($expected);
        $this->assertEquals($expected, $encoder->getCosts());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetAboveRange(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('32');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('3');
    }

    /**
     * @depends      testInstanceCanBeCreated
     * @dataProvider costsProvider
     * @doesNotPerformAssertions
     * @param string                     $costs
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCanBeSetInRange(string $costs, ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $encoder->setCosts($costs);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @return ilBcryptPhpPasswordEncoder
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilBcryptPhpPasswordEncoder $encoder
    ) : ilBcryptPhpPasswordEncoder {
        $encoder->setCosts(self::VALID_COSTS);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, '');
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, ''));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, ''));
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilBcryptPhpPasswordEncoder $encoder
    ) : void {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilBcryptPhpPasswordEncoder $encoder
    ) : void {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     */
    public function testNameShouldBeBcryptPhp(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $this->assertEquals('bcryptphp', $encoder->getName());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testCostsCanBeDeterminedDynamically(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $costs_default = $encoder->benchmarkCost();
        $costs_target  = $encoder->benchmarkCost(0.5);

        $this->assertTrue($costs_default > 4 && $costs_default < 32);
        $this->assertTrue($costs_target > 4 && $costs_target < 32);
        $this->assertIsInt($costs_default);
        $this->assertIsInt($costs_target);
        $this->assertNotEquals($costs_default, $costs_target);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     */
    public function testEncoderDoesNotRelyOnSalts(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilBcryptPhpPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testReencodingIsDetectedWhenNecessary(ilBcryptPhpPasswordEncoder $encoder) : void
    {
        $raw = self::PASSWORD;

        $encoder->setCosts('8');
        $encoded = $encoder->encodePassword($raw, '');
        $encoder->setCosts('8');
        $this->assertFalse($encoder->requiresReencoding($encoded));

        $encoder->setCosts('9');
        $this->assertTrue($encoder->requiresReencoding($encoded));
    }
}