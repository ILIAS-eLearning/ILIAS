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

use PHPUnit\Framework\TestCase;

class ilComponentBuildPluginInfoObjectiveTest extends TestCase
{
    private ?ilComponentBuildPluginInfoObjective $builder = null;

    protected function setUp(): void
    {
        $this->scanned = [];
        $this->dirs = [];
        $this->read = [];
        $this->files = [];
        $this->added = [];
        $this->builder = new class ($this) extends ilComponentBuildPluginInfoObjective {
            protected const BASE_PATH = "";
            protected ilComponentBuildPluginInfoObjectiveTest $test;
            public function __construct($test)
            {
                $this->test = $test;
            }
            protected function scanDir(string $dir): array
            {
                $this->test->scanned[] = $dir;
                return $this->test->dirs[$dir] ?? [];
            }
            public function _scanDir(string $dir): array
            {
                return parent::scanDir($dir);
            }
            protected function isDir(string $dir): bool
            {
                return true;
            }
            public function _isDir(string $dir): bool
            {
                return parent::isDir($dir);
            }
            public function _isDotFile(string $file): bool
            {
                return parent::isDotFile($file);
            }
            protected function buildPluginPath(string $type, string $component, string $slot, string $plugin): string
            {
                return $this->test->files[parent::buildPluginPath($type, $component, $slot, $plugin)];
            }

            public function _buildPluginPath(string $type, string $component, string $slot, string $plugin): string
            {
                return parent::buildPluginPath($type, $component, $slot, $plugin);
            }
            protected function addPlugin(array &$data, string $type, string $component, string $slot, string $plugin): void
            {
                $this->test->added[] = "$type/$component/$slot/$plugin";
            }
            public function _addPlugin(array &$data, string $type, string $component, string $slot, string $plugin): void
            {
                parent::addPlugin($data, $type, $component, $slot, $plugin);
            }
        };
    }

    public function testScanningTopLevel(): void
    {
        $this->builder->build();

        $expected = ["Modules", "Services"];
        sort($expected);
        sort($this->scanned);
        $this->assertEquals($expected, $this->scanned);
    }

    public function testScanningComplete(): void
    {
        $this->dirs = [
            "Modules" => ["Module1", "Module2"],
            "Services" => ["Service1"],
            "Modules/Module1" => ["Slot1", "Slot2"],
            "Modules/Module2" => [],
            "Services/Service1" => ["Slot3",".DS_Store"] // .DS_Store should be skipped
        ];

        $this->builder->build();

        $expected = ["Modules", "Services", "Modules/Module1", "Modules/Module2",
            "Services/Service1", "Modules/Module1/Slot1", "Modules/Module1/Slot2",
            "Services/Service1/Slot3"];
        sort($expected);
        sort($this->scanned);
        $this->assertEquals($expected, $this->scanned);
    }

    public function testPluginsAdded(): void
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

    public function testScanDir(): void
    {
        // Use the component directory without artifacts, because this should be mostly stable.
        $expected = ["ROADMAP.md", "classes", "exceptions", "maintenance.json", "service.xml", "test"];
        $actual = array_values(
            array_diff(
                $this->builder->_scanDir(__DIR__ . "/../.."),
                ["artifacts", ".DS_Store"] // .DS_Store is a macOS artifact which is not relevant for the test.
            )
        );
        $this->assertEquals($expected, $actual);
    }

    public function testIsDir(): void
    {
        // Use the component directory, because this should be mostly stable.
        $expected = true;
        $actual = $this->builder->_isDir(__DIR__ . "/../..");
        $this->assertEquals($expected, $actual);
    }

    public function testIsDotFile(): void
    {
        $expected = true;
        $actual = $this->builder->_isDotFile(".DS_Store");
        $this->assertEquals($expected, $actual);
    }

    public function testAddPlugins(): void
    {
        $data = [];
        $this->files["Modules/Module1/Slot1/Plugin1/"] = __DIR__ . "/";
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

    public function testBuildPluginPath(): void
    {
        $this->assertEquals("TYPE/COMPONENT/SLOT/PLUGIN/", $this->builder->_buildPluginPath("TYPE", "COMPONENT", "SLOT", "PLUGIN"));
    }
}
