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

namespace ILIAS\components\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\components\Test\Readable;
use ILIAS\DI\Container;
use ilAccessHandler;

class ReadableTest extends TestCase
{
    public function testConstruct(): void
    {
        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(Readable::class, new Readable($access));
    }

    public function testReferences(): void
    {
        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();
        $access->method('checkAccess')->with('read', '', 123)->willReturn(true);

        $this->assertTrue((new Readable($access))->references([123]));
    }

    public function testObjectId(): void
    {
        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();

        $access->method('checkAccess')->with('read', '', 456)->willReturn(true);

        $references_of = fn(int $object_id) => $this->assertSame(123, $object_id) ?: [456];

        $this->assertTrue((new Readable($access, $references_of))->objectId(123));
    }
}
