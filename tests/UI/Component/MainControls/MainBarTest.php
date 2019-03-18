<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy;
use \ILIAS\UI\Component\Signal;

/**
 * Tests for the Main Bar.
 */
class MainBarTest extends ILIAS_UI_TestBase
{
	public function setUp()
	{
		$sig_gen = 	new I\Component\SignalGenerator();
		$this->factory = new I\Component\MainControls\Factory($sig_gen);
		$this->button_factory = new I\Component\Button\Factory($sig_gen);
		$this->icon_factory = new I\Component\Icon\Factory();

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


	public function testActive()
	{
		$mb = $this->mainbar
			->withActive('test');
		$this->assertEquals($mb->getActive(), 'test');
	}

	public function testSignalsPresent()
	{
		$this->assertInstanceOf(Signal::class, $this->mainbar->getEntryClickSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getToolsClickSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getToolsRemovalSignal());
		$this->assertInstanceOf(Signal::class, $this->mainbar->getDisengageAllSignal());
	}

}
