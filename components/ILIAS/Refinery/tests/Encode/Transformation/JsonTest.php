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
use ILIAS\Refinery\Encode\Transformation\Json;
use JsonException;

require_once __DIR__ . '/ProvideUTF8CodepointRange.php';

class JsonTest extends TestCase
{
    use ProvideUTF8CodepointRange;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(Json::class, new Json());
    }

    /**
     * @dataProvider provideTransformData
     */
    public function testTransform(string $exptected, string $in, string $method): void
    {
        $this->$method($exptected, (new Json())->transform($in));
    }

    public function testInvalidEncoding(): void
    {
        $this->expectException(JsonException::class);
        (new Json())->transform(chr(128));
    }

    public static function provideTransformData(): array
    {
        return [
            'Empty string' => ['""', '', 'assertSame'],
            'UTF-8' => ['"\uc548\ub155\ud558\uc11c\u3163\uc694"', '안녕하서ㅣ요', 'assertSame'],
            'HTML special chars' => ['"\\u003C\\u003E\\u0027\\u0022\\u0026"', '<>\'"&', 'assertSame'],
            'Characters beyond ASCII value 255' => ['"\\u0100"', 'Ā', 'assertSame'],
            'Characters beyond Unicode BMP' => ['"\\ud800\\udc00"', "\xF0\x90\x80\x80", 'assertSame'],
            'Basic control characters and null' => ['"\\r\\n\\t\\u0000"', "\r\n\t\0", 'assertSame'],
            'Single space' => ['" "', ' ', 'assertSame'],
            'Slash' => ['"\\/"', '/', 'assertSame'],
        ];
    }
}
