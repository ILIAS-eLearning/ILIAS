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

namespace ILIAS\Modules\Test\test;

use PHPUnit\Framework\TestCase;
use ILIAS\Modules\Test\Readable;
use ILIAS\DI\Container;
use ilAccessHandler;

class ReadableTest extends TestCase
{
    public function testConstruct(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();

        $this->assertInstanceOf(Readable::class, new Readable($container));
    }

    public function testReferences(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();

        $container->method('access')->willReturn($access);
        $access->method('checkAccess')->with('read', '', 123)->willReturn(true);

        $this->assertTrue((new Readable($container))->references([123]));
    }

    public function testObjectId(): void
    {
        $container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $access = $this->getMockBuilder(ilAccessHandler::class)->disableOriginalConstructor()->getMock();

        $container->method('access')->willReturn($access);
        $access->method('checkAccess')->with('read', '', 456)->willReturn(true);

        $references_of = function (int $object_id): array {
            $this->assertSame(123, $object_id);
            return [456];
        };

        $this->assertTrue((new Readable($container, $references_of))->objectId(123));
    }
}
