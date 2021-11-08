<?php

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Implementation as I;

/**
 * Test on minimize button implementation.
 */
class MinimizeButtonTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->button_factory = new I\Component\Button\Factory();
    }

    public function test_implements_factory_interface()
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Minimize",
            $this->button_factory->minimize()
        );
    }
}
