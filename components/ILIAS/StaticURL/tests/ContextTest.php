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

namespace ILIAS\StaticURL\Tests;

use ILIAS\DI\Container;
use ILIAS\StaticURL\Context;
use PHPUnit\Framework\MockObject\MockObject;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ContextTest extends Base
{
    private Container|MockObject $container;
    private \ilTree|MockObject $tree;
    private \ilAccessHandler|MockObject $access;

    protected function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
        $this->tree = $this->createMock(\ilTree::class);
        $this->access = $this->createMock(\ilAccessHandler::class);
        $this->container->method('repositoryTree')->willReturn($this->tree);
        $this->container->method('access')->willReturn($this->access);
    }

    public function testFindFirstAccessibleParentReturnsNullForInvalidRefId(): void
    {
        $this->tree->method('isInTree')->willReturn(false);
        $context = new Context($this->container);

        $this->assertNull($context->findFirstAccessibleParentRefId(0));
        $this->assertNull($context->findFirstAccessibleParentRefId(-5));
        $this->assertNull($context->findFirstAccessibleParentRefId(42));
    }

    public function testFindFirstAccessibleParentReturnsDirectReadableParent(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 50],
        ]);
        $this->access->method('checkAccess')->willReturnCallback(
            static fn(string $perm, string $_, int $ref_id): bool => $perm === 'read' && $ref_id === 50
        );

        $context = new Context($this->container);

        $this->assertSame(50, $context->findFirstAccessibleParentRefId(100));
    }

    public function testFindFirstAccessibleParentWalksUntilReadPermissionFound(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 80],
            [80, 60],
            [60, 40],
        ]);
        $this->access->method('checkAccess')->willReturnCallback(
            static fn(string $perm, string $_, int $ref_id): bool => $perm === 'read' && $ref_id === 40
        );

        $context = new Context($this->container);

        $this->assertSame(40, $context->findFirstAccessibleParentRefId(100));
    }

    public function testFindFirstAccessibleParentReturnsNullWhenNoParentReadable(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 80],
            [80, 1],
            [1, 0],
        ]);
        $this->access->method('checkAccess')->willReturn(false);

        $context = new Context($this->container);

        $this->assertNull($context->findFirstAccessibleParentRefId(100));
    }

    public function testFindFirstAccessibleParentStopsAtRoot(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 1],
        ]);
        $this->access->method('checkAccess')->willReturn(false);

        $context = new Context($this->container);

        $this->assertNull($context->findFirstAccessibleParentRefId(100));
    }

    public function testFindFirstAccessibleParentHandlesCycle(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 80],
            [80, 100],
        ]);
        $this->access->method('checkAccess')->willReturn(false);

        $context = new Context($this->container);

        $this->assertNull($context->findFirstAccessibleParentRefId(100));
    }

    public function testFindFirstAccessibleParentHonoursCustomPermission(): void
    {
        $this->tree->method('isInTree')->willReturn(true);
        $this->tree->method('getRootId')->willReturn(1);
        $this->tree->method('getParentId')->willReturnMap([
            [100, 80],
        ]);
        $this->access->method('checkAccess')->willReturnCallback(
            static fn(string $perm, string $_, int $ref_id): bool => $perm === 'visible' && $ref_id === 80
        );

        $context = new Context($this->container);

        $this->assertNull($context->findFirstAccessibleParentRefId(100));
        $this->assertSame(80, $context->findFirstAccessibleParentRefId(100, 'visible'));
    }
}
