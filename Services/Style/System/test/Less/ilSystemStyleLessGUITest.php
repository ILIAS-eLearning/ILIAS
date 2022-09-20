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
include_once('./tests/UI/UITestHelper.php');

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
use ILIAS\UI\Implementation\Component\Input\Field\Text;

class ilSystemStyleLessGUITest extends ilSystemStyleBaseFSTest
{
    protected ilSystemStyleLessGUI $less_gui;

    protected function setUp(): void
    {
        parent::setUp();
        $ui_helper = new UITestHelper();

        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'getFormAction'
        ])->getMock();
        $lng = new ilLanguageMock();
        $tpl = $ui_helper->mainTemplate();
        $ui_factory = $ui_helper->factory();
        $renderer = $ui_helper->renderer();
        $request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->onlyMethods([
        ])->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $data_factory = new DataFactory();
        $refinery = new Refinery($data_factory, $lng);

        $factory = new ilSkinFactory($this->lng, $this->system_style_config);

        $this->less_gui = new ilSystemStyleLessGUI(
            $ctrl,
            $lng,
            $tpl,
            $ui_factory,
            $renderer,
            $request,
            $toolbar,
            $refinery,
            $factory,
            $this->container->getSkin()->getId(),
            $this->style->getId()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilSystemStyleLessGUI::class, $this->less_gui);
    }

    public function testInitSystemStyleLessForm(): void
    {
        $form = $this->less_gui->initSystemStyleLessForm();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertCount(1, $form->getInputs());
        $this->assertInstanceOf(Section::class, $form->getInputs()[0]);
        $this->assertCount(2, $form->getInputs()[0]->getInputs());
        $this->assertInstanceOf(Section::class, $form->getInputs()[0]->getInputs()[0]);
        $this->assertCount(3, $form->getInputs()[0]->getInputs()[0]->getInputs());
        $this->assertInstanceOf(Text::class, $form->getInputs()[0]->getInputs()[0]->getInputs()[0]);
    }
}
