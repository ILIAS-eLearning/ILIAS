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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\FileUpload\FileUpload;

class ilSystemStyleOverviewGUITest extends ilSystemStyleBaseFSTest
{
    protected ilSystemStyleOverviewGUI $overview_gui;

    protected ilCtrl $ctrl;

    protected function setUp() : void
    {
        parent::setUp();
        $ui_helper = new UITestHelper();

        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'getFormAction',
            'getCmd'
        ])->getMock();

        $lng = new ilLanguageMock();
        $tpl = $ui_helper->mainTemplate();
        $ui_factory = $ui_helper->factory();
        $renderer = $ui_helper->renderer();
        $request = $this->getMockBuilder(WrapperFactory::class)->disableOriginalConstructor()->onlyMethods([
        ])->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $tabs = $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $data_factory = new DataFactory();
        $refinery = new Refinery($data_factory, $lng);

        $factory = new ilSkinFactory($this->lng, $this->system_style_config);
        $help = $this->getMockBuilder(ilHelpGUI::class)->disableOriginalConstructor()->onlyMethods([
        ])->getMock();
        $upload = $this->getMockBuilder(FileUpload::class)->disableOriginalConstructor()->onlyMethods([])->getMock();

        $this->overview_gui = new ilSystemStyleOverviewGUI(
            $this->ctrl,
            $lng,
            $tpl,
            $ui_factory,
            $renderer,
            $request,
            $toolbar,
            $refinery,
            $factory,
            $upload,
            $tabs,
            $help,
            $this->container->getSkin()->getId(),
            $this->style->getId(),
            '1',
            false,
            true
        );
    }

    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilSystemStyleOverviewGUI::class, $this->overview_gui);
    }
}
