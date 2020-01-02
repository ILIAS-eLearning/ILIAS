<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
declare(strict_types=1);

/**
 * Class ilWhiteListUrlValidatorTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilWhiteListUrlValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function domainProvider() : array
    {
        return [
            ['', [], false],
            ['ilias.de', [], false],
            ['https://ilias.de', [], false],
            ['ilias.de', ['ilias.de'], false],
            ['https://ilias.de', ['ilias.de'], true],
            ['https://www.ilias.de', ['ilias.de'], true],
            ['https://server01.www.ilias.de', ['ilias.de'], true],
            ['https://server01.www.ilias.de', ['.ilias.de'], true],
            ['https://server01.www.ilias.de', ['www.ilias.de'], true],
            ['https://server01.www.ilias.de', ['.www.ilias.de'], true],
            ['https://server01.www.ilias.de', ['server01.www.ilias.de'], true],
            ['https://server01.www.ilias.de', ['.server01.www.ilias.de'], false],
        ];
    }

    /**
     * @dataProvider domainProvider
     * @param string $domain
     * @param array  $whitelist
     * @param bool   $result
     */
    public function testValidator(string $domain, array $whitelist, bool $result)
    {
        require_once 'Services/AuthApache/classes/class.ilWhiteListUrlValidator.php';
        $this->assertEquals((new ilWhiteListUrlValidator($domain, $whitelist))->isValid(), $result);
    }
}
