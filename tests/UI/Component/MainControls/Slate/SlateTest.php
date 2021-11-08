<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Slate;
use ILIAS\UI\Component\Signal;

/**
 * A generic Slate
 */
class TestGenericSlate extends Slate implements C\MainControls\Slate\Slate
{
    public function getContents() : array
    {
        return [];
    }
    public function withMappedSubNodes(callable $f) : C\MainControls\Slate\Slate
    {
        return $this;
    }
}

/**
 * Tests for the Slate.
 */
class SlateTest extends ILIAS_UI_TestBase
{
    protected I\SignalGenerator $sig_gen;
    protected I\Button\Factory $button_factory;
    protected I\Symbol\Icon\Factory $icon_factory;

    public function setUp() : void
    {
        $this->sig_gen = new I\SignalGenerator();
        $this->button_factory = new I\Button\Factory();
        $this->icon_factory = new I\Symbol\Icon\Factory();
    }

    public function testConstruction() : TestGenericSlate
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
        return $slate;
    }

    /**
     * @depends testConstruction
     */
    public function testWithEngaged(Slate $slate) : void
    {
        $slate = $slate->withEngaged(true);
        $this->assertTrue($slate->getEngaged());
    }

    /**
     * @depends testConstruction
     */
    public function testWithAriaRole(Slate $slate) : void
    {
        try {
            $slate = $slate->withAriaRole(Slate::MENU);
            $this->assertEquals("menu", $slate->getAriaRole());
        } catch (InvalidArgumentException $e) {
            $this->assertFalse("This should not happen");
        }
    }

    /**
     * @depends testConstruction
     */
    public function testWithAriaRoleIncorrect(Slate $slate) : void
    {
        try {
            $slate->withAriaRole("loremipsum");
            $this->assertFalse("This should not happen");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * @depends testConstruction
     */
    public function testSignals(Slate $slate) : array
    {
        $signals = [
            $slate->getToggleSignal(),
            $slate->getEngageSignal(),
            $slate->getReplaceSignal()
        ];
        foreach ($signals as $signal) {
            $this->assertInstanceOf(Signal::class, $signal);
        }
        return $signals;
    }

    /**
     * @depends testSignals
     */
    public function testDifferentSignals(array $signals) : void
    {
        $this->assertEquals(
            $signals,
            array_unique($signals)
        );
    }
}
