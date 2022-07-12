<?php declare(strict_types=1);

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
 
require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../Base.php");

use ILIAS\UI\Implementation\Crawler as Crawler;
use ILIAS\DI\Container;
use ILIAS\UI\NotImplementedException;
use ILIAS\FileUpload\FileUpload;

/**
 * Class ExamplesTest Checks if all examples are implemented and properly returning strings
 */
class ExamplesTest extends ILIAS_UI_TestBase
{
    protected string $path_to_base_factory = "src/UI/Factory.php";
    protected Container $dic;

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
        $this->dic["tpl"] = $this->getTemplateFactory()->getTemplate("tpl.main.html", false, false);
        $this->dic["lng"] = $this->getLanguage();
        $this->dic["refinery"] = $this->getRefinery();
        (new InitUIFramework())->init($this->dic);

        $this->dic["ui.template_factory"] = $this->getTemplateFactory();

        $this->dic["ilCtrl"] = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->onlyMethods([
            "getFormActionByClass","setParameterByClass","saveParameterByClass","getLinkTargetByClass"
        ])->getMock();
        $this->dic["ilCtrl"]->method("getFormActionByClass")->willReturn("Testing");
        $this->dic["ilCtrl"]->method("getLinkTargetByClass")->willReturn("2");

        $this->dic["upload"] = $this->getMockBuilder(FileUpload::class)->getMock();

        $this->dic["tree"] = $this->getMockBuilder(ilTree::class)
                                  ->disableOriginalConstructor()
                                  ->onlyMethods(["getNodeData"])->getMock();

        $this->dic["tree"]->method("getNodeData")->willReturn([
            "ref_id" => "1",
            "title" => "mock root node",
            "type" => "crs"
        ]);

        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getActivePluginsInSlot")->willReturn(new ArrayIterator());
        $this->dic["component.factory"] = $component_factory;

        (new InitHttpServices())->init($this->dic);
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    public function testAllNonAbstractComponentsShowcaseExamples() : void
    {
        global $DIC;
        $DIC = $this->dic;

        foreach ($this->getEntriesFromCrawler() as $entry) {
            if (!$entry->isAbstract()) {
                $this->assertGreaterThan(
                    0,
                    count($entry->getExamples()),
                    "Non abstract Component " . $entry->getNamespace()
                    . " does not provide any example. Please provide at least one in " . $entry->getExamplesNamespace() . " at " . $entry->getExamplesPath()
                );
            }
        }
    }

    /**
     * @dataProvider provideExampleFullFunctionNamesAndPath
     */
    public function testAllExamplesRenderAString(string $example_function_name, string $example_path) : void
    {
        global $DIC;
        $DIC = $this->dic;

        include_once $example_path;
        try {
            $this->assertIsString($example_function_name(), " Example $example_function_name does not render a string");
        } catch (NotImplementedException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @throws Crawler\Exception\CrawlerException
     */
    protected function getEntriesFromCrawler() : Crawler\Entry\ComponentEntries
    {
        $crawler = new Crawler\FactoriesCrawler();
        return $crawler->crawlFactory($this->path_to_base_factory);
    }

    public function provideExampleFullFunctionNamesAndPath() : array
    {
        $function_names = [];
        foreach ($this->getEntriesFromCrawler() as $entry) {
            foreach ($entry->getExamples() as $name => $example_path) {
                $function_names[$entry->getExamplesNamespace() . "\\" . $name] = [
                    $entry->getExamplesNamespace() . "\\" . $name,
                    $example_path
                ];
            }
        }
        return $function_names;
    }
}
