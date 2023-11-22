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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Dependencies\Resolver;
use ILIAS\Component\Dependencies as D;
use ILIAS\Component\Component;

class ResolverTest extends TestCase
{
    protected Resolver $resolver;

    public function setUp(): void
    {
        $this->resolver = new Resolver();
    }

    public function testEmptyComponentSet(): void
    {
        $result = $this->resolver->resolveDependencies([]);

        $this->assertEquals([], $result);
    }

    public function testResolvePull(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $pull = new D\In(D\InType::PULL, $name);
        $provide = new D\Out(D\OutType::PROVIDE, $name, null, []);

        $c1 = new D\OfComponent($component, $pull);
        $c2 = new D\OfComponent($component, $provide);

        $result = $this->resolver->resolveDependencies([], $c1, $c2);

        $pull = new D\In(D\InType::PULL, $name);
        $provide = new D\Out(D\OutType::PROVIDE, $name, null, []);
        $pull->addResolution($provide);

        $c1 = new D\OfComponent($component, $pull);
        $c2 = new D\OfComponent($component, $provide);

        $this->assertEquals([$c1, $c2], $result);
    }

    public function testPullFailsNotExistent(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $pull = new D\In(D\InType::PULL, $name);

        $c1 = new D\OfComponent($component, $pull);

        $this->resolver->resolveDependencies([], $c1);
    }

    public function testPullFailsDuplicate(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $pull = new D\In(D\InType::PULL, $name);
        $provide1 = new D\Out(D\OutType::PROVIDE, $name, null, []);
        $provide2 = new D\Out(D\OutType::PROVIDE, $name, null, []);

        $c1 = new D\OfComponent($component, $pull);
        $c2 = new D\OfComponent($component, $provide1);
        $c3 = new D\OfComponent($component, $provide2);

        $this->resolver->resolveDependencies([], $c1, $c2, $c3);
    }

    public function testEmptySeek(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $seek = new D\In(D\InType::SEEK, $name);

        $c1 = new D\OfComponent($component, $seek);

        $result = $this->resolver->resolveDependencies([], $c1);

        $this->assertEquals([$c1], $result);
    }

    public function testResolveSeek(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $seek = new D\In(D\InType::SEEK, $name);
        $contribute1 = new D\Out(D\OutType::CONTRIBUTE, $name, null, []);
        $contribute2 = new D\Out(D\OutType::CONTRIBUTE, $name, null, []);

        $c1 = new D\OfComponent($component, $seek);
        $c2 = new D\OfComponent($component, $contribute1);
        $c3 = new D\OfComponent($component, $contribute2);

        $result = $this->resolver->resolveDependencies([], $c1, $c2, $c3);


        $seek = new D\In(D\InType::SEEK, $name);
        $contribute1 = new D\Out(D\OutType::CONTRIBUTE, $name, null, []);
        $contribute2 = new D\Out(D\OutType::CONTRIBUTE, $name, null, []);
        $seek->addResolution($contribute1);
        $seek->addResolution($contribute2);

        $c1 = new D\OfComponent($component, $seek);
        $c2 = new D\OfComponent($component, $contribute1);
        $c3 = new D\OfComponent($component, $contribute2);

        $this->assertEquals([$c1, $c2, $c3], $result);
    }

    public function testResolveUseOneOption(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $use = new D\In(D\InType::USE, $name);
        $implement = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement);

        $result = $this->resolver->resolveDependencies([], $c1, $c2);

        $use = new D\In(D\InType::USE, $name);
        $implement = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $use->addResolution($implement);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement);

        $this->assertEquals([$c1, $c2], $result);
    }

    public function testUseFailsNotExistent(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $use = new D\In(D\InType::USE, $name);

        $c1 = new D\OfComponent($component, $use);

        $this->resolver->resolveDependencies([], $c1);
    }

    public function testUseFailsDuplicate(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $use = new D\In(D\InType::USE, $name);
        $implement1 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement1);
        $c3 = new D\OfComponent($component, $implement2);

        $this->resolver->resolveDependencies([], $c1, $c2, $c3);
    }

    public function testUseDisambiguateDuplicateSpecific(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $use = new D\In(D\InType::USE, $name);
        $implement1 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\OtherClass"], []);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement1);
        $c3 = new D\OfComponent($component, $implement2);

        $disambiguation = [
            get_class($component) => [
                TestInterface::class => "Some\\OtherClass"
            ]
        ];

        $result = $this->resolver->resolveDependencies($disambiguation, $c1, $c2, $c3);

        $use = new D\In(D\InType::USE, $name);
        $implement1 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\OtherClass"], []);
        $use->addResolution($implement2);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement1);
        $c3 = new D\OfComponent($component, $implement2);

        $this->assertEquals([$c1, $c2, $c3], $result);
    }

    public function testUseDisambiguateDuplicateGeneric(): void
    {
        $component = $this->createMock(Component::class);

        $name = TestInterface::class;

        $use = new D\In(D\InType::USE, $name);
        $implement1 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\OtherClass"], []);

        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement1);
        $c3 = new D\OfComponent($component, $implement2);

        $disambiguation = [
            "*" => [
                TestInterface::class => "Some\\OtherClass"
            ]
        ];

        $result = $this->resolver->resolveDependencies($disambiguation, $c1, $c2, $c3);

        $use = new D\In(D\InType::USE, $name);
        $implement1 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\Class"], []);
        $implement2 = new D\Out(D\OutType::IMPLEMENT, $name, ["class" => "Some\\OtherClass"], []);
        $use->addResolution($implement2);


        $c1 = new D\OfComponent($component, $use);
        $c2 = new D\OfComponent($component, $implement1);
        $c3 = new D\OfComponent($component, $implement2);

        $this->assertEquals([$c1, $c2, $c3], $result);
    }

    public function testFindSimpleCycle(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;
        $name2 = TestInterface2::class;

        $pull = new D\In(D\InType::PULL, $name);
        $provide = new D\Out(D\OutType::PROVIDE, $name2, "Some\\Class", [$pull], []);
        $c1 = new D\OfComponent($component, $pull, $provide);

        $pull = new D\In(D\InType::PULL, $name2);
        $provide = new D\Out(D\OutType::PROVIDE, $name, "Some\\OtherClass", [$pull], []);
        $c2 = new D\OfComponent($component, $pull, $provide);


        $result = $this->resolver->resolveDependencies([], $c1, $c2);
    }

    public function testFindLongerCycle(): void
    {
        $this->expectException(\LogicException::class);

        $component = $this->createMock(Component::class);

        $name = TestInterface::class;
        $name2 = TestInterface2::class;
        $name3 = TestInterface3::class;

        $pull = new D\In(D\InType::PULL, $name);
        $provide = new D\Out(D\OutType::PROVIDE, $name2, "Some\\Class", [$pull], []);
        $c1 = new D\OfComponent($component, $pull, $provide);

        $pull = new D\In(D\InType::PULL, $name2);
        $provide = new D\Out(D\OutType::PROVIDE, $name3, "Some\\OtherClass", [$pull], []);
        $c2 = new D\OfComponent($component, $pull, $provide);

        $pull = new D\In(D\InType::PULL, $name3);
        $provide = new D\Out(D\OutType::PROVIDE, $name, "Some\\OtherOtherClass", [$pull], []);
        $c3 = new D\OfComponent($component, $pull, $provide);


        $result = $this->resolver->resolveDependencies([], $c1, $c2, $c3);
    }
}
