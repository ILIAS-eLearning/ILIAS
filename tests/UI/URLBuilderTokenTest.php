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

namespace ILIAS\UI;

use PHPUnit\Framework\TestCase;

class URLBuilderTokenTest extends TestCase
{
    public function testConstruct(): void
    {
        $token = new URLBuilderToken(['test'], 'foo');
        $this->assertInstanceOf(URLBuilderToken::class, $token);
        $this->assertIsString($token->getToken());
        $this->assertNotEmpty($token->getToken());
    }

    public function testTokenLength(): void
    {
        $token = new URLBuilderToken(['test'], 'foo');
        $this->assertEquals(URLBuilderToken::TOKEN_LENGTH, strlen($token->getToken()));
    }

    public function testTokenName(): void
    {
        $token = new URLBuilderToken(['test'], 'foo');
        $this->assertEquals('test_foo', $token->getName());
    }
}
