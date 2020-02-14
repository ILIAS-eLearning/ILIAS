<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilCtrlStructureReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->reader = (new ilCtrlStructureReader())
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
}
