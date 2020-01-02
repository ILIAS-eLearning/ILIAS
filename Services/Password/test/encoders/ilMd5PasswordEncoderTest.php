<?php
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
    public function testInstanceCanBeCreated()
    {
        $encoder = new ilMd5PasswordEncoder();
        $this->assertInstanceOf('ilMd5PasswordEncoder', $encoder);
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordShouldBeCorrectlyEncoded(ilMd5PasswordEncoder $encoder)
    {
        $this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordCanBeVerified(ilMd5PasswordEncoder $encoder)
    {
        $this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilMd5PasswordEncoder $encoder)
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotSupportReencoding(ilMd5PasswordEncoder $encoder)
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @expectedException ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(ilMd5PasswordEncoder $encoder)
    {
        $this->assertException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(ilMd5PasswordEncoder $encoder)
    {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeMd5(ilMd5PasswordEncoder $encoder)
    {
        $this->assertEquals('md5', $encoder->getName());
    }
}
