<?php declare(strict_types=1);
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Password/classes/encoders/class.ilMd5PasswordEncoder.php';
require_once 'Services/Password/test/ilPasswordBaseTest.php';

/**
 * Class ilMd5PasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilMd5PasswordEncoderTest extends ilPasswordBaseTest
{
    /**
     * @return ilMd5PasswordEncoder
     */
    public function testInstanceCanBeCreated() : ilMd5PasswordEncoder
    {
        $encoder = new ilMd5PasswordEncoder();
        $this->assertInstanceOf('ilMd5PasswordEncoder', $encoder);
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncoded(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordCanBeVerified(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
     */
    public function testEncoderDoesNotRelyOnSalts(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
     */
    public function testEncoderDoesNotSupportReencoding(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
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
     * @param ilMd5PasswordEncoder $encoder
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilMd5PasswordEncoder $encoder
    ) : void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilMd5PasswordEncoder $encoder
     */
    public function testNameShouldBeMd5(ilMd5PasswordEncoder $encoder) : void
    {
        $this->assertEquals('md5', $encoder->getName());
    }
} 