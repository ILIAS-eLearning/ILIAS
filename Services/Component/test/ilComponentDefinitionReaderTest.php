<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

interface ilComponentDefinitionProcessorMock1 extends ilComponentDefinitionProcessor
{
}
interface ilComponentDefinitionProcessorMock2 extends ilComponentDefinitionProcessor
{
}

class ilComponentDefinitionReaderTest extends TestCase
{
    protected ilComponentDefinitionProcessor $processor1;
    protected ilComponentDefinitionProcessor $processor2;

    public static array $components = [
        ["Modules", "A_Module", "/path/to/module.xml"],
        ["Services", "A_Service", "/other/path/to/service.xml"]
    ];

    protected function setUp() : void
    {
        $this->processor1 = $this->createMock(ilComponentDefinitionProcessorMock1::class);
        $this->processor2 = $this->createMock(ilComponentDefinitionProcessorMock2::class);

        $this->reader = new class($this->processor1, $this->processor2) extends ilComponentDefinitionReader {
            protected function getComponents() : Iterator
            {
                return new ArrayIterator(ilComponentDefinitionReaderTest::$components);
            }
            public $read_files = [];
            protected function readFile(string $path) : string
            {
                $this->read_files[] = $path;
                if ($path === "/path/to/module.xml") {
                    return
'<?xml version = "1.0" encoding = "UTF-8"?>
<module a1="a1" a2="a2">
    <tag1>
        <tag11></tag11>
    </tag1>
    <tag2></tag2>
</module>';
                }
                if ($path === "/other/path/to/service.xml") {
                    return
'<?xml version = "1.0" encoding = "UTF-8"?>
<service>
</service>';
                }
                return "";
            }
        };
    }

    public function testPurge() : void
    {
        $this->processor1
            ->expects($this->once())
            ->method("purge")
            ->with();
        $this->processor2
            ->expects($this->once())
            ->method("purge")
            ->with();

        $this->reader->purge();
    }

    public function testGetComponents() : void
    {
        $reader = new class extends ilComponentDefinitionReader {
            public function __construct()
            {
            }
            public function _getComponents() : array
            {
                return iterator_to_array($this->getComponents());
            }
        };

        $components = $reader->_getComponents();

        $this->assertIsArray($components);
        $this->assertContains(["Modules", "Course", realpath(__DIR__ . "/../../../Modules/Course/module.xml")], $components);
        $this->assertContains(["Services", "Component", realpath(__DIR__ . "/../../../Services/Component/service.xml")], $components);
    }

    public function testReadComponentDefinitions() : void
    {
        $processor1_stack = [];
        $this->processor1
            ->method("beginComponent")
            ->willReturnCallback(function ($s1, $s2) use (&$processor1_stack) {
                $processor1_stack[] = "beginComponent";
                $processor1_stack[] = $s1;
                $processor1_stack[] = $s2;
            });
        $this->processor1
            ->method("endComponent")
            ->willReturnCallback(function ($s1, $s2) use (&$processor1_stack) {
                $processor1_stack[] = "endComponent";
                $processor1_stack[] = $s1;
                $processor1_stack[] = $s2;
            });
        $this->processor1
            ->method("beginTag")
            ->willReturnCallback(function ($s1, $s2) use (&$processor1_stack) {
                $processor1_stack[] = "beginTag";
                $processor1_stack[] = $s1;
                $processor1_stack[] = $s2;
            });
        $this->processor1
            ->method("endTag")
            ->willReturnCallback(function ($s1) use (&$processor1_stack) {
                $processor1_stack[] = "endTag";
                $processor1_stack[] = $s1;
            });

        $processor2_stack = [];
        $this->processor2
            ->method("beginComponent")
            ->willReturnCallback(function ($s1, $s2) use (&$processor2_stack) {
                $processor2_stack[] = "beginComponent";
                $processor2_stack[] = $s1;
                $processor2_stack[] = $s2;
            });
        $this->processor2
            ->method("endComponent")
            ->willReturnCallback(function ($s1, $s2) use (&$processor2_stack) {
                $processor2_stack[] = "endComponent";
                $processor2_stack[] = $s1;
                $processor2_stack[] = $s2;
            });
        $this->processor2
            ->method("beginTag")
            ->willReturnCallback(function ($s1, $s2) use (&$processor2_stack) {
                $processor2_stack[] = "beginTag";
                $processor2_stack[] = $s1;
                $processor2_stack[] = $s2;
            });
        $this->processor2
            ->method("endTag")
            ->willReturnCallback(function ($s1) use (&$processor2_stack) {
                $processor2_stack[] = "endTag";
                $processor2_stack[] = $s1;
            });


        $this->reader->readComponentDefinitions();


        $this->assertEquals([self::$components[0][2], self::$components[1][2]], $this->reader->read_files);

        $expected_processor_stack = [
            "beginComponent",
            self::$components[0][1], // A_Module
            self::$components[0][0], // Modules
            "beginTag",
            "module", ["a1" => "a1", "a2" => "a2"],
            "beginTag",
            "tag1", [],
            "beginTag",
            "tag11", [],
            "endTag",
            "tag11",
            "endTag",
            "tag1",
            "beginTag",
            "tag2", [],
            "endTag",
            "tag2",
            "endTag",
            "module",
            "endComponent",
            self::$components[0][1], // A_Module
            self::$components[0][0], // Modules
            "beginComponent",
            self::$components[1][1], // A_Service
            self::$components[1][0], // Services
            "beginTag",
            "service", [],
            "endTag",
            "service",
            "endComponent",
            self::$components[1][1], // A_Service
            self::$components[1][0], // Services
        ];
        $this->assertEquals($expected_processor_stack, $processor1_stack);
        $this->assertEquals($expected_processor_stack, $processor2_stack);
    }
}
