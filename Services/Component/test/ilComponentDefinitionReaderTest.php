<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

interface ilComponentDefinitionProcessorMock1 extends ilComponentDefinitionProcessor
{
};
interface ilComponentDefinitionProcessorMock2 extends ilComponentDefinitionProcessor
{
};

class ilComponentDefinitionReaderTest extends TestCase
{
    public static $component_xml_paths = [
        "/path/to/module.xml",
        "/other/path/to/service.xml"
    ];

    protected function setUp() : void
    {
        $this->processor1 = $this->createMock(ilComponentDefinitionProcessorMock1::class);
        $this->processor2 = $this->createMock(ilComponentDefinitionProcessorMock2::class);

        $this->reader = new class($this->processor1, $this->processor2) extends ilComponentDefinitionReader {
            protected function getCoreComponents() : array
            {
                return ilComponentDefinitionReaderTest::$component_xml_paths;
            }
            public $read_files = [];
            protected function readFile($path) : string
            {
                $this->read_files[] = $path;
            }
        };
    }

    public function testPurge()
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

    public function testGetComponents()
    {
        $reader = new class extends ilComponentDefinitionReader {
            public function __construct()
            {
            }
            public function _getComponents()
            {
                return $this->getComponents();
            }
        };

        $components = $reader->_getComponents();

        $this->assertIsArray($components);
        $this->assertContains(realpath(__DIR__ . "/../../../Modules/Course/module.xml"), $components);
        $this->assertContains(realpath(__DIR__ . "/../../../Services/Component/service.xml"), $components);
    }

    public function testReadComponentDefinitions()
    {
        $this->reader->readComponentDefinitions();

        $this->assertEquals(self::$component_xml_paths, $this->reader_read_files);
    }
}
