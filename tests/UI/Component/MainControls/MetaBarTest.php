<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Meta Bar.
 */
class MetaBarTest extends ILIAS_UI_TestBase
{
    public function setUp() : void
    {
        $sig_gen = 	new I\Component\SignalGenerator();
        $this->button_factory = new I\Component\Button\Factory($sig_gen);
        $this->icon_factory = new I\Component\Symbol\Icon\Factory();
        $this->counter_factory = new I\Component\Counter\Factory();
        $slate_factory = new I\Component\MainControls\Slate\Factory($sig_gen, $this->counter_factory );
        $this->factory = new I\Component\MainControls\Factory($sig_gen, $slate_factory);
        $this->metabar = $this->factory->metabar();
    }

    public function testConstruction()
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\MainControls\\MetaBar",
            $this->metabar
        );
    }

    protected function getButton()
    {
        $symbol = $this->icon_factory->custom('', '');
        return $this->button_factory->bulky($symbol, 'TestEntry', '#');
    }

    protected function getSlate()
    {
        $mock = $this->getMockBuilder(Legacy::class)
            ->disableOriginalConstructor()
            ->setMethods(["transformToLegacyComponent"])
            ->getMock();

        $mock->method('transformToLegacyComponent')->willReturn('content');
        return $mock;
    }

    public function testAddEntry()
    {
        $button = $this->getButton();
        $slate = $this->getSlate();
        $mb = $this->metabar
            ->withAdditionalEntry('button', $button)
            ->withAdditionalEntry('slate', $slate);
        $entries = $mb->getEntries();
        $this->assertEquals($button, $entries['button']);
        $this->assertEquals($slate, $entries['slate']);
    }

    public function testDisallowedEntry()
    {
        $this->expectException(\InvalidArgumentException::class);
        $mb = $this->metabar->withAdditionalEntry('test', 'wrong_param');
    }

    public function testSignalsPresent()
    {
        $this->assertInstanceOf(Signal::class, $this->metabar->getEntryClickSignal());
    }

    public function getUIFactory()
    {
        $factory = new class extends NoUIFactory {
            public function button()
            {
                return $this->button_factory;
            }
            public function mainControls() : C\MainControls\Factory
            {
                return $this->mc_factory;
            }
            public function symbol() : C\Symbol\Factory
            {
                return new I\Component\Symbol\Factory(
                    new I\Component\Symbol\Icon\Factory(),
                    new I\Component\Symbol\Glyph\Factory()
                );
            }
            public function counter() : C\Counter\Factory
            {
                return $this->counter_factory;
            }
        };
        $factory->button_factory = $this->button_factory;
        $factory->mc_factory = $this->factory;
        $factory->counter_factory = $this->counter_factory;
        return $factory;
    }

    public function testRendering()
    {
        $r = $this->getDefaultRenderer();

        $button = $this->getButton();
        $slate = $this->getSlate();
        $mb = $this->metabar
            ->withAdditionalEntry('button', $button)
            ->withAdditionalEntry('button2', $button);

        $html = $r->render($mb);

        $expected = <<<EOT
		<div class="il-maincontrols-metabar" id="id_5">
			<div class="il-metabar-entries">
				<button class="btn btn-bulky" data-action="#" id="id_1" >
					<div class="icon custom small" aria-label=""><img src="" /></div>
					<div><span class="bulky-label">TestEntry</span></div>
				</button>
				<button class="btn btn-bulky" data-action="#" id="id_2" >
					<div class="icon custom small" aria-label=""><img src="" /></div>
					<div><span class="bulky-label">TestEntry</span></div>
				</button>
				<button class="btn btn-bulky" id="id_3" aria-pressed="false" >
				    <span class="glyph" aria-label="more">
				        <span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span>
				        <span class="badge badge-notify il-counter-status">0</span>
				        <span class="badge badge-notify il-counter-novelty">0</span>
				    </span>
				    <div><span class="bulky-label">more</span></div>
				</button>
			</div>
			<div class="il-metabar-slates">
				<div class="il-maincontrols-slate disengaged" id="id_4">
					<div class="il-maincontrols-slate-content" data-replace-marker="content"></div>
				</div>
			</div>
		</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected),
            $this->brutallyTrimHTML($html)
        );
    }
}
