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

use PHPUnit\Framework\TestCase;
use ILIAS\UICore\Ctrl\CommandClassFactory;
use ILIAS\UICore\Ctrl\ContributedCommandClassResolver;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ContributedCommandClassResolverTest extends TestCase
{
    public function testClassWithoutFactoryIsCreatedWithConstructor(): void
    {
        $resolver = new ContributedCommandClassResolver([]);

        $instance = $resolver->create($this->createStub(ilCtrlInterface::class), ArrayObject::class, [1, 2]);

        $this->assertInstanceOf(ArrayObject::class, $instance);
        $this->assertSame([1, 2], $instance->getArrayCopy());
    }

    public function testClassWithFactoryIsCreatedByFactory(): void
    {
        $expected = new ArrayObject();
        $ctrl = $this->createStub(ilCtrlInterface::class);
        $factory = $this->createMock(CommandClassFactory::class);
        $factory->method('getCommandClasses')->willReturn([ArrayObject::class]);
        $factory
            ->expects($this->once())
            ->method('create')
            ->with($ctrl, 'arrayobject', 42, 'x')
            ->willReturn($expected);

        $resolver = new ContributedCommandClassResolver([$factory]);

        $this->assertSame($expected, $resolver->create($ctrl, 'arrayobject', 42, 'x'));
    }

    public function testFactoriesAreNotAskedBeforeFirstUse(): void
    {
        $factory = $this->createMock(CommandClassFactory::class);
        $factory->expects($this->never())->method('getCommandClasses');

        new ContributedCommandClassResolver([$factory]);
    }

    public function testClassContributedTwiceFails(): void
    {
        $factory_one = $this->createStub(CommandClassFactory::class);
        $factory_one->method('getCommandClasses')->willReturn([ArrayObject::class]);
        $factory_two = $this->createStub(CommandClassFactory::class);
        $factory_two->method('getCommandClasses')->willReturn(['\\' . ArrayObject::class]);

        $resolver = new ContributedCommandClassResolver([$factory_one, $factory_two]);

        $this->expectException(LogicException::class);
        $resolver->create($this->createStub(ilCtrlInterface::class), ArrayObject::class);
    }
}
