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
