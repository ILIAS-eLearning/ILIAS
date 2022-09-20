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

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

class ilSkinFactoryTest extends ilSystemStyleBaseFSTest
{
    protected ilSkin $skin;
    protected ilSkinStyle $style1;
    protected ilSkinStyle $style2;
    protected ilSkinFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('PATH_TO_LESSC')) {
            if (file_exists('ilias.ini.php')) {
                $ini = parse_ini_file('ilias.ini.php', true);
                define('PATH_TO_LESSC', $ini['tools']['lessc'] ?? '');
            } else {
                define('PATH_TO_LESSC', '');
            }
        }

        $this->skin = new ilSkin('skin1', 'skin 1');

        $this->style1 = new ilSkinStyle('style1', 'Style 1');
        $this->style1->setCssFile('style1css');
        $this->style1->setImageDirectory('style1image');
        $this->style1->setSoundDirectory('style1sound');
        $this->style1->setFontDirectory('style1font');

        $this->style2 = new ilSkinStyle('style2', 'Style 2');
        $this->style2->setCssFile('style2css');
        $this->style2->setImageDirectory('style2image');
        $this->style2->setSoundDirectory('style2sound');
        $this->style2->setFontDirectory('style2font');

        $this->factory = new ilSkinFactory($this->lng, $this->system_style_config);

        global $DIC;

        $DIC = new ilSystemStyleDICMock();
        $DIC['tpl'] = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
    }

    public function testSkinFromXML(): void
    {
        $factory = new ilSkinFactory($this->lng);
        $skin = $factory->skinFromXML($this->system_style_config->getCustomizingSkinPath() . 'skin1/template.xml');
        $this->assertEquals(
            $skin->asXML(),
            file_get_contents($this->system_style_config->getCustomizingSkinPath() . 'skin1/template.xml')
        );
    }

    public function testSkinStyleContainerFromId(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $this->assertEquals($container->getSkin()->getId(), $this->skin->getId());
        $this->assertEquals($container->getSkin()->getName(), $this->skin->getName());

        $this->assertEquals($container->getSkin()->getStyle($this->style1->getId()), $this->style1);
        $this->assertEquals($container->getSkin()->getStyle($this->style2->getId()), $this->style2);
    }

    public function testCopySkin(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $skin = $container->getSkin();

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . 'Copy'));

        $container_copy = $this->factory->copyFromSkinStyleContainer($container, $this->file_system, $this->message_stack);
        $skin_copy = $container_copy->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . 'Copy'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId()));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1image'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1sound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1font'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css-variables.less'));

        $this->assertEquals($skin->getName() . ' Copy', $skin_copy->getName());
        $this->assertEquals('0.1', $skin_copy->getVersion());
    }

    public function testCopySkinWithInjectedName(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $skin = $container->getSkin();
        $container_copy = $this->factory->copyFromSkinStyleContainer($container, $this->file_system, $this->message_stack, 'inject');
        $skin_copy = $container_copy->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . 'inject'));
        $this->assertEquals($skin->getName() . ' inject', $skin_copy->getName());
        $this->assertEquals('0.1', $skin_copy->getVersion());
    }

    public function testImportSkin(): void
    {
        if (!defined('PATH_TO_ZIP')) {
            if (file_exists('ilias.ini.php')) {
                $ini = parse_ini_file('ilias.ini.php', true);
                define('PATH_TO_ZIP', $ini['tools']['zip']);
            } elseif (is_executable('/usr/bin/zip')) {
                define('PATH_TO_ZIP', '/usr/bin/zip');
            } else {
                define('PATH_TO_ZIP', '');
            }
        }

        if (!defined('PATH_TO_UNZIP')) {
            if (file_exists('ilias.ini.php')) {
                $ini = parse_ini_file('ilias.ini.php', true);
                define('PATH_TO_UNZIP', $ini['tools']['unzip']);
            } elseif (is_executable('/usr/bin/unzip')) {
                define('PATH_TO_UNZIP', '/usr/bin/unzip');
            } else {
                define('PATH_TO_UNZIP', '');
            }
        }

        //Only perform this test, if an unzip path has been found.
        if (PATH_TO_UNZIP != '') {
            $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
            $skin = $container->getSkin();

            $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . 'Copy'));

            $container_import = $this->factory->skinStyleContainerFromZip(
                $container->createTempZip(),
                $this->skin->getId() . '.zip',
                $this->message_stack
            );
            $skin_copy = $container_import->getSkin();

            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId() . 'Copy'));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId()));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1image'));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1sound'));
            $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1font'));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css.css'));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css.less'));
            $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $skin_copy->getId() . '/style1css-variables.less'));
        } else {
            $this->markTestIncomplete('No unzip has been detected on the system');
        }
    }
}
