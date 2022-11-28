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

namespace ILIAS\Tests\Data\LanguageTag;

use ILIAS\Data\LanguageTag\Standard;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use ILIAS\Data\LanguageTag\PrivateUse;

class StandardTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Standard::class, new Standard('hej', null, null, null, null, null, null));
    }

    public function testLanguage(): void
    {
        $this->assertEquals('hej', (new Standard('hej', null, null, null, null, null, null))->language());
    }

    public function testExtlang(): void
    {
        $this->assertEquals('hej', (new Standard('ho', 'hej', null, null, null, null, null))->extlang());
    }

    public function testScript(): void
    {
        $this->assertEquals('hej', (new Standard('ho', null, 'hej', null, null, null, null))->script());
    }

    public function testRegion(): void
    {
        $this->assertEquals('hej', (new Standard('ho', null, null, 'hej', null, null, null))->region());
    }

    public function testVariant(): void
    {
        $this->assertEquals('hej', (new Standard('ho', null, null, null, 'hej', null, null))->variant());
    }

    public function testExtension(): void
    {
        $this->assertEquals('hej', (new Standard('ho', null, null, null, null, 'hej', null))->extension());
    }

    public function testPrivateuse(): void
    {
        $privateuse = $this->getMockBuilder(PrivateUse::class)->disableOriginalConstructor()->getMock();
        $privateuse->method('value')->willReturn('hej');

        $this->assertEquals($privateuse, (new Standard('ho', null, null, null, null, null, $privateuse))->privateuse());
    }

    public function testValue(): void
    {
        $privateuse = $this->getMockBuilder(PrivateUse::class)->disableOriginalConstructor()->getMock();
        $privateuse->method('value')->willReturn('hej');

        $this->assertEquals('hej', (new Standard('hej', null, null, null, null, null, null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', 'hej', null, null, null, null, null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', null, 'hej', null, null, null, null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', null, null, 'hej', null, null, null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', null, null, null, 'hej', null, null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', null, null, null, null, 'hej', null))->value());
        $this->assertEquals('hej-hej', (new Standard('hej', null, null, null, null, null, $privateuse))->value());
    }
}
