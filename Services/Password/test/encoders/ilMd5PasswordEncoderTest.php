<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMd5PasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilMd5PasswordEncoderTest extends ilPasswordBaseTest
{
    public function testInstanceCanBeCreated() : ilMd5PasswordEncoder
    {
        $encoder = new ilMd5PasswordEncoder();
        $this->assertInstanceOf(ilMd5PasswordEncoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncoded(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordCanBeVerified(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotSupportReencoding(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilMd5PasswordEncoder $encoder
    ) : void {
        $this->expectException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilMd5PasswordEncoder $encoder
    ) : void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeMd5(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertEquals('md5', $encoder->getName());
    }
}
