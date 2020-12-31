<?php
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../Base.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use \ILIAS\DI\Container;

/**
 * Class ExamplesTest Checks if all examples are implemented and properly returning strings
 */
class ExamplesTest extends ILIAS_UI_TestBase
{
    /**
     * @var string
     */
    protected $path_to_base_factory = "src/UI/Factory.php";

    /**
     * @var Container
     */
    protected $dic;

    public function setUp() : void
    {
        //This avoids various index not set warnings, which are only relevant in test context.
        $_SERVER["REQUEST_URI"] = "";
        //This avoids Undefined index: ilfilehash for the moment
        $_POST["ilfilehash"] = "";
        $this->setUpMockDependencies();
    }

    /**
     * Some wiring up of dependencies to get all the examples running. If you examples needs additional dependencies,
     * please add them here. However, please check carefully if those deps are really needed. Even if the examples,
     * we try to keep them minimal. Note the most deps are wired up here as mocks only.
     */
    protected function setUpMockDependencies() : void
    {
        $this->dic = new Container();
        $this->dic["tpl"] = $this->getTemplateFactory()->getTemplate("", false, false);
        $this->dic["lng"] = $this->getLanguage();
        $this->dic["refinery"] = $this->getRefinery();
        (new \InitUIFramework())->init($this->dic);

        $this->dic["ui.template_factory"] = $this->getTemplateFactory();
        $this->dic["ilCtrl"] = Mockery::mock("\ilCtrl");
        $this->dic["ilCtrl"]->shouldReceive("getFormActionByClass")->andReturn("");
        $this->dic["ilCtrl"]->shouldReceive("setParameterByClass");
        $this->dic["ilCtrl"]->shouldReceive("saveParameterByClass");
        $this->dic["ilCtrl"]->shouldReceive("initBaseClass");
        $this->dic["ilCtrl"]->shouldReceive("getLinkTargetByClass")->andReturn("");

        $this->dic["upload"] = Mockery::mock("\ILIAS\FileUpload\FileUpload");
        $this->dic["tree"] = Mockery::mock("\ilTree");
        $this->dic["tree"]->shouldReceive("getNodeData")->andReturn(["ref_id" => "1",
                                                                     "title" => "mock root node",
                                                                     "type" => "crs"
        ]);
        $this->dic["ilPluginAdmin"] = Mockery::mock("\ilPluginAdmin");
        $this->dic["ilPluginAdmin"]->shouldReceive("getActivePluginsForSlot")->andReturn([]);

        (new \InitHttpServices())->init($this->dic);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAllNonAbstractComponentsShowcaseExamples()
    {
        global $DIC;
        $DIC = $this->dic;

        foreach ($this->getEntriesFromCrawler() as $entry) {
            if (!$entry->isAbstract()) {
                $this->assertGreaterThan(0, count($entry->getExamples()),
                    "Non abstract Component " . $entry->getNamesapce()
                    . " does not provide any example. Please provide at least one in " . $entry->getExamplesNamespace());
            }
        }
    }

    /**
     * @dataProvider provideExampleFullFunctionNamesAndPath
     */
    public function testAllExamplesRenderAString(string $example_function_name, string $example_path)
    {
        include_once $example_path;
        try {
            $this->assertIsString($example_function_name(), " Example $example_function_name does not render a string");
        } catch (\ILIAS\UI\NotImplementedException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @return Crawler\Entry\ComponentEntries
     * @throws Crawler\Exception\CrawlerException
     */
    protected function getEntriesFromCrawler()
    {
        $crawler = new Crawler\FactoriesCrawler();
        return $crawler->crawlFactory($this->path_to_base_factory);
    }

    public function provideExampleFullFunctionNamesAndPath()
    {
        $function_names = [];
        foreach ($this->getEntriesFromCrawler() as $entry) {
            foreach ($entry->getExamples() as $name => $example_path) {

                $function_names[$entry->getExamplesNamespace() . "\\" . $name] = [$entry->getExamplesNamespace() . "\\" . $name,
                                                                                  $example_path
                ];
            }
        }
        return $function_names;
    }
}
