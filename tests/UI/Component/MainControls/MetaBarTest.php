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
	public function setUp()
	{
		$sig_gen = 	new I\Component\SignalGenerator();
		$this->factory = new I\Component\MainControls\Factory($sig_gen);
		$this->button_factory = new I\Component\Button\Factory($sig_gen);
		$this->icon_factory = new I\Component\Icon\Factory();
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

}
