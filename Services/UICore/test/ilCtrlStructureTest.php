<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilCtrlStructureTest extends TestCase
{
    protected function setUp() : void
    {
        $this->ctrl_structure = new \ilCtrlStructure();
    }

    public function testAddClassScript()
    {
        $this->ctrl_structure = $this->ctrl_structure
            ->withClassScript("class1", "file1")
            ->withClassScript("class2", "file2")
            ->withClassScript("class3", "file3")
            ->withClassScript("class2", "file2");

        $expected = [
            "class1" => "file1",
            "class2" => "file2",
            "class3" => "file3",
        ];

        $this->assertEquals(
            $expected,
            iterator_to_array($this->ctrl_structure->getClassScripts())
        );
    }

    public function testAddClassScriptPanicsOnDuplicate()
    {
        $this->expectException(\RuntimeException::class);

        $this->ctrl_structure
            ->withClassScript("class1", "file1")
            ->withClassScript("class1", "file2");
    }

    public function testAddClassChild()
    {
        $this->ctrl_structure = $this->ctrl_structure
            ->withClassChild("parent1", "child1")
            ->withClassChild("parent2", "child2")
            ->withClassChild("parent1", "child3");

        $expected = [
            "parent1" => ["child1", "child3"],
            "parent2" => ["child2"]
        ];

        $this->assertEquals(
            $expected,
            iterator_to_array($this->ctrl_structure->getClassChildren())
        );
    }

    public function testConstructor()
    {
        $scripts = [
            "class1" => "file1",
            "class2" => "file2",
            "class3" => "file3",
        ];

        $children = [
            "parent1" => ["child1", "child3"],
            "parent2" => ["child2"]
        ];

        $ctrl_structure = new \ilCtrlStructure($scripts, $children);
    
        $this->assertEquals(
            $scripts,
            iterator_to_array($ctrl_structure->getClassScripts())
        );

        $this->assertEquals(
            $children,
            iterator_to_array($ctrl_structure->getClassChildren())
        );
    }

    public function testGetClassScriptOf()
    {
        $scripts = [
            "class1" => "file1",
            "class2" => "file2",
            "class3" => "file3",
        ];

        $ctrl_structure = new \ilCtrlStructure($scripts, []);

        $this->assertEquals("file1", $ctrl_structure->getClassScriptOf("class1"));
        $this->assertEquals("file2", $ctrl_structure->getClassScriptOf("class2"));
        $this->assertEquals("file3", $ctrl_structure->getClassScriptOf("class3"));
        $this->assertSame(null, $ctrl_structure->getClassScriptOf("class4"));
    }
}
