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

namespace ILIAS\Tests\Data;

use ILIAS\Data\LanguageTag;
use PHPUnit\Framework\TestCase;
use ILIAS\Data\NotOKException;

class LanguageTagTest extends TestCase
{
    public function testFromString(): void
    {
        $tag = LanguageTag::fromString('de');
        $this->assertInstanceOf(LanguageTag::class, $tag);
    }

        /**
     * @dataProvider parseProvider
     */
    public function testParse(string $input, bool $isOk): void
    {
        if (!$isOk) {
            $this->expectException(NotOKException::class);
        }
        $tag = LanguageTag::fromString($input);
        $this->assertInstanceOf(LanguageTag::class, $tag);
    }

    public function parseProvider(): array
    {
        return [
            ['de', true],
            ['d$', false],
            ['aa-111', true],
            ['aa-7-123abc-abc-a-12', true],
            ['aa-b1b1b-6a8b-cccccc', true],
            ['aa-b1b1b', true],
            ['aa-bb', true],
            ['aa-bbb-ccc-1111-ccccc-b1b1b', true],
            ['aa-bbb-ccc-ddd', true],
            ['aa-bbb', true],
            ['aa-bbbb-cc', true],
            ['aa-bbbb', true],
            ['aa-x-1234ab-d', true],
            ['aa', true],
            ['aaa-bbb-ccc-ddd-abcd-123-abc123-0abc-b-01-abc123-x-01ab-abc12', true],
            ['aaa-bbb-ccc', true],
            ['aaaa', true],
            ['aaaaa', true],
            ['aaaaaa', true],
            ['aaaaaaa', true],
            ['aaaaaaaa', true],
            ['afb', true],
            ['ar-afb', true],
            ['art-lojban', true],
            ['ast', true],
            ['az-Arab-x-AZE-derbend', true],
            ['az-Latn', true],
            ['cel-gaulish', true],
            ['cmn-Hans-CN', true],
            ['de-CH-1901', true],
            ['de-CH-x-phonebk', true],
            ['de-DE-u-co-phonebk', true],
            ['de-DE', true],
            ['de-Qaaa', true],
            ['de', true],
            ['en-GB-oed', true],
            ['en-US-u-islamcal', true],
            ['en-US-x-twain', true],
            ['en-US', true],
            ['en-a-myext-b-another', true],
            ['en', true],
            ['es-005', true],
            ['es-419', true],
            ['fr-CA', true],
            ['fr', true],
            ['hak', true],
            ['hy-Latn-IT-arevela', true],
            ['i-ami', true],
            ['i-bnn', true],
            ['i-default', true],
            ['i-enochian', true],
            ['i-hak', true],
            ['i-klingon', true],
            ['i-lux', true],
            ['i-mingo', true],
            ['i-navajo', true],
            ['i-pwn', true],
            ['i-tao', true],
            ['i-tay', true],
            ['i-tsu', true],
            ['ja', true],
            ['mas', true],
            ['no-bok', true],
            ['no-nyn', true],
            ['qaa-Qaaa-QM-x-southern', true],
            ['sgn-BE-FR', true],
            ['sgn-BE-NL', true],
            ['sgn-CH-DE', true],
            ['sl-IT-nedis', true],
            ['sl-nedis', true],
            ['sl-rozaj-biske', true],
            ['sl-rozaj', true],
            ['sr-Cyrl', true],
            ['sr-Latn-QM', true],
            ['sr-Latn-RS', true],
            ['sr-Latn', true],
            ['sr-Qaaa-RS', true],
            ['x-111-aaaaa-BBB', true],
            ['x-whatever', true],
            ['yue-HK', true],
            ['zh-CN-a-myext-x-private', true],
            ['zh-Hans-CN', true],
            ['zh-Hans', true],
            ['zh-Hant-HK', true],
            ['zh-Hant', true],
            ['zh-cmn-Hans-CN', true],
            ['zh-guoyu', true],
            ['zh-hakka', true],
            ['zh-min-nan', true],
            ['zh-min', true],
            ['zh-xiang', true],
            ['zh-yue-HK', true],
            ['zh-yue', true],
        ];
    }
}
