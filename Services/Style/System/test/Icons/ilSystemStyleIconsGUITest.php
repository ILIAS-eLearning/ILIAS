<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
include_once("./tests/UI/UITestHelper.php");

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use \ILIAS\FileUpload\FileUpload;

class ilSystemStyleIconsGUITest extends ilSystemStyleBaseFSTest
{
    protected ilSystemStyleIconsGUI $icons_gui;

    protected ilCtrl $ctrl;

    protected function setUp() : void
    {
        parent::setUp();
        $ui_helper = new UITestHelper();

        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            "getFormAction","getCmd","getLinkTargetByClass"
        ])->getMock();

        $lng = new ilLanguageMock();
        $tpl = $ui_helper->mainTemplate();
        $ui_factory = $ui_helper->factory();
        $renderer = $ui_helper->renderer();
        $request = $this->getMockBuilder(WrapperFactory::class)->disableOriginalConstructor()->onlyMethods([
        ])->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $upload = $this->getMockBuilder(FileUpload::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $tabs = $this->getMockBuilder(ilTabsGUI::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $data_factory = new DataFactory();
        $refinery = new Refinery($data_factory, $lng);

        $factory = new ilSkinFactory($this->system_style_config);

        $this->icons_gui = new ilSystemStyleIconsGUI($this->ctrl, $lng, $tpl, $ui_factory, $renderer, $request, $toolbar,
            $refinery, $factory, $tabs, $upload, $this->container->getSkin()->getId(), $this->style->getId());
    }

    public function testConstruct() : void
    {
        $this->ctrl->method("getCmd")->willReturn("");
        $this->assertInstanceOf(ilSystemStyleIconsGUI::class, $this->icons_gui);
    }

    public function tesGetIconsPreviewstPreview() : void
    {
        $this->assertInstanceOf(\ILIAS\UI\Component\Panel\Report::class,$this->icons_gui->getIconsPreviews());
    }

    //this is only a smoke test
    public function testPreview() : void
    {
        $this->ctrl->method("getCmd")->willReturn("preview");
        $this->icons_gui->executeCommand();
        $this->assertTrue(true);
    }
}
