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

require_once("vendor/composer/vendor/autoload.php");

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Render\MathJaxConfig;

class InitUIFrameworkTest extends TestCase
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * Several dependencies need to be wired up before the UI Framework can be initialised.
     */
    protected function setUp(): void
    {
        $this->dic = new \ILIAS\DI\Container();

        $this->dic["lng"] = $this->createMock("\ilLanguage");
        $this->dic["tpl"] = $this->createMock("\ilGlobalTemplateInterface");
        $this->dic["refinery"] = $this->createMock("\ILIAS\Refinery\Factory");
        $this->dic["help.text_retriever"] = $this->createMock("ILIAS\UI\Help\TextRetriever\Echoing");
        $this->dic[MathjaxConfig::class] = $this->createMock(MathjaxConfig::class);
    }

    public function testUIFrameworkInitialization(): void
    {
        $this->assertInstanceOf("\ILIAS\DI\UIServices", $this->dic->ui());
        $this->assertFalse(isset($this->dic['ui.factory']));
        $this->assertFalse(isset($this->dic['ui.renderer']));
        (new \InitUIFramework())->init($this->dic);
        $this->assertTrue(isset($this->dic['ui.factory']));
        $this->assertTrue(isset($this->dic['ui.renderer']));
        $this->assertInstanceOf("\ILIAS\UI\Factory", $this->dic->ui()->factory());
        $this->assertInstanceOf("\ILIAS\UI\Renderer", $this->dic->ui()->renderer());
    }

    /**
     * This checks only by example that the factory is loaded and ready to work.
     * A complete check of the factory is performed in the Test cases of tests/UI
     */
    public function testByExampleThatFactoryIsLoaded(): void
    {
        (new \InitUIFramework())->init($this->dic);

        $this->assertInstanceOf(
            "ILIAS\UI\Implementation\Component\Divider\Vertical",
            $this->dic->ui()->factory()->divider()->vertical()
        );
    }

    /**
     * This checks only by example that the renderer is all up and ready to work.
     * A complete set of the rendering tests is performed in the Test cases of tests/UI
     * Note that some additional dependencies are needed for this to run.
     */
    public function testByExampleThatRendererIsReadyToWork(): void
    {
        (new \InitUIFramework())->init($this->dic);
        $this->dic["tpl"]->expects($this->atLeastOnce())->method("addJavaScript");

        //Note, this dep is not properly injected ilTemplate, therefore we need to hit on the global.
        global $DIC;
        $initial_state = $DIC;
        $DIC = new \ILIAS\DI\Container();
        $DIC["component.factory"] = $this->createMock(ilComponentFactory::class);

        $example_componanent = $this->dic->ui()->factory()->divider()->vertical();
        $example_out = $this->dic->ui()->renderer()->render($example_componanent);
        $this->assertIsString($example_out);
        $DIC = $initial_state;
    }
}
