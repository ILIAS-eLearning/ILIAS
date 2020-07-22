<?php

include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleConfigMock.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleDICMock.php");

include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIcon.php");
include_once("Services/Style/System/classes/Icons/class.ilSystemStyleIconFolder.php");


/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleIconFolderTest extends PHPUnit_Framework_TestCase
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
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $this->assertEquals($this->container->getImagesSkinPath($this->style->getId()), $folder->getPath());
    }

    public function testSetPath()
    {
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $folder->setPath("pathnew");

        $this->assertEquals("pathnew", $folder->getPath());
    }


    public function testReadRecursiveCount()
    {
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $this->assertEquals(4, count($folder->getIcons()));
    }

    public function testFolderDoesNotExist()
    {
        try {
            new ilSystemStyleIconFolder("Does not exist");
            $this->assertTrue(false);
        } catch (ilSystemStyleIconException $e) {
            $this->assertEquals(ilSystemStyleIconException::IMAGES_FOLDER_DOES_NOT_EXIST, $e->getCode());
        }
    }

    public function testFolderGetIcon()
    {
        $style_path = $this->container->getImagesSkinPath($this->style->getId());

        $path1 = $style_path . "/test_image_1.svg";
        $icon1 = new ilSystemStyleIcon("test_image_1.svg", $path1, "svg");
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $this->assertEquals($icon1, $folder->getIconByName("test_image_1.svg"));
    }

    public function testIconDoesNotExist()
    {
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        try {
            $folder->getIconByName("doesNotExist.svg");
            $this->assertTrue(false);
        } catch (ilSystemStyleIconException $e) {
            $this->assertEquals(ilSystemStyleIconException::ICON_DOES_NOT_EXIST, $e->getCode());
        }
    }

    public function testReadRecursiveAndSortByName()
    {
        $style_path = $this->container->getImagesSkinPath($this->style->getId());

        $path1 = $style_path . "/test_image_1.svg";
        $icon1 = new ilSystemStyleIcon("test_image_1.svg", $path1, "svg");

        $path2 = $style_path . "/image_subfolder/sub_test_image_1.svg";
        $icon2 = new ilSystemStyleIcon("sub_test_image_1.svg", $path2, "svg");

        $path3 = $style_path . "/image_subfolder/sub_test_image_2.svg";
        $icon3 = new ilSystemStyleIcon("sub_test_image_2.svg", $path3, "svg");

        $path4 = $style_path . "/nonsvg.png";
        $icon4 = new ilSystemStyleIcon("nonsvg.png", $path4, "png");

        $expected_icons = [$icon2,$icon3,$icon1,$icon4];

        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $this->assertEquals($expected_icons, $folder->getIcons());
    }

    public function testReadRecursiveAndSortByFolder()
    {
        $style_path = $this->container->getImagesSkinPath($this->style->getId());

        $path1 = $style_path . "/test_image_1.svg";
        $icon1 = new ilSystemStyleIcon("test_image_1.svg", $path1, "svg");

        $path2 = $style_path . "/image_subfolder/sub_test_image_1.svg";
        $icon2 = new ilSystemStyleIcon("sub_test_image_1.svg", $path2, "svg");

        $path3 = $style_path . "/image_subfolder/sub_test_image_2.svg";
        $icon3 = new ilSystemStyleIcon("sub_test_image_2.svg", $path3, "svg");

        $path4 = $style_path . "/nonsvg.png";
        $icon4 = new ilSystemStyleIcon("nonsvg.png", $path4, "png");

        $expected_icons = [
                $style_path => [$icon1,$icon4],
                $style_path . "/image_subfolder" => [$icon2,$icon3],
        ];

        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $this->assertEquals($expected_icons, $folder->getIconsSortedByFolder());
    }

    public function testExtractColorset()
    {
        $folder = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("505050", "505050", "505050", "505050");
        $color2 = new ilSystemStyleIconColor("6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $color5 = new ilSystemStyleIconColor("303030", "303030", "303030", "303030");
        $color6 = new ilSystemStyleIconColor("404040", "404040", "404040", "404040");

        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);
        $expected_color_set->addColor($color5);
        $expected_color_set->addColor($color6);

        $this->assertEquals($expected_color_set, $folder->getColorSet());
    }

    public function testExtractChangeColors()
    {
        $folder1 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $folder1->changeIconColors(["505050" => "555555"]);

        $folder2 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("555555", "555555", "555555", "555555");
        $color2 = new ilSystemStyleIconColor("6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $color5 = new ilSystemStyleIconColor("303030", "303030", "303030", "303030");
        $color6 = new ilSystemStyleIconColor("404040", "404040", "404040", "404040");

        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);
        $expected_color_set->addColor($color5);
        $expected_color_set->addColor($color6);
        $expected_color_set->addColor($color1);

        $this->assertEquals($expected_color_set, $folder2->getColorSet());
    }

    public function testGetUsages()
    {
        $style_path = $this->container->getImagesSkinPath($this->style->getId());

        $folder1 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $path1 = $style_path . "/test_image_1.svg";
        $icon1 = new ilSystemStyleIcon("test_image_1.svg", $path1, "svg");
        $icon1->getColorSet();

        $path2 = $style_path . "/image_subfolder/sub_test_image_1.svg";
        $icon2 = new ilSystemStyleIcon("sub_test_image_1.svg", $path2, "svg");
        $icon2->getColorSet();

        $path3 = $style_path . "/image_subfolder/sub_test_image_2.svg";
        $icon3 = new ilSystemStyleIcon("sub_test_image_2.svg", $path3, "svg");
        $icon3->getColorSet();

        $expected_icons_usages = [$icon2,$icon3,$icon1];

        $this->assertEquals($expected_icons_usages, $folder1->getUsagesOfColor("6B6B6B"));
    }

    public function testGetUsagesAfterChangeColor()
    {
        $style_path = $this->container->getImagesSkinPath($this->style->getId());

        $folder1 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));
        $folder1->changeIconColors(["6B6B6B" => "7B6B6B"]);

        $folder2 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $path1 = $style_path . "/test_image_1.svg";
        $icon1 = new ilSystemStyleIcon("test_image_1.svg", $path1, "svg");
        $icon1->getColorSet();

        $path2 = $style_path . "/image_subfolder/sub_test_image_1.svg";
        $icon2 = new ilSystemStyleIcon("sub_test_image_1.svg", $path2, "svg");
        $icon2->getColorSet();

        $path3 = $style_path . "/image_subfolder/sub_test_image_2.svg";
        $icon3 = new ilSystemStyleIcon("sub_test_image_2.svg", $path3, "svg");
        $icon3->getColorSet();

        $expected_icons_usages = [$icon2,$icon3,$icon1];

        $this->assertEquals($expected_icons_usages, $folder2->getUsagesOfColor("7B6B6B"));
        $this->assertEquals([], $folder2->getUsagesOfColor("6B6B6B"));
    }

    public function testGetUsagesAsString()
    {
        $folder1 = new ilSystemStyleIconFolder($this->container->getImagesSkinPath($this->style->getId()));

        $this->assertEquals(
            'sub_test_image_1; sub_test_image_2; test_image_1; ',
            $folder1->getUsagesOfColorAsString("6B6B6B")
        );
    }
}
