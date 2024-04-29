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
use ILIAS\Refinery\Encode\Transformation\HTMLSpecialCharsAsEntities;
use ValueError;
use TypeError;

class HTMLSpecialCharsAsEntitiesTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(HTMLSpecialCharsAsEntities::class, new HTMLSpecialCharsAsEntities());
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform(string $exptected, string $in): void
    {
        $this->assertSame($exptected, (new HTMLSpecialCharsAsEntities())->transform($in));
    }

    public function testInvalidType(): void
    {
        $this->expectException(TypeError::class);
        (new HTMLSpecialCharsAsEntities())->transform(9);
    }

    public function testInvalidEncoding(): void
    {
        $this->expectException(ValueError::class);
        (new HTMLSpecialCharsAsEntities())->transform(chr(128));
    }

    public static function provideTransformData(): array
    {
        $alpha = implode('', range(ord('A'), ord('z')));
        $numbers = implode('', range(ord('0'), ord('9')));

        return array_map(fn($s) => [htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'), $s], [
            'Empty string' => '',
            'Single quotes' => '\'',
            'Different quotes' => '"Hey, y' . "'" . 'all"',
            'Characters beyond ASCII value 255' => 'Ā',
            'Characters beyond Unicode BMP' => "\xF0\x90\x80\x80",
            'UTF-8' => '커피',
            'Immune chars' => ',.-_',
            'Alpha' => $alpha,
            'Numbers' => $numbers,
            'Basic control characters and null' => "\r\n\t\0",
            'Named entities' => '<>&"',
            'Single space' => ' ',
            'Encode entities' => '&quot;hello&quot;',
            'Braces' => '{[()]}',
        ]);
    }

    public function htmlSpecialCharsProvider(): array
    {
        return [
            '\'' => ['\'', '&#039;'],
            '"' => ['"', '&quot;'],
            '<' => ['<', '&lt;'],
            '>' => ['>', '&gt;'],
            '&' => ['&', '&amp;'],
        ];
    }
}
