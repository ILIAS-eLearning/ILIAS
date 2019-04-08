<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Meta Bar.
 */
class MetaBarTest extends ILIAS_UI_TestBase
{
	public function setUp(): void
	{
		$sig_gen = 	new I\Component\SignalGenerator();
		$this->button_factory = new I\Component\Button\Factory($sig_gen);
		$this->icon_factory = new I\Component\Icon\Factory();
		$counter_factory = new I\Component\Counter\Factory();
		$slate_factory = new I\Component\MainControls\Slate\Factory($sig_gen, $counter_factory);
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
		return $this->button_factory->bulky($symbol,'TestEntry', '#');
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

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function button() {
				return $this->button_factory;
			}
			public function glyph() {
				return new I\Glyph\Factory();
			}
			public function mainControls(): C\MainControls\Factory
			{
				$sig_gen = new I\SignalGenerator();
				return new I\MainControls\Factory($sig_gen);
			}
		};
		$factory->button_factory = $this->button_factory;
		return $factory;
	}

	public function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
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
		<div class="il-maincontrols-metabar" id="id_3">
			<div class="il-metabar-entries">
				<div class="il-metabar-entry">
					<button class="btn btn-bulky" data-action="#" id="id_1" >
						<div class="icon custom small" aria-label="">
							<img src="" />
						</div>
						<div>
							<span class="bulky-label">TestEntry</span>
						</div>
					</button>
					<div class="il-metabar-slates">
					</div>
				</div>

				<div class="il-metabar-entry">
					<button class="btn btn-bulky" data-action="#" id="id_2" >
						<div class="icon custom small" aria-label="">
							<img src="" />
						</div>
						<div>
							<span class="bulky-label">TestEntry</span>
						</div>
					</button>
					<div class="il-metabar-slates">
					</div>
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
