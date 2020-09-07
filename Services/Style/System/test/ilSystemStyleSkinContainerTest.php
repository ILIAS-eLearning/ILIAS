<?php

include_once("Services/Style/System/classes/Utilities/class.ilSkinStyleXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleConfigMock.php");
include_once("Services/Style/System/test/fixtures/mocks/ilSystemStyleDICMock.php");

include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessageStack.php");
include_once("Services/Utilities/classes/class.ilUtil.php");

/**
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleSkinContainerTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var ilSkinXML
     */
    protected $skin;

    /**
     * @var ilSkinStyleXML
     */
    protected $style1 = null;

    /**
     * @var ilSkinStyleXML
     */
    protected $style2 = null;

    /**
     * @var ilSystemStyleConfigMock
     */
    protected $system_style_config;

    protected $save_dic = null;

    protected function setUp()
    {
        global $DIC;

        $this->save_dic = $DIC;
        $DIC = new ilSystemStyleDICMock();

        if (!defined('PATH_TO_LESSC')) {
            if (file_exists("ilias.ini.php")) {
                $ini = parse_ini_file("ilias.ini.php", true);
                define('PATH_TO_LESSC', $ini['tools']['lessc']);
            } else {
                define('PATH_TO_LESSC', "");
            }
        }

        $this->skin = new ilSkinXML("skin1", "skin 1");

        $this->style1 = new ilSkinStyleXML("style1", "Style 1");
        $this->style1->setCssFile("style1css");
        $this->style1->setImageDirectory("style1image");
        $this->style1->setSoundDirectory("style1sound");
        $this->style1->setFontDirectory("style1font");

        $this->style2 = new ilSkinStyleXML("style2", "Style 2");
        $this->style2->setCssFile("style2css");
        $this->style2->setImageDirectory("style2image");
        $this->style2->setSoundDirectory("style2sound");
        $this->style2->setFontDirectory("style2font");

        $this->system_style_config = new ilSystemStyleConfigMock();

        mkdir($this->system_style_config->test_skin_temp_path);
        ilSystemStyleSkinContainer::xCopy($this->system_style_config->test_skin_original_path, $this->system_style_config->test_skin_temp_path);
    }

    protected function tearDown()
    {
        global $DIC;
        $DIC = $this->save_dic;

        ilSystemStyleSkinContainer::recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testGenerateFromId()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
        $this->assertEquals($container->getSkin()->getId(), $this->skin->getId());
        $this->assertEquals($container->getSkin()->getName(), $this->skin->getName());

        $this->assertEquals($container->getSkin()->getStyle($this->style1->getId()), $this->style1);
        $this->assertEquals($container->getSkin()->getStyle($this->style2->getId()), $this->style2);
    }

    public function testCreateDelete()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);

        $container->getSkin()->setId("newSkin");
        $container->create(new ilSystemStyleMessageStack());

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . "newSkin"));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . "newSkin"));
    }

    public function testUpdateSkin()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
        $old_skin = clone $container->getSkin();
        $container->getSkin()->setId("newSkin2");
        $container->updateSkin($old_skin);
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . "newSkin2"));
        $old_skin = clone $container->getSkin();
        $container->getSkin()->setId($this->skin->getId());
        $container->updateSkin($old_skin);
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . "newSkin2"));
    }

    public function testAddStyle()
    {
        $new_style = new ilSkinStyleXML("style1new", "new Style");
        $new_style->setCssFile("style1new");
        $new_style->setImageDirectory("style1newimage");
        $new_style->setSoundDirectory("style1newsound");
        $new_style->setFontDirectory("style1newfont");

        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1image"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1sound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1font"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css-variables.less"));

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newimage"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newsound"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newfont"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.css"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.less"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new-variables.less"));

        $container->addStyle($new_style);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1image"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1sound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1font"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css-variables.less"));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newimage"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newsound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newfont"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new-variables.less"));
    }

    public function testDeleteStyle()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);

        $container->deleteStyle($this->style1);

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1image"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1sound"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1font"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.css"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.less"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css-variables.less"));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2image"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2sound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2font"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2css.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2css.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style2css-variables.less"));
    }

    public function testUpdateStyle()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
        $skin = $container->getSkin();

        $old_style = clone $skin->getStyle($this->style1->getId());
        $new_style = $skin->getStyle($this->style1->getId());

        $new_style->setId("style1new");
        $new_style->setName("new Style");
        $new_style->setCssFile("style1new");
        $new_style->setImageDirectory("style1newimage");
        $new_style->setSoundDirectory("style1newsound");
        $new_style->setFontDirectory("style1newfont");

        $container->updateStyle($new_style->getId(), $old_style);

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1image"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1sound"));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1font"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.css"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css.less"));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1css-variables.less"));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newimage"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newsound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1newfont"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . "/style1new-variables.less"));
    }

    public function testDeleteSkin()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
        $skin = $container->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
    }

    public function testCopySkin()
    {
        $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
        $skin = $container->getSkin();

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . "Copy"));

        $container_copy = $container->copy();
        $skin_copy = $container_copy->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . "Copy"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId()));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1image"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1sound"));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1font"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css.css"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css.less"));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css-variables.less"));
    }

    public function testImportSkin()
    {
        if (!defined('PATH_TO_ZIP')) {
            if (file_exists("ilias.ini.php")) {
                $ini = parse_ini_file("ilias.ini.php", true);
                define('PATH_TO_ZIP', $ini['tools']['zip']);
            } elseif (is_executable("/usr/bin/zip")) {
                define('PATH_TO_ZIP', "/usr/bin/zip");
            } else {
                define('PATH_TO_ZIP', "");
            }
        }

        if (!defined('PATH_TO_UNZIP')) {
            if (file_exists("ilias.ini.php")) {
                $ini = parse_ini_file("ilias.ini.php", true);
                define('PATH_TO_UNZIP', $ini['tools']['unzip']);
            } elseif (is_executable("/usr/bin/unzip")) {
                define('PATH_TO_UNZIP', "/usr/bin/unzip");
            } else {
                define('PATH_TO_UNZIP', "");
            }
        }

        //Only perform this test, if an unzip path has been found.
        if (PATH_TO_UNZIP != "") {
            $container = ilSystemStyleSkinContainer::generateFromId($this->skin->getId(), null, $this->system_style_config);
            $skin = $container->getSkin();

            $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . "Copy"));

            $container_import = $container->import($container->createTempZip(), $this->skin->getId() . ".zip", null, $this->system_style_config, false);
            $skin_copy = $container_import->getSkin();

            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . "Copy"));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId()));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1image"));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1sound"));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1font"));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css.css"));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css.less"));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . "/style1css-variables.less"));
        } else {
            $this->markTestIncomplete('No unzip has been detected on the system');
        }
    }
}
