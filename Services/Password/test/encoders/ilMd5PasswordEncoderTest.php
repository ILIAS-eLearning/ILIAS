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
 * Class ilMd5PasswordEncoderTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilMd5PasswordEncoderTest extends ilPasswordBaseTest
{
    public function testInstanceCanBeCreated(): ilMd5PasswordEncoder
    {
        $encoder = new ilMd5PasswordEncoder();
        $this->assertInstanceOf(ilMd5PasswordEncoder::class, $encoder);
        return $encoder;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordShouldBeCorrectlyEncoded(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordCanBeVerified(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotRelyOnSalts(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testEncoderDoesNotSupportReencoding(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilMd5PasswordEncoder $encoder
    ): void {
        $this->expectException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @throws ilPasswordException
     */
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilMd5PasswordEncoder $encoder
    ): void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    /**
     * @depends testInstanceCanBeCreated
     */
    public function testNameShouldBeMd5(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertSame('md5', $encoder->getName());
    }
}
