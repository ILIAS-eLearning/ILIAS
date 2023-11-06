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

require_once('vendor/composer/vendor/autoload.php');

class ilSkinStyleContainerTest extends ilSystemStyleBaseFS
{
    protected ilSkin $skin;
    protected ilSkinStyle $style1;
    protected ilSkinStyle $style2;
    protected ilSkinFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('PATH_TO_SCSS')) {
            if (file_exists('ilias.ini.php')) {
                $ini = parse_ini_file('ilias.ini.php', true);
                define('PATH_TO_SCSS', $ini['tools']['lessc'] ?? '');
            } else {
                define('PATH_TO_SCSS', '');
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

        $this->skin_directory = $this->system_style_config->getCustomizingSkinPath() . $this->skin->getId();
    }

    public function testCreateDelete(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $container->getSkin()->setId('newSkin');
        $container->create($this->message_stack);

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin'));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . 'newSkin'));
    }

    public function testUpdateSkinNoIdChange(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $container->updateSkin();
        $this->assertTrue(is_dir($this->skin_directory));
    }

    public function testUpdateSkinWithChangedID(): void
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

    public function testAddStyle(): void
    {
        $new_style = new ilSkinStyle('style1new', 'new Style');
        $new_style->setCssFile('newcss');
        $new_style->setImageDirectory('newimage');
        $new_style->setSoundDirectory('newsound');
        $new_style->setFontDirectory('newfont');

        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $this->assertTrue(is_dir($this->skin_directory. '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertFalse(is_dir($this->skin_directory . '/style1new/newimage'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1new/newsound'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1new/newfont'));
        $this->assertFalse(is_file($this->skin_directory . '/style1new/newcss.css'));
        $this->assertFalse(is_file($this->skin_directory . '/style1new/newcss.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1new/010-settings/variables1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1new/010-settings/variables2.scss'));

        $container->addStyle($new_style);

        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertTrue(is_dir($this->skin_directory . '/style1new/newimage'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1new/newsound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1new/newfont'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/newcss.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/newcss.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/010-settings/variables2.scss'));
    }

    public function testDeleteStyle(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2font'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/style2.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/style2.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/010-settings/variables2.scss'));

        $container->deleteStyle($this->style1);

        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1image'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style2/style2font'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/style2.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/style2.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style2/010-settings/variables2.scss'));
    }

    public function testUpdateStyle(): void
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

        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $container->updateStyle($new_style->getId(), $old_style);

        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1image'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertFalse(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertTrue(is_dir($this->skin_directory . '/style1new/style1newimage'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1new/style1newsound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1new/style1newfont'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/style1new.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1new/010-settings/variables2.scss'));
    }

    public function testDeleteSkin(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);
        $skin = $container->getSkin();

        $this->assertTrue(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
        $container->delete();
        $this->assertFalse(is_dir($this->system_style_config->getCustomizingSkinPath() . $skin->getId()));
    }

    public function testAddSubstyle(): void
    {
        $container = $this->factory->skinStyleContainerFromId($this->skin->getId(), $this->message_stack);

        $new_sub_style = new ilSkinStyle('substyle1new', 'new Style');
        $new_sub_style->setCssFile('subnewcss');
        $new_sub_style->setImageDirectory('subnewimage');
        $new_sub_style->setSoundDirectory('subnewsound');
        $new_sub_style->setFontDirectory('subnewfont');

        $new_sub_style->setSubstyleOf("style1");

        $this->assertTrue(is_dir($this->skin_directory. '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertFalse(is_dir($this->skin_directory . '/substyle1new/subnewimage'));
        $this->assertFalse(is_dir($this->skin_directory . '/substyle1new/subnewsound'));
        $this->assertFalse(is_dir($this->skin_directory . '/substyle1new/subnewfont'));
        $this->assertFalse(is_file($this->skin_directory . '/substyle1new/newcss.css'));
        $this->assertFalse(is_file($this->skin_directory . '/substyle1new/newcss.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/substyle1new/010-settings/variables1.scss'));
        $this->assertFalse(is_file($this->skin_directory . '/substyle1new/010-settings/variables2.scss'));

        $this->assertCount(0, $container->getSkin()->getSubstylesOfStyle('style1'));
        $container->addStyle($new_sub_style);
        $this->assertCount(1, $container->getSkin()->getSubstylesOfStyle('style1'));


        $this->assertTrue(is_dir($this->skin_directory. '/style1/style1image'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1sound'));
        $this->assertTrue(is_dir($this->skin_directory . '/style1/style1font'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.css'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/style1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/style1/010-settings/variables2.scss'));

        $this->assertTrue(is_dir($this->skin_directory . '/substyle1new/subnewimage'));
        $this->assertTrue(is_dir($this->skin_directory . '/substyle1new/subnewsound'));
        $this->assertTrue(is_dir($this->skin_directory . '/substyle1new/subnewfont'));
        $this->assertTrue(is_file($this->skin_directory . '/substyle1new/subnewcss.css'));
        $this->assertTrue(is_file($this->skin_directory . '/substyle1new/subnewcss.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/substyle1new/010-settings/variables1.scss'));
        $this->assertTrue(is_file($this->skin_directory . '/substyle1new/010-settings/variables2.scss'));
    }
}
