<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');
include_once('./tests/UI/UITestHelper.php');

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;

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

        $factory = new ilSkinFactory($this->system_style_config);
        $help = $this->getMockBuilder(ilHelpGUI::class)->disableOriginalConstructor()->onlyMethods([
        ])->getMock();

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
