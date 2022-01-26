<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');
include_once('./tests/UI/UITestHelper.php');

use PHPUnit\Framework\TestCase;

use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntry as Entry;
use ILIAS\UI\Implementation\Crawler\Entry\ComponentEntries as Entries;
use ILIAS\Data\URI;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationFactory;
use ILIAS\GlobalScreen\Provider\ProviderFactory;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\ScreenContext\ContextRepository;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;

class SystemStylesGlobalScreenToolProviderTest extends TestCase
{
    protected UITestHelper $ui_helper;
    protected array $entries_data;
    protected Entries $entries;
    protected Entry $entry;
    protected array $entry_data;
    protected URI $test_uri;
    protected SystemStylesGlobalScreenToolProvider $tool_provider;
    protected Container $dic;

    protected function setUp() : void
    {
        global $DIC;

        $this->dic = new Container();
        $this->dic = (new UITestHelper())->init($this->dic);

        $this->dic['ilCtrl'] = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            'getLinkTargetByClass'
        ])->getMock();
        $this->dic['ilCtrl']->method('getLinkTargetByClass')->willReturn('1');

        (new InitHttpServices())->init($this->dic);

        $this->dic['global_screen'] = $this
            ->getMockBuilder(ILIAS\GlobalScreen\Services::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['identification'])
            ->getMock();
        $provider_factory = $this->getMockBuilder(ProviderFactory::class)->getMock();
        $identification = new IdentificationFactory($provider_factory);
        $this->dic['global_screen']->method('identification')->willReturn($identification);

        $DIC = $this->dic;
        $this->tool_provider = new SystemStylesGlobalScreenToolProvider($this->dic);

        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'http://localhost');
        }
    }

    public function testConstruct()
    {
        $this->assertInstanceOf('SystemStylesGlobalScreenToolProvider', $this->tool_provider);
    }

    public function testIsInterestedInContexts()
    {
        $this->assertEquals(
            ['administration'],
            $this->tool_provider->isInterestedInContexts()->getStackAsArray()
        );
    }

    public function testBuildTreeAsToolNotInContext()
    {
        $contexts = new CalledContexts(new ContextRepository());
        $this->assertEquals([], $this->tool_provider->getToolsForContextStack($contexts));
    }

    public function testBuildTreeAsToolIfInAdminstrationContext()
    {
        $contexts = (new CalledContexts(new ContextRepository()))->administration();
        $this->assertEquals([], $this->tool_provider->getToolsForContextStack($contexts));
    }

    public function testBuildTreeAsToolIfInAdminstrationContextAndTreeIsAvailable()
    {
        $tree_available_context = (new ILIAS\GlobalScreen\ScreenContext\BasicScreenContext('administration'))->addAdditionalData(ilSystemStyleDocumentationGUI::SHOW_TREE, true);
        $contexts = new CalledContexts(new ContextRepository());
        $contexts->push($tree_available_context);
        $tools = $this->tool_provider->getToolsForContextStack($contexts);
        $this->assertCount(1, $tools);
        $tool = array_pop($tools);
        $this->assertInstanceOf(Tool::class, $tool);
        $this->assertEquals('documentation', $tool->getTitle());
    }
}
