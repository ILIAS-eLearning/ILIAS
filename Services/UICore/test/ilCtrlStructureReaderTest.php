<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilCtrlStructureReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->reader = (new class() extends ilCtrlStructureReader {
            public function _shouldDescendToDirectory(string $il_absolute_path, string $dir)
            {
                return $this->shouldDescendToDirectory($il_absolute_path, $dir);
            }
            public function _getFilesIn(string $il_absolute_path, string $dir)
            {
                return $this->getFilesIn($il_absolute_path, $dir);
            }
            public function _isInterestingFile(string $file)
            {
                return $this->isInterestingFile($file);
            }
            public function _getGUIClassNameFromClassFileName(string $file)
            {
                return $this->getGUIClassNameFromClassFileName($file);
            }
        })
            ->withDB($this->db);
    }

    public function testSmoke()
    {
        $this->assertInstanceOf(ilCtrlStructureReader::class, $this->reader);
    }

    public function testReadSmoke()
    {
        $dir = __DIR__ . "/test_dir";
        $result = $this->reader->read($dir);
        $this->assertTrue($result === false || is_null($result));
    }

    public function testReadClassScriptIsAsExpected()
    {
        $dir = __DIR__ . "/test_dir/";
        $result = $this->reader->read($dir);

        $expected_class_script = [
           "ilmytestinggui" => "$dir/class.ilMyTestingGUI.php"
        ];
        $this->assertEquals($this->reader->class_script, $expected_class_script);
    }

    public function testReadClassChildsIsAsExpected()
    {
        $dir = __DIR__ . "/test_dir/";
        $result = $this->reader->read($dir);

        $expected_class_childs= [
           "ilmytestinggui" => [
                "ilmyothertestinggui"
            ],
            "ilmythirdtestinggui" => [
                "ilmytestinggui"
            ]
        ];
        $this->assertEquals($this->reader->class_childs, $expected_class_childs);
    }

    public function testReadRemovesDuplicateCallsInDatabase()
    {
        $this->expectException(\Exception::class);

        $dir = __DIR__ . "/test_dir/";

        $this->reader->comp_prefix = "";
        $this->reader->class_script = [
           "ilmytestinggui" => "/some/other/dir/class.ilMyTestingGUI.php"
        ];
        $this->db
            ->method("quote")
            ->will($this->returnCallback(function ($v, $_) {
                return "\"$v\"";
            }));
        $this->db
            ->method("equals")
            ->will($this->returnCallback(function ($f, $v, $_, $__) {
                return "$f = \"$v\"";
            }));
        $this->db->expects($this->exactly(4))
            ->method("manipulate")
            ->withConsecutive(
                ["DELETE FROM ctrl_classfile WHERE comp_prefix = \"\""],
                ["DELETE FROM ctrl_classfile WHERE comp_prefix = \"\""],
                ["DELETE FROM ctrl_calls WHERE comp_prefix = \"\""],
                ["DELETE FROM ctrl_calls WHERE comp_prefix IS NULL"]
            );

        $result = $this->reader->read($dir);
    }

    public function testReadRemovesDuplicateFilesInDatabaseIfCompPrefixIsSet()
    {
        $this->expectException(\Exception::class);

        $dir = __DIR__ . "/test_dir/";
        $my_comp_prefix = "mcp";

        $this->reader->comp_prefix = $my_comp_prefix;
        $this->reader->class_script = [
           "ilmytestinggui" => "/some/other/dir/class.ilMyTestingGUI.php"
        ];
        $this->db
            ->method("quote")
            ->will($this->returnCallback(function ($v, $_) {
                return "\"$v\"";
            }));
        $this->db
            ->method("equals")
            ->will($this->returnCallback(function ($f, $v, $_, $__) {
                return "$f = \"$v\"";
            }));
        $this->db->expects($this->exactly(2))
            ->method("manipulate")
            ->withConsecutive(
                ["DELETE FROM ctrl_classfile WHERE comp_prefix = \"$my_comp_prefix\""],
                ["DELETE FROM ctrl_calls WHERE comp_prefix = \"$my_comp_prefix\""],
            );

        $result = $this->reader->read($dir);
    }

    public function testShouldDescendToDirectory()
    {
        $this->assertTrue($this->reader->_shouldDescendToDirectory("", "/foo"));
        $this->assertTrue($this->reader->_shouldDescendToDirectory("", "/bar"));
        $this->assertFalse($this->reader->_shouldDescendToDirectory("", "/data"));
        $this->assertFalse($this->reader->_shouldDescendToDirectory("", "/Customizing"));
    }

    public function testFilesInDir()
    {
        $dir = __DIR__ . "/test_dir/";
        $expected = [
            ["class.ilMyTestingGUI.php", "$dir/class.ilMyTestingGUI.php"],
            ["test_file", "$dir/sub_test_dir/test_file"]
        ];
        $result = iterator_to_array($this->reader->_getFilesIn("", $dir));
        sort($expected);
        sort($result);
        $this->assertEquals($expected, $result);
    }

    public function testIsInterestingFile()
    {
        $this->assertTrue($this->reader->_isInterestingFile("ilSCORM13Player.php"));
        $this->assertTrue($this->reader->_isInterestingFile("class.ilMyTestingGUI.php"));
        $this->assertFalse($this->reader->_isInterestingFile("foo.php"));
        $this->assertFalse($this->reader->_isInterestingFile("picture.png"));
        $this->assertFalse($this->reader->_isInterestingFile("icon.svg"));
        $this->assertFalse($this->reader->_isInterestingFile("data.json"));
    }

    public function testGetGUIClassNameFromClassFileName()
    {
        $this->assertNull($this->reader->_getGUIClassNameFromClassFileName("ilSCORM13Player.php"));
        $this->assertEquals("ilmytestinggui", $this->reader->_getGUIClassNameFromClassFileName("class.ilMyTestingGUI.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassFileName("foo.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassFileName("picture.png"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassFileName("icon.svg"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassFileName("data.json"));
    }
}
