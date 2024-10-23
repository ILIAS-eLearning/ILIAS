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

class ilSkinFactoryTest extends ilSystemStyleBaseFS
{
    protected ilSkin $skin;
    protected ilSkinStyle $style1;
    protected ilSkinStyle $style2;
    protected ilSkinFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skin = new ilSkin('skin1', 'skin 1');

        $this->style1 = new ilSkinStyle('style1', 'Style 1');
        $this->style1->setCssFile('style1');
        $this->style1->setImageDirectory('style1image');
        $this->style1->setSoundDirectory('style1sound');
        $this->style1->setFontDirectory('style1font');

        $this->style2 = new ilSkinStyle('style2', 'Style 2');
        $this->style2->setCssFile('style2');
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
}
