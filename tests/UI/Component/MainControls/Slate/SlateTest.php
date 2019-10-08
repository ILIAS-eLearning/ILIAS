<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Slate;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Combined;
use \ILIAS\UI\Component\Signal;

/**
 * A generic Slate
 */
class TestGenericSlate extends Slate implements C\MainControls\Slate\Slate
{
    public function getContents() : array
    {
        return [];
    }
}

/**
 * Tests for the Slate.
 */
class SlateTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $this->sig_gen = new I\SignalGenerator();
        $this->button_factory = new I\Button\Factory($this->sig_gen);
        $this->icon_factory = new I\Symbol\Icon\Factory();
    }

    public function testConstruction()
    {
        $name = 'name';
        $icon = $this->icon_factory->custom('', '');
        $slate = new TestGenericSlate($this->sig_gen, $name, $icon);

        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\Slate\\Slate",
            $slate
        );
        $this->assertEquals($name, $slate->getName());
        $this->assertEquals($icon, $slate->getSymbol());
        $this->assertFalse($slate->getEngaged());
        $this->assertInstanceOf(Signal::class, $slate->getShowSignal());
        $this->assertInstanceOf(Signal::class, $slate->getToggleSignal());
        return $slate;
    }

    /**
     * @depends testConstruction
     */
    public function testWithEngaged(Slate $slate)
    {
        $slate = $slate->withEngaged(true);
        $this->assertTrue($slate->getEngaged());
    }
}
