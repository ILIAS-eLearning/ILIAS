<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;

class ilSystemStyleIconTest extends TestCase
{
    protected ilSystemStyleConfigMock $system_style_config;
    protected ilSkinStyleContainer $container;
    protected ilSkinStyle $style;
    protected string $icon_name = "test_image_1.svg";
    protected string $icon_type = "svg";
    protected ?ILIAS\DI\Container $save_dic = null;
    protected ilFileSystemHelper $file_system;

    protected function setUp() : void
    {
        global $DIC;

        if ($DIC) {
            $this->save_dic = $DIC;
        }

        $DIC = new ilSystemStyleDICMock();

        $this->system_style_config = new ilSystemStyleConfigMock();

        mkdir($this->system_style_config->test_skin_temp_path);
        $this->file_system = new ilFileSystemHelper($DIC->language());
        $this->file_system->recursiveCopy($this->system_style_config->test_skin_original_path,
            $this->system_style_config->test_skin_temp_path);

        $factory = new ilSkinFactory($this->system_style_config);

        $this->container = $factory->skinStyleContainerFromId("skin1");
        $this->style = $this->container->getSkin()->getStyle("style1");
    }

    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->save_dic;
        $this->file_system->recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testConstruct() : void
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals($icon->getName(), $this->icon_name);
        $this->assertEquals($icon->getPath(), $path);
        $this->assertEquals($icon->getType(), $this->icon_type);
    }

    public function testGetDirRelToCustomizing() : void
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals("", $icon->getDirRelToCustomizing());

        $name = "test.svg";
        $rel_path = "global/skin/unibe50/images";
        $path = "./Customizing" . $rel_path . "/" . $name;
        $icon = new ilSystemStyleIcon($name, $path, "svg");

        $this->assertEquals($rel_path, $icon->getDirRelToCustomizing());
    }

    public function testGetColorSet() : void
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("id_505050", "505050", "505050", "505050");
        $color2 = new ilSystemStyleIconColor("id_6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("id_838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("id_8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $this->assertEquals($expected_color_set, $icon->getColorSet());
    }

    public function testChangeColor() : void
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $icon->changeColors(['505050' => '555555']);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("id_555555", "555555", "555555", "555555");
        $color2 = new ilSystemStyleIconColor("id_6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("id_838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("id_8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $this->assertEquals($expected_color_set, $icon->getColorSet());
    }

    public function testChangeColorInIconFile() : void
    {
        $path = $this->container->getImagesSkinPath($this->style->getId()) . "/" . $this->icon_name;
        $icon = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $icon->changeColors(['505050' => '555555']);

        $expected_color_set = new ilSystemStyleIconColorSet();
        $color1 = new ilSystemStyleIconColor("id_555555", "555555", "555555", "555555");
        $color2 = new ilSystemStyleIconColor("id_6B6B6B", "6B6B6B", "6B6B6B", "6B6B6B");
        $color3 = new ilSystemStyleIconColor("id_838383", "838383", "838383", "838383");
        $color4 = new ilSystemStyleIconColor("id_8C8C8C", "8C8C8C", "8C8C8C", "8C8C8C");
        $expected_color_set->addColor($color1);
        $expected_color_set->addColor($color2);
        $expected_color_set->addColor($color3);
        $expected_color_set->addColor($color4);

        $icon_new = new ilSystemStyleIcon($this->icon_name, $path, $this->icon_type);

        $this->assertEquals($expected_color_set, $icon_new->getColorSet());
    }
}
