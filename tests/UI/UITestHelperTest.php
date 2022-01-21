<?php declare(strict_types=1);

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/UITestHelper.php");

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class UITestHelperTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf("UITestHelper", new UITestHelper());
    }

    public function testGetFactory() : void
    {
        $this->assertInstanceOf(Factory::class, (new UITestHelper())->factory());
    }

    public function testGetRenderer() : void
    {
        $this->assertInstanceOf(Renderer::class, (new UITestHelper())->renderer());
    }

    public function testGetMainTemplate() : void
    {
        $this->assertInstanceOf(ilIndependentGlobalTemplate::class, (new UITestHelper())->mainTemplate());
    }

    public function testRenderExample() : void
    {
        $helper = new UITestHelper();
        $c = $helper->factory()->legacy("hello world");
        $this->assertEquals("hello world", $helper->renderer()->render($c));
    }
}