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

namespace ILIAS\ResourceStorage\Information;

use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class MimeTypeSanitizerTest extends TestCase
{
    public static function validMimeTypeProvider(): \Iterator
    {
        yield 'simple' => ['image/svg+xml', 'image/svg+xml'];
        yield 'vendor type' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        yield 'upper case' => ['Text/HTML', 'text/html'];
        yield 'parameters are dropped' => ['text/plain; charset=UTF-8', 'text/plain'];
        yield 'whitespace is trimmed' => [' application/pdf ', 'application/pdf'];
    }

    /**
     * @dataProvider validMimeTypeProvider
     */
    public function testValidMimeTypesAreNormalised(string $given, string $expected): void
    {
        $this->assertSame($expected, (new MimeTypeSanitizer())->sanitize($given));
    }

    public static function invalidMimeTypeProvider(): \Iterator
    {
        yield 'xss payload' => ["<img src=x onmouseover='alert`xss`'> / blabla"];
        yield 'attribute breakout' => ['image/png" onload="alert(1)'];
        yield 'tag in subtype' => ['text/<script>'];
        yield 'missing subtype' => ['image'];
        yield 'empty' => [''];
        yield 'unknown' => ['unknown'];
        yield 'header injection' => ["text/plain\r\nX-Injected: 1"];
    }

    /**
     * @dataProvider invalidMimeTypeProvider
     */
    public function testInvalidMimeTypesFallBackToOctetStream(string $given): void
    {
        $this->assertSame('application/octet-stream', (new MimeTypeSanitizer())->sanitize($given));
    }

    public function testFileInformationSanitisesTheMimeType(): void
    {
        $information = (new FileInformation())->setMimeType("<img src=x onerror='alert(1)'>/x");

        $this->assertSame('application/octet-stream', $information->getMimeType());
    }
}
