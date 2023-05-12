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

namespace ILIAS\Tests\Setup;

use ILIAS\Setup\ImplementationOfInterfaceFinder;
use PHPUnit\Framework\TestCase;
use ILIAS\Setup\AbstractOfFinder;
use ILIAS\Setup\UsageOfAttributeFinder;

class TestAbstractOfFinder extends AbstractOfFinder
{
    public array $class_names = [];

    protected function getAllClassNames(array $additional_ignore, string $matching_path = null): \Iterator
    {
        foreach ($this->class_names as $class_name) {
            yield $class_name;
        }
    }

    /**
     * @return \Iterator<\Iterator>
     */
    public function getMatchingClassNames(callable $matcher): \Iterator
    {
        yield from $this->genericGetMatchingClassNames($matcher, [], null);
    }
}

interface TestInterface1
{
}

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

interface TestInterface2
{
}

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

interface TestInterface3
{
}

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

#[\Attribute(\Attribute::TARGET_CLASS)]
class TestAttribute1
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class TestAttribute2
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
class TestAttribute3
{
}

#[TestAttribute1()]
class TestClass1 implements TestInterface1
{
}

#[TestAttribute2()]
class TestClass2 implements TestInterface2
{
}

#[TestAttribute1()]
#[TestAttribute2()]
class TestClass3 implements TestInterface1, TestInterface2
{
}

class ClassFinderTest extends TestCase
{
    public function testClassImplementsInterface(): void
    {
        $finder = new ImplementationOfInterfaceFinder();
        $this->assertTrue($finder->isClassMatching(TestInterface1::class, new \ReflectionClass(TestClass1::class)));
        $this->assertTrue($finder->isClassMatching(TestInterface2::class, new \ReflectionClass(TestClass2::class)));
        $this->assertTrue($finder->isClassMatching(TestInterface1::class, new \ReflectionClass(TestClass3::class)));
        $this->assertTrue($finder->isClassMatching(TestInterface2::class, new \ReflectionClass(TestClass3::class)));

        $this->assertFalse($finder->isClassMatching(TestInterface3::class, new \ReflectionClass(TestClass3::class)));
        $this->assertFalse($finder->isClassMatching(TestInterface3::class, new \ReflectionClass(TestClass2::class)));
        $this->assertFalse($finder->isClassMatching(TestInterface3::class, new \ReflectionClass(TestClass1::class)));
    }

    public function testClassUsesAttribute(): void
    {
        $finder = new UsageOfAttributeFinder();
        $this->assertTrue($finder->isClassMatching(TestAttribute1::class, new \ReflectionClass(TestClass1::class)));
        $this->assertTrue($finder->isClassMatching(TestAttribute2::class, new \ReflectionClass(TestClass2::class)));
        $this->assertTrue($finder->isClassMatching(TestAttribute1::class, new \ReflectionClass(TestClass3::class)));
        $this->assertTrue($finder->isClassMatching(TestAttribute2::class, new \ReflectionClass(TestClass3::class)));

        $this->assertFalse($finder->isClassMatching(TestAttribute3::class, new \ReflectionClass(TestClass3::class)));
        $this->assertFalse($finder->isClassMatching(TestAttribute3::class, new \ReflectionClass(TestClass2::class)));
        $this->assertFalse($finder->isClassMatching(TestAttribute3::class, new \ReflectionClass(TestClass1::class)));
    }

    public function testGenericClassMatching(): void
    {
        $finder = new TestAbstractOfFinder();
        $finder->class_names = [
            TestClass1::class,
            TestClass2::class,
            TestClass3::class
        ];

        $all_true = fn (\ReflectionClass $r): bool => true;
        $expected = [
            TestClass1::class,
            TestClass2::class,
            TestClass3::class
        ];
        $this->assertEquals($expected, iterator_to_array($finder->getMatchingClassNames($all_true)));

        $all_false = fn (\ReflectionClass $r): bool => false;
        $expected = [];
        $this->assertEquals($expected, iterator_to_array($finder->getMatchingClassNames($all_false)));

        $only_class1 = fn (\ReflectionClass $r): bool => $r->getName() === TestClass1::class;
        $expected = [
            TestClass1::class
        ];
        $this->assertEquals($expected, iterator_to_array($finder->getMatchingClassNames($only_class1)));
    }
}
