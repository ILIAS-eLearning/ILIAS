<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilArgon2IdPasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilArgon2IdPasswordEncoderTest extends ilPasswordBaseTest
{
    /** @var string */
    private const PASSWORD = 'password';
    /** @var string */
    private const WRONG_PASSWORD = 'wrong_password';

    /**
     *
     */
    private function skipIfPhpVersionIsNotSupported() : void
    {
        if (version_compare(phpversion(), '7.2.0', '<')) {
            $this->markTestSkipped('Requires PHP >= 7.2.0');
        }
    }

    /**
     * @return ilArgon2idPasswordEncoder
     * @throws ilPasswordException
     */
    public function testInstanceCanBeCreated() : ilArgon2idPasswordEncoder
    {
        $this->skipIfPhpVersionIsNotSupported();

        $encoder = new ilArgon2idPasswordEncoder();
        $this->assertInstanceOf(ilArgon2idPasswordEncoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     * @return ilArgon2idPasswordEncoder
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncodedAndVerified(
        ilArgon2idPasswordEncoder $encoder
    ) : ilArgon2idPasswordEncoder {
        $encoded_password = $encoder->encodePassword(self::PASSWORD, '');
        $this->assertTrue($encoder->isPasswordValid($encoded_password, self::PASSWORD, ''));
        $this->assertFalse($encoder->isPasswordValid($encoded_password, self::WRONG_PASSWORD, ''));
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilArgon2idPasswordEncoder $encoder
    ) : void {
        $this->expectException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilArgon2idPasswordEncoder $encoder
    ) : void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     */
    public function testNameShouldBeArgon2id(ilArgon2idPasswordEncoder $encoder) : void
    {
        $this->assertEquals('argon2id', $encoder->getName());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     */
    public function testEncoderDoesNotRelyOnSalts(ilArgon2idPasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilArgon2idPasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testReencodingIsDetectedWhenNecessary(ilArgon2idPasswordEncoder $encoder) : void
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