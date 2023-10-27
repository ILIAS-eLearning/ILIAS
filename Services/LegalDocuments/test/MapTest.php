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
use ILIAS\LegalDocuments\Map;

class MapTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Map::class, new Map());
    }

    public function testAdd(): void
    {
        $this->assertSame(['foo' => ['bar']], (new Map())->add('foo', 'bar')->value());
    }

    public function testSet(): void
    {
        $this->assertSame(['foo' => ['bar' => 'baz']], (new Map())->set('foo', 'bar', 'baz')->value());
    }

    public function testHas(): void
    {
        $this->assertTrue((new Map(['foo' => ['bar' => 'baz']]))->has('foo', 'bar'));
        $this->assertFalse((new Map(['foo' => ['bar' => 'baz']]))->has('hoo', 'bar'));
    }

    public function testAppend(): void
    {
        $this->assertSame(['foo' => [1, 2], 'bar' => [1, 2]], (new Map(['foo' => [1, 2]]))->append(new Map(['bar' => [1, 2]]))->value());
    }

    public function testValue(): void
    {
        $this->assertSame(['a' => [], 'b' => ['foo' => 'bar']], (new Map(['a' => [], 'b' => ['foo' => 'bar']]))->value());
    }
}
