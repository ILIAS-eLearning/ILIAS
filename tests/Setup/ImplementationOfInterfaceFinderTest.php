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

namespace ILIAS\Tests\Setup;

use ILIAS\Setup\ImplementationOfInterfaceFinder;
use PHPUnit\Framework\TestCase;

class ImplementationOfInterfaceFinderForTest extends ImplementationOfInterfaceFinder
{
    public array $class_names = [];

    protected function getAllClassNames(array $additional_ignore, string $matching_path = null): \Iterator
    {
        foreach ($this->class_names as $name) {
            yield $name;
        }
    }
}

interface TestInterface1
{
}

interface TestInterface2
{
}

interface TestInterface3
{
}

class TestClass1 implements TestInterface1
{
}

class TestClass2 implements TestInterface2
{
}

class TestClass3 implements TestInterface1, TestInterface2
{
}

class ImplementationOfInterfaceFinderTest extends TestCase
{
    public function testWithTestInterface1()
    {
        $finder = new ImplementationOfInterfaceFinderForTest();
        $finder->class_names = [
            TestClass1::class,
            TestClass2::class,
            TestClass3::class
        ];
        $expected = [TestClass1::class, TestClass3::class];
        $result = iterator_to_array($finder->getMatchingClassNames(TestInterface1::class));
        $this->assertEquals($expected, $result);
    }

    public function testWithTestInterface2()
    {
        $finder = new ImplementationOfInterfaceFinderForTest();
        $finder->class_names = [
            TestClass1::class,
            TestClass2::class,
            TestClass3::class
        ];
        $expected = [TestClass2::class, TestClass3::class];
        $result = iterator_to_array($finder->getMatchingClassNames(TestInterface2::class));
        $this->assertEquals($expected, $result);
    }

    public function testWithTestInterface3()
    {
        $finder = new ImplementationOfInterfaceFinderForTest();
        $finder->class_names = [
            TestClass1::class,
            TestClass2::class,
            TestClass3::class
        ];
        $expected = [];
        $result = iterator_to_array($finder->getMatchingClassNames(TestInterface3::class));
        $this->assertEquals($expected, $result);
    }
}
