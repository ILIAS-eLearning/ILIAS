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
 * Class ilMailAddressParserTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressParserTest extends ilMailBaseTest
{
    private const DEFAULT_HOST = 'ilias';

    /**
     * @return array[]
     */
    public function emailAddressesProvider(): array
    {
        return [
            'Username Addresses' => [
                'phpunit@' . self::DEFAULT_HOST . ',phpunit',
                [
                    new ilMailAddress('phpunit', self::DEFAULT_HOST),
                    new ilMailAddress('phpunit', self::DEFAULT_HOST),
                ],
            ],
            'Email Address' => [
                'phpunit@ilias.de',
                [
                    new ilMailAddress('phpunit', 'ilias.de'),
                ],
            ],
            'Email Addresses with Umlauts' => [
                'phpünit@ilias.de,phpnitü@ilias.de,üphpnit@iliäs.de',
                [
                    new ilMailAddress('phpünit', 'ilias.de'),
                    new ilMailAddress('phpnitü', 'ilias.de'),
                    new ilMailAddress('üphpnit', 'iliäs.de'),
                ],
            ],
            'Trailing Dot in Local Part of Email Address' => [
                'phpunit.@ilias.de',
                [
                    new ilMailAddress('phpunit.', 'ilias.de'),
                ],
            ],
            'Mailing List Address' => [
                '#il_ml_4711',
                [
                    new ilMailAddress('#il_ml_4711', self::DEFAULT_HOST),
                ],
            ],
            'Role Address' => [
                '#il_role_1000',
                [
                    new ilMailAddress('#il_role_1000', self::DEFAULT_HOST),
                ],
            ],
            'Local Role Address' => [
                '#il_crs_member_998',
                [
                    new ilMailAddress('#il_crs_member_998', self::DEFAULT_HOST),
                ],
            ],
            'Course Role Address With Role Names for Course and Role' => [
                '#member@[French Course]',
                [
                    new ilMailAddress('#member', '[French Course]'),
                ],
            ],
            'Course Role Recipient with Course Role Address (Role Names for Course and Role)' => [
                'Course Administrator <#admin@[Math Course]>',
                [
                    new ilMailAddress('#admin', '[Math Course]'),
                ],
            ],
            'Course Role Recipient with Course Role Address (Numeric Id for Course Role)' => [
                'Course Administrator <#il_crs_admin_2581>',
                [
                    new ilMailAddress('#il_crs_admin_2581', self::DEFAULT_HOST),
                ],
            ],
            'sepp@some.where;done@web.de' => [
                // https://mantis.ilias.de/view.php?id=30306
                'sepp@some.where;done@web.de',
                [
                    new ilMailAddress('sepp', 'some.where'),
                    new ilMailAddress('done', 'web.de'),
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function emailInvalidAddressesProvider(): array
    {
        return [
            'Trailing Quote in Local Part' => [
                'phpunit"@',
            ],
            'Trailing Quote in Local Part of Email Address' => [
                'phpunit"@ilias.de',
            ],
        ];
    }

    /**
     * @dataProvider emailAddressesProvider
     */
    public function testBuiltInAddressParser(string $addresses, array $expected): void
    {
        if (!function_exists('imap_rfc822_parse_adrlist')) {
            $this->markTestSkipped('Skipped test, imap extension required');
        }

        $parser = new ilMailImapRfc822AddressParser($addresses);
        $parsedAddresses = $parser->parse();

        $this->assertCount(count($expected), $parsedAddresses);
        $this->assertEquals($expected, $parsedAddresses);
    }

    /**
     * @dataProvider emailAddressesProvider
     */
    public function testPearAddressParser(string $addresses, array $expected): void
    {
        $parser = new ilMailPearRfc822WrapperAddressParser($addresses);
        $parsedAddresses = $parser->parse();

        $this->assertEquals($expected, $parsedAddresses);
        $this->assertCount(count($expected), $parsedAddresses);
    }

    public function testAddressParserReturnsEmptyListIfAnEmptyAddressStringIsGiven(): void
    {
        $parser = new ilMailPearRfc822WrapperAddressParser('');
        $parsedAddresses = $parser->parse();

        $this->assertCount(0, $parsedAddresses);
    }

    /**
     * @dataProvider emailInvalidAddressesProvider
     */
    public function testExceptionShouldBeRaisedIfEmailCannotBeParsedWithPearAddressParser(string $addresses): void
    {
        $this->expectException(ilMailException::class);

        $parser = new ilMailPearRfc822WrapperAddressParser($addresses);
        $parser->parse();
    }

    /**
     * @throws ReflectionException
     */
    public function testWrappingParserDelegatesParsingToAggregatedParser(): void
    {
        $wrappedParser = $this->getMockBuilder(ilBaseMailRfc822AddressParser::class)
            ->setConstructorArgs(['phpunit', 'ilias'])
            ->getMock();
        $wrappedParser->expects($this->once())->method('parse');

        $parser = new ilMailRfc822AddressParser($wrappedParser);
        $parser->parse();
    }
}
