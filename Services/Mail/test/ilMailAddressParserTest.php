<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressParserTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressParserTest extends ilMailBaseTest
{
    const DEFAULT_HOST = 'ilias';

    /**
     * @return array[]
     */
    public function emailAddressesProvider() : array
    {
        return [
            'Username Addresses' => [
                'phpunit@' . self::DEFAULT_HOST . ',phpunit',
                [
                    new ilMailAddress('phpunit', self::DEFAULT_HOST),
                    new ilMailAddress('phpunit', self::DEFAULT_HOST)
                ]
            ],
            'Email Address' => [
                'phpunit@ilias.de',
                [
                    new ilMailAddress('phpunit', 'ilias.de')
                ]
            ],
            'Mailing List Address' => [
                '#il_ml_4711',
                [
                    new ilMailAddress('#il_ml_4711', self::DEFAULT_HOST)
                ]
            ],
            'Role Address' =>  [
                '#il_role_1000',
                [
                    new ilMailAddress('#il_role_1000', self::DEFAULT_HOST)
                ]
            ],
            'Local Role Address' => [
                '#il_crs_member_998',
                [
                    new ilMailAddress('#il_crs_member_998', self::DEFAULT_HOST)
                ]
            ],
            'Course Role Address With Role Names for Course and Role' => [
                '#member@[French Course]',
                [
                    new ilMailAddress('#member', '[French Course]')
                ]
            ],
            'Course Role Recipient with Course Role Address (Role Names for Course and Role)' => [
                'Course Administrator <#admin@[Math Course]>',
                [
                    new ilMailAddress('#admin', '[Math Course]')
                ]
            ],
            'Course Role Recipient with Course Role Address (Numeric Id for Course Role)' => [
                'Course Administrator <#il_crs_admin_2581>',
                [
                    new ilMailAddress('#il_crs_admin_2581', self::DEFAULT_HOST)
                ]
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function emailInvalidAddressesProvider() : array
    {
        return [
            'Trailing Dot in Local Part' => [
                'phpunit.'
            ],
            'Trailing Dot in Local Part of Email Address' => [
                'phpunit.@ilias.de'
            ],
        ];
    }

    /**
     * @param string $addresses
     * @param array $expected
     * @dataProvider emailAddressesProvider
     */
    public function testBuiltInAddressParser(string $addresses, array $expected) : void
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
     * @param string $addresses
     * @param array $expected
     * @dataProvider emailAddressesProvider
     */
    public function testPearAddressParser(string $addresses, array $expected) : void
    {
        $parser = new ilMailPearRfc822WrapperAddressParser($addresses);
        $parsedAddresses = $parser->parse();

        $this->assertCount(count($expected), $parsedAddresses);
        $this->assertEquals($expected, $parsedAddresses);
    }

    /**
     * @dataProvider emailInvalidAddressesProvider
     * @param string $addresses
     */
    public function testExceptionShouldBeRaisedIfEmailCannotBeParsedWithPearAddressParser(string $addresses) : void 
    {
        $this->expectException(ilMailException::class);

        $parser = new ilMailPearRfc822WrapperAddressParser($addresses);
        $parser->parse();
    }

    /**
     * @throws ReflectionException
     */
    public function testWrappingParserDelegatesParsingToAggregatedParser() : void
    {
        $wrappedParser = $this->getMockBuilder(ilBaseMailRfc822AddressParser::class)
            ->setConstructorArgs(['phpunit', 'ilias'])
            ->getMock();
        $wrappedParser->expects($this->once())->method('parse');

        $parser = new ilMailRfc822AddressParser($wrappedParser);
        $parser->parse();
    }
}