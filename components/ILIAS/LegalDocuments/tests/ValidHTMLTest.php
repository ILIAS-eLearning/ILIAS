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

namespace ILIAS\LegalDocuments\test;

use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ValidHTML;

class ValidHTMLTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ValidHTML::class, new ValidHTML());
    }

    /**
     * @dataProvider textProvider
     * @param string $text
     * @param bool $result
     */
    public function testIsTrue(string $text, bool $result): void
    {
        $instance = new ValidHTML();
        $this->assertSame($result, $instance->isTrue($text));
    }

    public function textProvider(): array
    {
        return [
            'Plain Text' => ['phpunit', false],
            'HTML Fragment' => ['php<b>unit</b>', true],
            'HTML Fragment with Email Address Wrapped in <>' => ['php<b>unit</b> <info@ilias.de>', false],
            'HTML' => ['<html><body>php<b>unit</b></body></html>', true],
            'HTML with Email Address Wrapped in <>' => ['<html><body>php<b>unit</b>Php Unit <info@ilias.de></body></html>', false],
            'HTML with Unsupported Entities' => ['<html><body>php<b>unit</b>Php Unit<figure></figure></body></html>', true],
            'Invalid HTML' => ['<html><body>php<b>unit</b>Php Unit<div </body></html>', false],
        ];
    }
}
