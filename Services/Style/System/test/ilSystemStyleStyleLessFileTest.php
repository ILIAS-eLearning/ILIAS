<?php
include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessFile.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleConfigMock.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleDICMock.php");

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleStyleLessFileTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var ilSystemStyleConfigMock
     */
    protected $system_style_config;

    /**
     * @var ilSystemStyleSkinContainer
     */
    protected $container;

    /**
     * @var ilSkinStyleXML
     */
    protected $style;

    protected $save_dic = null;

    protected function setUp()
    {
        global $DIC;

        $this->save_dic = $DIC;
        $DIC = new ilSystemStyleDICMock();

        $this->system_style_config = new ilSystemStyleConfigMock();

        mkdir($this->system_style_config->test_skin_temp_path);
        ilSystemStyleSkinContainer::xCopy($this->system_style_config->test_skin_original_path, $this->system_style_config->test_skin_temp_path);

        $this->container = ilSystemStyleSkinContainer::generateFromId("skin1", null, $this->system_style_config);
        $this->style = $this->container->getSkin()->getStyle("style1");
    }

    protected function tearDown()
    {
        global $DIC;
        $DIC = $this->save_dic;

        ilSystemStyleSkinContainer::recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testConstructAndRead()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));
        $this->assertEquals(14, count($file->getItems()));
    }

    public function testReadCorrectTypes()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $this->assertEquals(2, count($file->getCategories()));
        $this->assertEquals(6, count($file->getVariablesIds()));
        $this->assertEquals(6, count($file->getCommentsIds()));
    }


    public function testGetVariableByName()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $expected_variable11 = new ilSystemStyleLessVariable("variable11", "value11", "comment variable 11", "Category 1", []);
        $expected_variable12 = new ilSystemStyleLessVariable("variable12", "value12", "comment variable 12", "Category 1", []);
        $expected_variable13 = new ilSystemStyleLessVariable("variable13", "@variable11", "comment variable 13", "Category 1", ["variable11"]);

        $expected_variable21 = new ilSystemStyleLessVariable("variable21", "@variable11", "comment variable 21", "Category 2", ["variable11"]);
        $expected_variable22 = new ilSystemStyleLessVariable("variable22", "value21", "comment variable 22", "Category 2", []);
        $expected_variable23 = new ilSystemStyleLessVariable("variable23", "@variable21", "comment variable 23", "Category 2", ["variable21"]);

        $this->assertEquals($expected_variable11, $file->getVariableByName("variable11"));
        $this->assertEquals($expected_variable12, $file->getVariableByName("variable12"));
        $this->assertEquals($expected_variable13, $file->getVariableByName("variable13"));

        $this->assertEquals($expected_variable21, $file->getVariableByName("variable21"));
        $this->assertEquals($expected_variable22, $file->getVariableByName("variable22"));
        $this->assertEquals($expected_variable23, $file->getVariableByName("variable23"));
    }

    public function testGetCategory()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $expected_category1 = new ilSystemStyleLessCategory("Category 1", "Comment Category 1");
        $expected_category2 = new ilSystemStyleLessCategory("Category 2", "Comment Category 2");
        $expected_categories = [$expected_category1,$expected_category2];

        $this->assertEquals($expected_categories, $file->getCategories());
    }

    public function testGetItems()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $expected_category1 = new ilSystemStyleLessCategory("Category 1", "Comment Category 1");
        $expected_comment2 = new ilSystemStyleLessComment("// Random Section 1");
        $expected_comment3 = new ilSystemStyleLessComment("");
        $expected_variable11 = new ilSystemStyleLessVariable("variable11", "value11", "comment variable 11", "Category 1", []);
        $expected_variable12 = new ilSystemStyleLessVariable("variable12", "value12", "comment variable 12", "Category 1", []);
        $expected_variable13 = new ilSystemStyleLessVariable("variable13", "@variable11", "comment variable 13", "Category 1", ["variable11"]);
        $expected_comment4 = new ilSystemStyleLessComment("");
        $expected_category2 = new ilSystemStyleLessCategory("Category 2", "Comment Category 2");
        $expected_comment6 = new ilSystemStyleLessComment("/**");
        $expected_comment7 = new ilSystemStyleLessComment(" Random Section 2 **/");
        $expected_comment8 = new ilSystemStyleLessComment("");
        $expected_variable21 = new ilSystemStyleLessVariable("variable21", "@variable11", "comment variable 21", "Category 2", ["variable11"]);
        $expected_variable22 = new ilSystemStyleLessVariable("variable22", "value21", "comment variable 22", "Category 2", []);
        $expected_variable23 = new ilSystemStyleLessVariable("variable23", "@variable21", "comment variable 23", "Category 2", ["variable21"]);

        $expected_items = [$expected_category1,
            $expected_comment2,$expected_comment3,
            $expected_variable11,$expected_variable12,$expected_variable13,
            $expected_comment4,
            $expected_category2,
            $expected_comment6,$expected_comment7,$expected_comment8,
            $expected_variable21,$expected_variable22,$expected_variable23];

        $this->assertEquals($expected_items, $file->getItems());
    }

    public function testGetContent()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));
        $expected_content = file_get_contents($this->container->getLessVariablesFilePath($this->style->getId()));
        $this->assertEquals($expected_content, $file->getContent());
    }

    public function testReadWriteDouble()
    {
        $expected_content = file_get_contents($this->container->getLessVariablesFilePath($this->style->getId()));

        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));
        $file->write();
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));
        $file->write();
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $this->assertEquals($expected_content, $file->getContent());
    }

    public function testReadWriteDoubleFullLess()
    {
        $expected_content = file_get_contents($this->container->getSkinDirectory() . "full.less");

        $file = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "full.less");
        $file->write();
        $file = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "full.less");
        $file->write();
        $file = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "full.less");

        $this->assertEquals($expected_content, $file->getContent());
    }

    public function testChangeVariable()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));
        $variable = $file->getVariableByName("variable11");
        $variable->setValue("newvalue11");

        $expected_category1 = new ilSystemStyleLessCategory("Category 1", "Comment Category 1");
        $expected_comment2 = new ilSystemStyleLessComment("// Random Section 1");
        $expected_comment3 = new ilSystemStyleLessComment("");
        $expected_variable11 = new ilSystemStyleLessVariable("variable11", "newvalue11", "comment variable 11", "Category 1", []);
        $expected_variable12 = new ilSystemStyleLessVariable("variable12", "value12", "comment variable 12", "Category 1", []);
        $expected_variable13 = new ilSystemStyleLessVariable("variable13", "@variable11", "comment variable 13", "Category 1", ["variable11"]);
        $expected_comment4 = new ilSystemStyleLessComment("");
        $expected_category2 = new ilSystemStyleLessCategory("Category 2", "Comment Category 2");
        $expected_comment6 = new ilSystemStyleLessComment("/**");
        $expected_comment7 = new ilSystemStyleLessComment(" Random Section 2 **/");
        $expected_comment8 = new ilSystemStyleLessComment("");
        $expected_variable21 = new ilSystemStyleLessVariable("variable21", "@variable11", "comment variable 21", "Category 2", ["variable11"]);
        $expected_variable22 = new ilSystemStyleLessVariable("variable22", "value21", "comment variable 22", "Category 2", []);
        $expected_variable23 = new ilSystemStyleLessVariable("variable23", "@variable21", "comment variable 23", "Category 2", ["variable21"]);

        $expected_items = [$expected_category1,
            $expected_comment2,$expected_comment3,
            $expected_variable11,$expected_variable12,$expected_variable13,
            $expected_comment4,
            $expected_category2,
            $expected_comment6,$expected_comment7,$expected_comment8,
            $expected_variable21,$expected_variable22,$expected_variable23];

        $this->assertEquals($expected_items, $file->getItems());
    }

    public function testAddAndWriteItems()
    {
        $empty_less = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "empty.less");

        $expected_category1 = new ilSystemStyleLessCategory("Category 1", "Comment Category 1");
        $expected_comment2 = new ilSystemStyleLessComment("// Random Section 1");
        $expected_comment3 = new ilSystemStyleLessComment("");
        $expected_variable11 = new ilSystemStyleLessVariable("variable11", "value11", "comment variable 11", "Category 1", []);
        $expected_variable12 = new ilSystemStyleLessVariable("variable12", "value12", "comment variable 12", "Category 1", []);
        $expected_variable13 = new ilSystemStyleLessVariable("variable13", "@variable11", "comment variable 13", "Category 1", ["variable11"]);
        $expected_comment4 = new ilSystemStyleLessComment("");
        $expected_category2 = new ilSystemStyleLessCategory("Category 2", "Comment Category 2");
        $expected_comment6 = new ilSystemStyleLessComment("/**");
        $expected_comment7 = new ilSystemStyleLessComment(" Random Section 2 **/");
        $expected_comment8 = new ilSystemStyleLessComment("");
        $expected_variable21 = new ilSystemStyleLessVariable("variable21", "@variable11", "comment variable 21", "Category 2", ["variable11"]);
        $expected_variable22 = new ilSystemStyleLessVariable("variable22", "value21", "comment variable 22", "Category 2", []);
        $expected_variable23 = new ilSystemStyleLessVariable("variable23", "@variable21", "comment variable 23", "Category 2", ["variable21"]);

        $expected_items = [$expected_category1,
            $expected_comment2,$expected_comment3,
            $expected_variable11,$expected_variable12,$expected_variable13,
            $expected_comment4,
            $expected_category2,
            $expected_comment6,$expected_comment7,$expected_comment8,
            $expected_variable21,$expected_variable22,$expected_variable23];

        foreach ($expected_items as $item) {
            $empty_less->addItem($item);
        }
        $empty_less->write();

        $new_less = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "empty.less");
        $this->assertEquals($expected_items, $new_less->getItems());
    }

    public function testGetVariableReferences()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $this->assertEquals(["variable13","variable21"], $file->getReferencesToVariable("variable11"));
        $this->assertEquals([], $file->getReferencesToVariable("variable12"));
        $this->assertEquals([], $file->getReferencesToVariable("variable13"));

        $this->assertEquals(["variable23"], $file->getReferencesToVariable("variable21"));
        $this->assertEquals([], $file->getReferencesToVariable("variable22"));
        $this->assertEquals([], $file->getReferencesToVariable("variable23"));
    }

    public function testGetVariableReferencesAsString()
    {
        $file = new ilSystemStyleLessFile($this->container->getLessVariablesFilePath($this->style->getId()));

        $this->assertEquals("variable13; variable21; ", $file->getReferencesToVariableAsString("variable11"));
        $this->assertEquals("", $file->getReferencesToVariableAsString("variable12"));
        $this->assertEquals("", $file->getReferencesToVariableAsString("variable13"));

        $this->assertEquals("variable23; ", $file->getReferencesToVariableAsString("variable21"));
        $this->assertEquals("", $file->getReferencesToVariableAsString("variable22"));
        $this->assertEquals("", $file->getReferencesToVariableAsString("variable23"));
    }

    public function testReadCorrectTypesEdgeCases()
    {
        $file = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "edge-cases.less");

        $this->assertEquals(3, count($file->getCategories()));
        $this->assertEquals(7, count($file->getVariablesIds()));
        $this->assertEquals(4, count($file->getCommentsIds()));
    }

    public function testGetItemsEdgeCases()
    {
        $file = new ilSystemStyleLessFile($this->container->getSkinDirectory() . "edge-cases.less");

        $expected_comment1 = new ilSystemStyleLessComment("// No Category to start");
        $expected_comment2 = new ilSystemStyleLessComment("");

        $expected_variable11 = new ilSystemStyleLessVariable("variableNoCategory1", "value11", "comment variable 11", "", []);
        $expected_variable12 = new ilSystemStyleLessVariable("variableNoCategory1NoComment", "value12", "", "", []);

        $expected_category1 = new ilSystemStyleLessCategory("Category 1 no valid section", "");

        $expected_variable21 = new ilSystemStyleLessVariable("variableNoValidSection1", "value21", "", "Category 1 no valid section", []);
        $expected_variable22 = new ilSystemStyleLessVariable("variableNoValidSection2", "value22", "comment", "Category 1 no valid section", []);

        $expected_comment3 = new ilSystemStyleLessComment("");

        $expected_category2 = new ilSystemStyleLessCategory("Category 2", "Comment Category 2");

        $expected_variable31 = new ilSystemStyleLessVariable("regular", "value", "Hard references id", "Category 2", []);
        $expected_variable32 = new ilSystemStyleLessVariable("variable21", "floor((@regular * 1.6)) * lighten(@regular, 20%)", "Hard references", "Category 2", ["regular"]);

        $expected_comment4 = new ilSystemStyleLessComment("");

        $expected_category3 = new ilSystemStyleLessCategory("Category 3", "No Section Between");
        $expected_variable41 = new ilSystemStyleLessVariable("variable3", "value3", "", "Category 3", []);

        $expected_items = [$expected_comment1,$expected_comment2,
                $expected_variable11,$expected_variable12,
                $expected_category1,$expected_variable21,$expected_variable22,
                $expected_comment3,
                $expected_category2,$expected_variable31,$expected_variable32,
                $expected_comment4,
                $expected_category3,$expected_variable41];

        $this->assertEquals($expected_items, $file->getItems());
    }
}
