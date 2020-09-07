<?php

include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleConfigMock.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleDICMock.php");

include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIcon.php");


/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleIconTest extends PHPUnit_Framework_TestCase
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

    /**
     * @var string
     */
    protected $icon_name = "test_image_1.svg";

    /**
     * @var string
     */
    protected $icon_type = "svg";


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

    public function testConstruct()
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals($icon->getName(), $this->icon_name);
        $this->assertEquals($icon->getPath(), $path);
        $this->assertEquals($icon->getType(), $this->icon_type);
    }

    public function testGetDirRelToCustomizing()
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals($icon->getDirRelToCustomizing(), "");

        $name = "test.svg";
        $rel_path = "global/skin/unibe50/images";
        $path = "./Customizing" . $rel_path . "/" . $name;
        $icon = new ilSystemStyleIcon($name, $path, "svg");

        $this->assertEquals($icon->getDirRelToCustomizing(), $rel_path);
    }

    public function testGetColorSet()
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("505050", "505050", "505050", "505050");
        $color2 = new ilSystemStyleIconColor("6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $this->assertEquals($expected_color_set, $icon->getColorSet());
    }

    public function testChangeColor()
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $icon->changeColors(['505050' => '555555']);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("555555", "555555", "555555", "555555");
        $color2 = new ilSystemStyleIconColor("6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $this->assertEquals($expected_color_set, $icon->getColorSet());
    }

    public function testChangeColorInIconFile()
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $icon->changeColors(['505050' => '555555']);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("555555", "555555", "555555", "555555");
        $color2 = new ilSystemStyleIconColor("6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $icon_new = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals($expected_color_set, $icon_new->getColorSet());
    }
}
