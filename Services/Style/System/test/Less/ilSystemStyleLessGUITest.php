<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");
include_once("./tests/UI/UITestHelper.php");

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as Form;
use ILIAS\UI\Implementation\Component\Input\Field\Section;
use ILIAS\UI\Implementation\Component\Input\Field\Text;

class ilSystemStyleLessGUITest extends TestCase
{
    protected ilSystemStyleConfigMock $system_style_config;
    protected ilSkinStyleContainer $container;
    protected ilSkinStyle $style;
    protected ilFileSystemHelper $file_system;
    protected \ILIAS\DI\Container $save_dic;
    protected ilSystemStyleLessGUI $less_gui;

    protected function setUp() : void
    {
        global $DIC;

        if(isset($DIC)){
            $this->save_dic = $DIC;
        }

        $DIC = new ilSystemStyleDICMock();

        $this->system_style_config = new ilSystemStyleConfigMock();

        if (!file_exists($this->system_style_config->test_skin_temp_path)) {
            mkdir($this->system_style_config->test_skin_temp_path);
        }

        $ui_helper = new UITestHelper();

        $ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            "getFormAction"
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

        $this->file_system = new ilFileSystemHelper($DIC->language());
        $this->file_system->recursiveCopy($this->system_style_config->test_skin_original_path,
            $this->system_style_config->test_skin_temp_path);

        $factory = new ilSkinFactory($this->system_style_config);

        $this->container = $factory->skinStyleContainerFromId("skin1");
        $this->style = $this->container->getSkin()->getStyle("style1");

        $this->less_gui = new ilSystemStyleLessGUI($ctrl, $lng, $tpl, $ui_factory, $renderer, $request, $toolbar,
            $refinery, $factory, $this->container->getSkin()->getId(), $this->style->getId());
    }

    protected function tearDown() : void
    {
        global $DIC;
        if (isset($this->save_dic)) {
            $DIC = $this->save_dic;
        }

        $this->file_system->recursiveRemoveDir($this->system_style_config->test_skin_temp_path);
    }

    public function testConstruct() : void
    {
        $this->assertInstanceOf(ilSystemStyleLessGUI::class, $this->less_gui);
    }


    public function testInitSystemStyleLessForm() : void
    {
        $form = $this->less_gui->initSystemStyleLessForm();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertCount(1,$form->getInputs());
        $this->assertInstanceOf(Section::class,$form->getInputs()[0]);
        $this->assertCount(2,$form->getInputs()[0]->getInputs());
        $this->assertInstanceOf(Section::class,$form->getInputs()[0]->getInputs()[0]);
        $this->assertCount(3,$form->getInputs()[0]->getInputs()[0]->getInputs());
        $this->assertInstanceOf(Text::class,$form->getInputs()[0]->getInputs()[0]->getInputs()[0]);
    }

}
