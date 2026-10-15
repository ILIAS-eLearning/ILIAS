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

use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Legacy\CtrlProxy;
use ILIAS\StaticURL\Legacy\LanguageProxy;
use ILIAS\StaticURL\Legacy\MainTemplateProxy;
use ILIAS\StaticURL\Legacy\RepositoryTreeProxy;
use ILIAS\StaticURL\Legacy\SettingsProxy;
use ILIAS\StaticURL\Legacy\UserProxy;
use PHPUnit\Framework\MockObject\MockObject;

require_once "Base.php";

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ContextTest extends Base
{
    private RepositoryTreeProxy|MockObject $tree;
    private Access|MockObject $access;

    protected function setUp(): void
    {
        $this->tree = $this->createMock(RepositoryTreeProxy::class);
        $this->access = $this->createMock(Access::class);
    }

    private function context(): Context
    {
        return new Context(
            $this->createMock(GlobalHttpState::class),
            $this->createMock(Factory::class),
            $this->access,
            $this->createMock(URIBuilder::class),
            $this->createMock(UserProxy::class),
            $this->tree,
            $this->createMock(LanguageProxy::class),
            $this->createMock(MainTemplateProxy::class),
            $this->createMock(CtrlProxy::class),
            $this->createMock(SettingsProxy::class),
        );
    }

    public function testFindFirstAccessibleParentReturnsNullForInvalidRefId(): void
    {
        $this->tree->method('isInTree')->willReturn(false);
        $context = $this->context();

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

        $context = $this->context();

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

        $context = $this->context();

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

        $context = $this->context();

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

        $context = $this->context();

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

        $context = $this->context();

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

        $context = $this->context();

        $this->assertNull($context->findFirstAccessibleParentRefId(100));
        $this->assertSame(80, $context->findFirstAccessibleParentRefId(100, 'visible'));
    }
}
