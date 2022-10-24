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

/**
 * Class ilBcryptPhpPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilBcryptPhpPasswordEncoderTest extends ilPasswordBaseTest
{
    /** @var string */
    private const VALID_COSTS = '08';

    /** @var string */
    private const PASSWORD = 'password';

    /** @var string */
    private const WRONG_PASSWORD = 'wrong_password';

    /**
     * @return array<string, string[]>
     */
    public function costsProvider(): array
    {
        $data = [];
        for ($i = 4; $i <= 31; ++$i) {
            $data[sprintf('Costs: %s', $i)] = [(string) $i];
        }

        return $data;
    }

    public function testInstanceCanBeCreated(): ilBcryptPhpPasswordEncoder
    {
        $default_costs_encoder = new ilBcryptPhpPasswordEncoder();
        $this->assertTrue((int) $default_costs_encoder->getCosts() > 4 && (int) $default_costs_encoder->getCosts() < 32);

        $encoder = new ilBcryptPhpPasswordEncoder([
            'cost' => self::VALID_COSTS
        ]);
        $this->assertInstanceOf(ilBcryptPhpPasswordEncoder::class, $encoder);
        $this->assertSame(self::VALID_COSTS, $encoder->getCosts());

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCanBeRetrievedWhenCostsAreSet(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $expected = '04';

        $encoder->setCosts($expected);
        $this->assertSame($expected, $encoder->getCosts());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetAboveRange(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('32');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCannotBeSetBelowRange(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts('3');
    }

    /**
     * @depends      testInstanceCanBeCreated
     * @dataProvider costsProvider
     * @doesNotPerformAssertions
     * @throws ilPasswordException
     */
    public function testCostsCanBeSetInRange(string $costs, ilBcryptPhpPasswordEncoder $encoder): void
    {
        $encoder->setCosts($costs);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilBcryptPhpPasswordEncoder $encoder
    ): ilBcryptPhpPasswordEncoder {
        $encoder->setCosts(self::VALID_COSTS);
        $encoded_password = $encoder->encodePassword(self::PASSWORD, '');
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, ''));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, ''));

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilBcryptPhpPasswordEncoder $encoder
    ): void {
        $this->expectException(ilPasswordException::class);
        $encoder->setCosts(self::VALID_COSTS);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilBcryptPhpPasswordEncoder $encoder
    ): void {
        $encoder->setCosts(self::VALID_COSTS);
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeBcryptPhp(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $this->assertSame('bcryptphp', $encoder->getName());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testCostsCanBeDeterminedDynamically(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $costs_default = $encoder->benchmarkCost();
        $costs_target = $encoder->benchmarkCost(0.5);

        $this->assertTrue($costs_default > 4 && $costs_default < 32);
        $this->assertTrue($costs_target > 4 && $costs_target < 32);
        $this->assertIsInt($costs_default);
        $this->assertIsInt($costs_target);
        $this->assertNotEquals($costs_default, $costs_target);
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilBcryptPhpPasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testReencodingIsDetectedWhenNecessary(ilBcryptPhpPasswordEncoder $encoder): void
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
