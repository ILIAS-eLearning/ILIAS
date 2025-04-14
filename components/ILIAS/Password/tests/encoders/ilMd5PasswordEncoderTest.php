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

use PHPUnit\Framework\Attributes\Depends;

final class ilMd5PasswordEncoderTest extends ilPasswordBaseTestCase
{
    public function testInstanceCanBeCreated(): ilMd5PasswordEncoder
    {
        $encoder = new ilMd5PasswordEncoder();
        $this->assertInstanceOf(ilMd5PasswordEncoder::class, $encoder);
        return $encoder;
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testPasswordShouldBeCorrectlyEncoded(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertSame(md5('password'), $encoder->encodePassword('password', ''));
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testPasswordCanBeVerified(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertTrue($encoder->isPasswordValid(md5('password'), 'password', ''));
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testEncoderDoesNotRelyOnSalts(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresSalt());
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testEncoderDoesNotSupportReencoding(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertFalse($encoder->requiresReencoding('hello'));
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testExceptionIsRaisedIfThePasswordExceedsTheSupportedLengthOnEncoding(
        ilMd5PasswordEncoder $encoder
    ): void {
        $this->expectException(ilPasswordException::class);
        $encoder->encodePassword(str_repeat('a', 5000), '');
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testPasswordVerificationShouldFailIfTheRawPasswordExceedsTheSupportedLength(
        ilMd5PasswordEncoder $encoder
    ): void {
        $this->assertFalse($encoder->isPasswordValid('encoded', str_repeat('a', 5000), ''));
    }

    #[Depends('testInstanceCanBeCreated')]
    public function testNameShouldBeMd5(ilMd5PasswordEncoder $encoder): void
    {
        $this->assertSame('md5', $encoder->getName());
    }
}
