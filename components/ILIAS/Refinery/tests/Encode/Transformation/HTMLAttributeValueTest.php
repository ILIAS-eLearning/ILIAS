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
use ILIAS\Refinery\Encode\Transformation\HTMLAttributeValue;
use ValueError;
use TypeError;

require_once __DIR__ . '/ProvideUTF8CodepointRange.php';

class HTMLAttributeValueTest extends TestCase
{
    use ProvideUTF8CodepointRange;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(HTMLAttributeValue::class, new HTMLAttributeValue());
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform(string $exptected, string $in, string $method): void
    {
        $this->$method($exptected, (new HTMLAttributeValue())->transform($in));
    }

    public function testInvalidEncoding(): void
    {
        $this->expectException(ValueError::class);
        (new HTMLAttributeValue())->transform(chr(128));
    }

    public function testInvalidType(): void
    {
        $this->expectException(TypeError::class);
        (new HTMLAttributeValue())->transform(8);
    }

    public static function provideTransformData(): array
    {
        return [
            'Empty string' => ['', '', 'assertSame'],
            'Single quote' => ['&#x27;', '\'', 'assertSame'],
            'Different quotes' => ['&quot;Hey,&#x20;y&#x27;all&quot;', '"Hey, y' . "'" . 'all"', 'assertSame'],
            'UTF-8' => ['&#xAE40;&#xCE58;', '김치', 'assertSame'],
            'Characters beyond ASCII value 255' => ['&#x0100;', 'Ā', 'assertSame'],
            'Characters beyond Unicode BMP' => ['&#x10000;', "\xF0\x90\x80\x80", 'assertSame'],
            'Printable control characters' => ['&#x0D;&#x0A;&#x09;', "\r\n\t", 'assertSame'],
            'Unprintable control characters' => ['&#xFFFD;&#xFFFD;&#xFFFD;&#xFFFD;', "\0\1\x1F\x7F", 'assertSame'],
            'Named entities' => ['&lt;&gt;&amp;&quot;', '<>&"', 'assertSame'],
            'Single space' => ['&#x20;', ' ', 'assertSame'],
            'Encode entities' => ['&amp;quot&#x3B;hello&amp;quot&#x3B;', '&quot;hello&quot;', 'assertSame'],
            'Braces' => ['&#x7B;&#x5B;&#x28;&#x29;&#x5D;&#x7D;', '{[()]}', 'assertSame'],
            ...self::oneByteRangeExcept([',', '.', '-', '_']),
        ];
    }
}
