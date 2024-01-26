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

class ilCtrlStructureReaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->reader = (new class() extends ilCtrlStructureReader {
            public function _shouldDescendToDirectory(string $dir)
            {
                return $this->shouldDescendToDirectory($dir);
            }
            public function _getFilesIn(string $dir)
            {
                return $this->getFilesIn($dir);
            }
            public function _isInterestingFile(string $file)
            {
                return $this->isInterestingFile($file);
            }
            public function _getGUIClassNameFromClassPath(string $file)
            {
                return $this->getGUIClassNameFromClassPath($file);
            }
            public function _getIlCtrlCalls(string $content)
            {
                return $this->getIlCtrlCalls($content);
            }
            public function _getIlCtrlIsCalledBy(string $content)
            {
                return $this->getIlCtrlIsCalledBy($content);
            }
            public function _containsClassDefinitionFor(string $class, string $content)
            {
                return $this->containsClassDefinitionFor($class, $content);
            }
            protected function getILIASAbsolutePath() : string
            {
                return "";
            }
            public function _readDirTo(string $a_cdir, \ilCtrlStructure $cs = null) : \ilCtrlStructure
            {
                return $this->readDirTo($a_cdir, $cs ?? new \ilCtrlStructure());
            }
            protected function normalizePath(string $path) : string
            {
                return $path;
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
        $result = $this->reader->_readDirTo($dir);
        $this->assertInstanceOf(\ilCtrlStructure::class, $result);
    }

    public function testReadClassScriptIsAsExpected()
    {
        $dir = __DIR__ . "/test_dir";
        $result = $this->reader->_readDirTo($dir);

        $expected_class_script = [
           "ilmytestinggui" => "$dir/class.ilMyTestingGUI.php"
        ];
        $this->assertEquals(
            $expected_class_script,
            iterator_to_array($result->getClassScripts())
        );
    }

    public function testReadClassChildsIsAsExpected()
    {
        $dir = __DIR__ . "/test_dir/";
        $result = $this->reader->_readDirTo($dir);

        $expected_class_childs = [
           "ilmytestinggui" => [
                "ilmyothertestinggui"
            ],
            "ilmythirdtestinggui" => [
                "ilmytestinggui"
            ]
        ];
        $this->assertEquals(
            $expected_class_childs,
            iterator_to_array($result->getClassChildren())
        );
    }

    public function testReadRemovesDuplicateCallsInDatabase()
    {
        $this->expectException(\Exception::class);

        $dir = __DIR__ . "/test_dir/";

        $this->reader->comp_prefix = "";
        $ctrl_structure = new \ilCtrlStructure([
           "ilmytestinggui" => "/some/other/dir/class.ilMyTestingGUI.php"
        ]);
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

        $result = $this->reader->_readDirTo($dir, $ctrl_structure);
    }

    public function testReadRemovesDuplicateFilesInDatabaseIfCompPrefixIsSet()
    {
        $this->expectException(\Exception::class);

        $dir = __DIR__ . "/test_dir/";
        $my_comp_prefix = "mcp";

        $this->reader->comp_prefix = $my_comp_prefix;
        $ctrl_structure = new \ilCtrlStructure([
           "ilmytestinggui" => "/some/other/dir/class.ilMyTestingGUI.php"
        ]);
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
                ["DELETE FROM ctrl_calls WHERE comp_prefix = \"$my_comp_prefix\""]
            );

        $result = $this->reader->_readDirTo($dir, $ctrl_structure);
    }

    public function testShouldDescendToDirectory()
    {
        $this->assertTrue($this->reader->_shouldDescendToDirectory("/foo"));
        $this->assertTrue($this->reader->_shouldDescendToDirectory("/bar"));
        $this->assertFalse($this->reader->_shouldDescendToDirectory("/data"));
        $this->assertFalse($this->reader->_shouldDescendToDirectory("/Customizing"));
    }

    public function testFilesInDir()
    {
        $dir = __DIR__ . "/test_dir";
        $expected = [
            ["class.ilMyTestingGUI.php", "$dir/class.ilMyTestingGUI.php"],
            ["test_file", "$dir/sub_test_dir/test_file"]
        ];
        $result = iterator_to_array($this->reader->_getFilesIn($dir));
        sort($expected);
        sort($result);
        $this->assertEquals($expected, $result);
    }

    public function testIsInterestingFile()
    {
        $this->assertFalse($this->reader->_isInterestingFile("ilSCORM13Player.php"));
        $this->assertTrue($this->reader->_isInterestingFile("class.ilSCORM13PlayerGUI.php"));
        $this->assertTrue($this->reader->_isInterestingFile("class.ilMyTestingGUI.php"));
        $this->assertFalse($this->reader->_isInterestingFile("foo.php"));
        $this->assertFalse($this->reader->_isInterestingFile("picture.png"));
        $this->assertFalse($this->reader->_isInterestingFile("icon.svg"));
        $this->assertFalse($this->reader->_isInterestingFile("data.json"));
    }

    public function testGetGUIClassNameFromClassPath()
    {
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("/my/dir/ilSCORM13Player.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\ilSCORM13Player.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\ilSCORM13Player.php"));
        $this->assertEquals("ilscorm13playergui", $this->reader->_getGUIClassNameFromClassPath("/my/dir/class.ilSCORM13PlayerGUI.php"));
        $this->assertEquals("ilscorm13playergui", $this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\class.ilSCORM13PlayerGUI.php"));
        $this->assertEquals("ilscorm13playergui", $this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\class.ilSCORM13PlayerGUI.php"));
        $this->assertEquals("ilmytestinggui", $this->reader->_getGUIClassNameFromClassPath("/my/dir/class.ilMyTestingGUI.php"));
        $this->assertEquals("ilmytestinggui", $this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\class.ilMyTestingGUI.php"));
        $this->assertEquals("ilmytestinggui", $this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\class.ilMyTestingGUI.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("/my/dir/foo.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\foo.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\foo.php"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("/my/dir/picture.png"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\picture.png"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\picture.png"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("/my/dir/icon.svg"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\icon.svg"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\icon.svg"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("/my/dir/data.json"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("\\my\\dir\\data.json"));
        $this->assertNull($this->reader->_getGUIClassNameFromClassPath("c:\\my\\dir\\data.json"));
    }

    public function testGetIlCtrlCallsNoContent()
    {
        $gen = $this->reader->_getIlCtrlCalls(
            <<<"PHP"
class SomeRandomClass {
}
PHP
        );
        $this->assertNull($gen);
    }

    public function testGetIlCtrlCallsWithContent()
    {
        list($parent, $children) = $this->reader->_getIlCtrlCalls(
            <<<"PHP"
<?php
/* Copyright (c) 2020 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
 * Class ilObjCourseGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilCourseObjectivesGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI
 *
 * @extends ilContainerGUI
 */
class ilObjCourseGUI extends ilContainerGUI
{
}
PHP
        );
        $expected = [
            "ilcourseregistrationgui",
            "ilcourseobjectivesgui",
            "ilobjcoursegroupinggui",
            "ilinfoscreengui",
            "illearningprogressgui",
            "ilpermissiongui",
            "ilrepositorysearchgui"
        ];

        sort($expected);
        sort($children);

        $this->assertEquals("ilobjcoursegui", $parent);
        $this->assertEquals($expected, $children);
    }

    public function testGetIlCtrlIsCalledByNoContent()
    {
        $gen = $this->reader->_getIlCtrlIsCalledBy(
            <<<"PHP"
class SomeRandomClass {
}
PHP
        );
        $this->assertNull($gen);
    }

    public function testGetIlCtrlIsCalledByWithContent()
    {
        list($parent, $children) = $this->reader->_getIlCtrlIsCalledBy(
            <<<"PHP"
<?php
/* Copyright (c) 2020 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
 * Class ilObjCourseGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @ilCtrl_IsCalledBy ilObjCourseGUI: ilCourseRegistrationGUI, ilCourseObjectivesGUI
 * @ilCtrl_IsCalledBy ilObjCourseGUI: ilObjCourseGroupingGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjCourseGUI: ilRepositorySearchGUI
 *
 * @extends ilContainerGUI
 */
class ilObjCourseGUI extends ilContainerGUI
{
}
PHP
        );
        $expected = [
            "ilcourseregistrationgui",
            "ilcourseobjectivesgui",
            "ilobjcoursegroupinggui",
            "ilinfoscreengui",
            "illearningprogressgui",
            "ilpermissiongui",
            "ilrepositorysearchgui"
        ];

        sort($expected);
        sort($children);

        $this->assertEquals("ilobjcoursegui", $parent);
        $this->assertEquals($expected, $children);
    }

    public function testContainsClassDefinitionFor()
    {
        $res = $this->reader->_containsClassDefinitionFor(
            "SomeRandomClass",
            <<<"PHP"
class SomeRandomClass {
}
PHP
        );
        $this->assertTrue($res);
    }

    public function testDoesNotContainClassDefinitionFor()
    {
        $res = $this->reader->_containsClassDefinitionFor(
            "SomeRandomClass",
            <<<"PHP"
class fooSomeRandomClass {
}
PHP
        );
        $this->assertFalse($res);
    }


    public function testGetIlCtrlCallsWithNamespaces() : void
    {
        list($parent, $children) = $this->reader->_getIlCtrlCalls(
            <<<"PHP"
<?php
namespace ILIAS\UICore;

/**
 * @ilCtrl_Calls ILIAS\UICore\TestGUI: ilFormPropertyDispatchGUI
 * @ilCtrl_Calls ILIAS\UICore\TestGUI: ILIAS\UICore\Test2GUI
 */
class TestGUI
}
PHP
        );
        $expected = array_map("strtolower", [
            "ilFormPropertyDispatchGUI",
            "ILIAS\UICore\Test2GUI"
        ]);

        sort($expected);
        sort($children);

        $this->assertEquals(strtolower("ILIAS\UICore\TestGUI"), $parent);
        $this->assertEquals($expected, $children);
    }


    public function testGetIlCtrlIsCalledByWithNamespaces() : void
    {
        list($parent, $children) = $this->reader->_getIlCtrlIsCalledBy(
            <<<"PHP"
<?php
namespace ILIAS\UICore;

/**
 * @ilCtrl_IsCalledBy ILIAS\UICore\TestGUI: ilUIPluginRouterGUI
 * @ilCtrl_IsCalledBy ILIAS\UICore\TestGUI: ILIAS\UICore\Test3GUI
 */
class TestGUI
}
PHP
        );
        $expected = array_map("strtolower", [
            "ilUIPluginRouterGUI",
            "ILIAS\UICore\Test3GUI"
        ]);

        sort($expected);
        sort($children);

        $this->assertEquals(strtolower("ILIAS\UICore\TestGUI"), $parent);
        $this->assertEquals($expected, $children);
    }
}
