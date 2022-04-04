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

class ilSkinStyleContainerTest extends ilSystemStyleBaseFSTest
{
    protected ilSkin $skin;
    protected ilSkinStyle $style1;
    protected ilSkinStyle $style2;
    protected ilSkinFactory $factory;

    protected function setUp() : void
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
    }

    public function testCreateDelete() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $container->getSkin()->setId('newSkin');
        $container->create($this->message_stack);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin'));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin'));
    }

    public function testUpdateSkinNoIdChange() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $container->updateSkin();
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId()));
    }

    public function testUpdateSkinWithChangedID() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $old_skin = clone $container->getSkin();
        $container->getSkin()->setId('newSkin2');
        $container->updateSkin($old_skin);
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin2'));
        $old_skin = clone $container->getSkin();
        $container->getSkin()->setId($this->skin->getId());
        $container->updateSkin($old_skin);
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin2'));
    }

    public function testAddStyle() : void
    {
        $new_style = new ilSkinStyle('style1new', 'new Style');
        $new_style->setCssFile('style1new');
        $new_style->setImageDirectory('style1newimage');
        $new_style->setSoundDirectory('style1newsound');
        $new_style->setFontDirectory('style1newfont');

        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1image'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1sound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1font'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css-variables.less'));

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newimage'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newsound'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newfont'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.css'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.less'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new-variables.less'));

        $container->addStyle($new_style);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1image'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1sound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1font'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css-variables.less'));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newimage'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newsound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newfont'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new-variables.less'));
    }

    public function testDeleteStyle() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $container->deleteStyle($this->style1);

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1image'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1sound'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1font'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.css'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.less'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css-variables.less'));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2image'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2sound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2font'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2css.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2css.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style2css-variables.less'));
    }

    public function testUpdateStyle() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $skin = $container->getSkin();

        $old_style = clone $skin->getStyle($this->style1->getId());
        $new_style = $skin->getStyle($this->style1->getId());

        $new_style->setId('style1new');
        $new_style->setName('new Style');
        $new_style->setCssFile('style1new');
        $new_style->setImageDirectory('style1newimage');
        $new_style->setSoundDirectory('style1newsound');
        $new_style->setFontDirectory('style1newfont');

        $container->updateStyle($new_style->getId(), $old_style);

        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1image'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1sound'));
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1font'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.css'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css.less'));
        $this->assertFalse(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1css-variables.less'));

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newimage'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newsound'));
        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1newfont'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.css'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new.less'));
        $this->assertTrue(is_file($this->system_style_config->getCustomizingSkinPath() . $this->skin->getId() . '/style1new-variables.less'));
    }

    public function testDeleteSkin() : void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $skin = $container->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
    }
}
