<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilComponentBuildPluginInfoObjectiveTest extends TestCase
{
    public function setUp() : void
    {
        $this->scanned = [];
        $this->dirs = [];
        $this->read = [];
        $this->files = [];
        $this->added = [];
        $this->builder = new class($this) extends ilComponentBuildPluginInfoObjective {
            const BASE_PATH = "";
            public function __construct($test)
            {
                $this->test = $test;
            }
            protected function scanDir(string $dir) : array
            {
                $this->test->scanned[] = $dir;
                if (isset($this->test->dirs[$dir])) {
                    return $this->test->dirs[$dir];
                }
                return [];
            }
            public function _scanDir(string $dir) : array
            {
                return parent::scanDir($dir);
            }
            protected function readFile(string $path) : ?string
            {
                return $this->test->files[$path] ?? null;
            }
            public function _readFile(string $path) : ?string
            {
                return parent::readFile($path);
            }
            protected function addPlugin(array &$data, string $type, string $component, string $slot, string $plugin) : void
            {
                $this->test->added[] = "$type/$component/$slot/$plugin";
            }
            public function _addPlugin(array &$data, string $type, string $component, string $slot, string $plugin) : void
            {
                parent::addPlugin($data, $type, $component, $slot, $plugin);
            }
        };
    }

    public function testScanningTopLevel() : void
    {
        $this->builder->build();

        $expected = ["Modules", "Services"];
        sort($expected);
        sort($this->scanned);
        $this->assertEquals($expected, $this->scanned);
    }

    public function testScanningComplete() : void
    {
        $this->dirs = [
            "Modules" => ["Module1", "Module2"],
            "Services" => ["Service1"],
            "Modules/Module1" => ["Slot1", "Slot2"],
            "Modules/Module2" => [],
            "Services/Service1" => ["Slot3"]
        ];

        $this->builder->build();

        $expected = ["Modules", "Services", "Modules/Module1", "Modules/Module2",
            "Services/Service1", "Modules/Module1/Slot1", "Modules/Module1/Slot2",
            "Services/Service1/Slot3"];
        sort($expected);
        sort($this->scanned);
        $this->assertEquals($expected, $this->scanned);
    }

    public function testPluginsAdded() : void
    {
        $this->dirs = [
            "Modules" => ["Module1"],
            "Services" => ["Service1"],
            "Modules/Module1" => ["Slot1"],
            "Services/Service1" => ["Slot2"],
            "Modules/Module1/Slot1" => ["Plugin1", "Plugin2"],
            "Services/Service1/Slot2" => ["Plugin3"]
        ];

        $this->builder->build();

        $expected = [
            "Modules/Module1/Slot1/Plugin1",
            "Modules/Module1/Slot1/Plugin2",
            "Services/Service1/Slot2/Plugin3"
        ];
        sort($expected);
        sort($this->added);
        $this->assertEquals($expected, $this->added);
    }

    public function testScanDir() : void
    {
        // Use the component directory without artifacts, because this should be mostly stable.
        $expected = ["ROADMAP.md", "classes", "exceptions", "maintenance.json", "service.xml", "templates", "test"];
        $actual = array_values(array_diff($this->builder->_scanDir(__DIR__ . "/../.."), ["artifacts"]));
        $this->assertEquals($expected, $actual);
    }

    public function testAddPlugins() : void
    {
        $data = [];
        $this->files["Modules/Module1/Slot1/Plugin1/plugin.php"] = <<<'PLUGIN'
<?php
$id = "tstplg";
$version = "1.9.1";
$ilias_min_version = "8.0";
$ilias_max_version = "8.999";
$responsible = "Richard Klees";
$responsible_mail = "richard.klees@concepts-and-training.de";
$learning_progress = true;
$supports_export = false;
?>
PLUGIN;
        $this->files["Modules/Module1/Slot1/Plugin1/classes/class.ilPlugin1Plugin.php"] = "";

        $this->builder->_addPlugin($data, "Modules", "Module1", "Slot1", "Plugin1");

        $expected = [
            "tstplg" => [
                "Modules",
                "Module1",
                "Slot1",
                "Plugin1",
                "1.9.1",
                "8.0",
                "8.999",
                "Richard Klees",
                "richard.klees@concepts-and-training.de",
                true,
                false,
                null
            ]
        ];
        $this->assertEquals($expected, $data);
    }
}
