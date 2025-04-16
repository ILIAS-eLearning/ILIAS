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

use ILIAS\Data\EmailAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Link Datatype
 */
class EmailAddressTest extends TestCase
{
    #[DataProvider('validEmailProvider')]
    public function testEmailObjectConstruction(
        string $input,
        string $expected_local,
        string $expected_domain
    ): void {
        $email = new EmailAddress($input);

        $this->assertInstanceOf(EmailAddress::class, $email);
        $this->assertEquals($input, $email->getAddressFull(), 'Full address mismatch');
        $this->assertEquals($expected_local, $email->getLocalPart(), 'Local part mismatch');
        $this->assertEquals($expected_domain, $email->getDomainPart(), 'Domain part mismatch');
    }

    #[DataProvider('invalidEmailProvider')]
    public function testEmailThrows(
        string $input,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        new EmailAddress($input);
    }

    #[DataProvider('invalidEmailDomainProvider')]
    public function testEmailDomainThrows(
        string $input,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        new EmailAddress($input);
    }

    #[DataProvider('invalidEmailLocalProvider')]
    public function testEmailLocalThrows(
        string $input,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        new EmailAddress($input);
    }

    #[DataProvider('validAsciiEmailProvider')]
    public function testAsciiEmailFlags(
        string $input,
    ): void {
        $address = new EmailAddress($input);
        $this->assertTrue($address->getIsAscii());
        $this->assertTrue($address->getIsLocalPartAscii());
        $this->assertTrue($address->getIsDomainPartAscii());
    }

    #[DataProvider('validUnicodeEmailProvider')]
    public function testAsciiEmailFlagsWhenUnicode(
        string $input,
    ): void {
        $address = new EmailAddress($input);
        $this->assertFalse($address->getIsAscii());
        $this->assertFalse($address->getIsLocalPartAscii());
        $this->assertFalse($address->getIsDomainPartAscii());
    }
    public static function validEmailProvider(): array
    {
        return [
            "email_ascii" => [
                "john.doe@example.com",
                "john.doe",
                "example.com"
            ],
            "email_unicode" => [
                "jöhn.doe@exämple.com",
                "jöhn.doe",
                "exämple.com"
            ],
            "email_unicode2" => [
                "Ωéяגخ本आ한@Ωéяגخ本आ한.com",
                "Ωéяגخ本आ한",
                "Ωéяגخ本आ한.com"
            ],
            "email_unicode3" => [
                "hi@📖💡🤓.ws",
                "hi",
                "📖💡🤓.ws"
            ],
            "email_short_no_dot" => [
                "hi@example.com",
                "hi",
                "example.com"
            ],
            "email_domain_hyphen" => [
                "someone@domain-with-hyphens.com",
                "someone",
                "domain-with-hyphens.com"
            ],
            "email_domain_subdomain" => [
                "someone@subdomain.domain.org",
                "someone",
                "subdomain.domain.org"
            ],
            "email_domain_many_subdomains" => [
                "someone@en.m.wikipedia.org",
                "someone",
                "en.m.wikipedia.org"
            ],
            "email_localhost" => [
                "someone@localhost",
                "someone",
                "localhost"
            ],
            "email_ip_127" => [
                "someone@[127.0.0.1]",
                "someone",
                "[127.0.0.1]"
            ],
            "email_quoted_string" => [
                '"quoted.string"@example.com',
                '"quoted.string"',
                "example.com"
            ]
        ];
    }
    public static function invalidEmailProvider(): array
    {
        return [
            "email_nothing" => [
                "",
            ],
            "email_just_whitespace" => [
                " ",
            ],
            "email_no_domain" => [
                "john.doe@",
            ],
            "email_no_local" => [
                "@example.com",
            ],
            "email_two_ats" => [
                "john.doe@@example.com",
            ],
        ];
    }
    public static function invalidEmailDomainProvider(): array
    {
        $domainLabelsTooLong = str_repeat('a', 64);
        $domainLabelValidLength = str_repeat('a', 63);
        $domainTooLong = $domainLabelValidLength . "." . $domainLabelValidLength . "." . $domainLabelValidLength . "." . $domainLabelValidLength;
        return [
            "email_domain_no_dot" => [
                "hi@example",
            ],
            "email_domain_labels_too_long" => [
                "local@" . $domainLabelsTooLong . "." . $domainLabelsTooLong,
            ],
            "email_domain_too_long" => [
                "local@" . $domainTooLong,
            ],
            "email_domain_hyphen_start" => [
                "local@-baddomain.com"
            ],
            "email_domain_hyphen_end" => [
                "local@baddomain-.com"
            ],
            "email_consecutive_dots" => [
                "someone@bad..domain.com"
            ],
            "email_domain_label_empty" => [
                "someone@.baddomain.de"
            ],
            "email_domain_whitespace" => [
                "someone@bad domain.com"
            ]
        ];
    }
    public static function invalidEmailLocalProvider(): array
    {
        return [
            "email_local_broken_bytes" => [
                // invalid 2-byte sequence
                "\xC3\x28@example.com",
            ],
            "email_local_dot_start" => [
                ".invalid@example.com",
            ],
            "email_local_dot_end" => [
                "invalid.@example.com",
            ],
            "email_local_two_dots" => [
                "in..valid@example.com",
            ],
            "email_whitespace" => [
                "in valid@example.com"
            ],
            "email_local_emoji" => [
                "📖💡🤓@example.com",
            ],
            "email_local_invalid_control_character" => [
                "hello\x07world@example.com",
            ],
        ];
    }
    public static function validAsciiEmailProvider(): array
    {
        return [
            "email_ascii" => [
                "john.doe@example.com",
            ],
            "email_ascii_symbols" => [
                "&-.+~@example&my-sons.com"
            ]
        ];
    }

    public static function validUnicodeEmailProvider(): array
    {
        return [
            "email_unicode" => [
                "käthe@exämple.com",
            ],
            "email_unicode2" => [
                "frédericö.Ωéяגخ本आ한@äéö📖💡🤓.ws"
            ]
        ];
    }
}
