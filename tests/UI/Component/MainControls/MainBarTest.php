<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Main Bar.
 */
class MainBarTest extends ILIAS_UI_TestBase
{
	public function setUp(): void
	{
		$sig_gen = 	new I\SignalGenerator();
		$this->button_factory = new I\Button\Factory($sig_gen);
		$this->icon_factory = new I\Symbol\Icon\Factory();
		$counter_factory = new I\Counter\Factory();
		$slate_factory = new I\MainControls\Slate\Factory($sig_gen, $counter_factory);
		$this->factory = new I\MainControls\Factory($sig_gen, $slate_factory);

		$this->mainbar = $this->factory->mainBar();
	}

	public function testConstruction()
	{
		$this->assertInstanceOf(
			"ILIAS\\UI\\Component\\MainControls\\MainBar",
			$this->mainbar
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
		$btn = $this->getButton();
		$mb = $this->mainbar->withAdditionalEntry('test', $btn);
		$entries = $mb->getEntries();
		$this->assertEquals($btn, $entries['test']);
		return $mb;
	}

	public function testDisallowedEntry()
	{
		$this->expectException(\InvalidArgumentException::class);
		$mb = $this->mainbar->withAdditionalEntry('test', 'wrong_param');
	}

	public function testDouplicateIdEntry()
	{
		$this->expectException(\InvalidArgumentException::class);
		$btn = $this->getButton();
		$mb = $this->mainbar
			->withAdditionalEntry('test', $btn)
			->withAdditionalEntry('test', $btn);
	}

	public function testDisallowedToolEntry()
	{
		$this->expectException(\InvalidArgumentException::class);
		$mb = $this->mainbar->withAdditionalToolEntry('test', 'wrong_param');
	}

	public function testAddToolEntryWithoutToolsButton()
	{
		$this->expectException(\LogicException::class);
		$mb = $this->mainbar->withAdditionalToolEntry('test', $this->getSlate());
	}

	public function testAddToolEntry()
	{
		$slate = $this->getSlate();
		$mb = $this->mainbar
			->withToolsButton($this->getButton())
			->withAdditionalToolEntry('test', $slate);
		$entries = $mb->getToolEntries();
		$this->assertEquals($slate, $entries['test']);
	}

	public function testDouplicateIdToolEntry()
	{
		$this->expectException(\InvalidArgumentException::class);
		$btn = $this->getButton();
		$slate = $this->getSlate();
		$mb = $this->mainbar->withToolsButton($btn)
			->withAdditionalToolEntry('test', $slate)
			->withAdditionalToolEntry('test', $slate);
	}

	/**
	 * @depends testAddEntry
	 */
	public function testActive($mb)
	{
		$mb = $mb->withActive('test');
		$this->assertEquals('test', $mb->getActive());
	}

	public function testWithInvalidActive()
	{
		$this->expectException(\InvalidArgumentException::class);
		$mb = $this->mainbar
			->withActive('this-is-not-a-valid-entry');
	}

	public function testSignalsPresent()
	{
		$this->assertInstanceOf(Signal::class, $this->mainbar->getEntryClickSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getToolsClickSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getToolsRemovalSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getDisengageAllSignal());
	}

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function button() {
				return $this->button_factory;
			}
			public function symbol(): C\Symbol\Factory
			{
				$f_icon = new I\Symbol\Icon\Factory();
				$f_glyph = new I\Symbol\Glyph\Factory();
				return new I\Symbol\Factory($f_icon, $f_glyph);
			}
			public function mainControls(): C\MainControls\Factory
			{
				$sig_gen = new I\SignalGenerator();
				$counter_factory = new I\Counter\Factory();
				$slate_factory = new I\MainControls\Slate\Factory($sig_gen, $counter_factory);
				return new I\MainControls\Factory($sig_gen, $slate_factory);
			}
			public function legacy($legacy)
			{
				return new I\Legacy\Legacy($legacy);
			}

		};
		$factory->button_factory = $this->button_factory;
		return $factory;
	}

	public function brutallyTrimHTML($html)
	{
		$html = str_replace(["\n", "\r", "\t"], "", $html);
		$html = preg_replace('# {2,}#', " ", $html);
		return trim($html);
	}

	public function testRendering()
	{
		$r = $this->getDefaultRenderer();
		$icon = $this->icon_factory->custom('', '');
		$mb = $this->factory->mainBar()
			->withMoreButton(
				$this->button_factory->bulky($icon, 'more', '')
			)
			->withAdditionalEntry('test1', $this->getButton())
			->withAdditionalEntry('test2', $this->getButton());

		$html = $r->render($mb);

		$expected = <<<EOT
		<div class="il-maincontrols-mainbar" id="id_6">
			<div class="il-mainbar">
				<div class="il-mainbar-triggers">
					<div class="il-mainbar-entries">
						<button class="btn btn-bulky" data-action="#" id="id_2" >
							<div class="icon custom small" aria-label="">
								<img src="" />
							</div>
							<div>
								<span class="bulky-label">TestEntry</span>
							</div>
						</button>

						<button class="btn btn-bulky" data-action="#" id="id_3" >
							<div class="icon custom small" aria-label="">
								<img src="" />
							</div>
							<div>
								<span class="bulky-label">TestEntry</span>
							</div>
						</button>

						<button class="btn btn-bulky" id="id_4" aria-pressed="false" >
							<div class="icon custom small" aria-label="">
								<img src="" />
							</div>
							<div>
								<span class="bulky-label">more</span>
							</div>
						</button>
					</div>
				</div>
			</div>

			<div class="il-mainbar-slates">
				<div class="il-mainbar-tools-entries"></div>
				<div class="il-maincontrols-slate disengaged" id="id_5">
					<div class="il-maincontrols-slate-content" data-replace-marker="content"></div>
				</div>

				<div class="il-mainbar-close-slates">
					<button class="btn btn-bulky" id="id_1" >
						<span class="glyph" href="#" aria-label="back">
							<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
						</span>
						<div>
							<span class="bulky-label">close</span>
						</div>
					</button>
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
