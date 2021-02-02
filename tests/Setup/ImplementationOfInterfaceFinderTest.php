<?php

/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup\ImplementationOfInterfaceFinder;
use PHPUnit\Framework\TestCase;

class ImplementationOfInterfaceFinderForTest extends ImplementationOfInterfaceFinder
{
    public $class_names = [];

    protected function getAllClassNames(array $additional_ignore, string $matching_path = null) : \Iterator
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
