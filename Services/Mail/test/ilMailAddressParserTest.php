<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailAddressParserTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailAddressParserTest extends \ilMailBaseTest
{
    const DEFAULT_HOST = 'ilias';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @return array
     */
    public function emailAddressesProvider() : array
    {
        return [
            ['phpunit@' . self::DEFAULT_HOST . ',phpunit', [
                new \ilMailAddress('phpunit', self::DEFAULT_HOST),
                new \ilMailAddress('phpunit', self::DEFAULT_HOST)
            ]],
            ['phpunit@ilias.de', [
                new \ilMailAddress('phpunit', 'ilias.de')
            ]],
            ['#il_ml_4711', [
                new \ilMailAddress('#il_ml_4711', self::DEFAULT_HOST)
            ]],
            ['#il_role_1000', [
                new \ilMailAddress('#il_role_1000', self::DEFAULT_HOST)
            ]],
            ['#il_crs_member_998', [
                new \ilMailAddress('#il_crs_member_998', self::DEFAULT_HOST)
            ]],
            ['#member@[French Course]', [
                new \ilMailAddress('#member', '[French Course]')
            ]],
            ['Course Administrator <#admin@[Math Course]>', [
                new \ilMailAddress('#admin', '[Math Course]')
            ]],
            ['Course Administrator <#il_crs_admin_2581>', [
                new \ilMailAddress('#il_crs_admin_2581', self::DEFAULT_HOST)
            ]],
        ];
    }

    /**
     * @param string $addresses
     * @param array  $expected
     * @dataProvider emailAddressesProvider
     */
    public function testBuiltInAddressParser(string $addresses, array $expected)
    {
        if (!function_exists('imap_rfc822_parse_adrlist')) {
            $this->markTestSkipped('Skipped test, imap extension required');
        }

        $parser = new \ilMailImapRfc822AddressParser($addresses);
        $parsedAddresses = $parser->parse();

        $this->assertCount(count($expected), $parsedAddresses);
        $this->assertEquals($expected, $parsedAddresses);
    }

    /**
     * @param string $addresses
     * @param array  $expected
     * @dataProvider emailAddressesProvider
     */
    public function testPearAddressParser(string $addresses, array $expected)
    {
        $parser = new \ilMailPearRfc822WrapperAddressParser($addresses);
        $parsedAddresses = $parser->parse();

        $this->assertCount(count($expected), $parsedAddresses);
        $this->assertEquals($expected, $parsedAddresses);
    }

    /**
     *
     */
    public function testWrappingParserDelegatesParsingToAggregatedParser()
    {
        $wrappedParser = $this->getMockBuilder(\ilBaseMailRfc822AddressParser::class)
            ->setConstructorArgs(['phpunit', 'ilias'])
            ->getMock();
        $wrappedParser->expects($this->once())->method('parse');

        $parser = new \ilMailRfc822AddressParser($wrappedParser);
        $parser->parse();
    }
}
