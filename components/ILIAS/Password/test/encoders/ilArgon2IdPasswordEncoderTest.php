<?php

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

declare(strict_types=1);

class ilArgon2IdPasswordEncoderTest extends ilPasswordBaseTest
{
    private const PASSWORD = 'password';
    private const WRONG_PASSWORD = 'wrong_password';

    public function testInstanceCanBeCreated(): ilArgon2idPasswordEncoder
    {
        $encoder = new ilArgon2idPasswordEncoder();
        $this->assertInstanceOf(ilArgon2idPasswordEncoder::class, $encoder);

        if (!$encoder->isSupportedByRuntime()) {
            $this->markTestSkipped('Argon2id is not supported by the runtime.');
        }

        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilArgon2idPasswordEncoder $encoder
    ): ilArgon2idPasswordEncoder {
        $encoded_password = $encoder->encodePassword(self::PASSWORD, '');
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, ''));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, ''));
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilArgon2idPasswordEncoder $encoder
    ): void {
        $this->expectException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilArgon2idPasswordEncoder $encoder
    ): void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeArgon2id(ilArgon2idPasswordEncoder $encoder): void
    {
        $this->assertSame('argon2id', $encoder->getName());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilArgon2idPasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testReencodingIsDetectedWhenNecessary(ilArgon2idPasswordEncoder $encoder): void
    {
        $raw = self::PASSWORD;

        $encoder->setThreads(2);
        $encoder->setMemoryCost(4096);
        $encoder->setTimeCost(8);
        $encoded = $encoder->encodePassword($raw, '');

        $encoder->setThreads(2);
        $encoder->setMemoryCost(4096);
        $encoder->setTimeCost(8);
        $this->assertFalse($encoder->requiresReencoding($encoded));

        $encoder->setThreads(2);
        $encoder->setMemoryCost(4096);
        $encoder->setTimeCost(4);
        $this->assertTrue($encoder->requiresReencoding($encoded));

        $encoder->setThreads(1);
        $encoder->setMemoryCost(4096);
        $encoder->setTimeCost(8);
        $this->assertTrue($encoder->requiresReencoding($encoded));

        $encoder->setThreads(2);
        $encoder->setMemoryCost(2048);
        $encoder->setTimeCost(8);
        $this->assertTrue($encoder->requiresReencoding($encoded));
    }
}
