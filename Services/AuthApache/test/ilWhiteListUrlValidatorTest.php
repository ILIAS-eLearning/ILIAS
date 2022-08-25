<?php

declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * Class ilWhiteListUrlValidatorTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilWhiteListUrlValidatorTest extends TestCase
{
    public function domainProvider(): array
    {
        return [
            'Empty String / Empty Whitelist' => ['', [], false],
            'Host without Schema / Empty Whitelist' => ['ilias.de', [], false],
            'Schema with Host / Empty Whitelist' => ['https://ilias.de', [], false],
            'Host without Schema' => ['ilias.de', ['ilias.de'], false],
            'Schema with Host' => ['https://ilias.de', ['ilias.de'], true],
            'Sub Domain' => ['https://www.ilias.de', ['ilias.de'], true],
            'Multiple Sub Domains' => ['https://server01.www.ilias.de', ['ilias.de'], true],
            'Multiple Sub Domains / Whitelist Entry with Leading Dot' => [
                'https://server01.www.ilias.de',
                ['.ilias.de'],
                true
            ],
            'Multiple Sub Domains / Whitelist Entry with Sub Domain' => [
                'https://server01.www.ilias.de',
                ['www.ilias.de'],
                true
            ],
            'Multiple Sub Domains / Whitelist Entry with Sub Domain and Leading Dot' => [
                'https://server01.www.ilias.de',
                ['.www.ilias.de'],
                true
            ],
            'Multiple Sub Domains / Whitelist Entry with Multiple Sub Domains' => [
                'https://server01.www.ilias.de',
                ['server01.www.ilias.de'],
                true
            ],
            'Multiple Sub Domains / Whitelist Entry with Multiple Sub Domains and Leading Dot' => [
                'https://server01.www.ilias.de',
                ['.server01.www.ilias.de'],
                false
            ],
        ];
    }

    /**
     * @dataProvider domainProvider
     * @param string $domain
     * @param array $whitelist
     * @param bool $result
     */
    public function testValidator(string $domain, array $whitelist, bool $result): void
    {
        $this->assertSame((new ilWhiteListUrlValidator($domain, $whitelist))->isValid(), $result);
    }
}
