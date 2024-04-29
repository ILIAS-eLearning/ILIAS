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

namespace ILIAS\Tests\Refinery\Encode\Transformation;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Encode\Transformation\URL;
use ValueError;
use TypeError;

class URLTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(URL::class, new URL());
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform(string $exptected, string $in): void
    {
        $this->assertSame($exptected, (new URL())->transform($in));
    }

    public function testInvalidEncoding(): void
    {
        $this->expectException(ValueError::class);
        (new URL())->transform(chr(128));
    }

    public function testInvalidType(): void
    {
        $this->expectException(TypeError::class);
        (new URL())->transform(9);
    }

    public static function provideTransformData(): array
    {
        $alpha = implode('', range(ord('A'), ord('z')));
        $numbers = implode('', range(ord('0'), ord('9')));

        $data = array_map(fn($s) => [rawurlencode($s), $s], [
            'Empty string' => '',
            'Alpha' => $alpha,
            'Numbers' => $numbers,
            'Quotes' => "'" . '"',
            'Different quotes' => '"Hey, y' . "'" . 'all"',
            'UTF-8' => '두유',
            'Encode entities' => '&quot;hello&quot;',
            'Braces' => '{<>}',
            'HTML special chars' => '<>\'"&',
            'Characters beyond ASCII value 255' => 'Ā',
            'Punctuation and unreserved check' => ',._-:;',
            'Basic control characters and null' => "\r\n\t\0",
        ]);

        return [
            ...$data,
            'PHP quirks from the past' => ['%20~%2B', ' ~+'],
        ];
    }
}
